# Claude Commands Reference - LeaguesOfCode Lab Portal

## Quick Reference

Copy-paste these commands to tell Claude what to do.

---

## Server Information

```
Lab VM IP: 10.10.61.221 (not directly accessible)
Jump Host: root-agent@100.107.182.15 (via Tailscale)
Username: loc
Password: 123

SSH Command: ssh -J root-agent@100.107.182.15 loc@10.10.61.221
```

> **Note:** Claude must use the jump host (-J flag) to reach the Lab VM.

---

## 1. Server Management

### Start all labs
```
SSH into my VM at 10.10.61.221 and run: cd /home/loc/HackdayBc && docker-compose up -d
```

### Stop all labs
```
SSH into my VM at 10.10.61.221 and run: cd /home/loc/HackdayBc && docker-compose down
```

### Restart all labs
```
SSH into my VM at 10.10.61.221 and run: cd /home/loc/HackdayBc && docker-compose restart
```

### Check container status
```
SSH into my VM at 10.10.61.221 and check docker container status
```

### View logs
```
SSH into my VM at 10.10.61.221 and show me docker logs for [container_name]
```
Container names: `loc_portal`, `loc_db`, `loc_nginx`, `loc_file_upload`, `loc_csrf_bank`, `loc_csrf_evil`, `loc_ssrf_lab`, `loc_xss_lab`

### Rebuild specific lab
```
SSH into my VM at 10.10.61.221 and rebuild [lab_name] container
```

---

## 2. Testing Labs

### Test all labs
```
SSH into my VM at 10.10.61.221 and test all security labs to make sure they work
```

### Test specific lab
```
SSH into my VM at 10.10.61.221 and test the [lab_name] lab
```
Lab names: `SSH Key Auth`, `SQL Injection`, `JWT`, `File Upload`, `CSRF`, `SSRF`, `XSS`

### Test SSH lab connectivity
```
SSH into my VM at 10.10.61.221 and test SSH lab: nc -zv localhost 2222
```

---

## 3. Database Management

### Reset database
```
SSH into my VM at 10.10.61.221 and reset the MySQL database to initial state
```

### Check database tables
```
SSH into my VM at 10.10.61.221 and show me all tables in the database
```

### Add sample data
```
SSH into my VM at 10.10.61.221 and add more sample data to the [table_name] table
```

---

## 4. Modify Labs

### Update landing page
```
Update the portal landing page at /home/loc/HackdayBc/portal/www/index.php with [description of changes]
```

### Add new SQL injection lab
```
Create a new SQL injection lab at /resources/ with [description]
```

### Modify difficulty
```
Make the [lab_name] lab [easier/harder] by [description]
```

---

## 5. Student Management

### Create student guide
```
Create a student lab guide for [lab_name] without showing the answers
```

### Create answer key
```
Create an instructor answer key for [lab_name] lab
```

### Create new challenge
```
Create a new [vulnerability_type] challenge for students with difficulty level [easy/medium/hard]
```

---

## 6. Deployment (Git Workflow)

### GitHub Repository
```
https://github.com/phwrscr1pt/Hackdaybc.git
```

### Local Development
```bash
# Edit code in:
D:\LOC\HackdayBc\labs-source\

# Commit and push changes:
cd D:/LOC/HackdayBc/labs-source
git add .
git commit -m "Your message"
git push
```

### Deploy to Server (Quick - PHP/static files)
```bash
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "cd /home/loc/HackdayBc && git pull"
```

### Deploy to Server (Full - Docker changes)
```bash
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "cd /home/loc/HackdayBc && git pull && docker-compose down && docker-compose up -d --build"
```

### Backup current state
```
SSH into my VM at 10.10.61.221 and create a backup of the current lab setup
```

---

## 7. Troubleshooting

### Fix container not starting
```
SSH into my VM at 10.10.61.221 and fix the [container_name] container that won't start
```

### Fix database connection
```
SSH into my VM at 10.10.61.221 and fix the database connection issue
```

### Check nginx errors
```
SSH into my VM at 10.10.61.221 and check nginx error logs
```

---

## 8. Reports

### Generate lab status report
```
SSH into my VM at 10.10.61.221 and generate a status report of all labs
```

### Check which labs are accessible
```
SSH into my VM at 10.10.61.221 and test HTTP connectivity to all lab endpoints
```

---

## Lab URLs Reference

### SSH Lab (Pre-Lab)
| Endpoint | Description |
|----------|-------------|
| Port 2222 | SSH Key Auth Lab |
| User: noob | Password: noob |
| Target: john | Write SSH key to gain access |

### SQL Injection Labs (/resources/)
| Endpoint | Business Name | Vulnerability |
|----------|---------------|---------------|
| /resources/ | Resource Library | Index page |
| /resources/login.php | Employee Login | Login bypass (`admin'-- -`) |
| /resources/register.php | Account Registration | SQL injection |
| /resources/directory.php | Member Directory | UNION injection |
| /resources/catalog.php | Book Catalog | Error-based (extractvalue) |
| /resources/verify.php | Partner Verification | Blind injection (SUBSTRING) |
| /resources/books.php | Library Books | SQLMap practice |

### JWT Labs (/account/)
| Endpoint | Business Name | Vulnerability |
|----------|---------------|---------------|
| /account/ | Account Services | Index page |
| /account/signin.php | Account Login | Get JWT token |
| /account/portal.php | User Dashboard | None algorithm bypass |
| /account/refresh.php | Token Refresh | Token manipulation |
| /account/api.php | Admin API | Role escalation |
| /account/secure.php | Secure Login | Weak signing key |
| /account/admin.php | Secure Dashboard | Hashcat crack key |

### Other Labs
| Endpoint | Business Name | Vulnerability |
|----------|---------------|---------------|
| /members/ | Member Directory | SQL Injection (UNION) |
| /profile/ | Profile Settings | File Upload (Polyglot) |
| /share/ | Credit System | CSRF (Victim bank) |
| /evil/ | Partner Portal | CSRF (Attacker page) |
| /api/ | Developer API | SSRF |
| /search/ | Tech Blog | Reflected XSS |

### Legacy URLs (Redirects)
Old paths still work via 301 redirects:
- `/sqli/*` → `/resources/*`
- `/jwt/*` → `/account/*`

---

## Docker Commands Reference

```bash
# Start
docker-compose up -d

# Stop
docker-compose down

# Restart
docker-compose restart

# Rebuild
docker-compose up -d --build

# View logs
docker-compose logs -f [service_name]

# Check status
docker-compose ps

# Enter container
docker exec -it [container_name] /bin/bash

# Reset everything
docker-compose down -v && docker-compose up -d --build
```

---

## Lab Manager Script

A helper script is available on the VM at `/home/loc/HackdayBc/lab-manager.sh`:

```bash
# Check all lab status
./lab-manager.sh status

# Test all lab endpoints
./lab-manager.sh test

# Full reset (before class)
./lab-manager.sh reset

# Reset only database
./lab-manager.sh reset-db

# Reset only SSH lab
./lab-manager.sh reset-ssh

# View logs
./lab-manager.sh logs [container_name]

# Start/Stop
./lab-manager.sh start
./lab-manager.sh stop
```

### Quick reset before class
```
SSH into my VM at 10.10.61.221 and run: cd /home/loc/HackdayBc && ./lab-manager.sh reset
```

### Reset CSRF bank (before class)
Recreate container to reset database to only Somchai account:
```bash
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "cd /home/loc/HackdayBc && docker-compose up -d --force-recreate --no-deps csrf-bank"
```

### Check CSRF bank accounts
```bash
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "docker exec loc_csrf_bank python3 -c \"
import sqlite3
conn = sqlite3.connect('/tmp/bank.db')
users = conn.execute('SELECT username, account_no FROM users').fetchall()
accounts = conn.execute('SELECT account_no, balance FROM accounts').fetchall()
print('Users:', users)
print('Accounts:', accounts)
\""
```

---

## File Locations

**GitHub Repository:**
- URL: `https://github.com/phwrscr1pt/Hackdaybc.git`

**On Windows (Local Development):**
- Source Code: `D:\LOC\HackdayBc\labs-source\` (git repo)
- Portal code: `D:\LOC\HackdayBc\labs-source\portal\www\`
- Labs: `D:\LOC\HackdayBc\labs-source\labs\`
- Docker config: `D:\LOC\HackdayBc\labs-source\docker-compose.yml`
- Nginx config: `D:\LOC\HackdayBc\labs-source\nginx\nginx.conf`
- Documentation: `D:\LOC\HackdayBc\docs\`

**On VM (Server):**
- Project: `/home/loc/HackdayBc/` (git-enabled, pulls from GitHub)
- Portal code: `/home/loc/HackdayBc/portal/www/`
- Labs: `/home/loc/HackdayBc/labs/`

---

## Test Accounts & Secrets

### CSRF Bank Accounts (/share/) - Dynamic Registration
| Username | Password | Account No | Balance | Notes |
|----------|----------|------------|---------|-------|
| somchai | password123 | 1001 | ฿1,000,000 | Pre-created victim |
| (register) | (choose) | 1002+ | ฿0 | Students register own accounts |

**Students register at:** http://10.10.61.221/share/ → Click "Register"

### JWT Test Accounts (/account/)
| Username | Password | Role |
|----------|----------|------|
| john | password123 | user |
| wiener | peter | user |
| admin | admin | administrator |

### SSH Lab Accounts (Port 2222)
| Username | Password | Notes |
|----------|----------|-------|
| noob | noob | Starting user |
| john | (key only) | Target - has hint.txt |

### Database Credentials
- **Database:** leaguesofcode_db
- **User:** locadmin
- **Password:** locpass123

### JWT Secrets
- **None Algorithm Lab:** No secret needed (signature ignored)
- **Weak Key Lab:** `secret1` (crackable with hashcat)

### Secrets to Extract (Flags)
| Location | Secret |
|----------|--------|
| secret_data table | admin_pin: `1337` |
| secret_data table | api_key: `sk_live_abc123xyz789` |
| secret_data table | db_password: `sup3rs3cr3tDBp@ss` |
| JWT Admin Panel | Server Key: `MASTER_KEY_2026` |
| JWT Weak Key Lab | Flag: `JWT_WEAK_KEY_CRACKED_2026` |

---

## Walkthrough Documents

| Lab | Document |
|-----|----------|
| SSH Key Auth | `sshkeygen.md`, `walkthrough-ssh-lab.md` |
| SQL Injection + JWT | `walkthrough-sqli-jwt.md` |
| CSRF | `walkthrough-csrf.md` |
| SSRF | `walkthrough-ssrf.md` |
| File Upload | `walkthrough-file-upload.md` |
| XSS | `walkthrough-xss.md` |
| Student Handout | `student-handout.txt` |

---

## Notes

- Always make sure you're connected to the same network as the VM
- SSH key is already configured, no password needed for Claude to access VM
- Old URLs (/sqli/, /jwt/) redirect to new paths automatically
- Portal uses fictional company names (NEXGEN, CYBERTEK, DATAFLOW, etc.)
- SSRF lab uses POST with JSON body (route fixed: `/api/fetch` → `/fetch` internally)
- CSRF bank uses dynamic registration (students create own accounts, no pre-created student accounts)
- CSRF registration has retry logic to handle concurrent registrations safely
- JWT portal.php and admin.php: Token check must be BEFORE `require header.php` to allow redirects
- books.php links to `request.php` (not `lab5_request.php`)

---

## Old Files (To Be Archived)

The following walkthrough files use old paths and are superseded by newer documents:
- `walkthrough-sqli-final.md` - replaced by `walkthrough-sqli-jwt.md`
- `walkthrough-jwt-final.md` - replaced by `walkthrough-sqli-jwt.md`

---

Last Updated: 2026-03-06 (Added Git deployment workflow, GitHub repo)
