#!/bin/bash

# Generate SSH host keys if they do not exist
if [ ! -f /etc/ssh/ssh_host_rsa_key ]; then
    ssh-keygen -t rsa -f /etc/ssh/ssh_host_rsa_key -N ""
fi

if [ ! -f /etc/ssh/ssh_host_ecdsa_key ]; then
    ssh-keygen -t ecdsa -f /etc/ssh/ssh_host_ecdsa_key -N ""
fi

if [ ! -f /etc/ssh/ssh_host_ed25519_key ]; then
    ssh-keygen -t ed25519 -f /etc/ssh/ssh_host_ed25519_key -N ""
fi

# Ensure john .ssh directory exists with vulnerable permissions
mkdir -p /home/john/.ssh
chown john:john /home/john/.ssh
chmod 777 /home/john/.ssh
# Make john's home traversable (but not listable) so noob can access .ssh
chmod 711 /home/john

# Display banner
echo "============================================="
echo "  SSH Lab - LeaguesOfCode Security Training"
echo "============================================="
echo "Users available:"
echo "  - noob (password: noob)"
echo "  - john (password auth DISABLED)"
echo ""
echo "Hint: Check directory permissions..."
echo "============================================="

# Start SSH daemon in foreground
exec /usr/sbin/sshd -D -e
