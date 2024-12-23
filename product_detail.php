<?php 
session_start();
include('db.php');

// Kiểm tra nếu chưa đăng nhập
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Lọc theo từ khóa tìm kiếm
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];

    // Sử dụng prepared statement để bảo mật SQL
    $stmt = $conn->prepare("SELECT * FROM product WHERE name LIKE ?");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("s", $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
}

// Lấy product_id từ URL
if (isset($_GET['product_id']) && is_numeric($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);

    $stmt = $conn->prepare("SELECT * FROM product WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        die("Sản phẩm không tồn tại.");
    }
    $product = $result->fetch_assoc();
} else {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chi tiết sản phẩm</title>
    <link rel="stylesheet" href="css/header_product.css">
    <link rel="stylesheet" href="css/product_detail.css">
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

    <!-- Chi tiết sản phẩm -->
    <div class="product-detail-container">
        <div class="product-gallery">
            <div class="thumbnail-list">
                <img src="img/sp1.jpg" class="thumbnail active" alt="Góc 1">
                <img src="img/sp2.jpg" class="thumbnail" alt="Góc 2">
                <img src="img/sp3.jpg" class="thumbnail" alt="Góc 3">
                <img src="img/sp4.jpg" class="thumbnail" alt="Góc 4">
                <img src="img/sp5.jpg" class="thumbnail" alt="Góc 5">
            </div>
            <div class="main-image">
                <img src="<?php echo $product['image']; ?>" id="main-product-image" alt="Sản phẩm chính">
            </div>
        </div>

        <div class="product-info">
    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
    <p class="price" id="price-display" data-price="<?php echo $product['price']; ?>">
        <?php echo number_format($product['price'], 0, ',', '.') . ' VND'; ?>
    </p>
    <p><strong>Mô tả:</strong></p>
    <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

    <?php if (!empty($product['available_sizes'])) { 
        $sizes = explode(',', $product['available_sizes']); ?>
        <div class="product-sizes">
            <p><strong>Kích cỡ:</strong></p>
            <div class="size-options">
                <?php foreach ($sizes as $size): ?>
                    <div class="size-box" data-size="<?php echo trim($size); ?>">
                        <?php echo trim($size); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php } ?>

    <form action="add_to_cart.php" method="POST">
        <input type="hidden" id="selected-size" name="size" required>
        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
        <label for="quantity">Số lượng:</label>
        <input type="number" id="quantity" name="quantity" min="1" value="1" required oninput="updatePrice()">
        <button type="submit" class="order-button">Thêm vào giỏ hàng</button>
    </form>
</div>

    </div>

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

    <!-- Script -->
    <script>
        // Highlight kích cỡ
        document.addEventListener("DOMContentLoaded", () => {
            const sizeBoxes = document.querySelectorAll(".size-box");
            const selectedSizeInput = document.getElementById("selected-size");

            sizeBoxes.forEach((box) => {
                box.addEventListener("click", function () {
                    sizeBoxes.forEach((b) => b.classList.remove("active"));
                    this.classList.add("active");
                    selectedSizeInput.value = this.getAttribute("data-size");
                });
            });
        });

        function updatePrice() {
            const quantityInput = document.getElementById("quantity");
            const priceDisplay = document.getElementById("price-display");
            const unitPrice = parseFloat(priceDisplay.getAttribute("data-price"));
            const quantity = parseInt(quantityInput.value) || 1; // Đảm bảo giá trị là số và tối thiểu là 1
            const totalPrice = unitPrice * quantity;
            priceDisplay.textContent = totalPrice.toLocaleString("vi-VN") + " VND"; // Cập nhật hiển thị giá
}

        // Thay đổi ảnh chính khi click vào thumbnail
        const thumbnails = document.querySelectorAll('.thumbnail');
        const mainImage = document.getElementById('main-product-image');

        thumbnails.forEach((thumbnail) => {
            thumbnail.addEventListener('click', function () {
                thumbnails.forEach((thumb) => thumb.classList.remove('active'));
                this.classList.add('active');
                mainImage.src = this.src;
            });
        });
    </script>
</body>
</html>
