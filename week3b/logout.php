<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /vintage-vault/week3b//index.php');
exit;
?>