<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LeaguesOfCode Thailand - Code. Compete. Connect.</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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

        /* Hero */
        .hero {
            padding: 80px 0 60px;
            background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .hero h1 {
            font-size: 3rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .hero h1 span { color: var(--accent); }

        .hero p {
            font-size: 1.1rem;
            color: var(--text-muted);
            max-width: 600px;
        }

        .hero-stats {
            display: flex;
            gap: 40px;
            margin-top: 40px;
        }

        .hero-stat h3 {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent);
            margin-bottom: 4px;
        }

        .hero-stat p {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin: 0;
        }

        /* Services */
        .services {
            padding: 80px 0;
            background: var(--secondary);
        }

        .section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .section-header h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .section-header p {
            color: var(--text-muted);
            max-width: 600px;
            margin: 0 auto;
        }

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

        /* Announcement */
        .announcement {
            padding: 60px 0;
            background: var(--primary);
        }

        .announcement-card {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(59, 130, 246, 0.05));
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 16px;
            padding: 40px;
        }

        .announcement-card .badge {
            background: var(--gold);
            color: #000;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 6px 12px;
            border-radius: 20px;
            margin-bottom: 16px;
            display: inline-block;
        }

        .announcement-card h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .announcement-card p {
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        /* Partners */
        .partners {
            padding: 60px 0;
            background: var(--secondary);
            text-align: center;
        }

        .partners h6 {
            color: var(--text-muted);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 30px;
        }

        .partner-logos {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 50px;
            flex-wrap: wrap;
            opacity: 0.6;
        }

        .partner-logos span {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-muted);
        }

        /* Footer */
        footer {
            background: var(--primary);
            border-top: 1px solid var(--border);
            padding: 50px 0 30px;
        }

        .footer-brand {
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 12px;
        }

        .footer-brand span { color: var(--accent); }

        .footer-desc {
            color: var(--text-muted);
            font-size: 0.9rem;
            max-width: 300px;
        }

        .footer-links h6 {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 16px;
        }

        .footer-links ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: var(--text-muted);
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        .footer-links a:hover { color: var(--text); }

        .footer-bottom {
            border-top: 1px solid var(--border);
            margin-top: 40px;
            padding-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 16px;
        }

        .footer-bottom p {
            color: var(--text-muted);
            font-size: 0.85rem;
            margin: 0;
        }

        .social-links {
            display: flex;
            gap: 16px;
        }

        .social-links a {
            color: var(--text-muted);
            font-size: 1.2rem;
            transition: color 0.2s;
        }

        .social-links a:hover { color: var(--accent); }

        @media (max-width: 768px) {
            .hero h1 { font-size: 2rem; }
            .hero-stats { flex-wrap: wrap; gap: 24px; }
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
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="/members/">Members</a></li>
                    <li class="nav-item"><a class="nav-link" href="/share/">Banking</a></li>
                    <li class="nav-item"><a class="nav-link" href="/api/">API Tools</a></li>
                    <li class="nav-item"><a class="nav-link" href="/search/">Blog</a></li>
                </ul>
                <a href="/account/" class="btn btn-primary">Sign In</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <h1>Build Your Future in <span>Tech</span></h1>
                    <p>Join Thailand's premier coding community. Compete in hackathons, learn from industry experts, and connect with developers nationwide.</p>
                    <div class="d-flex gap-3 mt-4">
                        <a href="/account/" class="btn btn-primary btn-lg">Get Started</a>
                        <a href="/members/" class="btn btn-outline-light btn-lg">Find Members</a>
                    </div>
                    <div class="hero-stats">
                        <div class="hero-stat">
                            <h3>5,000+</h3>
                            <p>Active Members</p>
                        </div>
                        <div class="hero-stat">
                            <h3>120+</h3>
                            <p>Events Hosted</p>
                        </div>
                        <div class="hero-stat">
                            <h3>50+</h3>
                            <p>Partner Companies</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services" id="services">
        <div class="container">
            <div class="section-header">
                <h2>Platform Services</h2>
                <p>Everything you need to manage your coding journey and connect with the community</p>
            </div>
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <a href="/members/" class="service-card">
                        <div class="icon"><i class="bi bi-people-fill"></i></div>
                        <h5>Member Directory</h5>
                        <p>Search and connect with fellow developers. Find teammates for your next project or hackathon.</p>
                        <span class="link">Browse Members <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="/account/" class="service-card">
                        <div class="icon"><i class="bi bi-person-circle"></i></div>
                        <h5>Account Portal</h5>
                        <p>Manage your profile, view your achievements, and access exclusive member resources.</p>
                        <span class="link">Sign In <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="/profile/" class="service-card">
                        <div class="icon"><i class="bi bi-image"></i></div>
                        <h5>Profile Settings</h5>
                        <p>Upload your avatar, update your bio, and customize your public profile page.</p>
                        <span class="link">Update Profile <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="/share/" class="service-card">
                        <div class="icon"><i class="bi bi-bank"></i></div>
                        <h5>Credit System</h5>
                        <p>Manage your LOC credits. Transfer to other members or redeem for exclusive rewards.</p>
                        <span class="link">Open Banking <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="/api/" class="service-card">
                        <div class="icon"><i class="bi bi-braces"></i></div>
                        <h5>Developer API</h5>
                        <p>Access our REST API for integrations. Fetch member data, events, and more.</p>
                        <span class="link">API Documentation <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="/search/" class="service-card">
                        <div class="icon"><i class="bi bi-journal-code"></i></div>
                        <h5>Tech Blog</h5>
                        <p>Read articles from community members. Tips, tutorials, and industry insights.</p>
                        <span class="link">Read Blog <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="/resources/" class="service-card">
                        <div class="icon"><i class="bi bi-database"></i></div>
                        <h5>Resource Library</h5>
                        <p>Access training materials, documentation, and learning resources for all skill levels.</p>
                        <span class="link">Browse Resources <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
                <div class="col-md-6 col-lg-4">
                    <a href="/evil/" class="service-card">
                        <div class="icon"><i class="bi bi-link-45deg"></i></div>
                        <h5>Partner Portal</h5>
                        <p>For our corporate partners. Access recruitment tools and sponsorship dashboards.</p>
                        <span class="link">Partner Access <i class="bi bi-arrow-right"></i></span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Announcement -->
    <section class="announcement">
        <div class="container">
            <div class="announcement-card">
                <span class="badge"><i class="bi bi-megaphone-fill me-1"></i> Announcement</span>
                <h3>Bootcamp 2026 Registration Now Open!</h3>
                <p>Join our intensive 12-week coding bootcamp. Learn web development, cloud computing, and more from industry professionals. Limited seats available.</p>
                <a href="/account/" class="btn btn-primary">Register Now</a>
            </div>
        </div>
    </section>

    <!-- Partners -->
    <section class="partners">
        <div class="container">
            <h6>Trusted by Leading Tech Companies</h6>
            <div class="partner-logos">
                <span>NEXGEN</span>
                <span>CYBERTEK</span>
                <span>DATAFLOW</span>
                <span>CLOUDNINE</span>
                <span>BYTECRAFT</span>
                <span>SYNTHEX</span>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <div class="footer-brand">Leagues<span>Of</span>Code</div>
                    <p class="footer-desc">Thailand's premier coding community. Building the next generation of tech talent since 2020.</p>
                </div>
                <div class="col-6 col-lg-2 footer-links">
                    <h6>Platform</h6>
                    <ul>
                        <li><a href="/members/">Members</a></li>
                        <li><a href="/account/">Account</a></li>
                        <li><a href="/search/">Blog</a></li>
                        <li><a href="/api/">API</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2 footer-links">
                    <h6>Resources</h6>
                    <ul>
                        <li><a href="/resources/">Training</a></li>
                        <li><a href="/profile/">Settings</a></li>
                        <li><a href="/share/">Credits</a></li>
                        <li><a href="#">Help Center</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2 footer-links">
                    <h6>Company</h6>
                    <ul>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Careers</a></li>
                        <li><a href="/evil/">Partners</a></li>
                        <li><a href="#">Contact</a></li>
                    </ul>
                </div>
                <div class="col-6 col-lg-2 footer-links">
                    <h6>Legal</h6>
                    <ul>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Terms of Service</a></li>
                        <li><a href="#">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2026 LeaguesOfCode Thailand. All rights reserved. | v1.0.1</p>
                <div class="social-links">
                    <a href="#"><i class="bi bi-facebook"></i></a>
                    <a href="#"><i class="bi bi-twitter-x"></i></a>
                    <a href="#"><i class="bi bi-linkedin"></i></a>
                    <a href="#"><i class="bi bi-github"></i></a>
                    <a href="#"><i class="bi bi-discord"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
