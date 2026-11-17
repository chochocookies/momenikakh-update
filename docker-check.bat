@echo off
REM Docker Environment Check Script for Windows
REM Verifies Docker daemon is running and required ports are available

setlocal enabledelayedexpansion

echo Checking Docker environment prerequisites...
echo.

REM Check if Docker is installed
where docker >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Docker is not installed.
    echo Please install Docker Desktop from https://www.docker.com/products/docker-desktop
    exit /b 1
)

REM Check if Docker daemon is running
docker info >nul 2>nul
if %errorlevel% neq 0 (
    echo [ERROR] Docker daemon is not running.
    echo Please start Docker Desktop and try again.
    exit /b 1
)

echo [OK] Docker daemon is running

REM Check if docker-compose is available
docker compose version >nul 2>nul
if %errorlevel% neq 0 (
    docker-compose --version >nul 2>nul
    if !errorlevel! neq 0 (
        echo [ERROR] docker-compose is not available.
        echo Please ensure Docker Compose is installed.
        exit /b 1
    )
)

echo [OK] Docker Compose is available

REM Check for port conflicts
set PORT_CONFLICT=0

REM Check MySQL port 3306
netstat -ano | findstr ":3306.*LISTENING" >nul 2>nul
if %errorlevel% equ 0 (
    echo [ERROR] Port 3306 is already in use
    echo         ^(MySQL - Check if you have MySQL running locally^)
    set PORT_CONFLICT=1
)

REM Check Redis port 6379
netstat -ano | findstr ":6379.*LISTENING" >nul 2>nul
if %errorlevel% equ 0 (
    echo [ERROR] Port 6379 is already in use
    echo         ^(Redis - Check if you have Redis running locally^)
    set PORT_CONFLICT=1
)

if !PORT_CONFLICT! equ 1 (
    echo.
    echo Solutions:
    echo   1. Stop the services using these ports
    echo   2. Modify docker-compose.yml to use different ports
    echo.
    exit /b 1
)

echo [OK] All required ports ^(3306, 6379^) are available
echo.
echo [SUCCESS] All checks passed! You can start Docker services with:
echo    docker-compose up -d
echo.

exit /b 0
