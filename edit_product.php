<?php 
session_start();
include('db.php'); // Kết nối cơ sở dữ liệu

// Kiểm tra quyền Admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Lấy danh sách sản phẩm từ cơ sở dữ liệu
$sql_products = "SELECT p.*, c.name AS catalog_name FROM product p 
                 LEFT JOIN catalog c ON p.catalog_id = c.catalog_id";
$result_products = $conn->query($sql_products);
if (!$result_products) {
    die("Lỗi truy vấn sản phẩm: " . $conn->error);
}

// Xử lý xóa sản phẩm
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM product WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Có lỗi xảy ra khi xóa sản phẩm.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sản phẩm - Admin Dashboard</title>
    <link rel="stylesheet" href="css/admin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <!-- Thanh menu dọc bên trái -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>Trang chủ Admin</h2>
        </div>
        <ul class="sidebar-menu">
            <li><a href="manage_users.php">Quản lý người dùng</a></li>
            <li><a href="admin_dashboard.php">Quản lý sản phẩm</a></li>
            <li><a href="manage_orders.php">Quản lý đơn hàng</a></li>
        </ul>
    </div>

    <!-- Nội dung chính -->
    <div class="main-content">
    <div class="topbar">
            <div class="topbar-left">
                <a href="index.php" class="home-icon">🏠 Trang chủ</a>
            </div>
            <div class="topbar-right">
                <span class="notification-icon">🔔</span>
                <span class="admin-name">Xin chào, <?php echo $_SESSION['username']; ?>!</span>
                <a href="logout.php" class="logout-button">Đăng xuất</a>
            </div>
        </div>

        <!-- Bảng quản lý sản phẩm -->
        <div class="container">
            <h1>Quản lý sản phẩm</h1>
            <a href="admin_dashboard.php" class="add-product-button">⏪Quay lại</a>
            <table>
                <thead>
                    <tr>
                        <th>Danh mục</th>
                        <th>Tên sản phẩm</th>
                        <th>Mô tả</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Ảnh</th>
                        <th>Trạng thái</th>
                        <th>Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result_products->fetch_assoc()) { ?>
                        <tr>
                            <td>
                                <select class="catalog_id" data-id="<?php echo $row['product_id']; ?>">
                                    <?php
                                    $result_catalogs = $conn->query("SELECT * FROM catalog");
                                    while ($cat = $result_catalogs->fetch_assoc()) { ?>
                                        <option value="<?php echo $cat['catalog_id']; ?>" 
                                            <?php if ($cat['catalog_id'] == $row['catalog_id']) echo 'selected'; ?>>
                                            <?php echo $cat['name']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </td>
                            <td><input type="text" class="name" value="<?php echo $row['name']; ?>" data-id="<?php echo $row['product_id']; ?>"></td>
                            <td><textarea class="description" data-id="<?php echo $row['product_id']; ?>"><?php echo $row['description']; ?></textarea></td>
                            <td><input type="number" class="price" value="<?php echo $row['price']; ?>" data-id="<?php echo $row['product_id']; ?>"></td>
                            <td><input type="number" class="quantity" value="<?php echo $row['quantity']; ?>" data-id="<?php echo $row['product_id']; ?>"></td>
                            <td><img src="<?php echo $row['image']; ?>" width="50"></td>
                            <td>
                                <select class="status" data-id="<?php echo $row['product_id']; ?>">
                                    <option value="available" <?php if ($row['status'] == 'available') echo 'selected'; ?>>Available</option>
                                    <option value="out_of_stock" <?php if ($row['status'] == 'out_of_stock') echo 'selected'; ?>>Out of Stock</option>
                                </select>
                            </td>
                            <td>
                                <button class="update-button" data-id="<?php echo $row['product_id']; ?>">Cập nhật</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $(".update-button").click(function() {
                var productId = $(this).data('id');
                var name = $(".name[data-id='" + productId + "']").val();
                var description = $(".description[data-id='" + productId + "']").val();
                var price = $(".price[data-id='" + productId + "']").val();
                var quantity = $(".quantity[data-id='" + productId + "']").val();
                var catalogId = $(".catalog_id[data-id='" + productId + "']").val();
                var status = $(".status[data-id='" + productId + "']").val();

                $.ajax({
                    url: "update_product.php",
                    method: "POST",
                    data: {
                        product_id: productId,
                        name: name,
                        description: description,
                        price: price,
                        quantity: quantity,
                        catalog_id: catalogId,
                        status: status
                    },
                    success: function(response) {
                        alert("Cập nhật thành công!");
                        location.reload();
                    },
                    error: function() {
                        alert("Có lỗi xảy ra khi cập nhật sản phẩm.");
                    }
                });
            });
        });
    </script>
</body>
</html>
