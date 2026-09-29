<?php
require_once 'midtrans-php/Midtrans.php';
require_once 'config.php';

\Midtrans\Config::$serverKey = getenv('MIDTRANS_SERVER_KEY') ?: "YOUR_MIDTRANS_SERVER_KEY";
\Midtrans\Config::$isProduction = (getenv('MIDTRANS_IS_PRODUCTION') === 'true');
\Midtrans\Config::$isSanitized = true;
\Midtrans\Config::$is3ds = true;

$transaction_details = [
    'order_id' => rand(),
    'gross_amount' => 50000,
];

$params = [
    'transaction_details' => $transaction_details,
];

$snapToken = \Midtrans\Snap::getSnapToken($params);

echo "<script src='https://app.sandbox.midtrans.com/snap/snap.js' data-client-key='Mid-client-TrMjFBsi9iVrTt1Q'></script>";
echo "<script>snap.pay('$snapToken');</script>";
