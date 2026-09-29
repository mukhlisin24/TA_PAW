<?php
session_start();
require 'koneksi.php';

if (!isset($_SESSION['pending_booking'])) {
    header("Location: rooms.php");
    exit;
}

$data = $_SESSION['pending_booking'];

// amount - HARUS INTEGER untuk Midtrans (min 1000)
$amount = (int) $data['final_total'];

// Midtrans minimum amount adalah 1000
if ($amount < 1000) {
    die("Error: Minimum amount untuk Midtrans adalah Rp 1.000. Total: Rp " . number_format($amount));
}

// Get customer info if available
$customer_name = isset($_SESSION['name']) ? $_SESSION['name'] : "Customer";
$customer_email = isset($_SESSION['email']) ? $_SESSION['email'] : "customer@example.com";

require_once 'midtrans-php/Midtrans.php';
require_once 'config.php';

\Midtrans\Config::$serverKey = getenv('MIDTRANS_SERVER_KEY') ?: "YOUR_MIDTRANS_SERVER_KEY";
\Midtrans\Config::$isProduction = (getenv('MIDTRANS_IS_PRODUCTION') === 'true');
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

$order_id = "HOTEL" . time();

try {
    $params = [
        "transaction_details" => [
            "order_id" => $order_id,
            "gross_amount" => (int)$amount
        ],
        "customer_details" => [
            "first_name" => $customer_name,
            "email" => $customer_email
        ]
    ];

    $snapToken = \Midtrans\Snap::getSnapToken($params);
} catch (Exception $e) {
    die("Error: " . $e->getMessage() . "<br><a href='rooms.php'>Kembali ke Rooms</a>");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Processing</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #f5f5f7 0%, #e8e8ea 100%);
        }
        .payment-box {
            background: white;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(197, 157, 95, 0.2);
            text-align: center;
            border: 2px solid #f0f0f0;
        }
        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #c59d5f;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 {
            color: #333;
            margin: 0 0 10px;
            font-size: 24px;
        }
        p {
            color: #666;
            font-size: 14px;
            margin: 8px 0;
        }
        small {
            color: #c59d5f;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="payment-box">
    <div class="loader"></div>
    <h2>Processing Payment...</h2>
    <p>Please wait while we redirect you to payment page</p>
    <p><small>Amount: Rp <?php echo number_format($amount); ?></small></p>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" 
        data-client-key="Mid-client-TrMjFBsi9iVrTt1Q"></script>

<script>
snap.pay("<?php echo $snapToken; ?>", {
    onSuccess: function(result){
        window.location.href = "finish.php?status=success&transaction_id=" + result.transaction_id;
    },
    onPending: function(result){
        window.location.href = "finish.php?status=pending&transaction_id=" + result.transaction_id;
    },
    onError: function(result){
        window.location.href = "finish.php?status=failed&error=" + (result.status_message || 'Payment failed');
    },
    onClose: function(){
        alert('Anda menutup popup pembayaran sebelum menyelesaikan pembayaran');
        window.location.href = "rooms.php";
    }
});
</script>

</body>
</html>
