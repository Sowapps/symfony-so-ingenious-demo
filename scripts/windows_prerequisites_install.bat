:: Install necessary prerequisites on Windows

:: BATCH file is hard to maintain, we want no complexity in this script (no argument, no special behavior)

@echo off
setlocal

:: Request administrative privileges
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo Checking for administrative privileges...
    powershell start-process -verb runas -filepath "%~f0"
    exit /b
)

:: Install Chocolatey if not installed
where choco >nul 2>&1
if %errorlevel% neq 0 (
    echo Installing Chocolatey...
    :: Download and install Chocolatey
    powershell -NoProfile -InputFormat None -ExecutionPolicy Bypass -Command "[System.Net.ServicePointManager]::SecurityProtocol = 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))" && SET "PATH=%PATH%;%ALLUSERSPROFILE%\chocolatey\bin"
) else (
    echo Chocolatey is already installed.
)

:: Install or Upgrade all chocolatey dependencies
choco upgrade -y -vvv chocolatey composer symfony-cli php
:: Removed git because for now git is required to get project

pause

endlocal
