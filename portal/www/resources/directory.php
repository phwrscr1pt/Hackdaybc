<?php
require_once '../includes/header.php';
require_once '../config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = '';
$members = [];
$searched = false;

if (isset($_GET['id'])) {
    $searched = true;
    $id = $_GET['id'];
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

<div class="container py-5">
    <a href="/resources/" class="btn btn-outline-secondary mb-4" style="border-color: var(--border); color: var(--text-muted);">
        <i class="bi bi-arrow-left me-2"></i>Back to Resources
    </a>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #06b6d4, #22d3ee);">
                    <h4 class="mb-0"><i class="bi bi-people me-2"></i>Member Directory</h4>
                </div>
                <div class="card-body">
                    <form method="GET" class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text" style="background: var(--primary); border-color: var(--border);"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" name="id" placeholder="Enter Member ID" value="<?php echo htmlspecialchars($_GET['id'] ?? ''); ?>">
                            <button type="submit" class="btn btn-primary" style="background: linear-gradient(135deg, #06b6d4, #22d3ee); border: none;"><i class="bi bi-search me-1"></i>Search</button>
                        </div>
                    </form>

                    <div class="mb-4">
                        <span style="color: var(--text-muted);" class="me-2">Quick View:</span>
                        <a href="?id=1" class="btn btn-outline-info btn-sm me-1">Member 1</a>
                        <a href="?id=2" class="btn btn-outline-info btn-sm me-1">Member 2</a>
                        <a href="?id=3" class="btn btn-outline-info btn-sm">Member 3</a>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($searched): ?>
                        <?php if (count($members) > 0): ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Department</th></tr></thead>
                                    <tbody>
                                        <?php foreach ($members as $member): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($member['id']); ?></td>
                                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                                <td><span class="badge" style="background: var(--accent);"><?php echo htmlspecialchars($member['department']); ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No member found with ID: <?php echo htmlspecialchars($_GET['id']); ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert" style="background: rgba(59, 130, 246, 0.1); border-color: var(--border); color: var(--text-muted);"><i class="bi bi-info-circle me-2"></i>Enter a Member ID to search.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
