<?php
session_start();
include('db.php');

// Kiểm tra nếu chưa đăng nhập thì chuyển về trang đăng nhập
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Lấy user_id từ session
$user_id = $_SESSION['user_id'];

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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thanh Toán</title>
    <link rel="stylesheet" href="css/checkout.css">
</head>
<body>
<div class="checkout-container">
    <h1>Thanh Toán</h1>
    <form action="process_checkout.php" method="POST">
        <!-- Nhập thông tin khách hàng -->
        <div class="section">
            <h2>Nhập Thông Tin Khách Hàng</h2>
            <input type="text" name="phone" placeholder="Số điện thoại" required>
            <button type="button" class="btn-continue">Tiếp Tục</button>
        </div>

        <!-- Chọn phương thức giao hàng -->
        <div class="section">
            <h2>Phương Thức Giao Hàng</h2>
            <label>
                <input type="radio" name="shipping" value="free" checked> Miễn phí giao hàng - 0₫
            </label>
            <label>
                <input type="radio" name="shipping" value="express"> Ship nhanh - 25.000₫
            </label>
        </div>

        <!-- Chọn phương thức thanh toán -->
        <div class="section">
            <h2>Phương Thức Thanh Toán</h2>
            <label>
                <input type="radio" name="payment" value="cod" checked> Thanh toán khi nhận hàng
            </label>
            <label>
                <input type="radio" name="payment" value="bank_transfer"> Chuyển khoản ngân hàng
            </label>
        </div>

        <!-- Hiển thị tóm tắt giỏ hàng -->
        <div class="section cart-summary">
            <h2>Tóm Tắt Giỏ Hàng</h2>
            <table>
                <thead>
                <tr>
                    <th>Hình ảnh</th>
                    <th>Tên sản phẩm</th>
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
            <div class="total">
                <p>Thành tiền: <?= number_format($total, 0, ',', '.') ?>₫</p>
                <p>Phí giao hàng: <span id="shipping-fee">0₫</span></p>
                <p>Tổng cộng: <span id="total-amount"><?= number_format($total, 0, ',', '.') ?>₫</span></p>
            </div>
        </div>
        <button type="submit" class="btn-purchase">Mua Hàng</button>
    </form>
</div>
<script>
    // Cập nhật phí giao hàng và tổng tiền
    const shippingRadios = document.querySelectorAll('input[name="shipping"]');
    const shippingFeeElement = document.getElementById('shipping-fee');
    const totalAmountElement = document.getElementById('total-amount');
    let total = <?= $total ?>;

    shippingRadios.forEach(radio => {
        radio.addEventListener('change', () => {
            let shippingFee = 0;
            if (radio.value === 'express') shippingFee = 25000;

            shippingFeeElement.textContent = `${shippingFee.toLocaleString()}₫`;
            totalAmountElement.textContent = `${(total + shippingFee).toLocaleString()}₫`;
        });
    });
</script>
</body>
</html>
