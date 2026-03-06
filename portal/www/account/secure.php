<?php
require_once 'auth_helper_weak.php';
require_once '../includes/header.php';
require_once '../config.php';

$error = '';
$success = '';
$token = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        // Check credentials against database
        $stmt = $conn->prepare("SELECT * FROM accounts WHERE username = ? AND password = ?");
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            // Create JWT with weak signing key
            $payload = [
                'sub' => $user['id'],
                'user' => $user['username'],
                'role' => $user['role'],
                'email' => $user['email'],
                'iat' => time(),
                'exp' => time() + 3600  // 1 hour
            ];

            $token = create_jwt_weak($payload);
            setcookie('auth_token_secure', $token, time() + 3600, '/');
            $success = "Login successful! JWT token has been set.";
        } else {
            $error = "Invalid username or password";
        }
    } else {
        $error = "Please enter both username and password";
    }
}
?>

<div class="container py-4">
    <!-- Back Link -->
    <a href="/account/" class="btn btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back to Account
    </a>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card bg-dark border-info">
                <div class="card-header" style="background-color: #1a237e;">
                    <h4 class="mb-0 text-white">
                        <i class="bi bi-shield-lock me-2"></i>Secure Login
                    </h4>
                    <small class="text-light">Lab 4: Weak Signing Key Challenge</small>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($success); ?>
                        </div>

                        <div class="card bg-dark border-secondary mb-3">
                            <div class="card-body">
                                <h6 class="text-secondary">Your JWT Token:</h6>
                                <textarea class="form-control bg-dark text-info" rows="4" readonly><?php echo htmlspecialchars($token); ?></textarea>
                            </div>
                        </div>

                        <a href="admin.php" class="btn btn-primary w-100">
                            <i class="bi bi-speedometer2 me-2"></i>Go to Secure Dashboard
                        </a>
                    <?php else: ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label text-light">
                                    <i class="bi bi-person me-1"></i>Username
                                </label>
                                <input type="text" class="form-control" id="username" name="username"
                                       placeholder="Enter username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label text-light">
                                    <i class="bi bi-key me-1"></i>Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Enter password" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Login
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Challenge Info -->
            <div class="card bg-dark border-warning mt-4">
                <div class="card-body">
                    <h6 class="text-warning">
                        <i class="bi bi-exclamation-triangle me-1"></i>Challenge Info
                    </h6>
                    <p class="text-light small mb-2">
                        This lab uses JWT with <strong>signature verification enabled</strong>.
                        The "none" algorithm bypass won't work here.
                    </p>
                    <p class="text-light small mb-0">
                        However, the signing key might be weak and vulnerable to
                        <strong>offline brute-force attacks</strong>.
                    </p>
                </div>
            </div>

            <!-- Test Accounts -->
            <div class="card bg-dark border-secondary mt-3">
                <div class="card-body">
                    <h6 class="text-secondary">
                        <i class="bi bi-info-circle me-1"></i>Test Accounts
                    </h6>
                    <div class="text-light small">
                        <code>john / password123</code> (user)<br>
                        <code>wiener / peter</code> (user)
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
