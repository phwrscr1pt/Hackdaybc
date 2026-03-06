@echo off
REM ============================================================================
REM Remote Reset Script for Windows - Run from local machine
REM Executes reset-labs.sh on the lab server via SSH jump host
REM ============================================================================

echo ======================================================
echo        Remote Lab Reset - via Jump Host
echo ======================================================
echo.

set JUMP_HOST=root-agent@100.107.182.15
set LAB_SERVER=loc@10.10.61.221
set REMOTE_SCRIPT=/home/loc/HackdayBc/scripts/reset-labs.sh

echo Connecting to %LAB_SERVER% via %JUMP_HOST%...
echo.

REM Execute the reset script on the remote server
ssh -J %JUMP_HOST% %LAB_SERVER% "bash %REMOTE_SCRIPT% %*"

echo.
echo Done! Labs are ready for students.
pause
