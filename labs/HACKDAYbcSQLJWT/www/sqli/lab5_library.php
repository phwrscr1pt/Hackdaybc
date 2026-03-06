<?php
require_once '../includes/header.php';
require_once '../config.php';

// Enable error display
ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = '';
$book = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];  // No validation

    // INSECURE: Numeric injection
    $query = "SELECT * FROM books WHERE id = $id";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        $error = "Error: " . mysqli_error($conn);
    } else {
        $book = mysqli_fetch_assoc($result);
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
            <div class="card bg-dark border-secondary">
                <div class="card-header" style="background-color: #1a237e;">
                    <h4 class="mb-0 text-white">
                        <i class="bi bi-book me-2"></i>Library Catalog
                    </h4>
                    <small class="text-light">รายการหนังสือในห้องสมุด</small>
                </div>
                <div class="card-body">
                    <!-- Quick Links -->
                    <div class="mb-4">
                        <span class="text-light me-2">Quick View:</span>
                        <a href="?id=1" class="btn btn-outline-secondary btn-sm me-1">Book 1</a>
                        <a href="?id=2" class="btn btn-outline-secondary btn-sm me-1">Book 2</a>
                        <a href="?id=3" class="btn btn-outline-secondary btn-sm me-1">Book 3</a>
                        <a href="?id=4" class="btn btn-outline-secondary btn-sm me-1">Book 4</a>
                        <a href="?id=5" class="btn btn-outline-secondary btn-sm">Book 5</a>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($book): ?>
                        <!-- Book Details -->
                        <div class="card bg-dark border-info">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 text-center">
                                        <i class="bi bi-book display-1 text-info"></i>
                                    </div>
                                    <div class="col-md-9">
                                        <h4 class="text-white"><?php echo htmlspecialchars($book['title']); ?></h4>
                                        <p class="text-secondary mb-2">
                                            <i class="bi bi-person me-1"></i>
                                            Author: <?php echo htmlspecialchars($book['author']); ?>
                                        </p>
                                        <p class="text-secondary mb-2">
                                            <i class="bi bi-folder me-1"></i>
                                            Category: <span class="badge bg-info"><?php echo htmlspecialchars($book['category']); ?></span>
                                        </p>
                                        <p class="text-secondary mb-2">
                                            <i class="bi bi-upc me-1"></i>
                                            ISBN: <?php echo htmlspecialchars($book['isbn']); ?>
                                        </p>
                                        <p class="mb-0">
                                            <i class="bi bi-check-circle me-1"></i>
                                            Status:
                                            <?php if ($book['available']): ?>
                                                <span class="badge bg-success">Available</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Not Available</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Request Button -->
                        <div class="mt-3">
                            <a href="lab5_request.php?book_id=<?php echo $book['id']; ?>" class="btn btn-primary">
                                <i class="bi bi-journal-plus me-1"></i>Request This Book
                            </a>
                        </div>
                    <?php elseif (isset($_GET['id'])): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle me-2"></i>
                            No book found with ID: <?php echo htmlspecialchars($_GET['id']); ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle me-2"></i>
                            Select a book from the quick links above to view details.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Book Request Link -->
            <div class="mt-3 text-end">
                <a href="lab5_request.php" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-journal-plus me-1"></i>Book Request Form
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
