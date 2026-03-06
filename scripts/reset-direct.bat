@echo off
REM ============================================================================
REM Direct Reset Script for Windows (Tailscale Subnet Routing)
REM Use this if you have direct access to 10.10.61.221
REM ============================================================================

echo ======================================================
echo        Direct Lab Reset - Tailscale Access
echo ======================================================
echo.

set LAB_SERVER=loc@10.10.61.221
set REMOTE_SCRIPT=/home/loc/HackdayBc/scripts/reset-labs.sh

echo Connecting directly to %LAB_SERVER%...
echo.

REM Execute the reset script on the remote server
ssh %LAB_SERVER% "bash %REMOTE_SCRIPT% %*"

echo.
echo Done! Labs are ready for students.
pause
