<?php
require_once '../includes/header.php';
require_once '../config.php';

// Suppress all errors (Blind SQL Injection)
error_reporting(0);
ini_set('display_errors', 0);

$verified = null;
$code = $_GET['code'] ?? '';

if ($code) {
    // INSECURE: String concatenation
    $query = "SELECT * FROM partners WHERE partner_code = '$code'";
    $result = @mysqli_query($conn, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $verified = true;
    } else {
        $verified = false;
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
            <div class="card bg-dark border-danger">
                <div class="card-header" style="background-color: #1a237e;">
                    <h4 class="mb-0 text-white">
                        <i class="bi bi-shield-check me-2"></i>Partner Verification
                    </h4>
                    <small class="text-light">ตรวจสอบสถานะพันธมิตร</small>
                </div>
                <div class="card-body">
                    <!-- Verification Form -->
                    <form method="GET" action="" class="mb-4">
                        <div class="mb-3">
                            <label for="code" class="form-label text-light">
                                <i class="bi bi-upc me-1"></i>Partner Code
                            </label>
                            <input type="text" class="form-control" id="code" name="code"
                                   placeholder="e.g., LOC001"
                                   value="<?php echo htmlspecialchars($code); ?>">
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="bi bi-search me-2"></i>Verify
                        </button>
                    </form>

                    <!-- Sample Codes -->
                    <div class="mb-4">
                        <span class="text-secondary me-2">Sample Codes:</span>
                        <a href="?code=LOC001" class="btn btn-outline-secondary btn-sm me-1">LOC001</a>
                        <a href="?code=LOC002" class="btn btn-outline-secondary btn-sm me-1">LOC002</a>
                        <a href="?code=LOC003" class="btn btn-outline-secondary btn-sm">LOC003</a>
                    </div>

                    <?php if ($verified === true): ?>
                        <!-- Verified Response -->
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i>
                            <strong>Partner Verified</strong>
                            <p class="mb-0 mt-2">Partner code is valid and active.</p>
                        </div>
                    <?php elseif ($verified === false): ?>
                        <!-- Not Found Response -->
                        <div class="alert alert-danger">
                            <i class="bi bi-x-circle-fill me-2"></i>
                            <strong>Partner Not Found</strong>
                            <p class="mb-0 mt-2">Invalid partner code or inactive status.</p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle me-2"></i>
                            Enter a partner code to verify.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card bg-dark border-secondary mt-3">
                <div class="card-body">
                    <h6 class="text-secondary">
                        <i class="bi bi-info-circle me-1"></i>About Partner Verification
                    </h6>
                    <p class="text-light small mb-0">
                        This system verifies if a partner code is registered and active in our database.
                        Contact the Partnership team for new registrations.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
