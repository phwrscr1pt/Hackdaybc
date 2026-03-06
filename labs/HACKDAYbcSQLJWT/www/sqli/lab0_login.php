<?php
require_once '../includes/header.php';
require_once '../config.php';

// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Set header for detection
header('X-Powered-By: PHP/8.2');

$error = '';
$success = false;
$user_data = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get user input WITHOUT sanitization
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // INSECURE: String concatenation
    $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $result = mysqli_query($conn, $query);

    // Show error if query fails
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

<div class="container py-4">
    <!-- Back Link -->
    <a href="/sqli/" class="btn btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back to Member Management
    </a>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <?php if ($success && $user_data): ?>
                <!-- Success Dashboard -->
                <div class="card bg-dark border-success">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-check-circle-fill me-2"></i>Login Successful
                        </h4>
                    </div>
                    <div class="card-body">
                        <h5 class="text-success">Welcome, <?php echo htmlspecialchars($user_data['username']); ?>!</h5>
                        <hr class="bg-secondary">
                        <p class="text-light">
                            <i class="bi bi-envelope me-2"></i>
                            Email: <?php echo htmlspecialchars($user_data['email']); ?>
                        </p>
                        <p class="text-light">
                            <i class="bi bi-person-badge me-2"></i>
                            Role: <span class="badge bg-primary"><?php echo htmlspecialchars($user_data['role']); ?></span>
                        </p>
                        <p class="text-light">
                            <i class="bi bi-clock me-2"></i>
                            Last Login: <?php echo date('Y-m-d H:i:s'); ?>
                        </p>
                        <hr class="bg-secondary">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            You have <strong>3</strong> pending leave requests.
                        </div>
                        <div class="mt-3">
                            <a href="lab0_login.php" class="btn btn-outline-light">
                                <i class="bi bi-box-arrow-left me-2"></i>Logout
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- Login Form -->
                <div class="card bg-dark border-primary">
                    <div class="card-header" style="background-color: #1a237e;">
                        <h4 class="mb-0 text-white">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Employee Login
                        </h4>
                        <small class="text-light">ระบบเข้าสู่ระบบสำหรับพนักงาน</small>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo $error; ?>
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
                                <i class="bi bi-box-arrow-in-right me-2"></i>เข้าสู่ระบบ
                            </button>
                        </form>
                        <hr class="bg-secondary">
                        <p class="text-center text-secondary mb-0">
                            <small><i class="bi bi-question-circle me-1"></i>ลืมรหัสผ่าน? ติดต่อฝ่าย IT</small>
                        </p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
