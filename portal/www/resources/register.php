<?php
require_once '../includes/header.php';
require_once '../config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $email = $_POST['email'];

    if ($password !== $confirm_password) {
        $error = "Passwords do not match!";
    } else {
        $check = "SELECT * FROM users WHERE username = '$username'";
        $result = mysqli_query($conn, $check);

        if (!$result) {
            $error = "Database Error: " . mysqli_error($conn);
        } elseif (mysqli_num_rows($result) > 0) {
            $error = "Username already exists!";
        } else {
            $insert = "INSERT INTO users (username, password, email) VALUES ('$username', '$password', '$email')";
            if (mysqli_query($conn, $insert)) {
                $success = "Registration successful! Please login.";
            } else {
                $error = "Database Error: " . mysqli_error($conn);
            }
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
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #10b981, #34d399);">
                    <h4 class="mb-0"><i class="bi bi-person-plus me-2"></i>HR Registration</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i><?php echo $success; ?><br><br>
                            <a href="lab0_login.php" class="btn btn-success btn-sm"><i class="bi bi-box-arrow-in-right me-1"></i>Go to Login</a>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="username" class="form-label"><i class="bi bi-person me-1"></i>Username</label>
                            <input type="text" class="form-control" id="username" name="username" maxlength="30" placeholder="Enter username" required>
                            <small style="color: var(--text-muted);">Maximum 30 characters</small>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label"><i class="bi bi-key me-1"></i>Password</label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label"><i class="bi bi-key-fill me-1"></i>Confirm Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm password" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label"><i class="bi bi-envelope me-1"></i>Email</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Enter email address" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" style="background: linear-gradient(135deg, #10b981, #34d399); border: none;"><i class="bi bi-person-plus me-2"></i>Register</button>
                    </form>
                    <hr style="border-color: var(--border);">
                    <p class="text-center mb-0" style="color: var(--text-muted);"><small>Already have an account? <a href="lab0_login.php" style="color: var(--accent);">Login here</a></small></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
