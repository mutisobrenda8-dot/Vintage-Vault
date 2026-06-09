<?php
session_start();
$_SESSION = [];
session_destroy();
header('Location: /week1-brenda/Week4b/index.php');
exit;
?>