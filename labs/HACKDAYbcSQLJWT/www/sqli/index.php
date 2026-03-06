<?php require_once '../includes/header.php'; ?>

<div class="container py-4">
    <!-- Back Link -->
    <a href="/" class="btn btn-outline-secondary mb-4">
        <i class="bi bi-arrow-left"></i> Back to Home
    </a>

    <!-- Header -->
    <div class="card bg-dark border-primary mb-4">
        <div class="card-header" style="background-color: #1a237e;">
            <h3 class="mb-0 text-white">
                <i class="bi bi-people-fill me-2"></i>Member Management System
            </h3>
            <small class="text-light">ระบบจัดการสมาชิก</small>
        </div>
        <div class="card-body">
            <p class="text-light mb-0">
                ระบบจัดการสมาชิก LeaguesOfCode สำหรับ HR และพนักงาน ประกอบด้วยฟีเจอร์ต่างๆ ดังนี้
            </p>
        </div>
    </div>

    <!-- Feature Cards -->
    <div class="row g-4">
        <!-- Lab 0: Employee Login -->
        <div class="col-md-6">
            <a href="lab0_login.php" class="text-decoration-none">
                <div class="card bg-dark border-secondary h-100 hover-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-box-arrow-in-right display-6 text-primary me-3"></i>
                            <div>
                                <h5 class="card-title text-white mb-0">Employee Login</h5>
                                <small class="text-secondary">เข้าสู่ระบบสำหรับพนักงาน</small>
                            </div>
                        </div>
                        <p class="card-text text-light">
                            ระบบ Login สำหรับพนักงาน LeaguesOfCode เข้าสู่ระบบ HR Portal
                        </p>
                        <span class="badge bg-primary">Lab 0</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Lab 1: HR Registration -->
        <div class="col-md-6">
            <a href="lab1_register.php" class="text-decoration-none">
                <div class="card bg-dark border-secondary h-100 hover-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-person-plus display-6 text-success me-3"></i>
                            <div>
                                <h5 class="card-title text-white mb-0">HR Registration</h5>
                                <small class="text-secondary">ลงทะเบียนพนักงานใหม่</small>
                            </div>
                        </div>
                        <p class="card-text text-light">
                            แบบฟอร์มลงทะเบียนพนักงานใหม่เข้าระบบ HR
                        </p>
                        <span class="badge bg-success">Lab 1</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Lab 2: Member Directory -->
        <div class="col-md-6">
            <a href="lab2_members.php" class="text-decoration-none">
                <div class="card bg-dark border-secondary h-100 hover-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-search display-6 text-info me-3"></i>
                            <div>
                                <h5 class="card-title text-white mb-0">Member Directory</h5>
                                <small class="text-secondary">ค้นหาข้อมูลสมาชิก</small>
                            </div>
                        </div>
                        <p class="card-text text-light">
                            ค้นหาและดูข้อมูลสมาชิกในระบบ
                        </p>
                        <span class="badge bg-info">Lab 2</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Lab 3: Inventory Search -->
        <div class="col-md-6">
            <a href="lab3_inventory.php" class="text-decoration-none">
                <div class="card bg-dark border-secondary h-100 hover-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-box-seam display-6 text-warning me-3"></i>
                            <div>
                                <h5 class="card-title text-white mb-0">Inventory Search</h5>
                                <small class="text-secondary">ค้นหาอุปกรณ์ในคลัง</small>
                            </div>
                        </div>
                        <p class="card-text text-light">
                            ระบบค้นหาอุปกรณ์และสินค้าในคลังสินค้า
                        </p>
                        <span class="badge bg-warning text-dark">Lab 3</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Lab 4: Partner Verification -->
        <div class="col-md-6">
            <a href="lab4_partner.php" class="text-decoration-none">
                <div class="card bg-dark border-secondary h-100 hover-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-shield-check display-6 text-danger me-3"></i>
                            <div>
                                <h5 class="card-title text-white mb-0">Partner Verification</h5>
                                <small class="text-secondary">ตรวจสอบสถานะพันธมิตร</small>
                            </div>
                        </div>
                        <p class="card-text text-light">
                            ตรวจสอบสถานะและความถูกต้องของพันธมิตร
                        </p>
                        <span class="badge bg-danger">Lab 4</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Lab 5: Library System -->
        <div class="col-md-6">
            <a href="lab5_library.php" class="text-decoration-none">
                <div class="card bg-dark border-secondary h-100 hover-card">
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <i class="bi bi-book display-6 text-secondary me-3"></i>
                            <div>
                                <h5 class="card-title text-white mb-0">Library System</h5>
                                <small class="text-secondary">ระบบห้องสมุด</small>
                            </div>
                        </div>
                        <p class="card-text text-light">
                            ระบบยืม-คืนหนังสือและค้นหา Catalog
                        </p>
                        <span class="badge bg-secondary">Lab 5</span>
                    </div>
                </div>
            </a>
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
