from flask import Flask, render_template, request, redirect, session
from database import init_db_once, get_db, teardown_db
import sqlite3
import time

app = Flask(__name__)
app.secret_key = "supersecretkey"

# Intentionally vulnerable session config
app.config['SESSION_COOKIE_SAMESITE'] = None
app.config['SESSION_COOKIE_HTTPONLY'] = False
app.config['SESSION_COOKIE_SECURE'] = False

app.teardown_appcontext(teardown_db)

def login_required(f):
    from functools import wraps
    @wraps(f)
    def decorated(*args, **kwargs):
        if 'user_id' not in session:
            return redirect('./')
        return f(*args, **kwargs)
    return decorated

@app.route('/')
def index():
    if 'user_id' in session:
        return redirect('dashboard')
    return render_template('index.html')

@app.route('/login', methods=['POST'])
def login():
    username = request.form.get('username', '').strip()
    password = request.form.get('password', '').strip()
    db = get_db()
    user = db.execute(
        'SELECT * FROM users WHERE username = ? AND password = ?',
        (username, password)
    ).fetchone()
    if user:
        session['user_id']    = user['id']
        session['username']   = user['username']
        session['account_no'] = user['account_no']
        return redirect('dashboard')
    return render_template('index.html', error='ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง')

@app.route('/register', methods=['POST'])
def register():
    username = request.form.get('username', '').strip()
    password = request.form.get('password', '').strip()
    if not username or not password:
        return render_template('index.html', reg_error='กรุณากรอกข้อมูลให้ครบ')
    db = get_db()
    existing = db.execute('SELECT id FROM users WHERE username = ?', (username,)).fetchone()
    if existing:
        return render_template('index.html', reg_error='ชื่อผู้ใช้นี้ถูกใช้แล้ว')

    # Fix race condition: retry up to 3 times if account number conflicts
    for attempt in range(3):
        last = db.execute('SELECT MAX(CAST(account_no AS INT)) FROM users').fetchone()[0]
        new_acc = str((last or 1000) + 1)
        try:
            db.execute(
                'INSERT INTO users (username, password, account_no) VALUES (?, ?, ?)',
                (username, password, new_acc)
            )
            db.execute('INSERT INTO accounts (account_no, balance) VALUES (?, ?)', (new_acc, 0.0))
            db.commit()
            return render_template('index.html', reg_success=f'สมัครสำเร็จ! เลขบัญชีของคุณคือ {new_acc}')
        except sqlite3.IntegrityError:
            # Account number conflict, retry with small delay
            time.sleep(0.1)
            continue

    return render_template('index.html', reg_error='การสมัครล้มเหลว กรุณาลองใหม่อีกครั้ง')

@app.route('/logout')
def logout():
    session.clear()
    return redirect('./')

@app.route('/dashboard')
@login_required
def dashboard():
    db = get_db()
    acc = db.execute(
        'SELECT balance FROM accounts WHERE account_no = ?',
        (session['account_no'],)
    ).fetchone()
    txns = db.execute(
        '''SELECT * FROM transactions
           WHERE from_acc = ? OR to_acc = ?
           ORDER BY timestamp DESC LIMIT 10''',
        (session['account_no'], session['account_no'])
    ).fetchall()
    return render_template('dashboard.html', balance=acc['balance'], transactions=txns)

@app.route('/transfer', methods=['GET', 'POST'])
@login_required
def transfer():
    if request.method == 'POST':
        to_account = request.form.get('to_account', '').strip()
        try:
            amount = float(request.form.get('amount', 0))
        except ValueError:
            return render_template('transfer.html', error='จำนวนเงินไม่ถูกต้อง')

        db = get_db()
        from_acc = session['account_no']
        sender   = db.execute('SELECT balance FROM accounts WHERE account_no = ?', (from_acc,)).fetchone()
        receiver = db.execute('SELECT account_no FROM accounts WHERE account_no = ?', (to_account,)).fetchone()

        if not receiver:
            return render_template('transfer.html', error='ไม่พบเลขบัญชีปลายทาง')
        if amount <= 0:
            return render_template('transfer.html', error='จำนวนเงินต้องมากกว่า 0')
        if sender['balance'] < amount:
            return render_template('transfer.html', error='ยอดเงินไม่เพียงพอ')
        if from_acc == to_account:
            return render_template('transfer.html', error='ไม่สามารถโอนให้ตัวเองได้')

        db.execute('UPDATE accounts SET balance = balance - ? WHERE account_no = ?', (amount, from_acc))
        db.execute('UPDATE accounts SET balance = balance + ? WHERE account_no = ?', (amount, to_account))
        db.execute(
            'INSERT INTO transactions (from_acc, to_acc, amount, type) VALUES (?, ?, ?, ?)',
            (from_acc, to_account, amount, 'transfer')
        )
        db.commit()
        return redirect('dashboard')
    return render_template('transfer.html')

@app.route('/deposit', methods=['GET', 'POST'])
@login_required
def deposit():
    if request.method == 'POST':
        try:
            amount = float(request.form.get('amount', 0))
        except ValueError:
            return render_template('deposit.html', error='จำนวนเงินไม่ถูกต้อง')
        if amount <= 0:
            return render_template('deposit.html', error='จำนวนเงินต้องมากกว่า 0')
        db  = get_db()
        acc = session['account_no']
        db.execute('UPDATE accounts SET balance = balance + ? WHERE account_no = ?', (amount, acc))
        db.execute(
            'INSERT INTO transactions (from_acc, to_acc, amount, type) VALUES (?, ?, ?, ?)',
            (acc, acc, amount, 'deposit')
        )
        db.commit()
        return redirect('dashboard')
    return render_template('deposit.html')

@app.route('/withdraw', methods=['GET', 'POST'])
@login_required
def withdraw():
    if request.method == 'POST':
        try:
            amount = float(request.form.get('amount', 0))
        except ValueError:
            return render_template('withdraw.html', error='จำนวนเงินไม่ถูกต้อง')
        db  = get_db()
        acc = session['account_no']
        bal = db.execute('SELECT balance FROM accounts WHERE account_no = ?', (acc,)).fetchone()
        if amount <= 0:
            return render_template('withdraw.html', error='จำนวนเงินต้องมากกว่า 0')
        if bal['balance'] < amount:
            return render_template('withdraw.html', error='ยอดเงินไม่เพียงพอ')
        db.execute('UPDATE accounts SET balance = balance - ? WHERE account_no = ?', (amount, acc))
        db.execute(
            'INSERT INTO transactions (from_acc, to_acc, amount, type) VALUES (?, ?, ?, ?)',
            (acc, acc, amount, 'withdraw')
        )
        db.commit()
        return redirect('dashboard')
    return render_template('withdraw.html')

if __name__ == '__main__':
    init_db_once()
    app.run(host='0.0.0.0', port=5000, debug=False)
