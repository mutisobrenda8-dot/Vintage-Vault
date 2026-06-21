<?php
session_start();

// Clear remember-me token from database and cookie
if (isset($_COOKIE['remember_token'])) {
    require 'db.php';
    $pdo->prepare("UPDATE users SET remember_token = NULL WHERE remember_token = ?")
        ->execute([$_COOKIE['remember_token']]);
    setcookie('remember_token', '', [
        'expires'  => time() - 3600,
        'path'     => '/vintage-vault/Week7b/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

$_SESSION = [];
session_destroy();
header('Location: /vintage-vault/Week7b/index.php');
exit;
?>
