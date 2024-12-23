<?php 
session_start();
include('db.php');



// Lấy danh sách sản phẩm từ bảng "product"
$sql = "SELECT * FROM product";
$result = $conn->query($sql);

// Lọc theo từ khóa tìm kiếm
$search = '';
if (isset($_GET['search'])) {
    $search = $_GET['search'];

    // Sử dụng prepared statement để bảo mật SQL
    $stmt = $conn->prepare("SELECT * FROM product WHERE name LIKE ?");
    $searchTerm = "%" . $search . "%";
    $stmt->bind_param("s", $searchTerm); // Tránh SQL Injection
    $stmt->execute();
    $result = $stmt->get_result();
}

// Lấy danh mục catalog
$sql_catalog = "SELECT * FROM catalog";
$catalog_result = $conn->query($sql_catalog);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang chủ Giày Dép</title>
    <link rel="stylesheet" href="css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header: Thanh menu và tìm kiếm -->
    <header>
        <div class="container">
            <nav>
                <div class="left-nav">
                    <a href=""><img src="img/logo1.png" alt="" class="logo"></a>
                    <a href="index.php">🏠 Trang chủ</a>
                    <a href="cart.php">🛒 Giỏ hàng</a>
                    <a href="catalog.php">📂 Danh mục sản phẩm</a>
                </div>
                <!-- Thanh tìm kiếm ở giữa -->
                <form class="search-bar" action="index.php" method="GET">
                    <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit">Tìm kiếm</button>
                </form>
                <!-- Tên người dùng và đăng xuất ở góc phải -->
                    <div class="right-nav">
                <?php if (isset($_SESSION['username'])): ?>
                    <!-- Khi đã đăng nhập -->
                <span>Xin chào, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="logout.php">🔒 Đăng xuất</a>
                <?php else: ?>
                <!-- Khi chưa đăng nhập -->
                <a href="login.php">🔑 Đăng nhập</a>
                <?php endif; ?>
            </div>

            </nav>
        </div>
    </header>

    <!-- Banner -->
    <div class="banner">
        <img src="img/banner.png" alt="Giày thể thao">
        <div class="banner-overlay">
            <h1>Chào mừng đến với Giày Dép Shop</h1>
            <p>Khám phá các sản phẩm mới nhất với giá hấp dẫn</p>
            <a href="#products" class="cta-button">Mua ngay</a>
        </div>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="products" id="products">
        <h2>Sản phẩm nổi bật</h2>
        <div class="product-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <div class="product">
                        <?php 
                            // Kiểm tra ảnh tồn tại
                            $imagePath = $row['image']; 
                            if (!empty($imagePath) && file_exists($imagePath)) { 
                        ?>
                            <a href="product_detail.php?product_id=<?php echo $row['product_id']; ?>"><img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" width="100"></a>
                        <?php } else { ?>
                            <img src="img/default-image.jpg" alt="Ảnh mặc định" width="100">
                        <?php } ?>
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <p class="price"><?php echo number_format($row['price'], 0, ',', '.') . ' VND'; ?></p>
                        <a href="product_detail.php?product_id=<?php echo $row['product_id']; ?>" class="order-button">Đặt hàng</a>
                    </div>
                <?php } ?>
            <?php else: ?>
                <p>Không có sản phẩm nào phù hợp với tìm kiếm của bạn.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-container">
        <a href="#"><img src="img/logo1.png" alt="" class="logo"></a>
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
