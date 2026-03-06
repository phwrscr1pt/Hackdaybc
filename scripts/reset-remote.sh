#!/bin/bash
#===============================================================================
# Remote Reset Script - Run from local machine
# Executes reset-labs.sh on the lab server via SSH jump host
#===============================================================================

# Configuration
JUMP_HOST="root-agent@100.107.182.15"
LAB_SERVER="loc@10.10.61.221"
REMOTE_SCRIPT="/home/loc/HackdayBc/scripts/reset-labs.sh"

echo "╔═══════════════════════════════════════════════════════════╗"
echo "║       Remote Lab Reset - via Jump Host                    ║"
echo "╚═══════════════════════════════════════════════════════════╝"
echo ""
echo "Connecting to $LAB_SERVER via $JUMP_HOST..."
echo ""

# Execute the reset script on the remote server
ssh -J "$JUMP_HOST" "$LAB_SERVER" "bash $REMOTE_SCRIPT $*"

echo ""
echo "Done! Labs are ready for students."
