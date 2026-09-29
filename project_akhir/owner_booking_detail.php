<?php
require 'koneksi.php';
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>window.location='login.php';</script>";
    exit;
}

if (!isset($_GET['id']) || (int)$_GET['id'] <= 0) {
    echo "Invalid booking id.";
    exit;
}

$booking_id = (int) $_GET['id'];

/* FETCH BOOKING + USER */
$q = mysqli_query($conn,
    "SELECT 
        b.*,
        u.name  AS customer_name,
        u.email AS customer_email,
        u.phone AS customer_phone,
        d.discount_name,
        d.discount_type,
        d.discount_value
     FROM bookings b
     JOIN users u ON b.user_id = u.id
     LEFT JOIN discounts d ON d.id = b.discount_id
     WHERE b.id = '$booking_id'
     LIMIT 1"
);
$booking = mysqli_fetch_assoc($q);
if (!$booking) {
    echo "Booking not found.";
    exit;
}

/* FETCH ROOMS */
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
    <title>Booking Detail Admin</title>
    <link rel="stylesheet" href="./assets/style.css">
    <style>
        body {
            font-family: "Poppins", system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #f5f5f7;
            margin: 0;
            padding: 20px;
        }
        .detail-wrapper {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            border-radius: 14px;
            padding: 18px 20px 22px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.06);
        }
        h2 {
            margin-top: 0;
            margin-bottom: 10px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            font-size: 14px;
            margin-bottom: 4px;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            font-size: 11px;
        }
        .bg-pending {
            background: #fff3cd;
            color: #856404;
        }
        .bg-confirmed {
            background: #d4edda;
            color: #155724;
        }
        .bg-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .section-title {
            margin-top: 16px;
            margin-bottom: 8px;
            font-size: 16px;
            border-bottom: 1px solid #eee;
            padding-bottom: 4px;
        }
        .rooms-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            font-size: 14px;
        }
        .rooms-table th,
        .rooms-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #eee;
        }
        .rooms-table th {
            background: #faf7ee;
            text-align: left;
        }
        .total-row {
            text-align: right;
            font-weight: 600;
        }
        .btn-back {
            display: inline-block;
            margin-top: 15px;
            background: #333;
            color: #fff;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
        }
        @media (max-width: 600px) {
            .meta-row {
                flex-direction: column;
                gap: 4px;
            }
        }
    </style>
</head>
<body>

<div class="detail-wrapper">
    <h2>Booking Detail (Admin)</h2>

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
        <div><strong>No. Telepon:</strong> <?php echo htmlspecialchars($booking['customer_phone']); ?></div>
        <div><strong>Pembayaran:</strong> <?php echo strtoupper($booking['payment_status']); ?></div>
    </div>

    <div class="meta-row">
        <div><strong>Check-in:</strong> <?php echo $booking['check_in']; ?></div>
        <div><strong>Check-out:</strong> <?php echo $booking['check_out']; ?></div>
    </div>

    <div class="meta-row">
        <div><strong>Total Malam:</strong> <?php echo (int)$booking['total_nights']; ?></div>
        <div><strong>Tanggal Dibuat:</strong> <?php echo $booking['created_at']; ?></div>
    </div>

    <h3 class="section-title">Rooms</h3>
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
                <td colspan="7">No room data for this booking.</td>
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

    <a href="owner_dashboard.php?tab=bookings" class="btn-back">← Back to Bookings</a>
</div>

</body>
</html>
