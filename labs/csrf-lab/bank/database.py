import sqlite3
from flask import g
import os

DATABASE = '/tmp/bank.db'

def get_db():
    db = getattr(g, '_database', None)
    if db is None:
        db = g._database = sqlite3.connect(DATABASE)
        db.row_factory = sqlite3.Row
    return db

def teardown_db(exception):
    db = getattr(g, '_database', None)
    if db is not None:
        db.close()

def init_db_once():
    """Run once at startup outside of request context."""
    conn = sqlite3.connect(DATABASE)
    conn.row_factory = sqlite3.Row
    conn.execute('''CREATE TABLE IF NOT EXISTS users (
        id         INTEGER PRIMARY KEY AUTOINCREMENT,
        username   TEXT NOT NULL UNIQUE,
        password   TEXT NOT NULL,
        account_no TEXT NOT NULL UNIQUE
    )''')
    conn.execute('''CREATE TABLE IF NOT EXISTS accounts (
        account_no TEXT PRIMARY KEY,
        balance    REAL DEFAULT 0
    )''')
    conn.execute('''CREATE TABLE IF NOT EXISTS transactions (
        id        INTEGER PRIMARY KEY AUTOINCREMENT,
        from_acc  TEXT,
        to_acc    TEXT,
        amount    REAL,
        type      TEXT,
        timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
    )''')
    conn.commit()

    # Seed somchai if not exists
    existing = conn.execute("SELECT id FROM users WHERE username = 'somchai'").fetchone()
    if not existing:
        conn.execute(
            "INSERT INTO users (username, password, account_no) VALUES ('somchai', 'password123', '1001')"
        )
        conn.execute("INSERT INTO accounts (account_no, balance) VALUES ('1001', 1000000.0)")
        conn.commit()
    conn.close()
