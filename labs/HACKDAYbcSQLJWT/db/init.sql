-- LeaguesOfCode Lab Portal Database
-- Cybersecurity Bootcamp #1

-- =============================================
-- USERS TABLE (SQLi Lab 0, 1)
-- =============================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(20) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role VARCHAR(20) DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password, email, role) VALUES
('admin', 'supersecretadmin123', 'admin@leaguesofcode.com', 'admin'),
('john', 'password123', 'john@leaguesofcode.com', 'user'),
('jane', 'jane2024', 'jane@leaguesofcode.com', 'user'),
('bob', 'bob2024!', 'bob@leaguesofcode.com', 'user');

-- =============================================
-- MEMBERS TABLE (SQLi Lab 2 - UNION)
-- =============================================
CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    department VARCHAR(50),
    status VARCHAR(20) DEFAULT 'active'
);

INSERT INTO members (name, email, department, status) VALUES
('John Doe', 'john.doe@leaguesofcode.com', 'Engineering', 'active'),
('Jane Smith', 'jane.smith@leaguesofcode.com', 'Marketing', 'active'),
('Bob Wilson', 'bob.wilson@leaguesofcode.com', 'Finance', 'active'),
('Alice Brown', 'alice.brown@leaguesofcode.com', 'Engineering', 'active'),
('Charlie Davis', 'charlie.davis@leaguesofcode.com', 'HR', 'inactive');

-- =============================================
-- SECRET_DATA TABLE (Target for extraction)
-- =============================================
CREATE TABLE secret_data (
    id INT AUTO_INCREMENT PRIMARY KEY,
    data_type VARCHAR(50),
    data_value VARCHAR(255),
    description TEXT
);

INSERT INTO secret_data (data_type, data_value, description) VALUES
('admin_pin', '1337', 'Administrator PIN code for system access'),
('api_key', 'sk_live_abc123xyz789', 'Production API Key - DO NOT SHARE'),
('db_password', 'sup3rs3cr3tDBp@ss', 'Database backup administrator password'),
('encryption_key', 'AES256_K3Y_2026!', 'Data encryption master key');

-- =============================================
-- INVENTORY TABLE (SQLi Lab 3 - Error Based)
-- =============================================
CREATE TABLE inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_name VARCHAR(100),
    category VARCHAR(50),
    quantity INT,
    location VARCHAR(50)
);

INSERT INTO inventory (item_name, category, quantity, location) VALUES
('Laptop Dell XPS 15', 'Electronics', 25, 'Warehouse A - Shelf 1'),
('Monitor LG 27" 4K', 'Electronics', 50, 'Warehouse A - Shelf 2'),
('Keyboard Mechanical RGB', 'Accessories', 100, 'Warehouse B - Shelf 1'),
('Mouse Logitech MX Master', 'Accessories', 75, 'Warehouse B - Shelf 1'),
('USB-C Hub 7-in-1', 'Accessories', 200, 'Warehouse B - Shelf 3'),
('Webcam Logitech C920', 'Electronics', 30, 'Warehouse A - Shelf 3'),
('Headset Sony WH-1000XM5', 'Audio', 45, 'Warehouse C - Shelf 1');

-- =============================================
-- PARTNERS TABLE (SQLi Lab 4 - Blind)
-- =============================================
CREATE TABLE partners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    partner_code VARCHAR(20) NOT NULL,
    company_name VARCHAR(100),
    secret_key VARCHAR(50),
    contact_email VARCHAR(100),
    status VARCHAR(20) DEFAULT 'active'
);

INSERT INTO partners (partner_code, company_name, secret_key, contact_email, status) VALUES
('LOC001', 'TechCorp Thailand', 's3cr3tP@ss', 'contact@techcorp.co.th', 'active'),
('LOC002', 'Digital Solutions', 'p@ssw0rd123', 'info@digisol.com', 'active'),
('LOC003', 'Cloud Systems Ltd', 'cl0udK3y!', 'support@cloudsys.io', 'inactive');

-- =============================================
-- BOOKS TABLE (SQLi Lab 5 - SQLMap)
-- =============================================
CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200),
    author VARCHAR(100),
    category VARCHAR(50),
    isbn VARCHAR(20),
    available BOOLEAN DEFAULT true
);

INSERT INTO books (title, author, category, isbn, available) VALUES
('Clean Code', 'Robert C. Martin', 'Programming', '978-0132350884', true),
('The Pragmatic Programmer', 'David Thomas', 'Programming', '978-0135957059', true),
('Design Patterns', 'Gang of Four', 'Software Engineering', '978-0201633610', true),
('Introduction to Algorithms', 'Thomas H. Cormen', 'Computer Science', '978-0262033848', false),
('Web Application Security', 'Andrew Hoffman', 'Security', '978-1492053118', true);

-- =============================================
-- ACCOUNTS TABLE (JWT Labs)
-- =============================================
CREATE TABLE accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) DEFAULT 'user',
    email VARCHAR(100),
    secret_message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO accounts (username, password, role, email, secret_message) VALUES
('admin', 'admin', 'administrator', 'admin@leaguesofcode.com', 'Welcome Administrator! Server Key: MASTER_KEY_2026'),
('john', 'password123', 'user', 'john@leaguesofcode.com', 'Standard user account - limited access'),
('wiener', 'peter', 'user', 'wiener@leaguesofcode.com', 'Test account for security training'),
('carlos', 'montoya', 'user', 'carlos@leaguesofcode.com', 'Secondary test account');

-- =============================================
-- GRANT PERMISSIONS
-- =============================================
GRANT ALL PRIVILEGES ON leaguesofcode_db.* TO 'locadmin'@'%';
FLUSH PRIVILEGES;
