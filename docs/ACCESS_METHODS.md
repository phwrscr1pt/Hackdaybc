# Lab Access Methods Guide

> How to access the Lab VM (10.10.61.221) from your machine

---

## Overview

Your machine cannot reach 10.10.61.221 directly because it's on a private network. You have three options to access it:

| Method | Difficulty | Access URL | Persistent | Best For |
|--------|------------|------------|------------|----------|
| **SSH Port Forward** | Easy | http://localhost:8080 | No (manual) | Quick testing |
| **SOCKS Proxy** | Medium | http://10.10.61.221 | No (manual) | Browser access |
| **Tailscale Subnet** | Medium | http://10.10.61.221 | Yes (auto) | Permanent access |

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
│   Your Machine      │      │    Jump Host        │      │     Lab VM          │
│                     │      │    (root-agent)     │      │                     │
│   Tailscale IP:     │ ───► │   100.107.182.15    │ ───► │   10.10.61.221      │
│   100.71.1.35       │      │                     │      │                     │
│                     │      │   Can reach:        │      │   Hosts:            │
│   Cannot reach:     │      │   - 10.10.61.0/24   │      │   - Web labs (:80)  │
│   - 10.10.61.x      │      │   - Tailscale       │      │   - SSH lab (:2222) │
└─────────────────────┘      └─────────────────────┘      └─────────────────────┘
```

---

## Option 1: SSH Local Port Forwarding (Easiest)

### How It Works
Creates a tunnel: `Your Machine:8080` → `Jump Host` → `Lab VM:80`

### Setup

**Run on YOUR machine:**
```bash
ssh -L 8080:10.10.61.221:80 -N root-agent@100.107.182.15
```

**Flags explained:**
- `-L 8080:10.10.61.221:80` - Forward local port 8080 to remote 10.10.61.221:80
- `-N` - Don't execute remote command (just forward)

**Keep this terminal open while using the labs.**

### Access URLs

| Lab | URL |
|-----|-----|
| Portal | http://localhost:8080/ |
| SQL Injection | http://localhost:8080/resources/ |
| JWT | http://localhost:8080/account/ |
| File Upload | http://localhost:8080/profile/ |
| CSRF Bank | http://localhost:8080/share/ |
| CSRF Evil | http://localhost:8080/evil/ |
| SSRF | http://localhost:8080/api/ |
| XSS | http://localhost:8080/search/ |

### For SSH Lab (Port 2222)

Open another terminal:
```bash
ssh -L 2222:10.10.61.221:2222 -N root-agent@100.107.182.15
```

Then connect:
```bash
ssh noob@localhost -p 2222
```

### Multiple Ports at Once

```bash
ssh -L 8080:10.10.61.221:80 -L 2222:10.10.61.221:2222 -N root-agent@100.107.182.15
```

### Pros & Cons

| Pros | Cons |
|------|------|
| Simple, one command | Must keep terminal open |
| No configuration needed | Access via localhost, not real IP |
| Works immediately | Need separate forward for each port |

---

## Option 2: SOCKS Proxy

### How It Works
Creates a SOCKS5 proxy through the jump host. Your browser routes ALL traffic through it.

### Setup

**Step 1: Start the proxy (on YOUR machine):**
```bash
ssh -D 1080 -N root-agent@100.107.182.15
```

**Flags explained:**
- `-D 1080` - Create SOCKS proxy on local port 1080
- `-N` - Don't execute remote command

**Keep this terminal open.**

**Step 2: Configure your browser:**

#### Firefox
1. Settings → General → Network Settings → Settings
2. Select "Manual proxy configuration"
3. SOCKS Host: `localhost`, Port: `1080`
4. Select "SOCKS v5"
5. Check "Proxy DNS when using SOCKS v5"
6. Click OK

#### Chrome (Windows)
1. Settings → System → Open proxy settings
2. LAN settings → Use a proxy server
3. Advanced → Socks: `localhost:1080`

Or use command line:
```bash
chrome.exe --proxy-server="socks5://localhost:1080"
```

#### Chrome Extension (Easier)
Install "SwitchyOmega" or "FoxyProxy" extension to easily toggle proxy.

### Access URLs

Now you can access the **real IP** directly:

| Lab | URL |
|-----|-----|
| Portal | http://10.10.61.221/ |
| SQL Injection | http://10.10.61.221/resources/ |
| JWT | http://10.10.61.221/account/ |
| File Upload | http://10.10.61.221/profile/ |
| CSRF Bank | http://10.10.61.221/share/ |
| CSRF Evil | http://10.10.61.221/evil/ |
| SSRF | http://10.10.61.221/api/ |
| XSS | http://10.10.61.221/search/ |
| SSH Lab | `ssh noob@10.10.61.221 -p 2222` (via proxychains) |

### Using SSH through SOCKS (Optional)

Install proxychains:
```bash
# Linux
sudo apt install proxychains4

# Configure /etc/proxychains4.conf:
# socks5 127.0.0.1 1080
```

Then:
```bash
proxychains4 ssh noob@10.10.61.221 -p 2222
```

### Pros & Cons

| Pros | Cons |
|------|------|
| Access real IP directly | Requires browser configuration |
| All ports accessible | Must keep terminal open |
| Works with any protocol | All browser traffic goes through proxy |

---

## Option 3: Tailscale Subnet Routing (Best for Permanent Access)

### How It Works
The jump host advertises the 10.10.61.0/24 subnet to Tailscale. Your machine can then reach it directly.

```
Your Machine ──Tailscale──► Jump Host ──routes──► 10.10.61.0/24
                           (subnet router)
```

### Setup

#### Step 1: Enable IP Forwarding (on Jump Host)

SSH to jump host:
```bash
ssh root-agent@100.107.182.15
```

Check if enabled:
```bash
cat /proc/sys/net/ipv4/ip_forward
# Should output: 1
```

If not enabled:
```bash
sudo sysctl -w net.ipv4.ip_forward=1
echo 'net.ipv4.ip_forward = 1' | sudo tee -a /etc/sysctl.conf
```

#### Step 2: Advertise Subnet (on Jump Host)

```bash
sudo tailscale set --advertise-routes=10.10.61.0/24
```

Verify:
```bash
tailscale status
# Should show the advertised route
```

#### Step 3: Approve Route (in Tailscale Admin)

1. Go to: https://login.tailscale.com/admin/machines
2. Find machine: **rootagent-standard-pc-i440fx-piix-1996**
3. Click **...** menu → **Edit route settings**
4. Toggle ON: `10.10.61.0/24`
5. Click **Save**

#### Step 4: Accept Routes (on YOUR Machine)

**Windows (PowerShell as Admin):**
```powershell
tailscale set --accept-routes
```

**Linux/Mac:**
```bash
sudo tailscale set --accept-routes
```

#### Step 5: Verify Connection

```bash
ping 10.10.61.221
curl http://10.10.61.221/
```

### Access URLs

Direct access to real IP - no proxy or tunnel needed:

| Lab | URL |
|-----|-----|
| Portal | http://10.10.61.221/ |
| SQL Injection | http://10.10.61.221/resources/ |
| JWT | http://10.10.61.221/account/ |
| File Upload | http://10.10.61.221/profile/ |
| CSRF Bank | http://10.10.61.221/share/ |
| CSRF Evil | http://10.10.61.221/evil/ |
| SSRF | http://10.10.61.221/api/ |
| XSS | http://10.10.61.221/search/ |
| SSH Lab | `ssh noob@10.10.61.221 -p 2222` |

### Pros & Cons

| Pros | Cons |
|------|------|
| Permanent, automatic | Requires Tailscale admin access |
| Access real IP directly | One-time setup required |
| Works for all protocols | Jump host must be online |
| No tunnels to maintain | |

---

## Quick Reference

### Start SSH Port Forward
```bash
ssh -L 8080:10.10.61.221:80 -L 2222:10.10.61.221:2222 -N root-agent@100.107.182.15
```

### Start SOCKS Proxy
```bash
ssh -D 1080 -N root-agent@100.107.182.15
```

### Setup Tailscale Subnet (one-time)
```bash
# On jump host
sudo tailscale set --advertise-routes=10.10.61.0/24

# On your machine
tailscale set --accept-routes
```

---

## Troubleshooting

### Port Forward Not Working
- Check if SSH connection is still active
- Try a different local port (e.g., 8888 instead of 8080)
- Check if local port is already in use: `netstat -an | grep 8080`

### SOCKS Proxy Not Working
- Verify proxy is running: `netstat -an | grep 1080`
- Check browser proxy settings
- Try `curl --socks5 localhost:1080 http://10.10.61.221/`

### Tailscale Subnet Not Working
- Check route is approved in admin console
- Verify `--accept-routes` is set on your machine
- Check jump host is online: `tailscale status`
- Test from jump host: `ssh root-agent@100.107.182.15 "curl http://10.10.61.221/"`

### SSH Lab Connection Refused
- Make sure you're forwarding port 2222, not just 80
- Check SSH lab container is running: `docker ps | grep ssh`

---

## Recommendation

| Scenario | Recommended Method |
|----------|-------------------|
| Quick one-time test | SSH Port Forward |
| Regular browser testing | SOCKS Proxy |
| Daily use / teaching | Tailscale Subnet Routing |
| Multiple students | Each student uses Port Forward |

---

*Created: 2026-03-06*
*For LeaguesOfCode Security Labs*
