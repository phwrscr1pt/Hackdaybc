<?php
require_once 'jwt_helper.php';
require_once '../includes/header.php';

$token = $_COOKIE['auth_token'] ?? '';

if (empty($token)) {
    header('Location: login.php');
    exit;
}

// INSECURE: Decode without verifying signature!
$decoded = decode_jwt($token);
$payload = $decoded['payload'] ?? [];

// Trust the payload directly
$username = $payload['user'] ?? 'Unknown';
$role = $payload['role'] ?? 'user';
$email = $payload['email'] ?? '';
$secret_message = $payload['secret_message'] ?? '';
$exp = $payload['exp'] ?? 0;
?>

<div class="container py-4">
    <!-- Navbar with user info -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="/jwt/" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
        <div class="text-light">
            <i class="bi bi-person-circle me-1"></i>
            Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong>
            <span class="badge bg-<?php echo $role === 'administrator' ? 'warning text-dark' : 'info'; ?> ms-2">
                <?php echo htmlspecialchars($role); ?>
            </span>
        </div>
        <a href="login.php" class="btn btn-outline-danger btn-sm" onclick="document.cookie='auth_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
        </a>
    </div>

    <?php if ($role === 'administrator'): ?>
        <!-- Admin Panel -->
        <div class="card bg-dark border-warning">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">
                    <i class="bi bi-shield-lock me-2"></i>Admin Panel
                </h4>
                <small>User Management System</small>
            </div>
            <div class="card-body">
                <p class="text-warning"><strong>Welcome, Administrator!</strong></p>
                <p class="text-light">
                    <i class="bi bi-envelope me-2"></i>Email: <?php echo htmlspecialchars($email); ?>
                </p>
                <p class="text-light">
                    <i class="bi bi-chat-quote me-2"></i>Secret Message: <span class="text-success"><?php echo htmlspecialchars($secret_message); ?></span>
                </p>
                <hr class="bg-secondary">
                <h5 class="text-warning"><i class="bi bi-gear me-2"></i>Admin Tools</h5>
                <ul class="text-light">
                    <li>Total Users: <strong>156</strong></li>
                    <li>Active Sessions: <strong>23</strong></li>
                    <li>Server Status: <span class="text-success">Online</span></li>
                </ul>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <strong>Admin Access Granted</strong><br>
                    Server Key: <code>MASTER_KEY_2026</code>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- User Dashboard -->
        <div class="card bg-dark border-secondary">
            <div class="card-header bg-secondary text-white">
                <h4 class="mb-0">
                    <i class="bi bi-person me-2"></i>User Dashboard
                </h4>
                <small>Welcome, <?php echo htmlspecialchars($username); ?></small>
            </div>
            <div class="card-body">
                <p class="text-light">
                    <i class="bi bi-envelope me-2"></i>Email: <?php echo htmlspecialchars($email); ?>
                </p>
                <p class="text-light">
                    <i class="bi bi-person-badge me-2"></i>Role: <span class="badge bg-info"><?php echo htmlspecialchars($role); ?></span>
                </p>
                <hr class="bg-secondary">
                <h5 class="text-light"><i class="bi bi-bar-chart me-2"></i>Quick Stats</h5>
                <ul class="text-light">
                    <li>Competitions Joined: <strong>3</strong></li>
                    <li>Bootcamps Completed: <strong>2</strong></li>
                </ul>
                <p class="text-muted">
                    <i class="bi bi-lock me-1"></i>You don't have admin access.
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Token Info -->
    <div class="card bg-dark border-secondary mt-4">
        <div class="card-body">
            <h6 class="text-secondary">
                <i class="bi bi-key me-1"></i>Token Information
            </h6>
            <p class="text-light small mb-1">
                <strong>Expires:</strong> <?php echo date('Y-m-d H:i:s', $exp); ?>
            </p>
            <p class="text-light small mb-0">
                <strong>Current Token:</strong>
            </p>
            <textarea class="form-control bg-dark text-info mt-2" rows="3" readonly><?php echo htmlspecialchars($token); ?></textarea>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
