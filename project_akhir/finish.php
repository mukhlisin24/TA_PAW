<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['pending_booking'])) {
    die("No booking data found. Silakan <a href='rooms.php'>booking lagi</a>.");
}

$data = $_SESSION['pending_booking'];
// JANGAN unset dulu! Biar kalau error bisa refresh
// unset($_SESSION['pending_booking']); 

// Clean status dari URL
$status = isset($_GET['status']) ? trim(strtolower($_GET['status'])) : 'pending';
$transaction_id = $_GET['transaction_id'] ?? null;

// booking info
$user_id = $data['user_id'];
$rooms = $data['room_ids'];
$check_in = $data['check_in'];
$check_out = $data['check_out'];
$nights = $data['nights'];
$amount = $data['final_total'];
$guests = $data['guests'];
$discount_id = $data['discount_id'] ?? null;
$discount_amount = $data['discount_amount'] ?? 0;

// Matikan strict mode
mysqli_query($conn, "SET sql_mode = ''");

mysqli_begin_transaction($conn);

try {

    $booking_code = "BK" . time();

    // Set nilai untuk booking
    $discount_id_sql = $discount_id ? $discount_id : "NULL";
    $discount_amount_clean = (float) $discount_amount;

    // Set status berdasarkan payment
    $booking_status = ($status === "success") ? "confirmed" : "pending";
    $payment_status = ($status === "success") ? "paid" : "unpaid";

    // Insert booking TANPA kolom status terlebih dahulu (workaround untuk strict mode)
    $sql = "INSERT INTO bookings 
            (user_id, booking_code, check_in, check_out, total_nights, total_amount, discount_id, discount_amount, payment_status, created_at)
            VALUES
            ($user_id, '$booking_code', '$check_in', '$check_out', $nights, $amount, $discount_id_sql, $discount_amount_clean,
             '$payment_status', NOW())";

    if (!mysqli_query($conn, $sql)) {
        throw new Exception("Failed to insert booking: " . mysqli_error($conn));
    }
    $booking_id = mysqli_insert_id($conn);

    // Update status setelah insert berhasil
    $sql_update = "UPDATE bookings SET status = '$booking_status' WHERE id = $booking_id";
    if (!mysqli_query($conn, $sql_update)) {
        throw new Exception("Failed to update booking status: " . mysqli_error($conn));
    }

    // insert each room
    foreach ($rooms as $room_id) {
        $roomQuery = mysqli_query($conn, "SELECT price_per_night FROM rooms WHERE id=$room_id");
        $room = mysqli_fetch_assoc($roomQuery);
        $subtotal = $room['price_per_night'] * $nights;

        mysqli_query($conn, "INSERT INTO booking_rooms
            (booking_id, room_id, quantity, price_per_night, breakfast_included, subtotal)
            VALUES ($booking_id, $room_id, 1, {$room['price_per_night']}, 0, $subtotal)");
    }

    // insert payment
    mysqli_query($conn, "INSERT INTO payments (booking_id, amount, method, transaction_id, status, paid_at)
        VALUES ($booking_id, $amount, 'Midtrans', '$transaction_id', '$payment_status', NOW())");

    mysqli_commit($conn);

    // Hapus session SETELAH berhasil commit
    unset($_SESSION['pending_booking']);

    // Simpan booking success message
    $_SESSION['booking_success'] = "Booking berhasil! Kode booking: $booking_code";

} catch (Exception $e) {
    mysqli_rollback($conn);
    ?>
    <!DOCTYPE html>
    <html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Booking Failed</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
            rel="stylesheet">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: 'Poppins', sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }

            .error-container {
                background: white;
                border-radius: 20px;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                padding: 50px;
                max-width: 600px;
                text-align: center;
                animation: slideUp 0.5s ease-out;
            }

            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }

                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            .error-icon {
                font-size: 80px;
                margin-bottom: 20px;
            }

            .error-title {
                font-size: 32px;
                color: #e74c3c;
                margin-bottom: 15px;
                font-weight: 700;
            }

            .error-message {
                font-size: 16px;
                color: #666;
                margin-bottom: 30px;
                line-height: 1.6;
            }

            .error-btn {
                display: inline-block;
                padding: 15px 40px;
                background: #c59d5f;
                color: white;
                text-decoration: none;
                border-radius: 50px;
                font-weight: 600;
                font-size: 16px;
                transition: all 0.3s ease;
                box-shadow: 0 5px 15px rgba(197, 157, 95, 0.3);
            }

            .error-btn:hover {
                background: #b38a4d;
                transform: translateY(-2px);
                box-shadow: 0 8px 20px rgba(197, 157, 95, 0.4);
            }
        </style>
    </head>

    <body>
        <div class="error-container">
            <div class="error-icon">❌</div>
            <h1 class="error-title">Booking Gagal</h1>
            <p class="error-message"><?php echo htmlspecialchars($e->getMessage()); ?></p>
            <a href="rooms.php" class="error-btn">Kembali ke Rooms</a>
        </div>
    </body>

    </html>
    <?php
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Successful</title>
    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@700&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #c59d5f 0%, #8a6d34 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .success-container {
            background: white;
            border-radius: 25px;
            box-shadow: 0 25px 70px rgba(0, 0, 0, 0.3);
            padding: 60px 50px;
            max-width: 650px;
            width: 100%;
            text-align: center;
            animation: slideUp 0.6s ease-out;
            position: relative;
            overflow: hidden;
        }

        .success-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #c59d5f, #d4af37, #c59d5f);
            background-size: 200% 100%;
            animation: gradientMove 3s linear infinite;
        }

        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }

            100% {
                background-position: 200% 50%;
            }
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.95);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .checkmark-wrapper {
            margin-bottom: 25px;
            display: inline-block;
        }

        .checkmark {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: inline-block;
            stroke-width: 3;
            stroke: #28a745;
            stroke-miterlimit: 10;
            box-shadow: inset 0 0 0 #28a745;
            animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
            position: relative;
        }

        @keyframes fill {
            100% {
                box-shadow: inset 0 0 0 50px #28a745;
            }
        }

        @keyframes scale {

            0%,
            100% {
                transform: none;
            }

            50% {
                transform: scale(1.1);
            }
        }

        .checkmark-circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 3;
            stroke-miterlimit: 10;
            stroke: #28a745;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }

        .checkmark-check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }

        @keyframes stroke {
            100% {
                stroke-dashoffset: 0;
            }
        }

        .success-title {
            font-family: 'Playfair Display', serif;
            font-size: 42px;
            color: #c59d5f;
            margin-bottom: 15px;
            font-weight: 700;
            animation: fadeIn 0.6s ease-out 0.3s both;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-subtitle {
            font-size: 16px;
            color: #888;
            margin-bottom: 35px;
            animation: fadeIn 0.6s ease-out 0.4s both;
        }

        .booking-details {
            background: #f9f7f2;
            border-radius: 15px;
            padding: 30px;
            margin: 30px 0;
            animation: fadeIn 0.6s ease-out 0.5s both;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #e8e4d9;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 15px;
            color: #666;
            font-weight: 500;
        }

        .detail-value {
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }

        .booking-code {
            background: linear-gradient(135deg, #c59d5f, #d4af37);
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            font-family: 'Courier New', monospace;
            font-size: 18px;
            letter-spacing: 1px;
            box-shadow: 0 3px 10px rgba(197, 157, 95, 0.3);
        }

        .status-badge {
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-paid {
            background: #d4edda;
            color: #155724;
        }

        .status-unpaid {
            background: #fff3cd;
            color: #856404;
        }

        .discount-badge {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 8px 18px;
            border-radius: 25px;
            font-size: 16px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 3px 10px rgba(40, 167, 69, 0.3);
        }

        .total-amount {
            font-size: 32px;
            color: #c59d5f;
            font-weight: 700;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 35px;
            animation: fadeIn 0.6s ease-out 0.6s both;
        }

        .btn {
            flex: 1;
            padding: 16px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #c59d5f, #d4af37);
            color: white;
            box-shadow: 0 5px 20px rgba(197, 157, 95, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(197, 157, 95, 0.5);
        }

        .btn-secondary {
            background: white;
            color: #c59d5f;
            border: 2px solid #c59d5f;
        }

        .btn-secondary:hover {
            background: #f9f7f2;
            transform: translateY(-3px);
        }

        .confetti {
            position: fixed;
            width: 10px;
            height: 10px;
            background: #c59d5f;
            position: absolute;
            animation: confetti-fall 3s linear infinite;
        }

        @keyframes confetti-fall {
            to {
                transform: translateY(100vh) rotate(360deg);
                opacity: 0;
            }
        }

        @media (max-width: 600px) {
            .success-container {
                padding: 40px 25px;
            }

            .success-title {
                font-size: 32px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .detail-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
        }
    </style>
</head>

<body>
    <div class="success-container">
        <div class="checkmark-wrapper">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                <circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none" />
                <path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
            </svg>
        </div>

        <h1 class="success-title">Booking Berhasil!</h1>
        <p class="success-subtitle">Terima kasih telah melakukan pemesanan. Detail booking Anda telah dikirim.</p>

        <div class="booking-details">
            <div class="detail-row">
                <span class="detail-label">Kode Booking</span>
                <span class="booking-code"><?php echo $booking_code; ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Status Pembayaran</span>
                <span class="status-badge <?php echo $payment_status === 'paid' ? 'status-paid' : 'status-unpaid'; ?>">
                    <?php echo $payment_status === 'paid' ? '✓ Lunas' : '⏱ Belum Dibayar'; ?>
                </span>
            </div>

            <?php if ($discount_amount > 0): ?>
                <div class="detail-row">
                    <span class="detail-label">Diskon</span>
                    <span class="discount-badge">
                        🎁 Rp <?php echo number_format($discount_amount); ?>
                    </span>
                </div>
            <?php endif; ?>

            <div class="detail-row">
                <span class="detail-label">Check-in</span>
                <span class="detail-value"><?php echo date('d M Y', strtotime($check_in)); ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Check-out</span>
                <span class="detail-value"><?php echo date('d M Y', strtotime($check_out)); ?></span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Lama Menginap</span>
                <span class="detail-value"><?php echo $nights; ?> Malam</span>
            </div>

            <div class="detail-row" style="border-top: 2px solid #c59d5f; margin-top: 10px; padding-top: 20px;">
                <span class="detail-label" style="font-size: 18px; color: #333;">Total Pembayaran</span>
                <span class="total-amount">Rp <?php echo number_format($amount); ?></span>
            </div>
        </div>

        <div class="action-buttons">
            <a href="customer_dashboard.php" class="btn btn-primary">
                Ke Dashboard
            </a>
        </div>
    </div>

    <script>
        // Confetti effect
        function createConfetti() {
            const colors = ['#c59d5f', '#d4af37', '#f4e4c1', '#8a6d34', '#28a745'];
            for (let i = 0; i < 50; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.className = 'confetti';
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.top = '-10px';
                    confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.animationDelay = Math.random() * 3 + 's';
                    confetti.style.animationDuration = (Math.random() * 3 + 2) + 's';
                    document.body.appendChild(confetti);

                    setTimeout(() => confetti.remove(), 5000);
                }, i * 50);
            }
        }

        createConfetti();
    </script>
</body>

</html>