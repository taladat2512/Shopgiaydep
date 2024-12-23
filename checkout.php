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
    <!-- Header -->
    <header>
        <div class="container">
            <nav>
                <div class="left-nav">
                    <a href="index.php"><img src="img/logo1.png" alt="Logo" class="logo"></a>
                    <a href="index.php">🏠 Trang chủ</a>
                    <a href="cart.php">🛒 Giỏ hàng</a>
                    <a href="catalog.php">📂 Danh mục sản phẩm</a>
                </div>
                <form class="search-bar" action="index.php" method="GET">
                    <input type="text" name="search" placeholder="Tìm kiếm sản phẩm...">
                    <button type="submit">Tìm kiếm</button>
                </form>
                <div class="right-nav">
                    <span>Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <a href="logout.php">🔒 Đăng xuất</a>
                </div>
            </nav>
        </div>
    </header>
<div class="container">
    <h1>Thanh Toán</h1>
    <div class="checkout-container">
        <!-- Nhập thông tin khách hàng -->
        <div class="checkout-section">
            <h3>Nhập thông tin khách hàng</h3>
            <input type="text" name="phone" placeholder="Số điện thoại" required>
            <input type="text" name="address" placeholder="Địa chỉ giao hàng" required>
        </div>

        <!-- Phương thức giao hàng -->
        <div class="checkout-section">
            <h3>Phương thức giao hàng</h3>
            <div class="method-options">
                <label>
                    <input type="radio" name="shipping" value="free" checked> Miễn phí giao hàng - 0₫
                </label>
                <label>
                    <input type="radio" name="shipping" value="express"> Ship nhanh - 25,000₫
                </label>
            </div>

            <h3>Phương thức thanh toán</h3>
            <div class="method-options">
                <label>
                    <input type="radio" name="payment" value="cod" checked> Thanh toán khi nhận hàng
                </label>
                <label>
                    <input type="radio" name="payment" value="bank_transfer"> Chuyển khoản ngân hàng
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
                    <td><img src='{$row['image']}' alt='{$row['name']}' width='50'></td>
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
        <div class="summary">
            <div class="summary-row">
                <span>Thành tiền:</span>
                <span><?= number_format($total, 0, ',', '.') ?>₫</span>
            </div>
            <div class="summary-row">
                <span>Phí giao hàng:</span>
                <span id="shipping-fee">0₫</span>
            </div>
            <div class="summary-row">
                <span>Tổng cộng:</span>
                <span id="total-amount"><?= number_format($total, 0, ',', '.') ?>₫</span>
            </div>
        </div>
        <button class="checkout-btn">MUA HÀNG</button>
    </div>
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
<!-- Footer -->
<footer>
        <div class="footer-container">
            <a href="#"><img src="img/logo1.png" alt="Logo" class="logo"></a>
            <div class="footer-links">
                <ul>
                    <li><a href="#">Giới thiệu</a></li>
                    <li><a href="#">Điều khoản</a></li>
                    <li><a href="#">Chính sách bảo mật</a></li>
                </ul>
            </div>
            <div class="footer-contact">
                <p>Liên hệ: sdt: 0912345678 | email: info@giaydep.com</p>
            </div>
        </div>
    </footer>
</body>
</html>
