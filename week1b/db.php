<?php
// week1/db.php
// This file connects PHP to your MySQL database.
// We use PDO — it's safer than the old mysql_ functions.

$host     = 'localhost';      // XAMPP always uses localhost
$port     = '3307';          // ← Your custom XAMPP MySQL port
$dbname   = 'vintage_vault_db';
$username = 'root';           // Default XAMPP username
$password = '';               // Default XAMPP password is EMPTY

try {
    // Create the connection
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);

    // Tell PDO to throw errors instead of silently failing
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Make query results come back as associative arrays (like $row['name'])
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // If connection fails, stop everything and show the error
    die("❌ Database connection failed: " . $e->getMessage());
}
?>
