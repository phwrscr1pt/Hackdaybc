FROM ubuntu:22.04

# Install SSH server
RUN apt-get update && apt-get install -y     openssh-server     && rm -rf /var/lib/apt/lists/*     && mkdir /var/run/sshd

# Create users
# noob - can login with password, will have write access to john .ssh
# john - password auth disabled, only key auth
RUN useradd -m -s /bin/bash noob &&     echo "noob:noob" | chpasswd &&     useradd -m -s /bin/bash john &&     echo "john:$(head /dev/urandom | tr -dc A-Za-z0-9 | head -c 32)" | chpasswd

# Setup john home directory with hint file
COPY hint.txt /home/john/hint.txt
RUN chown john:john /home/john/hint.txt &&     chmod 644 /home/john/hint.txt

# Create .ssh directory for john with permissive permissions
# This is the vulnerability - noob can write to john .ssh
# chmod 711 on /home/john allows traversal but not listing
RUN mkdir -p /home/john/.ssh && \
    chown john:john /home/john/.ssh && \
    chmod 777 /home/john/.ssh && \
    chmod 711 /home/john

# Copy SSH config
COPY sshd_config /etc/ssh/sshd_config

# Copy and setup entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Expose SSH port
EXPOSE 22

# Start via entrypoint
ENTRYPOINT ["/entrypoint.sh"]
