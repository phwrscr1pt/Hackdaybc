<?php require_once '../includes/header.php'; ?>

<div class="container py-4">
    <!-- Back Link -->
    <a href="/" class="btn btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back to Home
    </a>

    <!-- Header -->
    <div class="card bg-dark border-info mb-4">
        <div class="card-header" style="background-color: #1a237e;">
            <h3 class="mb-0 text-white">
                <i class="bi bi-shield-lock-fill me-2"></i>Account Services
            </h3>
            <small class="text-light">ระบบจัดการบัญชี</small>
        </div>
        <div class="card-body">
            <p class="text-light mb-0">
                ระบบจัดการบัญชีผู้ใช้งาน LeaguesOfCode รวมถึง Authentication และ API Services
            </p>
        </div>
    </div>

    <!-- Feature Cards -->
    <div class="row g-4">
        <!-- Lab 0: Login -->
        <div class="col-md-6">
            <a href="login.php" class="text-decoration-none">
                <div class="card bg-dark border-secondary h-100 hover-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-person-circle display-6 text-primary me-3"></i>
                            <div>
                                <h5 class="card-title text-white mb-0">Account Login</h5>
                                <small class="text-secondary">เข้าสู่ระบบ</small>
                            </div>
                        </div>
                        <p class="card-text text-light">
                            Login เพื่อรับ JWT Token สำหรับเข้าใช้งานระบบ
                        </p>
                        <span class="badge bg-primary">Lab 0</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Lab 1: Dashboard -->
        <div class="col-md-6">
            <a href="dashboard.php" class="text-decoration-none">
                <div class="card bg-dark border-secondary h-100 hover-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-speedometer2 display-6 text-success me-3"></i>
                            <div>
                                <h5 class="card-title text-white mb-0">User Dashboard</h5>
                                <small class="text-secondary">แผงควบคุม</small>
                            </div>
                        </div>
                        <p class="card-text text-light">
                            Dashboard แสดงข้อมูลผู้ใช้และเมนูตามสิทธิ์
                        </p>
                        <span class="badge bg-success">Lab 1</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Lab 2: Refresh -->
        <div class="col-md-6">
            <a href="refresh.php" class="text-decoration-none">
                <div class="card bg-dark border-secondary h-100 hover-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-arrow-repeat display-6 text-warning me-3"></i>
                            <div>
                                <h5 class="card-title text-white mb-0">Token Refresh</h5>
                                <small class="text-secondary">ต่ออายุ Token</small>
                            </div>
                        </div>
                        <p class="card-text text-light">
                            Service สำหรับ refresh JWT token ที่ใกล้หมดอายุ
                        </p>
                        <span class="badge bg-warning text-dark">Lab 2</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Lab 3: API -->
        <div class="col-md-6">
            <a href="api.php" class="text-decoration-none">
                <div class="card bg-dark border-secondary h-100 hover-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-braces display-6 text-danger me-3"></i>
                            <div>
                                <h5 class="card-title text-white mb-0">Admin API</h5>
                                <small class="text-secondary">API สำหรับผู้ดูแลระบบ</small>
                            </div>
                        </div>
                        <p class="card-text text-light">
                            API endpoint สำหรับดึงข้อมูลระบบ (ต้องมีสิทธิ์ admin)
                        </p>
                        <span class="badge bg-danger">Lab 3</span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Test Accounts Info -->
    <div class="card bg-dark border-secondary mt-4">
        <div class="card-body">
            <h6 class="text-secondary">
                <i class="bi bi-info-circle me-1"></i>Test Accounts
            </h6>
            <div class="row text-light">
                <div class="col-md-4">
                    <small><code>john / password123</code> (user)</small>
                </div>
                <div class="col-md-4">
                    <small><code>wiener / peter</code> (user)</small>
                </div>
                <div class="col-md-4">
                    <small><code>admin / admin</code> (administrator)</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.hover-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    border-color: #1a237e !important;
}
</style>

<?php require_once '../includes/footer.php'; ?>
