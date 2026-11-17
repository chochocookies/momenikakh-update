@echo off
REM Windows wrapper for deployment script
REM This file calls the bash deployment script
REM Requires Git Bash or similar bash shell

bash "%~dp0deploy.sh" %*
