<?php require_once 'includes/header.php'; ?>

<div class="container py-5">
    <!-- Hero Section -->
    <div class="text-center mb-5">
        <h1 class="display-4 text-primary">
            <i class="bi bi-code-slash"></i> LeaguesOfCode
        </h1>
        <p class="lead text-light">Lab Portal - ระบบจัดการภายในองค์กร</p>
        <hr class="my-4 bg-secondary">
    </div>

    <!-- Feature Cards -->
    <div class="row g-4">
        <!-- Member Management -->
        <div class="col-md-6">
            <a href="/sqli/" class="text-decoration-none">
                <div class="card bg-dark border-primary h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-people-fill display-1 text-primary mb-3"></i>
                        <h3 class="card-title text-white">Member Management</h3>
                        <p class="text-secondary">จัดการข้อมูลสมาชิก</p>
                        <p class="card-text text-light">
                            ค้นหา ลงทะเบียน และจัดการข้อมูลสมาชิกในระบบ
                        </p>
                        <span class="badge bg-primary">6 Features</span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Account Services -->
        <div class="col-md-6">
            <a href="/jwt/" class="text-decoration-none">
                <div class="card bg-dark border-info h-100 hover-card">
                    <div class="card-body text-center p-4">
                        <i class="bi bi-shield-lock-fill display-1 text-info mb-3"></i>
                        <h3 class="card-title text-white">Account Services</h3>
                        <p class="text-secondary">ระบบจัดการบัญชี</p>
                        <p class="card-text text-light">
                            Login, Dashboard และ API Services สำหรับผู้ใช้งาน
                        </p>
                        <span class="badge bg-info">4 Features</span>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- System Info -->
    <div class="row mt-5">
        <div class="col-12">
            <div class="card bg-dark border-secondary">
                <div class="card-body">
                    <h5 class="text-secondary"><i class="bi bi-info-circle"></i> System Information</h5>
                    <div class="row text-light">
                        <div class="col-md-4">
                            <small>Server: Apache/PHP 8.2</small>
                        </div>
                        <div class="col-md-4">
                            <small>Database: MySQL 8.0</small>
                        </div>
                        <div class="col-md-4">
                            <small>Status: <span class="text-success">Online</span></small>
                        </div>
                    </div>
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
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.3);
}
</style>

<?php require_once 'includes/footer.php'; ?>
