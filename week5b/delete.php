<?php
// Week5/delete.php
// CRUD — DELETE operation

require 'db.php';

$id = $_GET['id'] ?? null;

if ($id && is_numeric($id)) {
    // DELETE — Remove record from database
    $pdo->prepare(
        "DELETE FROM products WHERE id = ?"
    )->execute([$id]);
}

header('Location: /week1-brenda/Week5b/index.php?deleted=1');
exit;
?>