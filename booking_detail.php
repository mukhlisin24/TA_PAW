<?php
require 'koneksi.php';
session_start();

/*
  booking_detail.php
  Shows details for a single booking.
  Works with URL:
  - booking_detail.php?booking_id=1
  or
  - booking_detail.php?id=1
*/

/* ========== AUTH CHECK ========== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'customer') {
    echo "<script>window.location='login.php';</script>";
    exit;
}

/* ========== GET BOOKING ID ========== */
$booking_id = 0;

if (isset($_GET['booking_id']) && $_GET['booking_id'] !== '') {
    $booking_id = (int) $_GET['booking_id'];
} elseif (isset($_GET['id']) && $_GET['id'] !== '') {
    $booking_id = (int) $_GET['id'];
}

if ($booking_id <= 0) {
    echo "Invalid booking!";
    exit;
}

/* ========== GET CURRENT USER ========== */
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id && isset($_SESSION['email'])) {
    $email = mysqli_real_escape_string($conn, $_SESSION['email']);
    $user_res = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' LIMIT 1");
    if ($user_res && mysqli_num_rows($user_res) > 0) {
        $u = mysqli_fetch_assoc($user_res);
        $user_id = $u['id'];
        $_SESSION['user_id'] = $user_id;
    }
}

/* ========== FETCH BOOKING (ONLY IF IT BELONGS TO THIS USER) ========== */
$q = mysqli_query($conn,
    "SELECT 
        b.*,
        u.name  AS customer_name,
        u.email AS customer_email,
        d.discount_name,
        d.discount_type,
        d.discount_value
     FROM bookings b
     JOIN users u ON b.user_id = u.id
     LEFT JOIN discounts d ON d.id = b.discount_id
     WHERE b.id = '$booking_id'
       AND b.user_id = '$user_id'
     LIMIT 1"
);

$booking = mysqli_fetch_assoc($q);

if (!$booking) {
    echo "Booking not found or you don't have access to this booking.";
    exit;
}

/* ========== FETCH ROOMS IN THIS BOOKING ========== */
$rooms_q = mysqli_query($conn,
    "SELECT 
        br.*,
        r.title,
        r.city,
        r.type
     FROM booking_rooms br
     LEFT JOIN rooms r ON r.id = br.room_id
     WHERE br.booking_id = '$booking_id'"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Booking Detail</title>
    <link rel="stylesheet" href="./assets/style.css">

<link rel="stylesheet" href="./assets/booking_details.css">

</head>
<body>

<div class="detail-wrapper">
    <h2>Booking Detail</h2>

    <?php
    $status_color = 'bg-pending';
    if ($booking['status'] == 'confirmed') $status_color = 'bg-confirmed';
    if ($booking['status'] == 'cancelled') $status_color = 'bg-cancelled';
    ?>

    <div class="meta-row">
        <div><strong>Kode Booking:</strong> #<?php echo htmlspecialchars($booking['booking_code']); ?></div>
        <div>
            <span class="badge <?php echo $status_color; ?>">
                <?php echo strtoupper($booking['status']); ?>
            </span>
        </div>
    </div>

    <div class="meta-row">
        <div><strong>Nama Tamu:</strong> <?php echo htmlspecialchars($booking['customer_name']); ?></div>
        <div><strong>Email:</strong> <?php echo htmlspecialchars($booking['customer_email']); ?></div>
    </div>

    <div class="meta-row">
        <div><strong>Check-in:</strong> <?php echo $booking['check_in']; ?></div>
        <div><strong>Check-out:</strong> <?php echo $booking['check_out']; ?></div>
    </div>

    <div class="meta-row">
        <div><strong>Total Malam:</strong> <?php echo (int)$booking['total_nights']; ?></div>
        <div><strong>Status Pembayaran:</strong> <?php echo strtoupper($booking['payment_status']); ?></div>
    </div>

    <h3>Rooms</h3>
    <table class="rooms-table">
        <thead>
            <tr>
                <th>Room</th>
                <th>City</th>
                <th>Type</th>
                <th>Qty</th>
                <th>Price / Night</th>
                <th>Breakfast</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $has_rooms = false;
        while ($r = mysqli_fetch_assoc($rooms_q)):
            $has_rooms = true;
        ?>
            <tr>
                <td><?php echo htmlspecialchars($r['title'] ?? 'Room'); ?></td>
                <td><?php echo htmlspecialchars($r['city'] ?? '-'); ?></td>
                <td><?php echo htmlspecialchars($r['type'] ?? '-'); ?></td>
                <td><?php echo (int)$r['quantity']; ?></td>
                <td>Rp <?php echo number_format($r['price_per_night']); ?></td>
                <td><?php echo $r['breakfast_included'] ? 'Yes' : 'No'; ?></td>
                <td>Rp <?php echo number_format($r['subtotal']); ?></td>
            </tr>
        <?php endwhile; ?>

        <?php if (!$has_rooms): ?>
            <tr>
                <td colspan="7">No room data found for this booking.</td>
            </tr>
        <?php endif; ?>

            <?php if ($booking['discount_amount'] > 0): ?>
            <tr>
                <td colspan="7" style="text-align:right; color:#28a745; font-weight:600; padding:10px;">
                    🎁 Diskon (<?php echo htmlspecialchars($booking['discount_name']); ?>): 
                    - Rp <?php echo number_format($booking['discount_amount']); ?>
                </td>
            </tr>
            <?php endif; ?>

            <tr>
                <td colspan="7" class="total-row">
                    Total Amount: Rp <?php echo number_format($booking['total_amount']); ?>
                </td>
            </tr>
        </tbody>
    </table>

    <a href="customer_dashboard.php?tab=bookings" class="btn-back">← Back to My Bookings</a>
</div>

</body>
</html>
