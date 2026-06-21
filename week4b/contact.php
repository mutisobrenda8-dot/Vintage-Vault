<?php
// week4b/contact.php
// Task 2 — HTML Forms and PHP Integration (standalone contact page)

$pageTitle = 'Contact Us';
require 'includes/header.php';

$success = '';
$error   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = trim($_POST['name']    ?? '');
    $email   = trim($_POST['email']   ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $error = 'Name, email and message are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        // In a real application this would send an email or save to a database.
        // For this demo, PHP simply processes and confirms the submission.
        $success = "Thank you, " . htmlspecialchars($name) . "! 
            Your message has been received. PHP processed your form 
            submission successfully using \$_POST.";
    }
}
?>

<div class="container py-5">

    <div style="max-width:640px; margin:0 auto;">

        <h1 style="font-family:var(--font-serif); font-style:italic;
                   color:var(--vv-dark); margin-bottom:4px;
                   text-align:center;">
            Contact Us
        </h1>
        <p style="color:var(--vv-muted); font-size:0.82rem;
                  margin-bottom:28px; text-align:center;">
            Week 4 — HTML Forms and PHP Integration
        </p>

        <?php if ($success): ?>
            <div class="vv-alert-success"><?= $success ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="vv-alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="vv-dashboard-card">
            <form method="POST" action="" data-validate>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-6">
                        <label style="font-size:0.7rem; letter-spacing:2px;
                                      text-transform:uppercase;
                                      color:var(--vv-brown); font-weight:700;
                                      display:block; margin-bottom:6px;">
                            Your Name *
                        </label>
                        <input type="text" name="name"
                               class="form-control"
                               style="border:1px solid var(--vv-gold);
                                      background:var(--vv-cream);
                                      border-radius:0;"
                               value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                               placeholder="Jane Smith" required>
                    </div>
                    <div class="col-12 col-md-6">
                        <label style="font-size:0.7rem; letter-spacing:2px;
                                      text-transform:uppercase;
                                      color:var(--vv-brown); font-weight:700;
                                      display:block; margin-bottom:6px;">
                            Email Address *
                        </label>
                        <input type="email" name="email"
                               class="form-control"
                               style="border:1px solid var(--vv-gold);
                                      background:var(--vv-cream);
                                      border-radius:0;"
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                               placeholder="you@example.com" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label style="font-size:0.7rem; letter-spacing:2px;
                                  text-transform:uppercase;
                                  color:var(--vv-brown); font-weight:700;
                                  display:block; margin-bottom:6px;">
                        Subject
                    </label>
                    <input type="text" name="subject"
                           class="form-control"
                           style="border:1px solid var(--vv-gold);
                                  background:var(--vv-cream);
                                  border-radius:0;"
                           value="<?= htmlspecialchars($_POST['subject'] ?? '') ?>"
                           placeholder="What is this regarding?">
                </div>

                <div class="mb-4">
                    <label style="font-size:0.7rem; letter-spacing:2px;
                                  text-transform:uppercase;
                                  color:var(--vv-brown); font-weight:700;
                                  display:block; margin-bottom:6px;">
                        Message *
                    </label>
                    <textarea name="message" rows="5"
                              class="form-control"
                              style="border:1px solid var(--vv-gold);
                                     background:var(--vv-cream);
                                     border-radius:0; resize:vertical;"
                              placeholder="Your message here..."
                              required><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
                </div>

                <button type="submit" class="btn btn-vv-primary w-100 py-2">
                    Send Message
                </button>

            </form>
        </div>

        <!-- How this works -->
        <div style="margin-top:24px; padding:16px 20px;
                    background:var(--vv-parchment);
                    border:1px solid var(--vv-gold); border-radius:4px;">
            <p style="font-size:0.8rem; color:var(--vv-text); margin:0;">
                <strong style="color:var(--vv-dark);">How this works:</strong>
                This form uses the HTML <code>&lt;form&gt;</code> tag with
                <code>method="POST"</code>. When submitted, PHP receives the
                data through the <code>$_POST</code> superglobal array,
                validates each field (checking it isn't empty and that the
                email is properly formatted), then responds with a
                confirmation — all without the page ever leaving
                <code>contact.php</code>.
            </p>
        </div>

    </div>

</div>

<?php require 'includes/footer.php'; ?>