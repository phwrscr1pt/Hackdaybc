<?php
require_once 'auth_helper.php';
require_once '../config.php';

$error = '';
$success = false;
$token = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $query = "SELECT * FROM accounts WHERE username = ? AND password = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $password);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
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
        setcookie('auth_token', $token, time() + 3600, '/');
        $success = true;
    } else {
        $error = "Invalid credentials";
    }
}

require_once '../includes/header.php';
?>

<div class="container py-5">
    <a href="/account/" class="btn btn-outline-secondary mb-4" style="border-color: var(--border); color: var(--text-muted);">
        <i class="bi bi-arrow-left me-2"></i>Back to Account Portal
    </a>

    <div class="row justify-content-center">
        <div class="col-md-5">
            <?php if ($success): ?>
                <div class="card" style="border-color: #10b981;">
                    <div class="card-header" style="background: linear-gradient(135deg, #10b981, #34d399);">
                        <h4 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i>Login Successful</h4>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="bi bi-key me-2"></i><strong>JWT Token Generated!</strong>
                            <p class="mt-2 mb-0 small">Your token has been stored in a cookie. Check DevTools → Application → Cookies.</p>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Your JWT Token:</label>
                            <textarea class="form-control" rows="4" readonly style="background: var(--primary); color: #10b981; font-family: monospace; font-size: 0.85rem;"><?php echo $token; ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="portal.php" class="btn btn-primary"><i class="bi bi-speedometer2 me-1"></i>Go to Dashboard</a>
                            <a href="signin.php" class="btn btn-outline-secondary"><i class="bi bi-box-arrow-left me-1"></i>Logout</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-person-circle me-2"></i>Account Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label"><i class="bi bi-person me-1"></i>Username</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label"><i class="bi bi-key me-1"></i>Password</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><i class="bi bi-box-arrow-in-right me-2"></i>Sign In</button>
                        </form>
                        <hr style="border-color: var(--border);">
                        <p class="text-center mb-0" style="color: var(--text-muted);"><small><i class="bi bi-info-circle me-1"></i>New user? Contact administrator</small></p>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-body">
                        <h6 style="color: var(--text-muted);"><i class="bi bi-info-circle me-1"></i>Test Accounts</h6>
                        <div class="small">
                            <code>john / password123</code> (user)<br>
                            <code>wiener / peter</code> (user)
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
