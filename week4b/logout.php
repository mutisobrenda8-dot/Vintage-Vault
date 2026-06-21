<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /vintage-vault/week4b//index.php');
exit;
?>