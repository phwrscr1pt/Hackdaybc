<?php
require_once 'auth_helper.php';

$token = $_COOKIE['auth_token'] ?? '';

if (empty($token)) {
    header('Location: signin.php');
    exit;
}

require_once '../includes/header.php';

$decoded = decode_jwt($token);
$payload = $decoded['payload'] ?? [];

$username = $payload['user'] ?? 'Unknown';
$role = $payload['role'] ?? 'user';
$email = $payload['email'] ?? '';
$secret_message = $payload['secret_message'] ?? '';
$exp = $payload['exp'] ?? 0;
?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="/account/" class="btn btn-outline-secondary" style="border-color: var(--border); color: var(--text-muted);"><i class="bi bi-arrow-left me-2"></i>Back</a>
        <div>
            <i class="bi bi-person-circle me-1" style="color: var(--accent);"></i>
            Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong>
            <span class="badge ms-2" style="background: <?php echo $role === 'administrator' ? 'var(--gold)' : 'var(--accent)'; ?>; color: <?php echo $role === 'administrator' ? '#000' : '#fff'; ?>;"><?php echo htmlspecialchars($role); ?></span>
        </div>
        <a href="signin.php" class="btn btn-outline-danger btn-sm" onclick="document.cookie='auth_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
    </div>

    <?php if ($role === 'administrator'): ?>
        <div class="card" style="border-color: var(--gold);">
            <div class="card-header" style="background: linear-gradient(135deg, var(--gold), #fbbf24); color: #000;">
                <h4 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Admin Panel</h4>
            </div>
            <div class="card-body">
                <p style="color: var(--gold);"><strong>Welcome, Administrator!</strong></p>
                <p><i class="bi bi-envelope me-2" style="color: var(--accent);"></i>Email: <?php echo htmlspecialchars($email); ?></p>
                <p><i class="bi bi-chat-quote me-2" style="color: var(--accent);"></i>Secret Message: <span style="color: #10b981;"><?php echo htmlspecialchars($secret_message); ?></span></p>
                <hr style="border-color: var(--border);">
                <h5 style="color: var(--gold);"><i class="bi bi-gear me-2"></i>Admin Tools</h5>
                <ul>
                    <li>Total Users: <strong>156</strong></li>
                    <li>Active Sessions: <strong>23</strong></li>
                    <li>Server Status: <span style="color: #10b981;">Online</span></li>
                </ul>
                <div class="alert alert-success"><i class="bi bi-check-circle-fill me-2"></i><strong>Admin Access Granted</strong><br>Server Key: <code>MASTER_KEY_2026</code></div>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #64748b, #94a3b8);">
                <h4 class="mb-0"><i class="bi bi-person me-2"></i>User Dashboard</h4>
            </div>
            <div class="card-body">
                <p><i class="bi bi-envelope me-2" style="color: var(--accent);"></i>Email: <?php echo htmlspecialchars($email); ?></p>
                <p><i class="bi bi-person-badge me-2" style="color: var(--accent);"></i>Role: <span class="badge" style="background: var(--accent);"><?php echo htmlspecialchars($role); ?></span></p>
                <hr style="border-color: var(--border);">
                <h5><i class="bi bi-bar-chart me-2"></i>Quick Stats</h5>
                <ul>
                    <li>Competitions Joined: <strong>3</strong></li>
                    <li>Bootcamps Completed: <strong>2</strong></li>
                </ul>
                <p style="color: var(--text-muted);"><i class="bi bi-lock me-1"></i>You don't have admin access.</p>
            </div>
        </div>
    <?php endif; ?>

    <div class="card mt-4">
        <div class="card-body">
            <h6 style="color: var(--text-muted);"><i class="bi bi-key me-1"></i>Token Information</h6>
            <p class="small mb-1"><strong>Expires:</strong> <?php echo date('Y-m-d H:i:s', $exp); ?></p>
            <p class="small mb-0"><strong>Current Token:</strong></p>
            <textarea class="form-control mt-2" rows="3" readonly style="font-family: monospace; font-size: 0.85rem;"><?php echo htmlspecialchars($token); ?></textarea>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
