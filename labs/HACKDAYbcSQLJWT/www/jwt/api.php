<?php
require_once 'jwt_helper.php';

// Check if this is an API request or a page view
$is_api_request = (
    isset($_SERVER['HTTP_AUTHORIZATION']) ||
    (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
);

if ($is_api_request) {
    // API Mode
    header('Content-Type: application/json');
    header('X-Powered-By: PHP/8.2');

    // Get token
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

    // PROPER: Verify signature (but secret is weak!)
    if (!verify_jwt($token)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid token signature']);
        exit;
    }

    // Decode payload
    $decoded = decode_jwt($token);
    $payload = $decoded['payload'];

    // Check role
    $role = $payload['role'] ?? 'user';

    if ($role !== 'administrator') {
        http_response_code(403);
        echo json_encode([
            'error' => 'Access denied',
            'message' => 'Administrator role required',
            'your_role' => $role
        ], JSON_PRETTY_PRINT);
        exit;
    }

    // Admin access granted
    echo json_encode([
        'status' => 'success',
        'message' => 'Welcome Administrator! Server Key: MASTER_KEY_2026',
        'admin_data' => [
            'total_users' => 156,
            'server_secret' => 'MASTER_KEY_2026',
            'database_key' => 'db_admin_2026',
            'encryption_key' => 'AES256_PROD_KEY',
            'api_master_key' => 'sk_live_master_9x8y7z'
        ],
        'system_status' => [
            'web_server' => 'online',
            'database' => 'online',
            'cache' => 'online'
        ]
    ], JSON_PRETTY_PRINT);
    exit;
}

// Page View Mode
require_once '../includes/header.php';

// Check current token for demo
$token = $_COOKIE['auth_token'] ?? '';
$current_role = '';
if ($token) {
    $decoded = decode_jwt($token);
    $current_role = $decoded['payload']['role'] ?? 'user';
}
?>

<div class="container py-4">
    <!-- Back Link -->
    <a href="/jwt/" class="btn btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back to Account Services
    </a>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-dark border-danger">
                <div class="card-header" style="background-color: #1a237e;">
                    <h4 class="mb-0 text-white">
                        <i class="bi bi-braces me-2"></i>Admin API
                    </h4>
                    <small class="text-light">API สำหรับผู้ดูแลระบบ</small>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        This API requires <strong>administrator</strong> role to access.
                        <?php if ($current_role): ?>
                            <br>Your current role: <span class="badge bg-<?php echo $current_role === 'administrator' ? 'success' : 'warning'; ?>"><?php echo $current_role; ?></span>
                        <?php endif; ?>
                    </div>

                    <button id="testApiBtn" class="btn btn-danger mb-3">
                        <i class="bi bi-play-fill me-2"></i>Test API with Current Token
                    </button>

                    <h5 class="text-light"><i class="bi bi-terminal me-2"></i>Response</h5>
                    <pre id="response" class="bg-black text-success p-3 rounded" style="min-height: 150px;">
// Click "Test API" to see the response
                    </pre>
                </div>
            </div>

            <!-- API Usage -->
            <div class="card bg-dark border-secondary mt-3">
                <div class="card-body">
                    <h6 class="text-secondary">
                        <i class="bi bi-code me-1"></i>API Usage
                    </h6>
                    <pre class="text-light small mb-0">
# Using curl with Authorization header
curl -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  http://localhost:8080/jwt/api.php

# Using cookie
curl -b "auth_token=YOUR_JWT_TOKEN" \
  http://localhost:8080/jwt/api.php</pre>
                </div>
            </div>

            <!-- Expected Responses -->
            <div class="card bg-dark border-secondary mt-3">
                <div class="card-body">
                    <h6 class="text-secondary">
                        <i class="bi bi-chat-quote me-1"></i>Expected Responses
                    </h6>

                    <p class="text-light small mb-1"><strong>401 - No Token:</strong></p>
                    <pre class="text-danger small">{"error": "Authentication required"}</pre>

                    <p class="text-light small mb-1"><strong>403 - User Role:</strong></p>
                    <pre class="text-warning small">{"error": "Access denied", "your_role": "user"}</pre>

                    <p class="text-light small mb-1"><strong>200 - Admin Role:</strong></p>
                    <pre class="text-success small">{"status": "success", "admin_data": {...}}</pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('testApiBtn').addEventListener('click', async function() {
    const responseDiv = document.getElementById('response');

    try {
        const response = await fetch('api.php', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json'
            },
            credentials: 'include' // Include cookies
        });

        const data = await response.json();
        responseDiv.textContent = 'Status: ' + response.status + '\n\n' + JSON.stringify(data, null, 2);

        if (response.status === 200) {
            responseDiv.className = 'bg-black text-success p-3 rounded';
        } else if (response.status === 403) {
            responseDiv.className = 'bg-black text-warning p-3 rounded';
        } else {
            responseDiv.className = 'bg-black text-danger p-3 rounded';
        }
    } catch (error) {
        responseDiv.textContent = 'Error: ' + error.message;
        responseDiv.className = 'bg-black text-danger p-3 rounded';
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
