<?php
require_once 'auth_helper_weak.php';

$token = $_COOKIE['auth_token_secure'] ?? '';

if (empty($token)) {
    header('Location: secure.php');
    exit;
}

require_once '../includes/header.php';

$error = '';

// SECURE: This dashboard VERIFIES the JWT signature
// To bypass, you must crack the signing key using hashcat:
// hashcat -a 0 -m 16500 <JWT> wordlist.txt
// The key is "secret1" (found in common wordlists)

if (!verify_jwt_weak($token)) {
    $error = 'Invalid JWT signature. Token has been tampered with or key is incorrect.';
    $decoded = null;
} else {
    $decoded = decode_jwt_weak($token);
}

$payload = $decoded['payload'] ?? [];
$username = $payload['user'] ?? 'Unknown';
$role = $payload['role'] ?? 'user';
$email = $payload['email'] ?? '';
$exp = $payload['exp'] ?? 0;
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="/account/" class="btn btn-outline-secondary" style="border-color: var(--border); color: var(--text-muted);"><i class="bi bi-arrow-left me-2"></i>Back</a>
        <?php if (!$error): ?>
        <div>
            <i class="bi bi-person-circle me-1" style="color: var(--accent);"></i>
            Logged in as: <strong><?php echo htmlspecialchars($username); ?></strong>
            <span class="badge ms-2" style="background: <?php echo $role === 'administrator' ? 'var(--gold)' : 'var(--accent)'; ?>; color: <?php echo $role === 'administrator' ? '#000' : '#fff'; ?>;"><?php echo htmlspecialchars($role); ?></span>
        </div>
        <?php endif; ?>
        <a href="secure.php" class="btn btn-outline-danger btn-sm" onclick="document.cookie='auth_token_secure=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';"><i class="bi bi-box-arrow-right me-1"></i>Logout</a>
    </div>

    <?php if ($error): ?>
        <div class="card" style="border-color: #ef4444;">
            <div class="card-header" style="background: linear-gradient(135deg, #ef4444, #f87171); color: #fff;">
                <h4 class="mb-0"><i class="bi bi-shield-x me-2"></i>Access Denied</h4>
            </div>
            <div class="card-body">
                <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><strong>Signature Verification Failed</strong><br><?php echo htmlspecialchars($error); ?></div>
                <p>This endpoint validates JWT signatures. You cannot simply modify the token.</p>
                <p style="color: var(--text-muted);"><i class="bi bi-lightbulb me-1"></i>Hint: The signing key might be weak and crackable with a wordlist attack.</p>
            </div>
        </div>
    <?php elseif ($role === 'administrator'): ?>
        <div class="card" style="border-color: var(--gold);">
            <div class="card-header" style="background: linear-gradient(135deg, var(--gold), #fbbf24); color: #000;">
                <h4 class="mb-0"><i class="bi bi-shield-lock me-2"></i>Secure Admin Panel</h4>
                <small>Signature-Verified Access</small>
            </div>
            <div class="card-body">
                <p style="color: var(--gold);"><strong>Welcome, Administrator!</strong></p>
                <p><i class="bi bi-envelope me-2" style="color: var(--accent);"></i>Email: <?php echo htmlspecialchars($email); ?></p>
                <hr style="border-color: var(--border);">
                <h5 style="color: var(--gold);"><i class="bi bi-gear me-2"></i>Admin Tools</h5>
                <ul>
                    <li>Total Users: <strong>156</strong></li>
                    <li>Active Sessions: <strong>23</strong></li>
                    <li>Server Status: <span style="color: #10b981;">Online</span></li>
                </ul>
                <div class="alert alert-success"><i class="bi bi-trophy-fill me-2"></i><strong>Congratulations!</strong><br>You successfully cracked the JWT signing key.<br>Secret Flag: <code>JWT_WEAK_KEY_CRACKED_2026</code></div>
            </div>
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header" style="background: linear-gradient(135deg, #64748b, #94a3b8);">
                <h4 class="mb-0"><i class="bi bi-person me-2"></i>Secure User Dashboard</h4>
                <small>Welcome, <?php echo htmlspecialchars($username); ?></small>
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
            <?php if (!$error && $exp): ?>
            <p class="small mb-1"><strong>Expires:</strong> <?php echo date('Y-m-d H:i:s', $exp); ?></p>
            <?php endif; ?>
            <p class="small mb-0"><strong>Current Token:</strong></p>
            <textarea class="form-control mt-2" rows="3" readonly style="font-family: monospace; font-size: 0.85rem;"><?php echo htmlspecialchars($token); ?></textarea>
        </div>
    </div>

    <div class="card mt-4" style="border-color: var(--accent);">
        <div class="card-body">
            <h6 style="color: var(--accent);"><i class="bi bi-lightbulb me-1"></i>Challenge Hint</h6>
            <p class="small mb-0">Unlike Lab 1, this dashboard <strong>verifies JWT signatures</strong>. Simply changing the payload won't work here. The signing key might be weak enough to crack using tools like <code>hashcat</code> or <code>john</code>.</p>
            <p class="small mt-2 mb-0" style="color: var(--text-muted);">Command: <code>hashcat -a 0 -m 16500 &lt;JWT&gt; /path/to/wordlist.txt</code></p>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
