<?php
// Week7/password_hashing_demo.php
// Visually illustrates password hashing using the same bcrypt function the system uses

$pageTitle = 'Password Hashing Demo';
require 'db.php';
require 'includes/header.php';

$plain = $_POST['plain_password'] ?? '';
$hash  = '';
$verifyResult = null;
$verifyHash   = $_POST['verify_hash'] ?? '';
$verifyPlain  = $_POST['verify_plain'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hash_action'])) {
    $hash = password_hash($plain, PASSWORD_DEFAULT);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_action'])) {
    $verifyResult = password_verify($verifyPlain, $verifyHash);
}

// Pull a real example from the database to show it's the same mechanism
$realUser = $pdo->query(
    "SELECT email, password FROM users WHERE email='admin@vintagevault.com'"
)->fetch();
?>

<div class="container py-5">
    <div style="max-width:760px; margin:0 auto;">

        <h1 style="font-family:var(--font-serif); font-style:italic;
                   color:var(--vv-dark); margin-bottom:4px;">
            Password Hashing
        </h1>
        <p style="color:var(--vv-muted); font-size:0.82rem; margin-bottom:28px;">
            Week 7 — User Authentication. Demonstrates how
            <code>password_hash()</code> and <code>password_verify()</code>
            protect Vintage Vault user passwords.
        </p>

        <!-- Hash a password -->
        <div style="background:#faf6ef; border:1px solid var(--vv-gold);
                    border-radius:6px; padding:24px; margin-bottom:20px;">
            <h5 style="font-family:var(--font-serif); font-style:italic;
                       margin-bottom:4px;">Step 1 — Hash a Password</h5>
            <div style="background:#2a1e14; border-radius:4px;
                        padding:10px 14px; margin-bottom:16px;
                        font-family:monospace; font-size:0.78rem; color:#c9b49a;">
                <span style="color:#7a9b4a;">$hash =</span>
                password_hash($plain, PASSWORD_DEFAULT);
            </div>
            <form method="POST">
                <input type="hidden" name="hash_action" value="1">
                <div class="mb-2">
                    <input type="text" name="plain_password"
                           class="form-control"
                           style="border:1px solid var(--vv-gold); border-radius:0;"
                           placeholder="Type any plain text password, e.g. mypassword123"
                           value="<?= htmlspecialchars($plain) ?>" required>
                </div>
                <button type="submit" class="btn btn-vv-primary">
                    Generate Hash
                </button>
            </form>

            <?php if ($hash): ?>
            <div style="margin-top:18px;">
                <div style="font-size:0.7rem; letter-spacing:2px;
                            text-transform:uppercase; color:var(--vv-muted);
                            margin-bottom:6px;">Plain Text Input</div>
                <div style="background:#fde8e8; padding:10px 14px;
                            border-radius:4px; font-family:monospace;
                            font-size:0.85rem; color:#8b1a1a; margin-bottom:14px;
                            word-break:break-all;">
                    <?= htmlspecialchars($plain) ?>
                </div>
                <div style="font-size:0.7rem; letter-spacing:2px;
                            text-transform:uppercase; color:var(--vv-muted);
                            margin-bottom:6px;">Bcrypt Hash Output (stored in DB)</div>
                <div style="background:#d4f0e0; padding:10px 14px;
                            border-radius:4px; font-family:monospace;
                            font-size:0.82rem; color:#1a5c36; word-break:break-all;">
                    <?= htmlspecialchars($hash) ?>
                </div>
                <p style="font-size:0.75rem; color:var(--vv-muted); margin-top:10px;">
                    Notice the hash starts with <code>$2y$10$</code> — that's the
                    bcrypt algorithm identifier and cost factor. Run this twice with
                    the same password and you'll get a <em>different</em> hash each
                    time (bcrypt uses a random salt), which is why we use
                    <code>password_verify()</code> to check it instead of comparing strings directly.
                </p>
            </div>
            <?php endif; ?>
        </div>

        <!-- Verify a password -->
        <div style="background:#faf6ef; border:1px solid var(--vv-gold);
                    border-radius:6px; padding:24px; margin-bottom:20px;">
            <h5 style="font-family:var(--font-serif); font-style:italic;
                       margin-bottom:4px;">Step 2 — Verify a Password Against a Hash</h5>
            <div style="background:#2a1e14; border-radius:4px;
                        padding:10px 14px; margin-bottom:16px;
                        font-family:monospace; font-size:0.78rem; color:#c9b49a;">
                <span style="color:#7a9b4a;">password_verify</span>($plain, $hash);
                <span style="color:#9a7a58;">// returns true or false</span>
            </div>
            <form method="POST">
                <input type="hidden" name="verify_action" value="1">
                <div class="mb-2">
                    <input type="text" name="verify_plain" class="form-control"
                           style="border:1px solid var(--vv-gold); border-radius:0;"
                           placeholder="Plain text password to test"
                           value="<?= htmlspecialchars($verifyPlain) ?>" required>
                </div>
                <div class="mb-2">
                    <textarea name="verify_hash" rows="2" class="form-control"
                              style="border:1px solid var(--vv-gold); border-radius:0;
                                     font-family:monospace; font-size:0.8rem;"
                              placeholder="Paste a hash from Step 1 here"
                              required><?= htmlspecialchars($verifyHash) ?></textarea>
                </div>
                <button type="submit" class="btn btn-vv-primary">
                    Verify
                </button>
            </form>

            <?php if ($verifyResult !== null): ?>
                <?php if ($verifyResult): ?>
                    <div class="vv-alert-success" style="margin-top:14px;">
                        ✅ Match! This password is correct for that hash —
                        this is exactly what runs in <code>login.php</code> when you sign in.
                    </div>
                <?php else: ?>
                    <div class="vv-alert-error" style="margin-top:14px;">
                        ❌ No match. This is what happens when someone enters the
                        wrong password at login.
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Real example from the live DB -->
        <?php if ($realUser): ?>
        <div style="background:#faf6ef; border:1px solid var(--vv-gold);
                    border-radius:6px; padding:24px;">
            <h5 style="font-family:var(--font-serif); font-style:italic;
                       margin-bottom:4px;">Real Example — Live From the Database</h5>
            <p style="font-size:0.8rem; color:var(--vv-muted); margin-bottom:12px;">
                This is the actual stored hash for
                <strong><?= htmlspecialchars($realUser['email']) ?></strong>
                in the <code>vintage_vault_db.users</code> table right now:
            </p>
            <div style="background:#2a1e14; border-radius:4px;
                        padding:10px 14px; font-family:monospace;
                        font-size:0.78rem; color:#c9b49a; word-break:break-all;">
                <?= htmlspecialchars($realUser['password']) ?>
            </div>
            <p style="font-size:0.75rem; color:var(--vv-muted); margin-top:10px;">
                No matter how many times an attacker views this database, the
                original password is never visible — only
                <code>password_verify()</code> can confirm a match.
            </p>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php require 'includes/footer.php'; ?>