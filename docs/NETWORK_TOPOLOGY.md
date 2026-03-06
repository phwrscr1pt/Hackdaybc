# Network Topology & SSH Access

> How Claude connects to the Lab VM through a jump host

---

## The Problem

The Lab VM (10.10.61.221) is on a private network that Claude's machine cannot reach directly. However, there's a bridge machine (root-agent) accessible via Tailscale that CAN reach the Lab VM.

---

## Network Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              INTERNET                                        │
└─────────────────────────────────────────────────────────────────────────────┘
                                    │
                                    │ Tailscale VPN
                                    ▼
┌─────────────────────┐      ┌─────────────────────┐      ┌─────────────────────┐
│   Claude's Machine  │      │    Jump Host        │      │     Lab VM          │
│   (Windows)         │ ───► │    (root-agent)     │ ───► │     (loc)           │
│                     │      │                     │      │                     │
│   Can reach:        │ SSH  │   IP: 100.107.182.15│ SSH  │   IP: 10.10.61.221  │
│   - Tailscale IPs   │      │   User: root-agent  │      │   User: loc         │
│   - Internet        │      │                     │      │   Pass: 123         │
│                     │      │   Can reach:        │      │                     │
│   Cannot reach:     │      │   - 10.10.61.x LAN  │      │   Hosts:            │
│   - 10.10.61.x LAN  │      │   - Internet        │      │   - Docker labs     │
│                     │      │   - Tailscale       │      │   - MySQL           │
└─────────────────────┘      └─────────────────────┘      └─────────────────────┘
         │                            │                            │
         │                            │                            │
         └────────────────────────────┴────────────────────────────┘
                              SSH Jump Connection
                         ssh -J root-agent@100.107.182.15 loc@10.10.61.221
```

---

## Connection Methods

### Method 1: Jump Host (-J flag) - Recommended

```bash
ssh -J root-agent@100.107.182.15 loc@10.10.61.221
```

This tells SSH to:
1. First connect to `root-agent@100.107.182.15` (jump host)
2. Then from there, connect to `loc@10.10.61.221` (lab VM)

### Method 2: ProxyJump in SSH Config

Add to `~/.ssh/config`:

```
Host lab-vm
    HostName 10.10.61.221
    User loc
    ProxyJump root-agent@100.107.182.15

Host jump
    HostName 100.107.182.15
    User root-agent
```

Then simply:
```bash
ssh lab-vm
```

### Method 3: Manual Two-Step

```bash
# Step 1: SSH to jump host
ssh root-agent@100.107.182.15

# Step 2: From jump host, SSH to lab VM
ssh loc@10.10.61.221
```

---

## Running Commands on Lab VM

### Single Command
```bash
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "docker ps"
```

### Multiple Commands
```bash
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "cd /home/loc/HackdayBc && docker-compose ps"
```

### Copy Files TO Lab VM
```bash
scp -J root-agent@100.107.182.15 localfile.txt loc@10.10.61.221:/home/loc/
```

### Copy Files FROM Lab VM
```bash
scp -J root-agent@100.107.182.15 loc@10.10.61.221:/home/loc/file.txt ./
```

---

## Machine Details

| Machine | IP | User | Auth | Purpose |
|---------|-----|------|------|---------|
| Jump Host | 100.107.182.15 (Tailscale) | root-agent | SSH Key | Bridge to private network |
| Lab VM | 10.10.61.221 (Private LAN) | loc | SSH Key / Password: 123 | Hosts Docker labs |

---

## Why This Setup?

```
┌────────────────────────────────────────────────────────────────┐
│                     Security Boundary                          │
│  ┌──────────────┐                                              │
│  │  Lab VM      │  Private network (10.10.61.0/24)             │
│  │  10.10.61.221│  - Not exposed to internet                   │
│  │              │  - Only accessible from local network        │
│  └──────────────┘                                              │
│         ▲                                                      │
│         │ Local network access                                 │
│  ┌──────────────┐                                              │
│  │  Jump Host   │  Has both:                                   │
│  │  root-agent  │  - Tailscale (100.107.182.15)               │
│  │              │  - Local network (can reach 10.10.61.x)      │
│  └──────────────┘                                              │
└────────────────────────────────────────────────────────────────┘
         ▲
         │ Tailscale VPN (encrypted tunnel)
         │
┌──────────────┐
│ Claude/User  │  Only has Tailscale access
│ Machine      │  Cannot directly reach 10.10.61.x
└──────────────┘
```

The jump host acts as a **bastion host** - a secure gateway that bridges the external Tailscale network with the internal private network where the lab VM lives.

---

## Quick Reference

```bash
# Check if lab VM is reachable (from jump host)
ssh root-agent@100.107.182.15 "ping -c 1 10.10.61.221"

# Check Docker status on lab VM
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "docker ps"

# Start all labs
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "cd /home/loc/HackdayBc && docker-compose up -d"

# View logs
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "cd /home/loc/HackdayBc && docker-compose logs -f"

# Create backup
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "cd /home/loc && tar -czvf HackdayBc-backup-\$(date +%Y%m%d).tar.gz HackdayBc/"
```

---

*Created: 2026-03-05*
*This documents how Claude connects to the lab infrastructure*
