<?php require_once '../includes/header.php'; ?>

<div class="container py-5">
    <!-- Back Link -->
    <a href="/" class="btn btn-outline-secondary mb-4" style="border-color: var(--border); color: var(--text-muted);">
        <i class="bi bi-arrow-left me-2"></i>Back to Home
    </a>

    <!-- Header Section -->
    <div class="text-center mb-5">
        <div style="width: 80px; height: 80px; background: linear-gradient(135deg, var(--accent), var(--accent-light)); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
            <i class="bi bi-database-fill" style="font-size: 2rem;"></i>
        </div>
        <h1 style="font-weight: 800; font-size: 2.5rem; margin-bottom: 16px;">Resource Library</h1>
        <p style="color: var(--text-muted); max-width: 600px; margin: 0 auto; font-size: 1.1rem;">
            Access training materials, documentation, and learning resources for all skill levels
        </p>
    </div>

    <!-- Feature Cards -->
    <div class="row g-4">
        
        <div class="col-md-6 col-lg-4">
            <a href="login.php" class="service-card">
                <div class="icon"><i class="bi bi-box-arrow-in-right"></i></div>
                <h5>Employee Login</h5>
                <p>Access the HR Portal with your employee credentials</p>
                <span class="link">Open Portal <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>

        
        <div class="col-md-6 col-lg-4">
            <a href="register.php" class="service-card">
                <div class="icon" style="background: linear-gradient(135deg, #10b981, #34d399);"><i class="bi bi-person-plus"></i></div>
                <h5>HR Registration</h5>
                <p>Register new employees into the HR management system</p>
                <span class="link">Register <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>

        
        <div class="col-md-6 col-lg-4">
            <a href="directory.php" class="service-card">
                <div class="icon" style="background: linear-gradient(135deg, #06b6d4, #22d3ee);"><i class="bi bi-search"></i></div>
                <h5>Member Directory</h5>
                <p>Search and browse organization member profiles</p>
                <span class="link">Search Members <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>

        
        <div class="col-md-6 col-lg-4">
            <a href="catalog.php" class="service-card">
                <div class="icon" style="background: linear-gradient(135deg, var(--gold), #fbbf24);"><i class="bi bi-box-seam"></i></div>
                <h5>Inventory Search</h5>
                <p>Search equipment and products in the warehouse catalog</p>
                <span class="link">Browse Inventory <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>

        
        <div class="col-md-6 col-lg-4">
            <a href="verify.php" class="service-card">
                <div class="icon" style="background: linear-gradient(135deg, #ef4444, #f87171);"><i class="bi bi-shield-check"></i></div>
                <h5>Partner Verification</h5>
                <p>Verify partner status and access business credentials</p>
                <span class="link">Verify Partner <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>

        
        <div class="col-md-6 col-lg-4">
            <a href="books.php" class="service-card">
                <div class="icon" style="background: linear-gradient(135deg, #8b5cf6, #a78bfa);"><i class="bi bi-book"></i></div>
                <h5>Library System</h5>
                <p>Browse and search the technical book catalog</p>
                <span class="link">Open Library <i class="bi bi-arrow-right"></i></span>
            </a>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
