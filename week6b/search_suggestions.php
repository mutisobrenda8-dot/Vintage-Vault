<?php
require 'db.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if (strlen($q) < 2) { echo json_encode([]); exit; }

$stmt = $pdo->prepare(
    "SELECT id, name, price FROM products
     WHERE name LIKE ? AND stock > 0 LIMIT 6"
);
$stmt->execute(["%$q%"]);
$results = $stmt->fetchAll();

foreach ($results as &$r) {
    $r['price'] = number_format($r['price'], 2);
}
echo json_encode($results);
?>