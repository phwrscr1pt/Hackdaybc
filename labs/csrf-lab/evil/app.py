from flask import Flask, render_template, request, jsonify, Response
import os

app = Flask(__name__)

PAYLOAD_FILE = '/tmp/evil.html'

@app.route('/')
def index():
    current = ''
    if os.path.exists(PAYLOAD_FILE):
        with open(PAYLOAD_FILE, 'r') as f:
            current = f.read()
    return render_template('builder.html', current=current)

@app.route('/store', methods=['POST'])
def store():
    payload = request.form.get('payload', '')
    with open(PAYLOAD_FILE, 'w') as f:
        f.write(payload)
    return jsonify({"status": "ok"})

@app.route('/site')
def site():
    if not os.path.exists(PAYLOAD_FILE):
        return Response('<h2>No payload stored yet.</h2>', content_type='text/html')
    with open(PAYLOAD_FILE, 'r') as f:
        content = f.read()
    return Response(content, content_type='text/html')

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=9999, debug=False)
