// Đợi DOM được load
document.addEventListener("DOMContentLoaded", function () {
    const dropdown = document.querySelector(".dropdown");
    const toggleButton = document.querySelector(".dropdown-toggle");

    // Toggle menu khi nhấn vào nút
    toggleButton.addEventListener("click", function (e) {
        e.stopPropagation(); // Ngăn sự kiện lan ra ngoài
        dropdown.classList.toggle("active");
    });

    // Đóng menu khi nhấn ra ngoài
    document.addEventListener("click", function () {
        dropdown.classList.remove("active");
    });
});
