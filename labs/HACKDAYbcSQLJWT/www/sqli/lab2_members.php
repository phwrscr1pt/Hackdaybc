<?php
require_once '../includes/header.php';
require_once '../config.php';

// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = '';
$members = [];
$searched = false;

if (isset($_GET['id'])) {
    $searched = true;
    $id = $_GET['id'];  // No validation!

    // INSECURE: No quotes around $id (numeric injection)
    $query = "SELECT id, name, email, department FROM members WHERE id = $id";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        $error = "Error: " . mysqli_error($conn);
    } else {
        while ($row = mysqli_fetch_assoc($result)) {
            $members[] = $row;
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
        <div class="col-md-8 mx-auto">
            <div class="card bg-dark border-info">
                <div class="card-header" style="background-color: #1a237e;">
                    <h4 class="mb-0 text-white">
                        <i class="bi bi-people me-2"></i>Member Directory
                    </h4>
                    <small class="text-light">ค้นหาข้อมูลสมาชิก</small>
                </div>
                <div class="card-body">
                    <!-- Search Form -->
                    <form method="GET" action="" class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text bg-dark border-secondary">
                                <i class="bi bi-search text-light"></i>
                            </span>
                            <input type="text" class="form-control" name="id"
                                   placeholder="Enter Member ID" value="<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>">
                            <button type="submit" class="btn btn-info">
                                <i class="bi bi-search me-1"></i>Search
                            </button>
                        </div>
                    </form>

                    <!-- Quick Links -->
                    <div class="mb-4">
                        <span class="text-light me-2">Quick View:</span>
                        <a href="?id=1" class="btn btn-outline-info btn-sm me-1">Member 1</a>
                        <a href="?id=2" class="btn btn-outline-info btn-sm me-1">Member 2</a>
                        <a href="?id=3" class="btn btn-outline-info btn-sm">Member 3</a>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($searched): ?>
                        <?php if (count($members) > 0): ?>
                            <!-- Results Table -->
                            <div class="table-responsive">
                                <table class="table table-dark table-striped table-hover">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Department</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($members as $member): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($member['id']); ?></td>
                                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo htmlspecialchars($member['department']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-info-circle me-2"></i>
                                No member found with ID: <?php echo htmlspecialchars($_GET['id']); ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle me-2"></i>
                            Enter a Member ID to search.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Export Button -->
            <div class="mt-3 text-end">
                <button class="btn btn-outline-secondary btn-sm" disabled>
                    <i class="bi bi-download me-1"></i>Export to CSV
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
