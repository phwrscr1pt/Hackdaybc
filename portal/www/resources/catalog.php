<?php
require_once '../includes/header.php';
require_once '../config.php';

// Enable error display for error-based extraction
ini_set('display_errors', 1);
error_reporting(E_ALL);

$error = '';
$book = null;
$searched = false;

// GET parameter with string injection (matches teaching materials)
$bookID = $_GET['bookID'] ?? '';

if ($bookID !== '') {
    $searched = true;

    // INSECURE: String concatenation with quotes - matches SQLerrorbased.pdf pattern
    // Query pattern: SELECT * FROM book_list WHERE bookID='$input'
    $query = "SELECT * FROM books WHERE id='$bookID'";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        // Show detailed error (enables extractvalue() error-based extraction)
        // Example payload: 3' AND extractvalue(rand(),concat(0x3a,version())) -- #
        $error = mysqli_error($conn);
    } else {
        $book = mysqli_fetch_assoc($result);
    }
}
?>

<div class="container py-4">
    <!-- Back Link -->
    <a href="/resources/" class="btn btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back to Resources
    </a>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card bg-dark border-warning">
                <div class="card-header" style="background-color: #1a237e;">
                    <h4 class="mb-0 text-white">
                        <i class="bi bi-book me-2"></i>Book Catalog
                    </h4>
                    <small class="text-light">ค้นหาหนังสือในห้องสมุด</small>
                </div>
                <div class="card-body">
                    <!-- Search Form - GET method with bookID parameter -->
                    <form method="GET" action="" class="mb-4">
                        <div class="mb-3">
                            <label for="bookID" class="form-label text-light">
                                <i class="bi bi-upc me-1"></i>Book ID
                            </label>
                            <input type="text" class="form-control" id="bookID" name="bookID"
                                   placeholder="e.g., 1, 2, 3..."
                                   value="<?php echo htmlspecialchars($bookID); ?>">
                        </div>
                        <button type="submit" class="btn btn-warning text-dark w-100">
                            <i class="bi bi-search me-1"></i>Search Book
                        </button>
                    </form>

                    <!-- Quick Links -->
                    <div class="mb-4">
                        <span class="text-light me-2">Quick View:</span>
                        <a href="?bookID=1" class="btn btn-outline-secondary btn-sm me-1">Book 1</a>
                        <a href="?bookID=2" class="btn btn-outline-secondary btn-sm me-1">Book 2</a>
                        <a href="?bookID=3" class="btn btn-outline-secondary btn-sm me-1">Book 3</a>
                        <a href="?bookID=4" class="btn btn-outline-secondary btn-sm me-1">Book 4</a>
                        <a href="?bookID=5" class="btn btn-outline-secondary btn-sm">Book 5</a>
                    </div>

                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <strong><i class="bi bi-exclamation-triangle-fill me-2"></i>Error:</strong><br>
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($searched && !$error): ?>
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
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-info-circle me-2"></i>
                                No book found with ID: <strong><?php echo htmlspecialchars($bookID); ?></strong>
                            </div>
                        <?php endif; ?>
                    <?php elseif (!$searched): ?>
                        <div class="alert alert-secondary">
                            <i class="bi bi-info-circle me-2"></i>
                            Enter a Book ID or click one of the quick links above.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Hint Card -->
            <div class="card bg-dark border-secondary mt-3">
                <div class="card-body">
                    <h6 class="text-secondary">
                        <i class="bi bi-lightbulb me-1"></i>Hint
                    </h6>
                    <p class="text-light small mb-0">
                        The database shows detailed error messages. Can you use them to extract information?
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
