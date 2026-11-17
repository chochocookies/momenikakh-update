@echo off
setlocal enabledelayedexpansion

:: Database Backup Script for Momenikah (Windows)
:: Usage: backup-db.bat [--manifest-only] [--disk=s3]

set SCRIPT_DIR=%~dp0
set PROJECT_DIR=%SCRIPT_DIR%..

:: Configuration
set BACKUP_DIR=%PROJECT_DIR%\storage\app\backups
set LOCK_FILE=%TEMP%\momenikah-db-backup.lock
set DISK=%BACKUP_DISK:-=s3%
set MANIFEST_ONLY=%BACKUP_MANIFEST_ONLY:-=false%

:: Load environment variables
if exist "%PROJECT_DIR%\.env" (
    for /f "tokens=*" %%a in ("%PROJECT_DIR%\.env") do (
        set "line=%%a"
        if not "!line:~0,1!"=="#" if not "!line!"=="" (
            for /f "tokens=1,2 delims==" %%b in ("!line!") do (
                set "%%b=%%c"
            )
        )
    )
)

:: Create backup directory
if not exist "%BACKUP_DIR%" (
    mkdir "%BACKUP_DIR%"
)

:: Generate timestamp
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
set timestamp=%dt:~0,4%%dt:~4,2%%dt:~6,2%_%dt:~8,2%%dt:~10,2%%dt:~12,2%
set backup_name=backup_%timestamp%
set backup_path=%BACKUP_DIR%\%backup_name%

mkdir "%backup_path%"

:: Get database credentials from environment
set DB_HOST=%DB_HOST:-=127.0.0.1%
set DB_PORT=%DB_PORT:-=3306%
set DB_DATABASE=%DB_DATABASE:-=momenikah%
set DB_USERNAME=%DB_USERNAME:-=momenikah%
set DB_PASSWORD=%DB_PASSWORD:-=secret%

echo [INFO] Starting database backup at %timestamp%

:: Create database dump
set DUMP_FILE=%backup_path%\backup.sql
echo [INFO] Creating database dump...

:: Build mysqldump command (requires mysqldump in PATH)
set MYSQLDUMP_CMD=mysqldump --host=%DB_HOST% --port=%DB_PORT% --user=%DB_USERNAME%

if not "%DB_PASSWORD%"=="" (
    set MYSQLDUMP_CMD=%MYSQLDUMP_CMD% --password=%DB_PASSWORD%
)

set MYSQLDUMP_CMD=%MYSQLDUMP_CMD% --single-transaction --quick --routines --triggers %DB_DATABASE%

:: Execute mysqldump
%MYSQLDUMP_CMD% > "%DUMP_FILE%" 2>&1
if errorlevel 1 (
    echo [ERROR] mysqldump failed
    type "%DUMP_FILE%"
    exit /b 1
)

echo [INFO] Database dump created: %DUMP_FILE%

:: Generate manifest with row counts
set MANIFEST_FILE=%backup_path%\manifest.json
echo [INFO] Generating manifest with row counts...

:: Build JSON manifest (simplified for Windows batch)
echo { > "%MANIFEST_FILE%"
echo   "timestamp_utc": "%dt:~0,4%-%dt:~4,2%-%dt:~6,2%T%dt:~8,2%:%dt:~10,2%:%dt:~12,2%Z", >> "%MANIFEST_FILE%"
echo   "backup_name": "%backup_name%", >> "%MANIFEST_FILE%"
echo   "database": "%DB_DATABASE%", >> "%MANIFEST_FILE%"
echo   "tables": { >> "%MANIFEST_FILE%"

:: Note: Windows batch doesn't support complex JSON generation
:: Use PowerShell for manifest generation
powershell -Command ^
    "$manifest = @{ timestamp_utc = Get-Date -Format 'yyyy-MM-ddTHH:mm:ssZ'; backup_name = '%backup_name%'; database = '%DB_DATABASE%'; tables = @{} }; ^
    $tables = & 'mysql' -N -B -h '%DB_HOST%' -P '%DB_PORT%' -u '%DB_USERNAME%' -p'%DB_PASSWORD%' -e 'SELECT table_name FROM information_schema.tables WHERE table_schema = '\''%DB_DATABASE%'\'' AND table_type = '\''BASE TABLE'\'';' 2^>^&1; ^
    foreach ($table in $tables) { if ($table) { $count = & 'mysql' -N -B -h '%DB_HOST%' -P '%DB_PORT%' -u '%DB_USERNAME%' -p'%DB_PASSWORD%' -e 'SELECT COUNT(*) FROM \`%DB_DATABASE%\`.\`\$table\`;' 2^>^&1; $manifest.tables[$table] = $count } }; ^
    $manifest | ConvertTo-Json -Depth 10 | Out-File -FilePath '%MANIFEST_FILE%' -Encoding utf8"

echo [INFO] Manifest created: %MANIFEST_FILE%

:: If DISK is not s3, just keep locally
if "%DISK%" neq "s3" (
    echo [INFO] Backup stored locally at: %backup_path%
    echo [SUCCESS] Backup completed at %backup_path%
    exit /b 0
)

:: Upload to S3 (if DISK=s3)
if "%DISK%"=="s3" (
    echo [INFO] Uploading backup to S3...
    
    :: Check if aws cli is available
    where aws >nul 2>&1
    if errorlevel 1 (
        echo [ERROR] AWS CLI not found. Please install and configure AWS CLI.
        exit /b 1
    )
    
    :: Upload backup files to S3
    set S3_BUCKET=%AWS_BUCKET:-=momenikah-staging%
    set S3_REGION=%AWS_DEFAULT_REGION:-=ap-southeast-1%
    set S3_PATH=backups/%backup_name%
    
    echo [INFO] Uploading to s3://%S3_BUCKET%/%S3_PATH%/
    
    aws s3 cp "%DUMP_FILE%" "s3://%S3_BUCKET%/%S3_PATH%/backup.sql" --region "%S3_REGION%" --storage-class STANDARD_IA
    if errorlevel 1 (
        echo [ERROR] Failed to upload backup.sql to S3
        exit /b 1
    )
    
    aws s3 cp "%MANIFEST_FILE%" "s3://%S3_BUCKET%/%S3_PATH%/manifest.json" --region "%S3_REGION%" --storage-class STANDARD_IA
    if errorlevel 1 (
        echo [ERROR] Failed to upload manifest.json to S3
        exit /b 1
    )
    
    echo [SUCCESS] Backup uploaded to S3: s3://%S3_BUCKET%/%S3_PATH%/
)

endlocal
exit /b 0
