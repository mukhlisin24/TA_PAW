<?php
$host = $_ENV['APP_DB_HOST'] ?? "localhost:3307";
$db = $_ENV['APP_DB_NAME'] ?? "hotel_booking_db";
$user = $_ENV['APP_DB_USER'] ?? "root";
$pass = $_ENV['APP_DB_PASSWORD'] ?? "";

// $conn = mysqli_connect($host, $user, $pass, $db);
// if (!$conn) {
//     echo "koneksi error";
// }

$conn = mysqli_connect('localhost', 'root', '', 'hotel_booking_revamped');
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

?>