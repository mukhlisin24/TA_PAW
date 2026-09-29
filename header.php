<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexora Suites</title>

    <link rel="stylesheet" href="assets/header.css?v=<?= time(); ?>">
</head>

<body>

<nav class="navbar">
    <div class="nav-container">

        <!-- LOGO -->
        <div class="logo">
            <a href="index.php">
                <img height="80" src="./upload/logo.png" alt="">
            </a>
        </div>

        <!-- HAMBURGER -->
        <div class="menu-toggle" onclick="toggleMenu()">☰</div>

        <!-- MENU -->
        <ul class="nav-menu" id="menu">
            <?php if (!isset($_SESSION['role'])): ?>
                <li><a class="menu-link" href="index.php">Home</a></li>
                <li><a class="menu-link" href="about.php">About Us</a></li>
                <li><a class="menu-link" href="rooms.php">Rooms</a></li>
                <li><a class="menu-link" href="login.php">Login</a></li>
            <?php endif; ?>

            <?php if (isset($_SESSION['role'])): ?>
                <li><a class="menu-link" href="index.php">Home</a></li>
            <?php endif; ?>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <li><a class="menu-link" href="owner_dashboard.php">Admin Panel</a></li>
            <?php endif; ?>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'customer'): ?>
                <li><a class="menu-link" href="rooms.php">Rooms</a></li>
                <li><a class="menu-link" href="customer_dashboard.php">Dashboard</a></li>
            <?php endif; ?>

            <?php if (isset($_SESSION['role'])): ?>
                <li><a class="menu-link logout" href="logout.php">Logout</a></li>
            <?php endif; ?>
        </ul>

    </div>
</nav>

<script>
    function toggleMenu() {
        document.getElementById("menu").classList.toggle("active");
    }
</script>

</body>
</html>
