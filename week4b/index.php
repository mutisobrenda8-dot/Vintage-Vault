<?php
// Week4/index.php
// Task 1 — Dynamic welcome page
// Demonstrates server-side PHP processing

$pageTitle = 'Home';
require 'includes/header.php';

// Dynamic greeting based on time of day
$hour = date('H');
if ($hour < 12) {
    $greeting = 'Good morning';
} elseif ($hour < 17) {
    $greeting = 'Good afternoon';
} else {
    $greeting = 'Good evening';
}

// Get name from URL if provided
$name = htmlspecialchars($_GET['name'] ?? '');
?>

<div class="container py-5">

    <!-- Hero Section -->
    <div style="text-align:center; padding:60px 20px;
                background:linear-gradient(135deg, var(--vv-dark) 0%,
                #5c3d28 100%); margin-bottom:40px; border-radius:8px;">
        <h1 style="color:#f0e4cc; font-style:italic; font-size:2.5rem;">
            <?= $greeting ?>
            <?= $name ? ", $name" : "" ?>!
        </h1>
        <p style="color:var(--vv-gold); font-size:0.9rem;
                  letter-spacing:1px; margin-top:8px;">
            Welcome to Vintage Vault — Week 4
        </p>
        <p style="color:#a08060; font-size:0.85rem; margin-top:4px;">
            Server-Side Components & Backend Development
        </p>
    </div>

    <!-- Dynamic Welcome Form -->
    <div class="vv-form-card" style="max-width:500px;">
        <h2>Try Dynamic PHP</h2>
        <p class="vv-form-subtitle">
            Enter your name to see server-side processing in action
        </p>
        <form method="GET" action="">
            <div class="mb-3">
                <label class="form-label">Your Name</label>
                <input type="text" name="name"
                       class="form-control"
                       value="<?= $name ?>"
                       placeholder="Enter your name">
            </div>
            <button type="submit" class="btn btn-vv-primary w-100">
                Generate Greeting
            </button>
        </form>

        <?php if ($name): ?>
        <div class="vv-alert-success" style="margin-top:16px;">
            ✅ PHP processed your name and generated:
            <strong>"<?= $greeting ?>, <?= $name ?>!"</strong>
        </div>
        <?php endif; ?>
    </div>

    <!-- Week 4 Tasks Overview -->
    <div class="row g-4 mt-4">
        <div class="col-12 col-md-4">
            <div class="vv-dashboard-card" style="text-align:center;">
                <div style="font-size:2.5rem; margin-bottom:12px;">📝</div>
                <h5 style="font-family:var(--font-serif);
                           font-style:italic;">
                    Task 1
                </h5>
                <p style="font-size:0.85rem; color:var(--vv-muted);">
                    Server-side PHP processing and dynamic pages
                </p>
                <span style="font-size:0.7rem; letter-spacing:1px;
                             text-transform:uppercase; color:#2d7a4f;">
                    ✅ Complete
                </span>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="vv-dashboard-card" style="text-align:center;">
                <div style="font-size:2.5rem; margin-bottom:12px;">🔐</div>
                <h5 style="font-family:var(--font-serif);
                           font-style:italic;">
                    Task 2 & 3
                </h5>
                <p style="font-size:0.85rem; color:var(--vv-muted);">
                    HTML forms, PHP integration and authentication
                </p>
                <a href="/week1-brenda/Week4b/login.php"
                   style="font-size:0.7rem; letter-spacing:1px;
                          text-transform:uppercase; color:var(--vv-brown);">
                    View Login →
                </a>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="vv-dashboard-card" style="text-align:center;">
                <div style="font-size:2.5rem; margin-bottom:12px;">📁</div>
                <h5 style="font-family:var(--font-serif);
                           font-style:italic;">
                    Task 4
                </h5>
                <p style="font-size:0.85rem; color:var(--vv-muted);">
                    Professional backend folder organization
                </p>
                <span style="font-size:0.7rem; letter-spacing:1px;
                             text-transform:uppercase; color:#2d7a4f;">
                    ✅ Complete
                </span>
            </div>
        </div>
    </div>

</div>

<?php require 'includes/footer.php'; ?>