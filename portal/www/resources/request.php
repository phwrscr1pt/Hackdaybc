<?php
require_once '../includes/header.php';
require_once '../config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = '';
$book = null;
$success = false;

$book_id = $_POST['book_id'] ?? $_GET['book_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $book_id) {
    $query = "SELECT * FROM books WHERE id = $book_id";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        $error = "Error: " . mysqli_error($conn);
    } else {
        $book = mysqli_fetch_assoc($result);
        if ($book) {
            $success = true;
        }
    }
} elseif ($book_id) {
    $query = "SELECT * FROM books WHERE id = " . intval($book_id);
    $result = mysqli_query($conn, $query);
    if ($result) {
        $book = mysqli_fetch_assoc($result);
    }
}
?>

<div class="container py-5">
    <a href="books.php" class="btn btn-outline-secondary mb-4" style="border-color: var(--border); color: var(--text-muted);">
        <i class="bi bi-arrow-left me-2"></i>Back to Library Catalog
    </a>

    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
                    <h4 class="mb-0"><i class="bi bi-journal-plus me-2"></i>Book Request</h4>
                </div>
                <div class="card-body">
                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($success && $book): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle-fill me-2"></i><strong>Request Submitted!</strong>
                            <p class="mt-2 mb-0">Your request for "<strong><?php echo htmlspecialchars($book['title']); ?></strong>" has been submitted.<br>You will be notified when it's ready for pickup.</p>
                        </div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="book_id" class="form-label"><i class="bi bi-hash me-1"></i>Book ID</label>
                            <input type="text" class="form-control" id="book_id" name="book_id" placeholder="Enter Book ID" value="<?php echo htmlspecialchars($book_id); ?>">
                        </div>

                        <?php if ($book): ?>
                            <div class="alert alert-info mb-3"><strong>Selected Book:</strong><br><?php echo htmlspecialchars($book['title']); ?> by <?php echo htmlspecialchars($book['author']); ?></div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label for="requester_name" class="form-label"><i class="bi bi-person me-1"></i>Requester Name</label>
                            <input type="text" class="form-control" id="requester_name" name="requester_name" placeholder="Enter your name" required>
                        </div>
                        <div class="mb-3">
                            <label for="request_date" class="form-label"><i class="bi bi-calendar me-1"></i>Request Date</label>
                            <input type="date" class="form-control" id="request_date" name="request_date" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="bi bi-send me-2"></i>Submit Request</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
