<?php
require_once 'jwt_helper.php';

// Check if this is an API request or a page view
$is_api_request = (
    isset($_SERVER['HTTP_AUTHORIZATION']) ||
    (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) ||
    isset($_POST['token'])
);

if ($is_api_request) {
    // API Mode
    header('Content-Type: application/json');

    // Get token from various sources
    $token = '';
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
        $token = $matches[1];
    } elseif (isset($_COOKIE['auth_token'])) {
        $token = $_COOKIE['auth_token'];
    } elseif (isset($_POST['token'])) {
        $token = $_POST['token'];
    }

    if (empty($token)) {
        echo json_encode(['error' => 'No token provided']);
        exit;
    }

    // Decode token
    $parts = explode('.', $token);
    if (count($parts) !== 3) {
        echo json_encode(['error' => 'Invalid token format']);
        exit;
    }

    $header = json_decode(base64url_decode($parts[0]), true);
    $payload = json_decode(base64url_decode($parts[1]), true);

    // INSECURE: Accept "none" algorithm
    if (strtolower($header['alg'] ?? '') === 'none') {
        // Skip signature verification entirely!
        $valid = true;
    } else {
        // Normal verification
        $valid = verify_jwt($token);
    }

    if ($valid) {
        $response = [
            'status' => 'success',
            'message' => 'Token valid',
            'user' => $payload['user'] ?? 'unknown',
            'role' => $payload['role'] ?? 'user',
            'expires' => date('Y-m-d H:i:s', $payload['exp'] ?? time())
        ];

        // If admin, show extra data
        if (($payload['role'] ?? '') === 'administrator') {
            $response['admin_data'] = [
                'server_secret' => 'MASTER_KEY_2026',
                'db_connection' => 'mysql://admin:password@localhost/loc_db'
            ];
        }

        echo json_encode($response, JSON_PRETTY_PRINT);
    } else {
        echo json_encode(['error' => 'Invalid signature']);
    }
    exit;
}

// Page View Mode
require_once '../includes/header.php';
?>

<div class="container py-4">
    <!-- Back Link -->
    <a href="/jwt/" class="btn btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back to Account Services
    </a>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card bg-dark border-warning">
                <div class="card-header" style="background-color: #1a237e;">
                    <h4 class="mb-0 text-white">
                        <i class="bi bi-arrow-repeat me-2"></i>Token Refresh Service
                    </h4>
                    <small class="text-light">ต่ออายุ JWT Token</small>
                </div>
                <div class="card-body">
                    <p class="text-light">
                        Submit your JWT token to validate and refresh it.
                    </p>

                    <form id="refreshForm">
                        <div class="mb-3">
                            <label for="token" class="form-label text-light">
                                <i class="bi bi-key me-1"></i>JWT Token
                            </label>
                            <textarea class="form-control" id="token" name="token" rows="4"
                                      placeholder="Paste your JWT token here..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-arrow-repeat me-2"></i>Refresh Token
                        </button>
                        <?php if (isset($_COOKIE['auth_token'])): ?>
                            <button type="button" class="btn btn-outline-info" onclick="document.getElementById('token').value = '<?php echo $_COOKIE['auth_token']; ?>';">
                                <i class="bi bi-clipboard me-1"></i>Use Current Token
                            </button>
                        <?php endif; ?>
                    </form>

                    <hr class="bg-secondary">

                    <h5 class="text-light"><i class="bi bi-terminal me-2"></i>Response</h5>
                    <pre id="response" class="bg-black text-success p-3 rounded" style="min-height: 100px;">
// Response will appear here
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
curl -H "Authorization: Bearer YOUR_TOKEN" \
  http://localhost:8080/jwt/refresh.php

# Or with POST
curl -X POST -d "token=YOUR_TOKEN" \
  http://localhost:8080/jwt/refresh.php</pre>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('refreshForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const token = document.getElementById('token').value;
    const responseDiv = document.getElementById('response');

    try {
        const response = await fetch('refresh.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'token=' + encodeURIComponent(token)
        });

        const data = await response.json();
        responseDiv.textContent = JSON.stringify(data, null, 2);
    } catch (error) {
        responseDiv.textContent = 'Error: ' + error.message;
    }
});
</script>

<?php require_once '../includes/footer.php'; ?>
