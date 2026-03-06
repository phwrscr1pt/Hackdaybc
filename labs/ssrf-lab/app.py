import requests
import os
import base64
from flask import Flask, request, jsonify, send_file, Response

app = Flask(__name__)

WORDLIST_PATH = os.path.join(os.path.dirname(__file__), 'wordlist.txt')

def is_internal(req):
    return req.remote_addr in ('127.0.0.1', '::1')

CONGRATS = "🎉 Congratulation you pass exam!"


@app.route('/', methods=['GET'])
def index():
    html = r"""<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>LinkScope — Developer API Tools</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --primary: #0f172a;
    --secondary: #1e293b;
    --accent: #3b82f6;
    --accent-light: #60a5fa;
    --gold: #f59e0b;
    --text: #f1f5f9;
    --text-muted: #94a3b8;
    --border: rgba(148, 163, 184, 0.1);
    --success: #10b981;
    --danger: #ef4444;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', sans-serif; background: var(--primary); color: var(--text); min-height: 100vh; display: flex; flex-direction: column; }

  nav { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid var(--border); padding: 0 40px; height: 60px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
  .logo { font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px; text-decoration: none; color: var(--text); }
  .logo-icon { width: 36px; height: 36px; background: linear-gradient(135deg, var(--accent), var(--accent-light)); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
  .logo span { color: var(--accent); }
  .nav-links { display: flex; align-items: center; gap: 24px; list-style: none; }
  .nav-links a { color: var(--text-muted); text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: color 0.2s; }
  .nav-links a:hover { color: var(--text); }

  .hero { padding: 64px 40px 48px; text-align: center; max-width: 720px; margin: 0 auto; width: 100%; }
  .hero-badge { display: inline-flex; align-items: center; gap: 6px; background: rgba(59, 130, 246, 0.1); color: var(--accent); font-size: 0.75rem; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; padding: 6px 14px; border-radius: 20px; margin-bottom: 24px; border: 1px solid rgba(59, 130, 246, 0.2); }
  h1 { font-size: 3rem; font-weight: 800; line-height: 1.2; margin-bottom: 16px; }
  h1 span { color: var(--accent); }
  .hero-sub { font-size: 1.1rem; color: var(--text-muted); line-height: 1.7; max-width: 500px; margin: 0 auto 36px; }

  .fetch-card { background: var(--secondary); border: 1px solid var(--border); border-radius: 16px; padding: 28px; margin-bottom: 24px; }
  .input-row { display: flex; gap: 10px; }
  .url-input { flex: 1; height: 48px; border: 1px solid var(--border); border-radius: 10px; padding: 0 16px; font-family: 'Inter', sans-serif; font-size: 0.9rem; color: var(--text); background: var(--primary); outline: none; transition: border-color 0.2s; }
  .url-input::placeholder { color: var(--text-muted); }
  .url-input:focus { border-color: var(--accent); }
  .btn-fetch { height: 48px; padding: 0 24px; background: linear-gradient(135deg, var(--accent), var(--accent-light)); color: white; border: none; border-radius: 10px; font-family: 'Inter', sans-serif; font-size: 0.9rem; font-weight: 600; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 8px; }
  .btn-fetch:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3); }
  .btn-fetch:disabled { opacity: 0.5; cursor: not-allowed; transform: none; }
  .fetch-note { font-size: 0.8rem; color: var(--text-muted); margin-top: 14px; display: flex; align-items: center; gap: 6px; }

  #result-wrap { display: none; background: var(--secondary); border: 1px solid var(--border); border-radius: 16px; overflow: hidden; margin-bottom: 24px; animation: slideIn 0.25s ease; }
  @keyframes slideIn { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:translateY(0)} }
  .result-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; background: var(--primary); border-bottom: 1px solid var(--border); }
  .result-title { font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
  .status-chip { display: inline-flex; align-items: center; gap: 5px; font-size: 0.75rem; font-weight: 600; padding: 4px 12px; border-radius: 20px; }
  .status-chip.ok { background: rgba(16, 185, 129, 0.15); color: var(--success); }
  .status-chip.err { background: rgba(239, 68, 68, 0.15); color: var(--danger); }
  .status-chip.warn { background: rgba(245, 158, 11, 0.15); color: var(--gold); }
  .status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
  .result-body { padding: 20px; font-family: 'JetBrains Mono', monospace; font-size: 0.85rem; line-height: 1.7; color: var(--text); white-space: pre-wrap; word-break: break-all; max-height: 400px; overflow-y: auto; background: var(--secondary); }

  .spinner { display: none; width: 18px; height: 18px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin 0.7s linear infinite; }
  @keyframes spin { to{transform:rotate(360deg)} }

  .features { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; max-width: 720px; margin: 0 auto 64px; padding: 0 40px; }
  .feature-card { background: var(--secondary); border: 1px solid var(--border); border-radius: 12px; padding: 24px; transition: all 0.3s; }
  .feature-card:hover { border-color: var(--accent); transform: translateY(-4px); }
  .feature-icon { font-size: 1.5rem; margin-bottom: 12px; }
  .feature-title { font-size: 0.95rem; font-weight: 600; margin-bottom: 8px; }
  .feature-desc { font-size: 0.85rem; color: var(--text-muted); line-height: 1.6; }

  footer { margin-top: auto; border-top: 1px solid var(--border); padding: 24px 40px; display: flex; align-items: center; justify-content: space-between; background: var(--primary); }
  .footer-logo { font-weight: 700; font-size: 1rem; }
  .footer-logo span { color: var(--accent); }

  @media (max-width: 640px) { .features { grid-template-columns: 1fr; } .input-row { flex-direction: column; } .btn-fetch { width: 100%; justify-content: center; } }
</style>
</head>
<body>

<nav>
  <a href="/" class="logo"><div class="logo-icon"><i class="bi bi-braces"></i></div>Leagues<span>Of</span>Code</a>
  <ul class="nav-links">
    <li><a href="/">Home</a></li>
    <li><a href="/resources">Resources</a></li>
  </ul>
</nav>

<main style="flex:1">
  <div class="hero">
    <div class="hero-badge"><i class="bi bi-lightning-charge-fill"></i> URL Content Inspector</div>
    <h1>Developer <span>API</span> Tools</h1>
    <p class="hero-sub">Paste a URL and we'll fetch its content on your behalf — headers, body, status codes and more.</p>

    <div class="fetch-card">
      <div class="input-row">
        <input class="url-input" id="urlInput" type="text" placeholder="https://example.com" autocomplete="off" spellcheck="false"/>
        <button class="btn-fetch" id="fetchBtn" onclick="doFetch()">
          <span class="spinner" id="spinner"></span>
          <span id="btnText">Inspect URL</span>
        </button>
      </div>
      <div class="fetch-note"><i class="bi bi-shield-check"></i> Requests are proxied through our servers</div>
    </div>

    <div id="result-wrap">
      <div class="result-header">
        <span class="result-title">Response</span>
        <span class="status-chip" id="statusChip"><span class="status-dot"></span><span id="statusText">—</span></span>
      </div>
      <div class="result-body" id="resultBody"></div>
    </div>
  </div>

  <div class="features">
    <div class="feature-card"><div class="feature-icon">🌐</div><div class="feature-title">Any URL</div><div class="feature-desc">Fetch content from any publicly accessible web address.</div></div>
    <div class="feature-card"><div class="feature-icon">⚡</div><div class="feature-title">Instant Results</div><div class="feature-desc">Get the raw server response in milliseconds.</div></div>
    <div class="feature-card"><div class="feature-icon">📋</div><div class="feature-title">Full Response</div><div class="feature-desc">See status codes, headers, and complete body content.</div></div>
  </div>
</main>

<footer>
  <span class="footer-logo">Leagues<span>Of</span>Code</span>
  <span style="color: var(--text-muted); font-size: 0.85rem;">© 2026 Cybersecurity Bootcamp</span>
</footer>

<script>
  const input = document.getElementById('urlInput');
  input.addEventListener('keydown', e => { if (e.key === 'Enter') doFetch(); });

  async function doFetch() {
    let url = input.value.trim();
    if (!url) { input.focus(); return; }
    if (!/^[a-zA-Z][a-zA-Z0-9+\-.]*:\/\//.test(url)) { url = 'http://' + url; input.value = url; }

    const btn = document.getElementById('fetchBtn');
    const spinner = document.getElementById('spinner');
    const btnText = document.getElementById('btnText');
    const wrap = document.getElementById('result-wrap');
    const body = document.getElementById('resultBody');
    const chip = document.getElementById('statusChip');
    const chipTxt = document.getElementById('statusText');

    btn.disabled = true;
    spinner.style.display = 'block';
    btnText.textContent = 'Fetching…';
    wrap.style.display = 'none';

    try {
      const res = await fetch('/api/fetch', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({url}) });
      const data = await res.json();
      wrap.style.display = 'block';

      if (data.error) {
        chip.className = 'status-chip err';
        chipTxt.textContent = 'Error';
        body.textContent = data.error;
      } else {
        const sc = data.status;
        if (sc >= 200 && sc < 300) { chip.className = 'status-chip ok'; chipTxt.textContent = sc + ' OK'; }
        else if (sc >= 400 && sc < 500) { chip.className = 'status-chip warn'; chipTxt.textContent = '' + sc; }
        else { chip.className = 'status-chip err'; chipTxt.textContent = '' + sc; }

        if (data.type === 'image') {
          body.innerHTML = '';
          const img = document.createElement('img');
          img.src = 'data:' + data.content_type + ';base64,' + data.data;
          img.style.cssText = 'max-width:100%;max-height:360px;display:block;margin:0 auto;border-radius:8px;';
          body.style.textAlign = 'center';
          body.appendChild(img);
        } else {
          body.style.textAlign = '';
          try { body.textContent = JSON.stringify(JSON.parse(data.body), null, 2); }
          catch { body.textContent = data.body; }
        }
      }
    } catch (err) {
      wrap.style.display = 'block';
      chip.className = 'status-chip err';
      chipTxt.textContent = 'Error';
      body.textContent = String(err);
    } finally {
      btn.disabled = false;
      spinner.style.display = 'none';
      btnText.textContent = 'Inspect URL';
    }
  }
</script>
</body>
</html>"""
    return Response(html, content_type='text/html')


@app.route('/resources', methods=['GET'])
def resources():
    html = """<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Developer Resources — LeaguesOfCode</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root { --primary: #0f172a; --secondary: #1e293b; --accent: #3b82f6; --accent-light: #60a5fa; --text: #f1f5f9; --text-muted: #94a3b8; --border: rgba(148, 163, 184, 0.1); }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: 'Inter', sans-serif; background: var(--primary); color: var(--text); min-height: 100vh; display: flex; flex-direction: column; }
  nav { background: rgba(15, 23, 42, 0.95); backdrop-filter: blur(10px); border-bottom: 1px solid var(--border); padding: 0 40px; height: 60px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 100; }
  .logo { font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px; text-decoration: none; color: var(--text); }
  .logo-icon { width: 36px; height: 36px; background: linear-gradient(135deg, var(--accent), var(--accent-light)); border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; }
  .logo span { color: var(--accent); }
  .nav-links { display: flex; align-items: center; gap: 24px; list-style: none; }
  .nav-links a { color: var(--text-muted); text-decoration: none; font-size: 0.9rem; font-weight: 500; transition: color 0.2s; }
  .nav-links a:hover, .nav-links a.active { color: var(--text); }
  main { flex: 1; max-width: 760px; margin: 0 auto; padding: 56px 40px; width: 100%; }
  .page-title { font-size: 2.5rem; font-weight: 800; margin-bottom: 8px; }
  .page-sub { color: var(--text-muted); font-size: 1rem; margin-bottom: 48px; }
  .section-label { font-size: 0.7rem; font-weight: 600; letter-spacing: 0.1em; text-transform: uppercase; color: var(--accent); margin-bottom: 16px; }
  .resource-card { background: var(--secondary); border: 1px solid var(--border); border-radius: 14px; padding: 24px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; gap: 20px; transition: all 0.3s; }
  .resource-card:hover { border-color: var(--accent); transform: translateY(-2px); }
  .rc-left { display: flex; align-items: center; gap: 16px; }
  .rc-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; background: rgba(59, 130, 246, 0.1); }
  .rc-title { font-size: 1rem; font-weight: 600; margin-bottom: 4px; }
  .rc-desc { font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; }
  .rc-meta { font-size: 0.75rem; color: var(--text-muted); margin-top: 8px; display: flex; align-items: center; gap: 8px; }
  .rc-tag { background: var(--primary); border: 1px solid var(--border); border-radius: 4px; padding: 2px 8px; font-size: 0.7rem; }
  .btn-dl { display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, var(--accent), var(--accent-light)); color: white; text-decoration: none; font-size: 0.85rem; font-weight: 600; padding: 10px 20px; border-radius: 8px; transition: all 0.2s; }
  .btn-dl:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(59, 130, 246, 0.3); }
  footer { border-top: 1px solid var(--border); padding: 24px 40px; display: flex; align-items: center; justify-content: space-between; background: var(--primary); }
  .footer-logo { font-weight: 700; }
  .footer-logo span { color: var(--accent); }
</style>
</head>
<body>
<nav>
  <a href="/" class="logo"><div class="logo-icon"><i class="bi bi-braces"></i></div>Leagues<span>Of</span>Code</a>
  <ul class="nav-links">
    <li><a href="/">Home</a></li>
    <li><a href="/resources" class="active">Resources</a></li>
  </ul>
</nav>
<main>
  <h1 class="page-title">Developer Resources</h1>
  <p class="page-sub">Tools and references for developers and security researchers.</p>
  <div class="section-label">Security Testing</div>
  <div class="resource-card">
    <div class="rc-left">
      <div class="rc-icon"><i class="bi bi-file-text"></i></div>
      <div>
        <div class="rc-title">Common Web Path Wordlist</div>
        <div class="rc-desc">A curated list of common web application paths for endpoint discovery.</div>
        <div class="rc-meta"><span class="rc-tag">TXT</span><span class="rc-tag">wordlist</span><span>~30 entries</span></div>
      </div>
    </div>
    <a class="btn-dl" href="/wordlist.txt" download="wordlist.txt"><i class="bi bi-download"></i> Download</a>
  </div>
  <div class="resource-card">
    <div class="rc-left">
      <div class="rc-icon" style="background: rgba(16, 185, 129, 0.1);"><i class="bi bi-book"></i></div>
      <div>
        <div class="rc-title">API Reference</div>
        <div class="rc-desc">Full documentation for the <code style="background: var(--primary); padding: 2px 6px; border-radius: 4px; font-size: 0.8rem;">/api/fetch</code> endpoint.</div>
        <div class="rc-meta"><span class="rc-tag">JSON</span><span class="rc-tag">REST</span></div>
      </div>
    </div>
    <a class="btn-dl" href="#" style="background: var(--primary); border: 1px solid var(--border);"><i class="bi bi-arrow-right"></i> View Docs</a>
  </div>
</main>
<footer>
  <span class="footer-logo">Leagues<span>Of</span>Code</span>
  <span style="color: var(--text-muted); font-size: 0.85rem;">© 2026 Cybersecurity Bootcamp</span>
</footer>
</body>
</html>"""
    return Response(html, content_type='text/html')


@app.route('/wordlist.txt', methods=['GET'])
def download_wordlist():
    return send_file(WORDLIST_PATH, as_attachment=True, download_name='wordlist.txt', mimetype='text/plain')


@app.route('/fetch', methods=['POST'])
def fetch():
    data = request.get_json(silent=True)
    if not data or 'url' not in data:
        return jsonify({"error": "Missing 'url' in JSON body"}), 400
    url = data.get('url', '')
    try:
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8',
            'Accept-Language': 'en-US,en;q=0.9',
        }
        resp = requests.get(url, timeout=10, headers=headers, allow_redirects=True)
        raw  = resp.content
        content_type = resp.headers.get('Content-Type', '').lower().split(';')[0].strip()

        IMAGE_EXTS = ('.jpg', '.jpeg', '.png', '.gif', '.webp', '.bmp', '.ico', '.tiff')
        final_url  = resp.url.lower().split('?')[0]
        orig_url   = url.lower().split('?')[0]

        MAGIC = [
            (b'\xff\xd8\xff',      'image/jpeg'),
            (b'\x89PNG\r\n\x1a\n', 'image/png'),
            (b'GIF87a',            'image/gif'),
            (b'GIF89a',            'image/gif'),
            (b'RIFF',              'image/webp'),
            (b'\x00\x00\x01\x00', 'image/x-icon'),
            (b'BM',                'image/bmp'),
        ]
        EXT_MAP = {'.jpg': 'image/jpeg', '.jpeg': 'image/jpeg', '.png': 'image/png',
                   '.gif': 'image/gif', '.webp': 'image/webp',
                   '.bmp': 'image/bmp', '.ico': 'image/x-icon', '.tiff': 'image/tiff'}

        detected_mime = None
        if content_type.startswith('image/'):
            detected_mime = content_type
        if detected_mime is None:
            for ext in IMAGE_EXTS:
                if final_url.endswith(ext) or orig_url.endswith(ext):
                    detected_mime = EXT_MAP.get(ext, 'image/jpeg')
                    break
        if detected_mime is None:
            for magic, mime in MAGIC:
                if raw[:len(magic)] == magic:
                    if mime == 'image/webp' and raw[8:12] != b'WEBP':
                        continue
                    detected_mime = mime
                    break

        if detected_mime:
            img_b64 = base64.b64encode(raw).decode('utf-8')
            return jsonify({
                "status":       resp.status_code,
                "type":         "image",
                "content_type": detected_mime,
                "data":         img_b64
            })

        return jsonify({"status": resp.status_code, "type": "text", "body": resp.text})
    except Exception as e:
        return jsonify({"error": str(e)}), 500


@app.route('/internal/config')
def internal_config():
    if not is_internal(request):
        return jsonify({"error": "Forbidden"}), 403
    return jsonify({
        "message": CONGRATS,
        "database": {
            "host":     "db.internal.thaibank.local",
            "port":     5432,
            "name":     "thaibank_prod",
            "username": "db_admin",
            "password": "Sup3rS3cr3tP@ssw0rd!"
        },
        "api_keys": {
            "payment_gateway": "sk_prod_xK92mNpQ7vR3wL8j",
            "sms_service":     "sms_live_Tz4nB6cY1eA9dF2h"
        },
        "jwt_secret":  "jwt_HS256_9xP2mK7vN4bQ8wR1",
        "environment": "production"
    })


@app.errorhandler(404)
def not_found(e):
    return jsonify({"error": "Not found"}), 404


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=7070, debug=False)
