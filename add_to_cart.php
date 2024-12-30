<?php
session_start();
include('db.php');

// Kiểm tra nếu người dùng đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập trước khi thêm sản phẩm vào giỏ hàng.");
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy dữ liệu từ form
    $product_id = intval($_POST['product_id']);
    $size = $_POST['size'];
    $quantity = intval($_POST['quantity']);

    // Kiểm tra thông tin sản phẩm
    $stmt = $conn->prepare("SELECT price, available_sizes, quantity FROM product WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("Sản phẩm không tồn tại.");
    }

    $product = $result->fetch_assoc();
    $price = $product['price'];
    $available_sizes = explode(',', $product['available_sizes']);
    $stock_quantity = $product['quantity'];

    // Kiểm tra kích thước hợp lệ
    if (!in_array($size, $available_sizes)) {
        die("Kích thước không hợp lệ.");
    }

    // Kiểm tra số lượng tồn kho
    if ($quantity > $stock_quantity) {
        die("Không đủ số lượng trong kho.");
    }

    $total_amount = $price * $quantity;

    // Kiểm tra transaction tồn tại
    $stmt = $conn->prepare("SELECT transaction_id FROM transaction WHERE user_id = ? AND status = 'pending'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Tạo transaction mới nếu chưa tồn tại
        $stmt = $conn->prepare("INSERT INTO transaction (user_id, total_amount, shipping_fee, status) VALUES (?, 0, 0, 'pending')");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $transaction_id = $stmt->insert_id;
    } else {
        // Lấy transaction_id nếu đã tồn tại
        $transaction = $result->fetch_assoc();
        $transaction_id = $transaction['transaction_id'];
    }

    // Kiểm tra sản phẩm đã tồn tại trong giỏ hàng chưa
    $stmt = $conn->prepare("
        SELECT quantity FROM `order` 
        WHERE transaction_id = ? AND product_id = ? AND size = ?
    ");
    $stmt->bind_param("iis", $transaction_id, $product_id, $size);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Nếu sản phẩm đã tồn tại, cập nhật số lượng
        $existing_order = $result->fetch_assoc();
        $new_quantity = $existing_order['quantity'] + $quantity;

        // Cập nhật số lượng và tổng tiền trong `order`
        $stmt = $conn->prepare("
            UPDATE `order` SET quantity = ?, total_amount = total_amount + ?
            WHERE transaction_id = ? AND product_id = ? AND size = ?
        ");
        $stmt->bind_param("idiis", $new_quantity, $total_amount, $transaction_id, $product_id, $size);
        $stmt->execute();
    } else {
        // Nếu sản phẩm chưa tồn tại, thêm mới
        $stmt = $conn->prepare("
            INSERT INTO `order` (user_id, transaction_id, product_id, size, quantity, total_amount, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->bind_param("iiisid", $user_id, $transaction_id, $product_id, $size, $quantity, $total_amount);
        $stmt->execute();
    }

    // Cập nhật tổng tiền trong `transaction`
    $stmt = $conn->prepare("
        UPDATE transaction SET total_amount = (
            SELECT SUM(total_amount) FROM `order` WHERE transaction_id = ?
        ) WHERE transaction_id = ?
    ");
    $stmt->bind_param("ii", $transaction_id, $transaction_id);
    $stmt->execute();

    // Chuyển hướng đến trang giỏ hàng
    header("Location: cart.php");
    exit();
}
?>
