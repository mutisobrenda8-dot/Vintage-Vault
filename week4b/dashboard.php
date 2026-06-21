<?php
// Week4b/dashboard.php
// Task 3 — Session-based welcome page

$pageTitle = 'Dashboard';
require 'includes/header.php';

// Protect page — redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: /vintage-vault/week4b//login.php');
    exit;
}
?>

<div class="container py-5">

    <!-- Welcome Banner -->
    <div style="background:linear-gradient(135deg,
                var(--vv-dark) 0%, #5c3d28 100%);
                padding:40px; border-radius:8px;
                margin-bottom:32px; text-align:center;">
        <div style="width:64px; height:64px;
                    background:var(--vv-gold);
                    color:var(--vv-dark); border-radius:50%;
                    display:flex; align-items:center;
                    justify-content:center;
                    font-family:var(--font-serif);
                    font-size:1.8rem; font-weight:700;
                    margin:0 auto 16px;">
            <?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?>
        </div>
        <h1 style="color:#f0e4cc; font-style:italic;
                   font-size:2rem; margin-bottom:8px;">
            Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?>!
        </h1>
        <p style="color:var(--vv-gold); font-size:0.85rem;
                  letter-spacing:1px;">
            You are logged in as
            <strong><?= ucfirst($_SESSION['user_role']) ?></strong>
        </p>
    </div>

    <!-- Session Info -->
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-6">
            <div class="vv-dashboard-card">
                <h5 style="font-family:var(--font-serif);
                           font-style:italic; margin-bottom:16px;">
                    📋 Session Information
                </h5>
                <table style="width:100%; font-size:0.88rem;">
                    <tr style="border-bottom:1px solid var(--vv-parchment);">
                        <td style="padding:8px 0; color:var(--vv-muted);
                                   font-size:0.75rem; letter-spacing:1px;
                                   text-transform:uppercase;">
                            User ID
                        </td>
                        <td style="padding:8px 0; font-weight:600;">
                            #<?= $_SESSION['user_id'] ?>
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--vv-parchment);">
                        <td style="padding:8px 0; color:var(--vv-muted);
                                   font-size:0.75rem; letter-spacing:1px;
                                   text-transform:uppercase;">
                            Name
                        </td>
                        <td style="padding:8px 0; font-weight:600;">
                            <?= htmlspecialchars($_SESSION['user_name']) ?>
                        </td>
                    </tr>
                    <tr style="border-bottom:1px solid var(--vv-parchment);">
                        <td style="padding:8px 0; color:var(--vv-muted);
                                   font-size:0.75rem; letter-spacing:1px;
                                   text-transform:uppercase;">
                            Role
                        </td>
                        <td style="padding:8px 0; font-weight:600;">
                            <?= ucfirst($_SESSION['user_role']) ?>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:8px 0; color:var(--vv-muted);
                                   font-size:0.75rem; letter-spacing:1px;
                                   text-transform:uppercase;">
                            Session ID
                        </td>
                        <td style="padding:8px 0; font-size:0.78rem;
                                   color:var(--vv-muted);">
                            <?= substr(session_id(), 0, 20) ?>...
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="vv-dashboard-card">
                <h5 style="font-family:var(--font-serif);
                           font-style:italic; margin-bottom:16px;">
                    🎯 Week 4 Achievements
                </h5>
                <div style="display:flex; flex-direction:column;
                            gap:10px;">
                    <?php
                    $tasks = [
                        '✅ PHP server-side processing',
                        '✅ Dynamic welcome page',
                        '✅ HTML form handling',
                        '✅ Registration form',
                        '✅ Login form',
                        '✅ Session-based authentication',
                        '✅ Professional folder structure',
                        '✅ Database connection',
                    ];
                    foreach ($tasks as $task):
                    ?>
                    <div style="display:flex; align-items:center;
                                gap:10px; font-size:0.85rem;
                                padding:8px 12px;
                                background:var(--vv-parchment);
                                border-radius:4px;">
                        <?= $task ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Form Demo -->
    <div class="vv-dashboard-card">
        <h5 style="font-family:var(--font-serif);
                   font-style:italic; margin-bottom:4px;">
            📬 Contact Form
        </h5>
        <p style="font-size:0.82rem; color:var(--vv-muted);
                  margin-bottom:20px;">
            Task 2 — HTML Forms and PHP Integration demo
        </p>

        <?php
        $contactMsg = '';
        if (isset($_POST['contact_submit'])) {
            $contactName    = htmlspecialchars(trim($_POST['contact_name'] ?? ''));
            $contactEmail   = htmlspecialchars(trim($_POST['contact_email'] ?? ''));
            $contactMessage = htmlspecialchars(trim($_POST['contact_message'] ?? ''));

            if ($contactName && $contactEmail && $contactMessage) {
                $contactMsg = "✅ Thank you $contactName! 
                    Your message has been received.
                    PHP processed your form submission successfully.";
            }
        }
        ?>

        <?php if ($contactMsg): ?>
            <div class="vv-alert-success"><?= $contactMsg ?></div>
        <?php endif; ?>

        <form method="POST" class="row g-3">
            <div class="col-12 col-md-6">
                <label style="font-size:0.7rem; letter-spacing:2px;
                              text-transform:uppercase;
                              color:var(--vv-brown); font-weight:700;
                              display:block; margin-bottom:6px;">
                    Your Name
                </label>
                <input type="text" name="contact_name"
                       class="form-control"
                       style="border:1px solid var(--vv-gold);
                              background:var(--vv-cream);
                              border-radius:0;"
                       placeholder="Jane Smith" required>
            </div>
            <div class="col-12 col-md-6">
                <label style="font-size:0.7rem; letter-spacing:2px;
                              text-transform:uppercase;
                              color:var(--vv-brown); font-weight:700;
                              display:block; margin-bottom:6px;">
                    Email Address
                </label>
                <input type="email" name="contact_email"
                       class="form-control"
                       style="border:1px solid var(--vv-gold);
                              background:var(--vv-cream);
                              border-radius:0;"
                       placeholder="you@example.com" required>
            </div>
            <div class="col-12">
                <label style="font-size:0.7rem; letter-spacing:2px;
                              text-transform:uppercase;
                              color:var(--vv-brown); font-weight:700;
                              display:block; margin-bottom:6px;">
                    Message
                </label>
                <textarea name="contact_message" rows="4"
                          class="form-control"
                          style="border:1px solid var(--vv-gold);
                                 background:var(--vv-cream);
                                 border-radius:0; resize:vertical;"
                          placeholder="Your message here..."
                          required></textarea>
            </div>
            <div class="col-12">
                <button type="submit" name="contact_submit"
                        class="btn btn-vv-primary px-5 py-2">
                    Send Message
                </button>
            </div>
        </form>
    </div>

    <!-- Logout -->
    <div style="text-align:center; margin-top:24px;">
        <a href="/vintage-vault/week4b//logout.php"
           style="font-size:0.8rem; letter-spacing:1px;
                  text-transform:uppercase; color:#c0522a;">
            🚪 Logout
        </a>
    </div>

</div>

<?php require 'includes/footer.php'; ?>