<?php
require_once 'auth_helper.php';

$is_api_request = (
    isset($_SERVER['HTTP_AUTHORIZATION']) ||
    (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
);

if ($is_api_request) {
    header('Content-Type: application/json');
    header('X-Powered-By: PHP/8.2');

    $token = '';
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
        $token = $matches[1];
    } elseif (isset($_COOKIE['auth_token'])) {
        $token = $_COOKIE['auth_token'];
    }

    if (empty($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }

    if (!verify_jwt($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token signature']);
        exit;
    }

    $decoded = decode_jwt($token);
    $payload = $decoded['payload'];
    $role = $payload['role'] ?? 'user';

    if ($role !== 'administrator') {
        http_response_code(403);
        echo json_encode(['error' => 'Access denied', 'message' => 'Administrator role required', 'your_role' => $role], JSON_PRETTY_PRINT);
        exit;
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Welcome Administrator! Server Key: MASTER_KEY_2026',
        'admin_data' => ['total_users' => 156, 'server_secret' => 'MASTER_KEY_2026', 'database_key' => 'db_admin_2026', 'encryption_key' => 'AES256_PROD_KEY', 'api_master_key' => 'sk_live_master_9x8y7z'],
        'system_status' => ['web_server' => 'online', 'database' => 'online', 'cache' => 'online']
    ], JSON_PRETTY_PRINT);
    exit;
}

require_once '../includes/header.php';

$token = $_COOKIE['auth_token'] ?? '';
$current_role = '';
if ($token) {
    $decoded = decode_jwt($token);
    $current_role = $decoded['payload']['role'] ?? 'user';
}
?>

<div class="container py-5">
    <a href="/account/" class="btn btn-outline-secondary mb-4" style="border-color: var(--border); color: var(--text-muted);">
        <i class="bi bi-arrow-left me-2"></i>Back to Account Portal
    </a>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card" style="border-color: #ef4444;">
                <div class="card-header" style="background: linear-gradient(135deg, #ef4444, #f87171);">
                    <h4 class="mb-0"><i class="bi bi-braces me-2"></i>Admin API</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>This API requires <strong>administrator</strong> role to access.
                        <?php if ($current_role): ?>
                            <br>Your current role: <span class="badge" style="background: <?php echo $current_role === 'administrator' ? '#10b981' : 'var(--gold)'; ?>; color: <?php echo $current_role === 'administrator' ? '#fff' : '#000'; ?>;"><?php echo $current_role; ?></span>
                        <?php endif; ?>
                    </div>

                    <button id="testApiBtn" class="btn mb-3" style="background: linear-gradient(135deg, #ef4444, #f87171); border: none;"><i class="bi bi-play-fill me-2"></i>Test API with Current Token</button>

                    <h5><i class="bi bi-terminal me-2"></i>Response</h5>
                    <pre id="response" style="background: #000; color: #10b981; padding: 16px; border-radius: 8px; min-height: 150px;">// Click "Test API" to see the response</pre>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 style="color: var(--text-muted);"><i class="bi bi-code me-1"></i>API Usage</h6>
                    <pre style="color: var(--text); font-size: 0.85rem;" class="mb-0">curl -H "Authorization: Bearer YOUR_JWT_TOKEN"   http://localhost:8080/account/api.php</pre>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h6 style="color: var(--text-muted);"><i class="bi bi-chat-quote me-1"></i>Expected Responses</h6>
                    <p class="small mb-1"><strong>401 - No Token:</strong></p>
                    <pre style="color: #ef4444; font-size: 0.85rem;">{"error": "Authentication required"}</pre>
                    <p class="small mb-1"><strong>403 - User Role:</strong></p>
                    <pre style="color: var(--gold); font-size: 0.85rem;">{"error": "Access denied", "your_role": "user"}</pre>
                    <p class="small mb-1"><strong>200 - Admin Role:</strong></p>
                    <pre style="color: #10b981; font-size: 0.85rem;">{"status": "success", "admin_data": {...}}</pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('testApiBtn').addEventListener('click', async function() {
    const responseDiv = document.getElementById('response');
    try {
        const response = await fetch('api.php', { method: 'GET', headers: { 'Content-Type': 'application/json' }, credentials: 'include' });
        const data = await response.json();
        responseDiv.textContent = 'Status: ' + response.status + '\n\n' + JSON.stringify(data, null, 2);
        responseDiv.style.color = response.status === 200 ? '#10b981' : (response.status === 403 ? '#fbbf24' : '#ef4444');
    } catch (error) {
        responseDiv.textContent = 'Error: ' + error.message;
        responseDiv.style.color = '#ef4444';
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
