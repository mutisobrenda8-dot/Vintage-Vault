<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /vintage-vault/week6b/index.php');
exit;
?>