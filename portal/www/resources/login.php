<?php
require_once '../includes/header.php';
require_once '../config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('X-Powered-By: PHP/8.2');

$error = '';
$success = false;
$user_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        $error = "Database Error: " . mysqli_error($conn);
    } else {
        if (mysqli_num_rows($result) > 0) {
            $user_data = mysqli_fetch_assoc($result);
            $success = true;
        } else {
            $error = "Invalid credentials. Please try again.";
        }
    }
}
?>

<div class="container py-5">
    <a href="/resources/" class="btn btn-outline-secondary mb-4" style="border-color: var(--border); color: var(--text-muted);">
        <i class="bi bi-arrow-left me-2"></i>Back to Resources
    </a>

    <div class="row justify-content-center">
        <div class="col-md-5">
            <?php if ($success && $user_data): ?>
                <div class="card" style="border-color: #10b981;">
                    <div class="card-header" style="background: linear-gradient(135deg, #10b981, #34d399);">
                        <h4 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i>Login Successful</h4>
                    </div>
                    <div class="card-body">
                        <h5 style="color: #10b981;">Welcome, <?php echo htmlspecialchars($user_data['username']); ?>!</h5>
                        <hr style="border-color: var(--border);">
                        <p><i class="bi bi-envelope me-2" style="color: var(--accent);"></i>Email: <?php echo htmlspecialchars($user_data['email']); ?></p>
                        <p><i class="bi bi-person-badge me-2" style="color: var(--accent);"></i>Role: <span class="badge" style="background: var(--accent);"><?php echo htmlspecialchars($user_data['role']); ?></span></p>
                        <p><i class="bi bi-clock me-2" style="color: var(--accent);"></i>Last Login: <?php echo date('Y-m-d H:i:s'); ?></p>
                        <hr style="border-color: var(--border);">
                        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>You have <strong>3</strong> pending leave requests.</div>
                        <a href="lab0_login.php" class="btn btn-outline-light"><i class="bi bi-box-arrow-left me-2"></i>Logout</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-box-arrow-in-right me-2"></i>Employee Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?></div>
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
                        <p class="text-center mb-0" style="color: var(--text-muted);"><small><i class="bi bi-question-circle me-1"></i>Forgot password? Contact IT</small></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
