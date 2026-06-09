<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'redirect' => true]);
    exit;
}

$action     = $_POST['action']     ?? '';
$product_id = (int)($_POST['product_id'] ?? 0);
$user_id    = $_SESSION['user_id'];

// ADD
if ($action === 'add') {
    $stmt = $pdo->prepare(
        "SELECT * FROM products WHERE id=? AND stock>0"
    );
    $stmt->execute([$product_id]);
    if (!$stmt->fetch()) {
        echo json_encode([
            'success' => false,
            'message' => 'Product not available.'
        ]);
        exit;
    }
    $stmt = $pdo->prepare(
        "SELECT * FROM cart WHERE user_id=? AND product_id=?"
    );
    $stmt->execute([$user_id, $product_id]);
    if ($stmt->fetch()) {
        $pdo->prepare(
            "UPDATE cart SET quantity=quantity+1
             WHERE user_id=? AND product_id=?"
        )->execute([$user_id, $product_id]);
    } else {
        $pdo->prepare(
            "INSERT INTO cart (user_id,product_id,quantity)
             VALUES (?,?,1)"
        )->execute([$user_id, $product_id]);
    }
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id=?"
    );
    $stmt->execute([$user_id]);
    echo json_encode([
        'success'    => true,
        'cart_count' => (int)$stmt->fetchColumn()
    ]);
    exit;
}

// REMOVE
if ($action === 'remove') {
    $pdo->prepare(
        "DELETE FROM cart WHERE user_id=? AND product_id=?"
    )->execute([$user_id, $product_id]);
    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id=?"
    );
    $stmt->execute([$user_id]);
    echo json_encode([
        'success'    => true,
        'cart_count' => (int)$stmt->fetchColumn()
    ]);
    exit;
}

// UPDATE
if ($action === 'update') {
    $qty = (int)($_POST['quantity'] ?? 1);
    if ($qty <= 0) {
        $pdo->prepare(
            "DELETE FROM cart WHERE user_id=? AND product_id=?"
        )->execute([$user_id, $product_id]);
    } else {
        $pdo->prepare(
            "UPDATE cart SET quantity=?
             WHERE user_id=? AND product_id=?"
        )->execute([$qty, $user_id, $product_id]);
    }
    $stmt = $pdo->prepare(
        "SELECT p.price*? FROM products p WHERE p.id=?"
    );
    $stmt->execute([$qty, $product_id]);
    $itemTotal = number_format((float)$stmt->fetchColumn(), 2);

    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(p.price*c.quantity),0)
         FROM cart c JOIN products p ON c.product_id=p.id
         WHERE c.user_id=?"
    );
    $stmt->execute([$user_id]);
    $cartTotal = number_format((float)$stmt->fetchColumn(), 2);

    $stmt = $pdo->prepare(
        "SELECT COALESCE(SUM(quantity),0) FROM cart WHERE user_id=?"
    );
    $stmt->execute([$user_id]);
    echo json_encode([
        'success'    => true,
        'item_total' => $itemTotal,
        'cart_total' => $cartTotal,
        'cart_count' => (int)$stmt->fetchColumn()
    ]);
    exit;
}

echo json_encode(['success'=>false,'message'=>'Invalid action.']);
?>