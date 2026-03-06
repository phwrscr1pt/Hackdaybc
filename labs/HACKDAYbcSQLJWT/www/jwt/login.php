<?php
require_once 'jwt_helper.php';
require_once '../config.php';
require_once '../includes/header.php';

$error = '';
$success = false;
$token = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Check credentials (plaintext comparison for lab)
    $query = "SELECT * FROM accounts WHERE username = ? AND password = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        // Create JWT payload
        $payload = [
            'sub' => $user['id'],
            'user' => $user['username'],
            'role' => $user['role'],
            'email' => $user['email'],
            'secret_message' => $user['secret_message'],
            'iat' => time(),
            'exp' => time() + 3600
        ];

        $token = create_jwt($payload);

        // Set cookie
        setcookie('auth_token', $token, time() + 3600, '/');

        $success = true;
    } else {
        $error = "Invalid credentials";
    }
}
?>

<div class="container py-4">
    <!-- Back Link -->
    <a href="/jwt/" class="btn btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back to Account Services
    </a>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php if ($success): ?>
                <!-- Success -->
                <div class="card bg-dark border-success">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-check-circle-fill me-2"></i>Login Successful
                        </h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-key me-2"></i>
                            <strong>JWT Token Generated!</strong>
                            <p class="mt-2 mb-0 small">
                                Your token has been stored in a cookie. Check DevTools → Application → Cookies.
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-light">Your JWT Token:</label>
                            <textarea class="form-control bg-dark text-success" rows="4" readonly><?php echo $token; ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <a href="dashboard.php" class="btn btn-primary">
                                <i class="bi bi-speedometer2 me-1"></i>Go to Dashboard
                            </a>
                            <a href="login.php" class="btn btn-outline-secondary">
                                <i class="bi bi-box-arrow-left me-1"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Login Form -->
                <div class="card bg-dark border-primary">
                    <div class="card-header" style="background-color: #1a237e;">
                        <h4 class="mb-0 text-white">
                            <i class="bi bi-person-circle me-2"></i>Account Login
                        </h4>
                        <small class="text-light">เข้าสู่ระบบ Account Management</small>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="username" class="form-label text-light">
                                    <i class="bi bi-person me-1"></i>Username
                                </label>
                                <input type="text" class="form-control" id="username" name="username"
                                       placeholder="Enter your username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label text-light">
                                    <i class="bi bi-key me-1"></i>Password
                                </label>
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Enter your password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label text-light" for="remember">Remember me</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-box-arrow-in-right me-2"></i>Sign In
                            </button>
                        </form>
                        <hr class="bg-secondary">
                        <p class="text-center text-secondary mb-0">
                            <small><i class="bi bi-info-circle me-1"></i>New user? Contact administrator</small>
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
                            <code>wiener / peter</code> (user)<br>
                            <code>admin / admin</code> (administrator)
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
