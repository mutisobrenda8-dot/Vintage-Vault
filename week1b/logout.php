<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /vintage-vault/week1b/index.php');
exit;
?>