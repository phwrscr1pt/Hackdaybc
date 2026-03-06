# Project Context for Claude
> Read this file first to understand the project

---

## Project Overview

**Project Name:** LeaguesOfCode Lab Portal
**Purpose:** Security training platform for Cybersecurity Bootcamp 2026
**Owner:** Instructor teaching web security to students
**Created:** March 2026

This is a unified web application hosting multiple security vulnerability labs for student training. Students learn to identify and exploit common web vulnerabilities in a safe, controlled environment.

---

## Infrastructure

### Server
- **Hardware:** HPE ProLiant DL380 Gen9
- **VM OS:** Ubuntu 22.04
- **VM Specs:** 6 cores, 8GB RAM, 80GB disk
- **VM IP:** 10.10.61.221
- **Username:** loc
- **Password:** 123
- **SSH Key:** Configured (Claude can SSH without password)

### Network Access
Claude cannot reach the Lab VM (10.10.61.221) directly. Connection requires a jump host:
```
Claude/User → (Tailscale) → root-agent@100.107.182.15 → (LAN) → loc@10.10.61.221
```
See `docs/NETWORK_TOPOLOGY.md` for full details.

### Architecture
```
Nginx (Port 80) → Reverse Proxy (loc_network)
    ├── /              → PHP Portal (loc_portal)
    ├── /members/      → PHP Portal (Member Directory)
    ├── /resources/    → PHP Portal (Resource Library - SQL Labs)
    ├── /account/      → PHP Portal (Account Services - JWT Labs)
    ├── /profile/      → File Upload Lab (loc_file_upload)
    ├── /share/        → Credit System (loc_csrf_bank)
    ├── /evil/         → Partner Portal (loc_csrf_evil)
    ├── /api/          → Developer API (loc_ssrf_lab)
    └── /search/       → Tech Blog (loc_xss_lab)

Legacy redirects: /sqli/* → /resources/*, /jwt/* → /account/*

MySQL 8.0 (loc_db) → Shared database for PHP labs

SSH Lab (Port 2222) → Isolated network (ssh_network)
    └── loc_ssh_lab    → SSH Key Authentication Lab
```

---

## What Has Been Done

### 1. Cloned GitHub Labs (Initial Setup)
- `hackday-afu` - File upload polyglot vulnerability
- `csrf-lab` - CSRF with bank transfer simulation
- `ssrf-lab` - SSRF with internal service access
- `xss_lab3` - Reflected XSS in search
- `HACKDAYbcSQLJWT` - SQL injection and JWT (original lab)

### 2. Created Unified Portal
- Bootstrap 5 dark theme landing page
- Nginx reverse proxy routing all labs under single domain
- Shared MySQL database with sample data
- Docker Compose orchestration for all containers

### 3. Added SSH Key Authentication Pre-Lab (2026-03-05)
- Created isolated SSH container on port 2222
- Users: `noob` (password: noob) and `john` (target)
- Vulnerability: World-writable .ssh directory + StrictModes disabled
- Reward: john's hint.txt contains credentials for all other labs

### 4. Refactored for Realism (2026-03-06)
Applied "realism rules" from Bootcamp1 Hackday Prompt.pdf:
- **Renamed paths:** `/sqli/` → `/resources/`, `/jwt/` → `/account/`
- **Renamed files:** `lab0_login.php` → `login.php`, `lab2_members.php` → `directory.php`, etc.
- **Removed "Lab X" badges** from all UI elements
- **Added 301 redirects** in nginx.conf for backward compatibility
- **Business-like naming:** "Resource Library", "Account Services", "Employee Login"

### 5. Enhanced SQL Injection Labs (2026-03-06)
Updated to match teaching materials (SQLblind.pdf, SQLerrorbased.pdf):
- **Error-based injection** using `extractvalue()` function
- **Blind injection** using `SUBSTRING()` for character extraction
- Added `partners` table with secret_key column for blind SQLi practice

### 6. Added JWT Weak Key Lab (2026-03-06)
- New `/account/secure.php` and `/account/admin.php` endpoints
- Uses weak signing key `secret1` (crackable with hashcat)
- Signature verification enabled (none algorithm won't work)
- Flag: `JWT_WEAK_KEY_CRACKED_2026`

### 7. Created Walkthrough Documents (2026-03-06)
- `walkthrough-sqli-jwt.md` - SQL injection and JWT labs
- `walkthrough-csrf.md` - CSRF lab with bank transfer
- `walkthrough-ssrf.md` - SSRF lab with internal config access
- `walkthrough-file-upload.md` - File upload polyglot bypass
- `walkthrough-xss.md` - XSS with filter bypass
- `sshkeygen.md` - Updated as student guide for SSH lab
- Updated `hint.txt` with all new paths

### 8. Portal UI Updates (2026-03-06)
- Replaced real company logos with fictional names
- KBANK→NEXGEN, SCB→CYBERTEK, TRUE→DATAFLOW, AIS→CLOUDNINE, AGODA→BYTECRAFT, LINE→SYNTHEX

### 9. CSRF Bank Dynamic Registration (2026-03-06)
- Fixed race condition in registration code (retry on conflict)
- Database reset to only Somchai account (฿1,000,000)
- Students/siblings register their own accounts dynamically
- Account numbers auto-assigned: 1002, 1003, 1004...
- New accounts start with ฿0 balance (use Deposit feature)

### 10. Labs Tested and Working
| Lab | Path/Port | Status | Vulnerability |
|-----|-----------|--------|---------------|
| SSH Key Auth | Port 2222 | ✅ Working | Weak .ssh permissions, key injection |
| SQL - Login Bypass | /resources/login.php | ✅ Working | `admin'-- -` bypasses password |
| SQL - UNION | /resources/directory.php | ✅ Working | Extract from secret_data table |
| SQL - Error-based | /resources/catalog.php | ✅ Working | extractvalue() data extraction |
| SQL - Blind | /resources/verify.php | ✅ Working | SUBSTRING() character extraction |
| SQL - SQLMap | /resources/books.php | ✅ Working | Automated tool practice |
| JWT - None Algorithm | /account/portal.php | ✅ Working | Algorithm tampering, role change |
| JWT - Weak Key | /account/admin.php | ✅ Working | Hashcat brute-force key |
| File Upload | /profile/ | ✅ Working | Polyglot JPEG+PHP |
| CSRF | /share/, /evil/ | ✅ Working | Cross-site request forgery |
| SSRF | /api/ | ✅ Working | Internal service access |
| XSS | /search/ | ✅ Working | Reflected XSS via img onerror |

---

## GitHub Repository & Deployment

### Source Code Repository
- **GitHub:** https://github.com/phwrscr1pt/Hackdaybc.git
- **Local Clone:** `D:\LOC\HackdayBc\labs-source\`
- **Server Location:** `/home/loc/HackdayBc/` (git-enabled)

### Deployment Workflow
```
1. Edit code locally in D:\LOC\HackdayBc\labs-source\
2. Commit and push:
   cd D:/LOC/HackdayBc/labs-source
   git add . && git commit -m "message" && git push

3. Deploy to server:
   ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "cd /home/loc/HackdayBc && git pull"

4. If Docker changes needed:
   ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "cd /home/loc/HackdayBc && git pull && docker-compose down && docker-compose up -d"
```

### Version
- **Current Version:** v1.0.1 (shown in footer)

---

## File Locations

### Windows (Local Development)
```
D:\LOC\HackdayBc\
├── docker-compose.yml       # Main orchestration (keep at root)
├── nginx/                   # Nginx reverse proxy config
│   └── nginx.conf
├── portal/                  # Main PHP portal
│   ├── Dockerfile
│   ├── www/
│   │   ├── index.php        # Landing page
│   │   ├── config.php       # DB connection
│   │   ├── members/         # Member Directory (SQL Injection)
│   │   ├── resources/       # Resource Library (SQL Labs)
│   │   └── account/         # Account Services (JWT Labs)
│   └── db/init.sql          # Database schema
├── labs/                    # Cloned GitHub repos + custom labs
│   ├── hackday-afu/         # File upload
│   ├── csrf-lab/            # CSRF
│   ├── ssrf-lab/            # SSRF
│   ├── xss_lab3/            # XSS
│   └── ssh-lab/             # SSH Key Auth (custom, port 2222)
├── docs/                    # Documentation
│   ├── CLAUDE_CONTEXT.md    # This file (read first!)
│   ├── CLAUDE_COMMANDS.md   # Command reference
│   ├── PROJECT_BRIEF.md     # Project overview for Claude browser
│   ├── NETWORK_TOPOLOGY.md  # How to SSH via jump host
│   ├── student-handout.txt  # Printable guide for students
│   ├── sshkeygen.md         # SSH lab student guide
│   ├── walkthrough-ssh-lab.md    # SSH lab walkthrough
│   ├── walkthrough-sqli-jwt.md   # SQL + JWT walkthrough
│   ├── walkthrough-csrf.md       # CSRF walkthrough
│   ├── walkthrough-ssrf.md       # SSRF walkthrough
│   ├── walkthrough-file-upload.md # File upload walkthrough
│   ├── walkthrough-xss.md        # XSS walkthrough
│   └── *.pdf                # Reference materials
├── notes/                   # Working notes
│   ├── summarizeSQLI.txt
│   ├── summarizeBrokenAuth.txt
│   └── linkgithub.txt
├── archive/                 # Old versions (backup)
│   └── walkthrough-*.md
└── lab/                     # Original lab (backup)
```

### VM (Production)
```
/home/loc/HackdayBc/
├── portal/www/
│   ├── resources/           # SQL Injection labs (was sqli/)
│   ├── account/             # JWT labs (was jwt/)
│   └── members/             # Member Directory
├── labs/                    # External labs
├── nginx/nginx.conf         # Reverse proxy with redirects
└── docker-compose.yml       # Container orchestration
```
Note: VM structure updated 2026-03-06 with realism refactoring.

---

## Database

**Database:** MySQL 8.0
**Name:** leaguesofcode_db
**User:** locadmin
**Password:** locpass123

### Tables
| Table | Purpose | Used By |
|-------|---------|---------|
| `users` | Login credentials | /resources/login.php, /resources/register.php |
| `members` | Organization members | /resources/directory.php (UNION injection) |
| `secret_data` | Sensitive data to extract | Target for all SQL injection labs |
| `inventory` | Product inventory | /resources/catalog.php (Error-based) |
| `partners` | Partner info with secret_key | /resources/verify.php (Blind injection) |
| `books` | Library books | /resources/books.php (SQLMap practice) |
| `accounts` | JWT user accounts with roles | /account/* (JWT labs) |

---

## Docker Containers

| Container | Image | Port | Network | Purpose |
|-----------|-------|------|---------|---------|
| loc_nginx | nginx:alpine | 80 | loc_network | Reverse proxy |
| loc_portal | php:8.2-apache | 80 (internal) | loc_network | Main PHP portal |
| loc_db | mysql:8.0 | 3306 (internal) | loc_network | Database |
| loc_file_upload | php:8.2-fpm-alpine | 80 (internal) | loc_network | File upload lab |
| loc_csrf_bank | python:3.11-slim | 5000 (internal) | loc_network | CSRF victim |
| loc_csrf_evil | python:3.11-slim | 9999 (internal) | loc_network | CSRF attacker |
| loc_ssrf_lab | python:3.11-slim | 7070 (internal) | loc_network | SSRF lab |
| loc_xss_lab | node:18-slim | 3000 (internal) | loc_network | XSS lab |
| loc_ssh_lab | ubuntu:22.04 | 2222 | ssh_network | SSH key auth lab |

---

## Known Issues Fixed

1. **Windows line endings** - Fixed entrypoint.sh in hackday-afu with `sed -i 's/\r$//'`
2. **Duplicate JWT_SECRET** - Fixed in jwt_helper.php with `if (!defined())`
3. **Missing $conn variable** - Added global $conn in config.php
4. **SSRF route mismatch** - Fixed Flask route from `/api/fetch` to `/fetch` (nginx strips `/api/` prefix)
5. **CSRF registration race condition** - Fixed with retry logic in app.py to handle concurrent registrations
6. **JWT portal.php redirect error** - Moved token check before `header.php` include to fix "headers already sent" error
7. **JWT admin.php redirect error** - Same fix as portal.php
8. **books.php broken link** - Changed link from `lab5_request.php` to `request.php`
9. **login.php headers warning** - Moved `header()` call before `header.php` include to fix "headers already sent" error
10. **signin.php headers warning** - Moved `header.php` include after `setcookie()` call to fix "headers already sent" error
11. **config.php JWT_SECRET redefinition** - Added `if (!defined('JWT_SECRET'))` wrapper to prevent warning
12. **header.php outdated links** - Updated navigation: `/sqli/` → `/resources/`, `/jwt/` → `/account/`
13. **Missing nginx redirects for new filenames** - Added redirects for `/sqli/*.php` and `/jwt/*.php` using new filenames (login.php, directory.php, portal.php, etc.)
14. **SSH lab permission denied** - john's home was `drwxr-x---` (750), preventing noob from traversing to .ssh directory. Fixed by adding `chmod 711 /home/john` in both Dockerfile and entrypoint.sh to allow directory traversal.
15. **SSRF lab navigation broken** - Links in app.py used root paths (`/`, `/resources`, `/wordlist.txt`) but lab is served at `/api/` via nginx. Fixed by updating all links to use `/api/` prefix.

---

## Student Accounts

### CSRF Bank (/share/) - Dynamic Registration
| Username | Password | Account No | Balance | Notes |
|----------|----------|------------|---------|-------|
| somchai | password123 | 1001 | ฿1,000,000 | Pre-created victim |
| (register) | (choose) | 1002+ | ฿0 | Students register own accounts |

**How it works:**
1. Students go to http://10.10.61.221/share/
2. Click "Register" and create username/password
3. System assigns unique account number (1002, 1003, ...)
4. Use "Deposit" to add funds, or practice CSRF transfers

### JWT Labs (/account/)
| Username | Password | Role |
|----------|----------|------|
| john | password123 | user |
| wiener | peter | user |
| admin | admin | administrator |

### SSH Lab (Port 2222)
| Username | Password | Notes |
|----------|----------|-------|
| noob | noob | Starting user |
| john | (key only) | Target - has hint.txt |

---

## Student Objectives

| Lab | Goal |
|-----|------|
| SSH Key Auth | Write SSH key to john's authorized_keys, get dev notes |
| SQL Injection | Extract data from `secret_data` table |
| JWT | Change role from `user` to `administrator` |
| File Upload | Upload PHP webshell disguised as JPEG |
| CSRF | Transfer money without victim's consent |
| SSRF | Access `http://127.0.0.1:7070/internal/config` |
| XSS | Execute `alert()` in victim's browser |

---

## Quick Commands for Claude

### SSH to VM (via jump host)
```bash
ssh -J root-agent@100.107.182.15 loc@10.10.61.221
```

### Start all labs
```bash
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "cd /home/loc/HackdayBc && docker-compose up -d"
```

### Check status
```bash
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "docker ps"
```

### Test web endpoints
```bash
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "curl -s -o /dev/null -w '%{http_code}' http://localhost/"
```

### Test SSH lab
```bash
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "nc -zv localhost 2222"
```

### Create backup
```bash
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "cd /home/loc && tar -czvf HackdayBc-backup-\$(date +%Y%m%d).tar.gz HackdayBc/"
```

---

## Next Steps / Future Work

### Completed
- [x] Add SSH Key Authentication pre-lab (2026-03-05)
- [x] Refactor paths for realism - no security keywords in URLs (2026-03-06)
- [x] Add Error-based SQL injection with extractvalue() (2026-03-06)
- [x] Add Blind SQL injection with SUBSTRING() (2026-03-06)
- [x] Add JWT Weak Signing Key lab (2026-03-06)
- [x] Create walkthrough documents for all labs (2026-03-06)
- [x] Add backward-compatible redirects for old paths (2026-03-06)
- [x] Replace real company logos with fictional names (2026-03-06)
- [x] Fix CSRF registration race condition (2026-03-06)
- [x] Enable dynamic registration for CSRF bank (2026-03-06)
- [x] Set up GitHub repository for source code (2026-03-06)
- [x] Create local labs-source folder with all code (2026-03-06)
- [x] Set up git deployment workflow (edit → push → pull) (2026-03-06)
- [x] Add version numbering to portal footer (v1.0.1) (2026-03-06)

### Pending
- [ ] Create student scoring system
- [ ] Add time-based challenges
- [ ] Add more flags to labs without explicit flags (CSRF, SSRF, XSS, SQL)
- [ ] Add more vulnerability types (XXE, IDOR, etc.)
- [ ] Improve individual lab UIs to match main portal style
- [ ] Add instructor dashboard
- [ ] Archive/remove old walkthrough files (walkthrough-sqli-final.md, walkthrough-jwt-final.md) - replaced by walkthrough-sqli-jwt.md

---

## Contact

If you need to contact the instructor or have questions about the project purpose, ask the user directly.

---

## Changelog

| Date | Changes |
|------|---------|
| 2026-03-07 | Fixed SSRF lab navigation links to use /api/ prefix (Home, Resources, Wordlist download) for nginx reverse proxy compatibility |
| 2026-03-07 | Fixed JWT Weak Key walkthrough: correct cookie name (auth_token_secure), added OpenSSL signing method, working forged token |
| 2026-03-07 | Enhanced walkthrough-sqli-jwt.md: added detailed step-by-step for UNION, Error-based, Blind SQLi, JWT None Algorithm, JWT Weak Key (690 lines added) |
| 2026-03-07 | Enhanced SSH walkthrough: added key transfer methods (SCP, copy-paste, one-line injection), fixed output format, added directory permissions explanation (r vs x) |
| 2026-03-07 | Fixed SSH lab permissions: added chmod 711 /home/john in Dockerfile and entrypoint.sh |
| 2026-03-06 | Updated walkthroughs: XSS search path (/search/search?q=), SSRF (LinkScope), File Upload (AetherVision AI) |
| 2026-03-06 | Added missing nginx redirects for /sqli/*.php and /jwt/*.php with new filenames |
| 2026-03-06 | Fixed login.php, signin.php headers warnings; config.php JWT_SECRET redefinition; header.php outdated nav links |
| 2026-03-06 | Set up GitHub repo and git deployment workflow |
| 2026-03-06 | Created labs-source folder with all source code |
| 2026-03-06 | Added version v1.0.1 to portal footer |
| 2026-03-06 | Fixed JWT portal.php and admin.php redirect errors (headers already sent) |
| 2026-03-06 | Fixed books.php broken link (lab5_request.php → request.php) |
| 2026-03-06 | Fixed CSRF registration race condition, enabled dynamic registration |
| 2026-03-06 | Reset CSRF database to only Somchai account (students register own) |
| 2026-03-06 | Fixed SSRF route mismatch (`/api/fetch` → `/fetch`) |
| 2026-03-06 | Verified all labs working on server |
| 2026-03-06 | Created student-handout.txt for class distribution |
| 2026-03-06 | Replaced real company logos with fictional names |
| 2026-03-06 | Created walkthroughs: CSRF, SSRF, File Upload, XSS |
| 2026-03-06 | Updated hint.txt and sshkeygen.md with new paths |
| 2026-03-06 | Verified all labs on server, fixed SSRF (POST) and XSS (script filter) docs |
| 2026-03-06 | Refactored for realism: /sqli/→/resources/, /jwt/→/account/, removed "Lab X" badges |
| 2026-03-06 | Added error-based (extractvalue) and blind (SUBSTRING) SQL injection labs |
| 2026-03-06 | Added JWT weak signing key lab (secret1, hashcat crackable) |
| 2026-03-06 | Created walkthrough-sqli-jwt.md with all payloads and solutions |
| 2026-03-05 | Added SSH Key Authentication pre-lab on port 2222 |
| 2026-03-05 | Initial portal setup with all labs integrated |

---

*Last Updated: 2026-03-07 (Fixed SSRF lab navigation links for /api/ prefix)*
*This file helps Claude understand the project context in new sessions.*
