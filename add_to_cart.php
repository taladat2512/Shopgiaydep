<?php
session_start();
include('db.php');

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $size = $_POST['size'];
    $quantity = intval($_POST['quantity']);

    // Lấy thông tin sản phẩm từ bảng `product`
    $stmt = $conn->prepare("SELECT price FROM `product` WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Sản phẩm không tồn tại.");
    }

    $product = $result->fetch_assoc();
    $price = $product['price'];
    $total_amount = $price * $quantity;

    // Kiểm tra nếu đã có transaction cho user này
    $stmt = $conn->prepare("SELECT transaction_id FROM `transaction` WHERE user_id = ? AND status = 'pending'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Nếu chưa có, tạo một transaction mới
        $stmt = $conn->prepare("INSERT INTO `transaction` (user_id, total_amount, status) VALUES (?, 0, 'pending')");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $transaction_id = $stmt->insert_id;
    } else {
        // Nếu đã có, lấy `transaction_id`
        $transaction = $result->fetch_assoc();
        $transaction_id = $transaction['transaction_id'];
    }

    // Cập nhật tổng số tiền của transaction
    $stmt = $conn->prepare("UPDATE `transaction` SET total_amount = total_amount + ? WHERE transaction_id = ?");
    $stmt->bind_param("di", $total_amount, $transaction_id);
    $stmt->execute();

    // Thêm sản phẩm vào bảng `order`
    $stmt = $conn->prepare("
        INSERT INTO `order` (user_id, transaction_id, product_id, size, quantity, total_amount, status)
        VALUES (?, ?, ?, ?, ?, ?, 'pending')
    ");
    $stmt->bind_param("iiisid", $user_id, $transaction_id, $product_id, $size, $quantity, $total_amount);
    if ($stmt->execute()) {
        header("Location: cart.php");
        exit();
    } else {
        die("Lỗi khi thêm vào giỏ hàng: " . $stmt->error);
    }
}
?>
