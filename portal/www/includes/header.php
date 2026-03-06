<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeaguesOfCode Lab Portal</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Portal Theme Styles -->
    <style>
        :root {
            --primary: #0f172a;
            --secondary: #1e293b;
            --accent: #3b82f6;
            --accent-light: #60a5fa;
            --gold: #f59e0b;
            --text: #f1f5f9;
            --text-muted: #94a3b8;
            --border: rgba(148, 163, 184, 0.1);
        }

        * { font-family: 'Inter', sans-serif; }

        body {
            background: var(--primary);
            color: var(--text);
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
            padding: 0.75rem 0;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.4rem;
            color: var(--text) !important;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar-brand .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
        }

        .navbar-brand span { color: var(--accent); }

        .nav-link {
            color: var(--text-muted) !important;
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.5rem 1rem !important;
            transition: color 0.2s;
        }

        .nav-link:hover { color: var(--text) !important; }

        .btn-primary {
            background: var(--accent);
            border: none;
            font-weight: 600;
            padding: 0.5rem 1.25rem;
        }

        .btn-primary:hover { background: var(--accent-light); }

        /* Cards */
        .card {
            background: var(--secondary);
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .card-header {
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            border-bottom: 1px solid var(--border);
            border-radius: 12px 12px 0 0 !important;
        }

        /* Service Cards */
        .service-card {
            background: var(--primary);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            height: 100%;
            text-decoration: none;
            color: inherit;
            display: block;
            transition: all 0.3s ease;
        }

        .service-card:hover {
            border-color: var(--accent);
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            color: inherit;
        }

        .service-card .icon {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, var(--accent), var(--accent-light));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 20px;
        }

        .service-card h5 {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 10px;
        }

        .service-card p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 16px;
            line-height: 1.6;
        }

        .service-card .link {
            color: var(--accent);
            font-size: 0.85rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Forms */
        .form-control, .form-select {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid var(--border);
            color: var(--text);
        }

        .form-control:focus, .form-select:focus {
            background: rgba(15, 23, 42, 0.9);
            border-color: var(--accent);
            color: var(--text);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
        }

        .form-control::placeholder { color: var(--text-muted); }

        /* Tables */
        .table { color: var(--text); }
        .table thead th {
            border-bottom: 2px solid var(--border);
            color: var(--accent-light);
        }
        .table td { border-color: var(--border); }

        /* Alerts */
        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border-color: rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border-color: rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.15);
            border-color: rgba(59, 130, 246, 0.3);
            color: var(--accent-light);
        }

        /* Badge */
        .badge.bg-gold {
            background: var(--gold) !important;
            color: #000;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="/">
                <div class="logo-icon"><i class="bi bi-code-slash"></i></div>
                Leagues<span>Of</span>Code
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto me-3">
                    <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/members/">Members</a></li>
                    <li class="nav-item"><a class="nav-link" href="/sqli/">Resources</a></li>
                    <li class="nav-item"><a class="nav-link" href="/share/">Banking</a></li>
                    <li class="nav-item"><a class="nav-link" href="/api/">API</a></li>
                    <li class="nav-item"><a class="nav-link" href="/search/">Blog</a></li>
                </ul>
                <a href="/jwt/" class="btn btn-primary">Sign In</a>
            </div>
        </div>
    </nav>
