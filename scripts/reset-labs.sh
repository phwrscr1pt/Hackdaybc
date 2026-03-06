#!/bin/bash
#===============================================================================
# LeaguesOfCode Lab Portal - Reset Script
# Run this before each student session to ensure clean lab state
#===============================================================================

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
LAB_DIR="/home/loc/HackdayBc"
COMPOSE_FILE="$LAB_DIR/docker-compose.yml"

#-------------------------------------------------------------------------------
# Helper functions
#-------------------------------------------------------------------------------
print_header() {
    echo ""
    echo -e "${BLUE}========================================${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}========================================${NC}"
}

print_success() {
    echo -e "${GREEN}[✓]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[!]${NC} $1"
}

print_error() {
    echo -e "${RED}[✗]${NC} $1"
}

print_info() {
    echo -e "${BLUE}[i]${NC} $1"
}

#-------------------------------------------------------------------------------
# Check if running on the correct server
#-------------------------------------------------------------------------------
check_environment() {
    print_header "Checking Environment"

    if [ ! -d "$LAB_DIR" ]; then
        print_error "Lab directory not found: $LAB_DIR"
        echo "Make sure you're running this on the lab server (10.10.61.221)"
        exit 1
    fi

    if ! command -v docker &> /dev/null; then
        print_error "Docker not found"
        exit 1
    fi

    if ! command -v docker-compose &> /dev/null && ! docker compose version &> /dev/null; then
        print_error "Docker Compose not found"
        exit 1
    fi

    print_success "Environment check passed"
}

#-------------------------------------------------------------------------------
# Reset CSRF Bank Database
#-------------------------------------------------------------------------------
reset_csrf_bank() {
    print_header "Resetting CSRF Bank"

    # Check if container is running
    if ! docker ps --format '{{.Names}}' | grep -q 'loc_csrf_bank'; then
        print_warning "CSRF bank container not running, starting..."
        cd "$LAB_DIR" && docker-compose up -d loc_csrf_bank
        sleep 3
    fi

    # Reset database - database is at /tmp/bank.db
    # Structure: users table (id, username, password, account_no)
    #            accounts table (account_no, balance)
    #            transactions table
    docker exec loc_csrf_bank python3 -c "
import sqlite3
import os

db_path = '/tmp/bank.db'

# Initialize database if not exists
conn = sqlite3.connect(db_path)
cursor = conn.cursor()

# Create tables if they don't exist
cursor.execute('''CREATE TABLE IF NOT EXISTS users (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    username   TEXT NOT NULL UNIQUE,
    password   TEXT NOT NULL,
    account_no TEXT NOT NULL UNIQUE
)''')
cursor.execute('''CREATE TABLE IF NOT EXISTS accounts (
    account_no TEXT PRIMARY KEY,
    balance    REAL DEFAULT 0
)''')
cursor.execute('''CREATE TABLE IF NOT EXISTS transactions (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    from_acc  TEXT,
    to_acc    TEXT,
    amount    REAL,
    type      TEXT,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
)''')
conn.commit()

# Get current user count
cursor.execute('SELECT COUNT(*) FROM users')
before_count = cursor.fetchone()[0]

# Delete all users except somchai
cursor.execute('DELETE FROM users WHERE username != \"somchai\"')

# Delete all accounts except 1001
cursor.execute('DELETE FROM accounts WHERE account_no != \"1001\"')

# Reset somchai's balance to 1,000,000
cursor.execute('UPDATE accounts SET balance = 1000000 WHERE account_no = \"1001\"')

# Ensure somchai exists
existing = cursor.execute(\"SELECT id FROM users WHERE username = 'somchai'\").fetchone()
if not existing:
    cursor.execute(\"INSERT INTO users (username, password, account_no) VALUES ('somchai', 'password123', '1001')\")
    cursor.execute(\"INSERT OR REPLACE INTO accounts (account_no, balance) VALUES ('1001', 1000000.0)\")

# Reset account number sequence (next account will be 1002)
cursor.execute('DELETE FROM sqlite_sequence WHERE name=\"users\"')
cursor.execute('INSERT OR REPLACE INTO sqlite_sequence (name, seq) VALUES (\"users\", 1001)')

# Clear transaction history
cursor.execute('DELETE FROM transactions')

conn.commit()

# Get final count
cursor.execute('SELECT COUNT(*) FROM users')
after_count = cursor.fetchone()[0]

cursor.execute('SELECT u.username, a.balance, u.account_no FROM users u JOIN accounts a ON u.account_no = a.account_no')
users = cursor.fetchall()

conn.close()

print(f'Users before: {before_count}, after: {after_count}')
for u in users:
    print(f'  - {u[0]}: Account {u[2]}, Balance: {u[1]:,.0f} baht')
" 2>/dev/null

    if [ $? -eq 0 ]; then
        print_success "CSRF bank reset (somchai: ฿1,000,000)"
    else
        print_warning "CSRF bank reset may have issues - check manually"
    fi
}

#-------------------------------------------------------------------------------
# Reset SSH Lab
#-------------------------------------------------------------------------------
reset_ssh_lab() {
    print_header "Resetting SSH Lab"

    # Check if container is running
    if ! docker ps --format '{{.Names}}' | grep -q 'loc_ssh_lab'; then
        print_warning "SSH lab container not running, starting..."
        cd "$LAB_DIR" && docker-compose up -d loc_ssh_lab
        sleep 3
    fi

    # Remove any injected SSH keys from john's authorized_keys
    docker exec loc_ssh_lab bash -c "
        rm -f /home/john/.ssh/authorized_keys 2>/dev/null
        # Recreate empty file with correct permissions
        touch /home/john/.ssh/authorized_keys
        chmod 644 /home/john/.ssh/authorized_keys
        chown john:john /home/john/.ssh/authorized_keys
        echo 'Cleared john authorized_keys'
    " 2>/dev/null

    # Also clear noob's generated keys (optional - students will regenerate)
    docker exec loc_ssh_lab bash -c "
        rm -f /home/noob/.ssh/id_rsa /home/noob/.ssh/id_rsa.pub 2>/dev/null
        echo 'Cleared noob SSH keys'
    " 2>/dev/null

    print_success "SSH lab reset (john's authorized_keys cleared)"
}

#-------------------------------------------------------------------------------
# Reset File Upload Lab
#-------------------------------------------------------------------------------
reset_file_upload() {
    print_header "Resetting File Upload Lab"

    # Check if container is running
    if ! docker ps --format '{{.Names}}' | grep -q 'loc_file_upload'; then
        print_warning "File upload container not running, starting..."
        cd "$LAB_DIR" && docker-compose up -d loc_file_upload
        sleep 3
    fi

    # Clear uploaded files (use sh, not bash - alpine image)
    docker exec loc_file_upload sh -c "
        count=\$(ls -1 /app/uploads/ 2>/dev/null | wc -l)
        rm -rf /app/uploads/* 2>/dev/null
        echo \"Removed \$count uploaded files\"
    " 2>/dev/null

    print_success "File upload lab reset (uploads cleared)"
}

#-------------------------------------------------------------------------------
# Reset XSS Lab (clear any stored data if applicable)
#-------------------------------------------------------------------------------
reset_xss_lab() {
    print_header "Resetting XSS Lab"

    # XSS lab is stateless (reflected XSS), but restart for clean state
    if docker ps --format '{{.Names}}' | grep -q 'loc_xss_lab'; then
        docker restart loc_xss_lab > /dev/null 2>&1
        print_success "XSS lab restarted"
    else
        print_warning "XSS lab not running"
    fi
}

#-------------------------------------------------------------------------------
# Reset SSRF Lab
#-------------------------------------------------------------------------------
reset_ssrf_lab() {
    print_header "Resetting SSRF Lab"

    # SSRF lab is stateless, just restart for clean state
    if docker ps --format '{{.Names}}' | grep -q 'loc_ssrf_lab'; then
        docker restart loc_ssrf_lab > /dev/null 2>&1
        print_success "SSRF lab restarted"
    else
        print_warning "SSRF lab not running"
    fi
}

#-------------------------------------------------------------------------------
# Reset Main Portal Database (SQL/JWT labs)
#-------------------------------------------------------------------------------
reset_portal_db() {
    print_header "Resetting Portal Database (SQL/JWT)"

    # The main database doesn't need reset typically, but we can verify it
    if docker ps --format '{{.Names}}' | grep -q 'loc_db'; then
        # Just verify the database is accessible
        docker exec loc_db mysql -ulocadmin -plocpass123 -e "SELECT 'Database OK' as status;" leaguesofcode_db 2>/dev/null | grep -q "OK"
        if [ $? -eq 0 ]; then
            print_success "Portal database verified"
        else
            print_warning "Portal database may have issues"
        fi
    else
        print_warning "Database container not running"
    fi
}

#-------------------------------------------------------------------------------
# Verify all containers are running
#-------------------------------------------------------------------------------
verify_containers() {
    print_header "Verifying Containers"

    cd "$LAB_DIR"

    # Expected containers
    containers=(
        "loc_nginx"
        "loc_portal"
        "loc_db"
        "loc_file_upload"
        "loc_csrf_bank"
        "loc_csrf_evil"
        "loc_ssrf_lab"
        "loc_xss_lab"
        "loc_ssh_lab"
    )

    all_running=true

    for container in "${containers[@]}"; do
        if docker ps --format '{{.Names}}' | grep -q "^${container}$"; then
            print_success "$container is running"
        else
            print_error "$container is NOT running"
            all_running=false
        fi
    done

    if [ "$all_running" = false ]; then
        print_warning "Some containers are not running. Starting all..."
        docker-compose up -d
        sleep 5
        print_info "Containers started. Re-verifying..."
        for container in "${containers[@]}"; do
            if docker ps --format '{{.Names}}' | grep -q "^${container}$"; then
                print_success "$container is now running"
            else
                print_error "$container failed to start"
            fi
        done
    fi
}

#-------------------------------------------------------------------------------
# Quick connectivity test
#-------------------------------------------------------------------------------
test_connectivity() {
    print_header "Testing Lab Connectivity"

    endpoints=(
        "http://localhost/:Portal Home"
        "http://localhost/resources/login.php:SQL Login"
        "http://localhost/account/signin.php:JWT Signin"
        "http://localhost/profile/:File Upload"
        "http://localhost/share/:CSRF Bank"
        "http://localhost/evil/:CSRF Evil"
        "http://localhost/api/:SSRF API"
        "http://localhost/search/:XSS Search"
    )

    for endpoint in "${endpoints[@]}"; do
        url="${endpoint%%:*}"
        name="${endpoint##*:}"

        status=$(curl -s -o /dev/null -w '%{http_code}' "$url" --connect-timeout 5 2>/dev/null)

        if [ "$status" = "200" ]; then
            print_success "$name ($status)"
        elif [ "$status" = "302" ] || [ "$status" = "301" ]; then
            print_success "$name ($status redirect)"
        else
            print_error "$name (HTTP $status)"
        fi
    done

    # Test SSH port
    if timeout 2 bash -c 'echo > /dev/tcp/localhost/2222' 2>/dev/null; then
        print_success "SSH Lab (port 2222 open)"
    else
        print_error "SSH Lab (port 2222 closed)"
    fi
}

#-------------------------------------------------------------------------------
# Print summary
#-------------------------------------------------------------------------------
print_summary() {
    print_header "Reset Complete"

    echo ""
    echo "Lab environment has been reset. Students can now begin."
    echo ""
    echo "Student Starting Points:"
    echo "  - SSH Lab:    ssh noob@<IP> -p 2222  (password: noob)"
    echo "  - Web Labs:   http://<IP>/"
    echo "  - CSRF Bank:  Register at /share/ (somchai has ฿1,000,000)"
    echo "  - JWT Labs:   Login with john/password123 at /account/"
    echo ""
    echo "Instructor Materials:"
    echo "  - Walkthroughs: docs/walkthrough-*.md"
    echo "  - Student Handout: docs/student-handout.txt"
    echo ""
}

#-------------------------------------------------------------------------------
# Main
#-------------------------------------------------------------------------------
main() {
    echo ""
    echo -e "${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
    echo -e "${GREEN}║     LeaguesOfCode Lab Portal - Environment Reset          ║${NC}"
    echo -e "${GREEN}║                 Cybersecurity Bootcamp 2026                ║${NC}"
    echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}"

    check_environment
    reset_csrf_bank
    reset_ssh_lab
    reset_file_upload
    reset_xss_lab
    reset_ssrf_lab
    reset_portal_db
    verify_containers
    test_connectivity
    print_summary
}

# Run with optional flags
case "${1:-}" in
    --help|-h)
        echo "Usage: $0 [options]"
        echo ""
        echo "Options:"
        echo "  --help, -h     Show this help"
        echo "  --quick, -q    Quick reset (skip connectivity tests)"
        echo "  --verify, -v   Verify only (no reset)"
        echo ""
        exit 0
        ;;
    --quick|-q)
        check_environment
        reset_csrf_bank
        reset_ssh_lab
        reset_file_upload
        verify_containers
        print_summary
        ;;
    --verify|-v)
        check_environment
        verify_containers
        test_connectivity
        ;;
    *)
        main
        ;;
esac
