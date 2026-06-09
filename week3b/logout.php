<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /week1-brenda/week3b/index.php');
exit;
?>