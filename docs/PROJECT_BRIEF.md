# LeaguesOfCode Lab Portal - Project Brief

Use this document to explain the project to Claude (browser) when asking for help or improvements.
 I have a security training website that looks like a real tech company portal.
  Here is my current index.php: [paste code]
  Please improve it by adding animated hero section.
  Keep the same lab URLs (/members/, /account/, /profile/, etc.)

---

## What Is This Project?

This is a **web security training platform** disguised as a real company website. It's used to teach students about web vulnerabilities in a safe, controlled environment.

**Key Point:** The website looks like a real tech company portal, but each feature contains an intentional security vulnerability for students to discover and exploit.

---

## Who Is This For?

- **Instructor:** Teaching cybersecurity bootcamp
- **Students:** Learning web application security
- **Purpose:** Hands-on practice with real vulnerabilities

---

## Technology Stack

| Component | Technology |
|-----------|------------|
| Reverse Proxy | Nginx |
| Main Portal | PHP 8.2 + Apache |
| Database | MySQL 8.0 |
| File Upload Lab | PHP + Nginx |
| CSRF Lab | Python Flask |
| SSRF Lab | Python Flask |
| XSS Lab | Node.js + Express |
| SSH Lab | Ubuntu 22.04 + OpenSSH |
| Deployment | Docker Compose |

---

## Architecture

```
http://10.10.61.221/
        │
        ▼
┌─────────────────────────────────────────┐
│            Nginx (Port 80)               │
│           Reverse Proxy                  │
└─────────────────────────────────────────┘
        │
        ├── /              → Landing Page (PHP)
        ├── /members/      → Member Directory (SQL Injection)
        ├── /resources/    → Resource Library (SQL Injection Labs)
        ├── /account/      → Account Services (JWT Labs)
        ├── /profile/      → File Upload Lab
        ├── /share/        → CSRF Lab (Bank)
        ├── /evil/         → CSRF Lab (Attacker)
        ├── /api/          → SSRF Lab
        └── /search/       → XSS Lab

┌─────────────────────────────────────────┐
│         SSH Lab (Port 2222)              │
│      Isolated Network (ssh_network)      │
└─────────────────────────────────────────┘
        │
        └── ssh noob@IP -p 2222 → SSH Key Auth Lab
```

---

## Labs and Vulnerabilities

### 0. SSH Key Authentication (Pre-Lab) - Port 2222
**Disguised as:** Developer SSH Server
**Vulnerability:** World-writable .ssh directory + StrictModes disabled
**Student Goal:** Gain access to user "john" and retrieve his developer notes
**Reward:** John's notes contain credentials, payloads, and hints for ALL web labs!
**Technique:**
1. SSH as noob (password: noob)
2. Notice /home/john/.ssh has 777 permissions
3. Generate SSH keypair and write public key to john's authorized_keys
4. SSH as john using the private key
5. Read hint.txt containing database creds, JWT secret, SQL payloads, and more

**Why Pre-Lab:** Teaches basic Linux skills (permissions, SSH keys) before web vulnerabilities. Completing this lab gives students a significant advantage on all other labs.

---

### 1. SQL Injection (/members/, /resources/)
**Disguised as:** Member Directory, Resource Library
**Vulnerability:** User input directly concatenated into SQL queries

**Sub-labs in /resources/:**
| Endpoint | Business Name | Technique |
|----------|---------------|-----------|
| login.php | Employee Login | Login bypass (`admin'-- -`) |
| directory.php | Member Directory | UNION injection |
| catalog.php | Book Catalog | Error-based (extractvalue) |
| verify.php | Partner Verification | Blind injection (SUBSTRING) |
| books.php | Library Books | SQLMap practice |

**Student Goal:** Extract data from `secret_data` table
**Key Secrets:** admin_pin: `1337`, api_key: `sk_live_abc123xyz789`

### 2. JWT Authentication (/account/)
**Disguised as:** Account Services, Sign In
**Vulnerability:** Algorithm tampering + Weak signing key

**Sub-labs in /account/:**
| Endpoint | Business Name | Technique |
|----------|---------------|-----------|
| signin.php | Account Login | Get JWT token |
| portal.php | User Dashboard | None algorithm bypass |
| secure.php | Secure Login | Weak key (hashcat) |
| admin.php | Secure Dashboard | Crack key, forge token |

**Student Goal:** Change role from "user" to "administrator"
**Key Secrets:** Server Key: `MASTER_KEY_2026`, Weak Key: `secret1`

### 3. File Upload (/profile/)
**Disguised as:** Profile Settings, Avatar Upload
**Vulnerability:** Only checks first 256 bytes for JPEG header
**Student Goal:** Upload PHP webshell disguised as image
**Technique:** Polyglot file (JPEG header + PHP code)

### 4. CSRF (/share/, /evil/)
**Disguised as:** Credit System / Banking, Partner Portal
**Vulnerability:** No CSRF token protection on transfer endpoint
**Student Goal:** Make victim transfer money without consent
**Technique:** Hidden form on attacker page auto-submits to bank

### 5. SSRF (/api/)
**Disguised as:** Developer API, URL Fetcher
**Vulnerability:** Server fetches user-provided URLs
**Student Goal:** Access internal endpoint at `http://127.0.0.1:7070/internal/config`
**Reward:** Returns database credentials and API keys

### 6. XSS (/search/)
**Disguised as:** Tech Blog, Search
**Vulnerability:** User input reflected without sanitization
**Student Goal:** Execute JavaScript in victim's browser
**Example Payload:** `<img src=x onerror=alert(document.cookie)>`

---

## Database Schema

**Database:** `leaguesofcode_db`
**User:** `locadmin`
**Password:** `locpass123`

### Tables:
- `users` - Login credentials
- `members` - Organization members
- `secret_data` - Sensitive data (target for extraction)
- `inventory` - Product inventory
- `partners` - Partner information
- `books` - Library books
- `accounts` - JWT user accounts

---

## Current Landing Page Design

The landing page looks like a real tech company website:
- **Brand:** LeaguesOfCode Thailand
- **Tagline:** "Code. Compete. Connect."
- **Theme:** Dark blue/navy with accent blue
- **Sections:** Hero, Services, Announcement, Partners, Footer
- **Style:** Modern, professional, Bootstrap 5
- **Partner Companies:** Fictional names (NEXGEN, CYBERTEK, DATAFLOW, CLOUDNINE, BYTECRAFT, SYNTHEX)

---

## File Structure

### GitHub Repository
```
https://github.com/phwrscr1pt/Hackdaybc.git
```

### Local Development (labs-source)
```
D:\LOC\HackdayBc\labs-source\    # Git repo - edit code here
├── docker-compose.yml           # Docker orchestration
├── nginx/nginx.conf             # Reverse proxy routes
├── portal/                      # Main PHP application
│   ├── www/
│   │   ├── index.php            # Landing page (v1.0.1)
│   │   ├── config.php           # Database config
│   │   ├── members/             # Member Directory (SQL Injection)
│   │   ├── resources/           # Resource Library (SQL Labs)
│   │   └── account/             # Account Services (JWT Labs)
│   └── db/init.sql              # Database schema
├── labs/                        # Other vulnerability labs
│   ├── hackday-afu/             # File upload
│   ├── csrf-lab/                # CSRF
│   ├── ssrf-lab/                # SSRF
│   ├── xss_lab3/                # XSS
│   └── ssh-lab/                 # SSH Key Auth (port 2222)
└── .git/                        # Git repository

D:\LOC\HackdayBc\docs\           # Documentation (separate)
```

### Deployment Workflow
```
1. Edit code in labs-source/
2. git add . && git commit -m "message" && git push
3. ssh server "cd /home/loc/HackdayBc && git pull"
```

---

## How to Ask Claude for Improvements

### To improve the landing page UI:
```
I have a security training website that looks like a real tech company portal.
Here is my current index.php: [paste code]
Please improve it by [describe what you want].
Keep the same lab URLs (/members/, /account/, /profile/, etc.)
```

### To add a new lab:
```
I have a security training platform with these existing labs:
- SSH Key Auth at port 2222 (pre-lab)
- SQL Injection at /members/ and /resources/
- JWT at /account/
- File Upload at /profile/
- CSRF at /share/
- SSRF at /api/
- XSS at /search/

I want to add a new [vulnerability type] lab.
It should be disguised as a normal business feature.
Please create the PHP/Python/Node.js code for this lab.
```

### To improve an existing lab:
```
I have a [vulnerability type] lab at [path].
Here is my current code: [paste code]
I want to [describe improvement].
Keep the vulnerability exploitable for students.
```

### To improve documentation:
```
I have a security training platform for students.
I need a student guide for the [lab name] lab.
It should give hints but not reveal the answer.
```

---

## Design Guidelines

When improving or adding to this project:

1. **Look realistic** - Website should look like a real company, not a "hacking lab"
2. **No security keywords** - Don't use words like "vulnerable", "injection", "exploit", "hack" in visible UI
3. **Business language** - Use normal feature names (Member Directory, Account Portal, etc.)
4. **Dark theme** - Navy/blue colors, modern design
5. **Bootstrap 5** - Use Bootstrap components and utilities
6. **Keep vulnerabilities working** - Changes should not fix the security holes

---

## Server Access (for context)

- **VM IP:** 10.10.61.221
- **OS:** Ubuntu 22.04
- **Docker:** Yes, using docker-compose
- **All services running in containers**

---

## What Has Been Completed

### March 2026
- [x] **SSH Key Authentication pre-lab** (2026-03-05) - Port 2222, teaches Linux permissions
- [x] **Refactored for realism** (2026-03-06) - Renamed /sqli/ → /resources/, /jwt/ → /account/
- [x] **Removed security keywords** (2026-03-06) - No "Lab X" badges, business-like naming
- [x] **Error-based SQL injection** (2026-03-06) - Using extractvalue() function
- [x] **Blind SQL injection** (2026-03-06) - Using SUBSTRING() for character extraction
- [x] **JWT Weak Key lab** (2026-03-06) - secret1 key, crackable with hashcat
- [x] **All walkthrough documents** (2026-03-06) - SQL/JWT, CSRF, SSRF, File Upload, XSS
- [x] **Backward compatibility** (2026-03-06) - Old URLs redirect to new paths
- [x] **Fictional company logos** (2026-03-06) - Replaced real Thai companies with fictional names
- [x] **All labs verified** (2026-03-06) - Tested all 7 labs on server, fixed SSRF route issue
- [x] **CSRF dynamic registration** (2026-03-06) - Fixed race condition, students register own accounts
- [x] **JWT redirect bugs fixed** (2026-03-06) - Fixed portal.php and admin.php "headers already sent" errors
- [x] **books.php link fixed** (2026-03-06) - Changed lab5_request.php → request.php
- [x] **GitHub repo setup** (2026-03-06) - Source code at github.com/phwrscr1pt/Hackdaybc
- [x] **Git deployment workflow** (2026-03-06) - Edit locally → push → pull on server
- [x] **Version numbering** (2026-03-06) - Added v1.0.1 to portal footer

---

## Student Setup

### CSRF Bank Accounts (/share/) - Dynamic Registration
| Username | Password | Account | Balance | Notes |
|----------|----------|---------|---------|-------|
| somchai | password123 | 1001 | ฿1,000,000 | Pre-created victim |
| (register) | (choose) | 1002+ | ฿0 | Students register own accounts |

**How to register:** Go to http://10.10.61.221/share/ → Click "Register"

### JWT Accounts (/account/)
- john / password123 (user)
- wiener / peter (user)
- admin / admin (administrator)

### SSH Lab (Port 2222)
- noob / noob (starting user)
- john / (key only) - target

### Student Handout
See `docs/student-handout.txt` for printable guide.

---

## Walkthrough Documents

| Lab | Document |
|-----|----------|
| SSH Key Auth | `sshkeygen.md` |
| SQL + JWT | `walkthrough-sqli-jwt.md` |
| CSRF | `walkthrough-csrf.md` |
| SSRF | `walkthrough-ssrf.md` |
| File Upload | `walkthrough-file-upload.md` |
| XSS | `walkthrough-xss.md` |

---

## Flags/Rewards

| Lab | Flag/Reward |
|-----|-------------|
| File Upload | `flag{php_include_is_dangerous_2026_AetherBreach_polyglot}` |
| JWT (None Alg) | `MASTER_KEY_2026` |
| JWT (Weak Key) | `JWT_WEAK_KEY_CRACKED_2026` |
| SQL Injection | Secrets: admin_pin `1337`, api_key, db_password |
| SSRF | Database credentials from /internal/config |
| SSH | Access to hint.txt with all lab hints |
| CSRF | Successfully transfer money |
| XSS | Execute JavaScript alert |

---

## What Can Be Improved?

Ideas for future improvements:
- [ ] Add more realistic content (fake articles, fake member profiles)
- [ ] Add login/registration page for the main portal
- [ ] Add CTF-style flags to CSRF, SSRF, XSS, SQL labs
- [ ] Add difficulty levels to labs
- [ ] Add student scoring system
- [ ] Add more vulnerability types (XXE, IDOR, Deserialization)
- [ ] Improve individual lab UIs to match main portal style
- [ ] Add Thai language option
- [ ] Add instructor dashboard

---

*Copy the relevant sections when asking Claude for help.*
*Last Updated: 2026-03-06 (GitHub repo, deployment workflow, version v1.0.1)*
