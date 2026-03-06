<?php
require_once '../config.php';
header('X-Powered-By: PHP/8.2');

$results = [];
$search = '';
$error = '';

if (isset($_GET['q'])) {
    $search = $_GET['q'];
    $conn = getDBConnection();

    // Search query
    $query = "SELECT id, name, email, department, status FROM members WHERE name LIKE '%$search%' OR department LIKE '%$search%'";

    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
    } else {
        $error = "Database error: " . $conn->error;
    }

    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Search - LeaguesOfCode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --loc-navy: #0a1628;
            --loc-blue: #1e3a5f;
            --loc-accent: #3b82f6;
            --loc-light: #64748b;
        }
        body {
            background: linear-gradient(135deg, var(--loc-navy) 0%, var(--loc-blue) 100%);
            min-height: 100vh;
            color: #e2e8f0;
        }
        .navbar {
            background: rgba(10, 22, 40, 0.95) !important;
            border-bottom: 1px solid rgba(59, 130, 246, 0.3);
        }
        .navbar-brand { font-weight: 700; font-size: 1.5rem; }
        .navbar-brand span { color: var(--loc-accent); }
        .card {
            background: rgba(30, 58, 95, 0.6);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 12px;
        }
        .card-header {
            background: rgba(59, 130, 246, 0.1);
            border-bottom: 1px solid rgba(59, 130, 246, 0.2);
        }
        .form-control {
            background: rgba(10, 22, 40, 0.8);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #e2e8f0;
        }
        .form-control:focus {
            background: rgba(10, 22, 40, 0.9);
            border-color: var(--loc-accent);
            color: #e2e8f0;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }
        .form-control::placeholder { color: var(--loc-light); }
        .btn-primary {
            background: var(--loc-accent);
            border-color: var(--loc-accent);
        }
        .btn-primary:hover {
            background: #2563eb;
            border-color: #2563eb;
        }
        .table { color: #e2e8f0; }
        .table thead th {
            border-bottom: 2px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
        }
        .table td { border-color: rgba(59, 130, 246, 0.1); }
        .badge-active { background: #10b981; }
        .badge-inactive { background: #ef4444; }
        footer {
            background: rgba(10, 22, 40, 0.95);
            border-top: 1px solid rgba(59, 130, 246, 0.3);
            padding: 20px 0;
            margin-top: 60px;
        }
        footer p { margin: 0; color: var(--loc-light); font-size: 0.9rem; }
        .alert-danger {
            background: rgba(239, 68, 68, 0.2);
            border-color: rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="/">
                <i class="bi bi-code-slash me-2"></i>Leagues<span>Of</span>Code
            </a>
            <span class="navbar-text">Member Search</span>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="bi bi-people me-2"></i>Member Directory</h4>
                        <small class="text-muted">Search organization members</small>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="mb-4">
                            <div class="input-group">
                                <input type="text" name="q" class="form-control form-control-lg"
                                       placeholder="Search by name or department..."
                                       value="<?php echo htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-search"></i> Search
                                </button>
                            </div>
                        </form>

                        <?php if ($error): ?>
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i><?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($results)): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Department</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($results as $member): ?>
                                        <tr>
                                            <td><?php echo $member['id']; ?></td>
                                            <td><?php echo $member['name']; ?></td>
                                            <td><?php echo $member['email']; ?></td>
                                            <td><?php echo $member['department']; ?></td>
                                            <td>
                                                <span class="badge <?php echo $member['status'] == 'active' ? 'badge-active' : 'badge-inactive'; ?>">
                                                    <?php echo ucfirst($member['status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <p class="text-muted mt-2">
                                <small>Found <?php echo count($results); ?> member(s)</small>
                            </p>
                        <?php elseif ($search && empty($error)): ?>
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>No members found matching "<?php echo htmlspecialchars($search); ?>"
                            </div>
                        <?php endif; ?>

                        <?php if (!$search): ?>
                            <div class="text-center text-muted py-4">
                                <i class="bi bi-search" style="font-size: 3rem;"></i>
                                <p class="mt-3">Enter a search term to find members</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="bi bi-download me-2"></i>Export Members</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="export.php">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Department Filter</label>
                                    <input type="text" name="dept" class="form-control" placeholder="e.g. Engineering">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Export Format</label>
                                    <select name="format" class="form-control">
                                        <option value="csv">CSV</option>
                                        <option value="json">JSON</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">
                                <i class="bi bi-file-earmark-arrow-down me-2"></i>Export Data
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <p>&copy; 2026 LeaguesOfCode TH - Cyber Security Bootcamp</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
