<?php
session_start();
include('db.php');

// Kiểm tra nếu chưa đăng nhập thì chuyển về trang đăng nhập
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Lấy thông tin từ session
$username = $_SESSION['username'];

// Lấy dữ liệu giỏ hàng
$query = "SELECT o.quantity, o.size, p.name, p.image, p.price, p.product_id 
          FROM `order` o 
          JOIN product p ON o.product_id = p.product_id 
          WHERE o.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán</title>
    <link rel="stylesheet" href="css/checkout.css">
</head>
<body>
<div class="container">
    <h1>Thanh Toán</h1>
    <div class="checkout-container">
        <!-- Nhập thông tin khách hàng -->
        <div class="checkout-section">
            <h3>Nhập thông tin khách hàng</h3>
            <input type="text" placeholder="Số điện thoại" required>
            <button class="button-primary">Tiếp tục</button>
        </div>

        <!-- Phương thức giao hàng -->
        <div class="checkout-section">
            <h3>Phương thức giao hàng</h3>
            <div class="method-options">
                <label>
                    <input type="radio" name="shipping" checked> Miễn phí giao hàng - 0₫
                </label>
                <label>
                    <input type="radio" name="shipping">Ship nhanh - 25,000₫
                </label>
            </div>

            <h3>Phương thức thanh toán</h3>
            <div class="method-options">
                <label>
                    <input type="radio" name="payment" checked>
                    Thanh toán khi nhận hàng
                </label>
                <label>
                    <input type="radio" name="payment">
                    Chuyển khoản ngân hàng
                </label>
            </div>
        </div>
    </div>

    <!-- Bảng sản phẩm -->
    <table class="product-table">
        <thead>
            <tr>
                <th>Hình ảnh</th>
                <th>Tên sản phẩm</th>
                <th>Mã sản phẩm</th>
                <th>Số lượng</th>
                <th>Giá</th>
                <th>Tổng</th>
            </tr>
        </thead>
        <tbody>
        <?php
                $total = 0;
                while ($row = $result->fetch_assoc()) {
                    $subtotal = $row['quantity'] * $row['price'];
                    $total += $subtotal;
                    echo "<tr>
                            <td><img src='{$row['image']}' alt='{$row['name']}' width='100'></td>
                            <td>{$row['name']} (Size: {$row['size']})</td>
                            <td>{$row['quantity']}</td>
                            <td>" . number_format($row['price'], 0, ',', '.') . "₫</td>
                            <td>" . number_format($subtotal, 0, ',', '.') . "₫</td>
                        </tr>";
                }
                ?>
</tbody>

    </table>

    <!-- Tổng kết -->
    <div class="checkout-section">
        <h3>Địa chỉ giao hàng</h3>
        <input type="text" placeholder="Địa chỉ">
        <select>
            <option>Hà Nội</option>
            <option>TP. Hồ Chí Minh</option>
            <option>Đà Nẵng</option>
        </select>

        <div class="summary">
            <div class="summary-row">
                <span>Thành tiền:</span>
                <span><?= number_format(4590000, 0, ',', '.') ?>₫</span>
            </div>
            <div class="summary-row">
                <span>Miễn phí giao hàng:</span>
                <span>0₫</span>
            </div>
            <div class="summary-row">
                <span>Tổng:</span>
                <span><?= number_format(4590000, 0, ',', '.') ?>₫</span>
            </div>
        </div>

        <button class="checkout-btn">MUA HÀNG</button>
    </div>
</div>
</body>
</html>
