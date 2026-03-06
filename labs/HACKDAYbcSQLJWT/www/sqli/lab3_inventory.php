<?php
require_once '../includes/header.php';
require_once '../config.php';

// Enable error display for error-based extraction
ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = '';
$items = [];
$searched = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searched = true;
    $search = $_POST['search'];

    // INSECURE: String in LIKE clause
    $query = "SELECT * FROM inventory WHERE item_name LIKE '%$search%'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        // Show detailed error (enables error-based extraction)
        $error = mysqli_error($conn);
    } else {
        while ($row = mysqli_fetch_assoc($result)) {
            $items[] = $row;
        }
    }
}
?>

<div class="container py-4">
    <!-- Back Link -->
    <a href="/sqli/" class="btn btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back to Member Management
    </a>

    <div class="row">
        <div class="col-md-10 mx-auto">
            <div class="card bg-dark border-warning">
                <div class="card-header" style="background-color: #1a237e;">
                    <h4 class="mb-0 text-white">
                        <i class="bi bi-box-seam me-2"></i>Inventory Search
                    </h4>
                    <small class="text-light">ค้นหาอุปกรณ์ในคลัง</small>
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <form method="POST" action="" class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary">
                                <i class="bi bi-search text-light"></i>
                            </span>
                            <input type="text" class="form-control" name="search"
                                   placeholder="Search keyword (e.g., Laptop, Monitor)"
                                   value="<?php echo htmlspecialchars($_POST['search'] ?? ''); ?>">
                            <button type="submit" class="btn btn-warning text-dark">
                                <i class="bi bi-search me-1"></i>ค้นหา
                            </button>
                        </div>
                    </form>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Database Error:</strong><br>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($searched && !$error): ?>
                        <?php if (count($items) > 0): ?>
                            <!-- Results Table -->
                            <div class="table-responsive">
                                <table class="table table-dark table-striped table-hover">
                                    <thead class="table-warning text-dark">
                                        <tr>
                                            <th>Item Name</th>
                                            <th>Category</th>
                                            <th>Quantity</th>
                                            <th>Location</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $item): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                                                <td>
                                                    <?php
                                                    $badge_class = 'bg-secondary';
                                                    if ($item['category'] == 'Electronics') $badge_class = 'bg-primary';
                                                    if ($item['category'] == 'Accessories') $badge_class = 'bg-info';
                                                    if ($item['category'] == 'Audio') $badge_class = 'bg-success';
                                                    ?>
                                                    <span class="badge <?php echo $badge_class; ?>">
                                                        <?php echo htmlspecialchars($item['category']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark">
                                                        <?php echo htmlspecialchars($item['quantity']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($item['location']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-light">
                                <small>Found <?php echo count($items); ?> item(s)</small>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-info-circle me-2"></i>
                                No items found matching: <strong><?php echo htmlspecialchars($_POST['search']); ?></strong>
                            </div>
                        <?php endif; ?>
                    <?php elseif (!$searched): ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle me-2"></i>
                            Enter a search keyword to find items in inventory.
                        </div>

                        <!-- Sample Items -->
                        <h6 class="text-light mt-4"><i class="bi bi-list me-1"></i>Sample Categories:</h6>
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-primary">Electronics</span>
                            <span class="badge bg-info">Accessories</span>
                            <span class="badge bg-success">Audio</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
