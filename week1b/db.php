
<?php
$env = parse_ini_file('.env');
$host = $env['sql305.infinityfree.com'];
$username = $env['if0_42141673'];
$password = $env['(Your vPanel Password)'];
$dbname = $env['f0_42141673_BMM'];

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