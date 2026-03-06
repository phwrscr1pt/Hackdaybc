<?php
require_once '../includes/header.php';
require_once '../config.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = '';
$book = null;

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $query = "SELECT * FROM books WHERE id = $id";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        $error = "Error: " . mysqli_error($conn);
    } else {
        $book = mysqli_fetch_assoc($result);
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
                <div class="card-header" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);">
                    <h4 class="mb-0"><i class="bi bi-book me-2"></i>Library Catalog</h4>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <span style="color: var(--text-muted);" class="me-2">Quick View:</span>
                        <a href="?id=1" class="btn btn-outline-secondary btn-sm me-1">Book 1</a>
                        <a href="?id=2" class="btn btn-outline-secondary btn-sm me-1">Book 2</a>
                        <a href="?id=3" class="btn btn-outline-secondary btn-sm me-1">Book 3</a>
                        <a href="?id=4" class="btn btn-outline-secondary btn-sm me-1">Book 4</a>
                        <a href="?id=5" class="btn btn-outline-secondary btn-sm">Book 5</a>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error; ?></div>
                    <?php endif; ?>

                    <?php if ($book): ?>
                        <div class="card" style="border-color: #8b5cf6;">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3 text-center">
                                        <i class="bi bi-book display-1" style="color: #8b5cf6;"></i>
                                    </div>
                                    <div class="col-md-9">
                                        <h4><?php echo htmlspecialchars($book['title']); ?></h4>
                                        <p style="color: var(--text-muted);" class="mb-2"><i class="bi bi-person me-1"></i>Author: <?php echo htmlspecialchars($book['author']); ?></p>
                                        <p style="color: var(--text-muted);" class="mb-2"><i class="bi bi-folder me-1"></i>Category: <span class="badge" style="background: #8b5cf6;"><?php echo htmlspecialchars($book['category']); ?></span></p>
                                        <p style="color: var(--text-muted);" class="mb-2"><i class="bi bi-upc me-1"></i>ISBN: <?php echo htmlspecialchars($book['isbn']); ?></p>
                                        <p class="mb-0"><i class="bi bi-check-circle me-1"></i>Status:
                                            <?php if ($book['available']): ?>
                                                <span class="badge" style="background: #10b981;">Available</span>
                                            <?php else: ?>
                                                <span class="badge" style="background: #ef4444;">Not Available</span>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3">
                            <a href="request.php?book_id=<?php echo $book['id']; ?>" class="btn btn-primary"><i class="bi bi-journal-plus me-1"></i>Request This Book</a>
                        </div>
                    <?php elseif (isset($_GET['id'])): ?>
                        <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No book found with ID: <?php echo htmlspecialchars($_GET['id']); ?></div>
                    <?php else: ?>
                        <div class="alert" style="background: rgba(59, 130, 246, 0.1); border-color: var(--border); color: var(--text-muted);"><i class="bi bi-info-circle me-2"></i>Select a book from the quick links above to view details.</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="mt-3 text-end">
                <a href="request.php" class="btn btn-outline-primary btn-sm"><i class="bi bi-journal-plus me-1"></i>Book Request Form</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
