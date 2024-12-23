<?php
session_start();
include('db.php'); // Kết nối CSDL

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
// Truy vấn danh mục từ bảng catalog
$sql_catalog = "SELECT * FROM catalog";
$result_catalog = $conn->query($sql_catalog);

// Nếu yêu cầu AJAX, trả về danh sách sản phẩm
if (isset($_GET['ajax']) && isset($_GET['catalog_id'])) {
    $catalog_id = intval($_GET['catalog_id']);
    $sql_products = $catalog_id > 0 ? "SELECT * FROM product WHERE catalog_id = ?" : "SELECT * FROM product";
    $stmt = $conn->prepare($sql_products);
    if ($catalog_id > 0) {
        $stmt->bind_param("i", $catalog_id);
    }
    $stmt->execute();
    $result_products = $stmt->get_result();

    $products = [];
    while ($row = $result_products->fetch_assoc()) {
        $products[] = $row;
    }
    echo json_encode($products); // Trả về dữ liệu JSON
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh mục sản phẩm</title>
    <link rel="stylesheet" href="css/catalog.css">
    <script>
        // AJAX để tải sản phẩm theo danh mục
        function loadProducts(catalogId = 0) {
            const productList = document.getElementById('product-list');
            productList.innerHTML = '<p>Đang tải sản phẩm...</p>'; // Hiển thị thông báo đang tải

            fetch(`catalog.php?ajax=1&catalog_id=${catalogId}`)
                .then(response => response.json())
                .then(products => {
                    let html = '';
                    if (products.length > 0) {
                        products.forEach(product => {
                            html += `
                                <div class="product">
                                    <a href="product_detail.php?product_id=${product.product_id}">
                                        <img src="${product.image ? product.image : 'img/default-image.jpg'}" alt="${product.name}">
                                    </a>
                                    <h3>${product.name}</h3>
                                    <p>${Number(product.price).toLocaleString()} VND</p>
                                    <a href="product_detail.php?product_id=${product.product_id}" class="order-button">Đặt hàng</a>
                                </div>
                            `;
                        });
                    } else {
                        html = '<p>Không có sản phẩm nào trong danh mục này.</p>';
                    }
                    productList.innerHTML = html;
                })
                .catch(err => {
                    productList.innerHTML = '<p>Đã xảy ra lỗi khi tải sản phẩm.</p>';
                    console.error(err);
                });
        }

        // Tải toàn bộ sản phẩm khi trang được load
        window.onload = function() {
            loadProducts(); // Tải tất cả sản phẩm
        };
    </script>
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

    <!-- Sidebar Danh mục -->
    <div class="catalog">
        <div class="sidebar">
            <h2>Danh mục sản phẩm</h2>
            <ul>
                <li><a href="#" onclick="loadProducts(0); return false;">Tất cả sản phẩm</a></li>
                <?php while ($cat = $result_catalog->fetch_assoc()) { ?>
                    <li>
                        <a href="#" onclick="loadProducts(<?php echo $cat['catalog_id']; ?>); return false;">
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <!-- Nội dung chính -->
        <div class="main-content">
            <h1>Danh sách sản phẩm</h1>
            <div id="product-list" class="product-list">
                <p>Đang tải danh sách sản phẩm...</p>
            </div>
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
</body>
</html>
