<?php
session_start();
require 'koneksi.php';
include 'header.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    $_SESSION['redirect_to'] = $_SERVER['REQUEST_URI'];
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$room_ids = [];

// Handle single or multiple room booking
if (isset($_POST['book_single']) && !empty($_POST['book_single'])) {
    $room_ids = [(int)$_POST['book_single']];
} elseif (isset($_POST['rooms']) && !empty($_POST['rooms'])) {
    $room_ids = array_map('intval', $_POST['rooms']);
} else {
    header('Location: rooms.php');
    exit;
}

// Get selected rooms
$ids_str = implode(',', $room_ids);
$sql = "SELECT * FROM rooms WHERE id IN ($ids_str)";
$result = mysqli_query($conn, $sql);
$selected_rooms = [];
$total_price = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $selected_rooms[] = $row;
    $total_price += $row['price_per_night'];
}

if (empty($selected_rooms)) {
    header('Location: rooms.php');
    exit;
}

// Get available discounts - show all active discounts
$available_discounts = [];
$discount_query = mysqli_query($conn, "
    SELECT * FROM discounts 
    WHERE is_active = 1 
    AND end_date >= CURDATE()
    ORDER BY discount_value DESC
");
while ($discount = mysqli_fetch_assoc($discount_query)) {
    $available_discounts[] = $discount;
}

// Process booking when Confirm Booking is clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_booking'])) {

    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $guests = $_POST['guests'];
    $discount_id = isset($_POST['discount_id']) && $_POST['discount_id'] != '' ? (int)$_POST['discount_id'] : null;

    $check_in_date  = new DateTime($check_in);
    $check_out_date = new DateTime($check_out);

    $nights = $check_in_date->diff($check_out_date)->days;

    if ($nights <= 0) {
        $error = "Check-out date must be after check-in date.";
    } else {

        $final_total = $total_price * $nights;
        $discount_amount = 0;

        // Apply discount if selected
        if ($discount_id) {
            $disc_query = mysqli_query($conn, "SELECT * FROM discounts WHERE id = $discount_id AND is_active = 1");
            if ($disc_data = mysqli_fetch_assoc($disc_query)) {
                // Check if check_in is within discount period
                if ($check_in >= $disc_data['start_date'] && $check_in <= $disc_data['end_date']) {
                    if ($disc_data['discount_type'] == 'percentage') {
                        $discount_amount = ($final_total * $disc_data['discount_value']) / 100;
                    } else {
                        $discount_amount = $disc_data['discount_value'];
                    }
                    $final_total = $final_total - $discount_amount;
                }
            }
        }

        // Store everything for payment page
        $_SESSION['pending_booking'] = [
            'user_id'       => $user_id,
            'room_ids'      => $room_ids,
            'check_in'      => $check_in,
            'check_out'     => $check_out,
            'nights'        => $nights,
            'guests'        => $guests,
            'final_total'   => $final_total,
            'discount_id'   => $discount_id,
            'discount_amount' => $discount_amount
        ];

        header("Location: payment.php");
        exit;
    }
}
?>


<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f5f5f7;
    margin: 0;
    padding: 0;
}

.booking-wrapper {
    max-width: 1000px;
    margin: 30px auto;
    padding: 0 20px;
}

.booking-header {
    text-align: center;
    margin-bottom: 30px;
}

.booking-header h1 {
    font-size: 32px;
    color: #333;
    margin: 0 0 10px;
}

.booking-header p {
    color: #777;
    font-size: 14px;
}

.booking-content {
    display: grid;
    grid-template-columns: 1fr 400px;
    gap: 20px;
}

/* LEFT SIDE - BOOKING FORM */
.booking-form-section {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 6px 24px rgba(0,0,0,0.06);
}

.booking-form-section h2 {
    margin: 0 0 20px;
    font-size: 20px;
    color: #333;
}

.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
    font-weight: 500;
    color: #444;
}

.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 12px 14px;
    border: 1px solid #ddd;
    border-radius: 10px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    transition: border 0.3s;
}

.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: #c59d5f;
}

.form-group textarea {
    resize: vertical;
    min-height: 80px;
}

.date-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.error-message {
    background: #fee;
    color: #d00;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    font-size: 14px;
    border: 2px solid #d00;
}

/* RIGHT SIDE - SUMMARY */
.booking-summary {
    background: #fff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 6px 24px rgba(0,0,0,0.06);
    align-self: flex-start;
    position: sticky;
    top: 20px;
}

.booking-summary h2 {
    margin: 0 0 16px;
    font-size: 18px;
    color: #333;
}

.selected-rooms-list {
    margin-bottom: 20px;
}

.summary-room-item {
    display: flex;
    gap: 12px;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #eee;
}

.summary-room-item:last-child {
    border-bottom: none;
}

.summary-room-thumb img {
    width: 80px;
    height: 60px;
    border-radius: 8px;
    object-fit: cover;
}

.summary-room-info {
    flex: 1;
}

.summary-room-info h4 {
    margin: 0 0 4px;
    font-size: 14px;
    color: #333;
}

.summary-room-info small {
    font-size: 12px;
    color: #777;
}

.summary-room-price {
    font-size: 14px;
    font-weight: 600;
    color: #c59d5f;
}

.price-breakdown {
    background: #f9f9f9;
    padding: 14px;
    border-radius: 10px;
    margin-bottom: 16px;
}

.price-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 13px;
}

.price-row.total {
    font-size: 16px;
    font-weight: 600;
    color: #c59d5f;
    padding-top: 8px;
    border-top: 2px solid #ddd;
    margin-top: 8px;
}

.btn-confirm-booking {
    width: 100%;
    padding: 14px;
    background: #c59d5f;
    color: #fff;
    border: none;
    border-radius: 999px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-confirm-booking:hover {
    background: #b08d4f;
}

.btn-back {
    display: block;
    margin-top: 12px;
    text-align: center;
    color: #777;
    text-decoration: none;
    font-size: 13px;
}

.btn-back:hover {
    color: #c59d5f;
}

/* RESPONSIVE */
@media(max-width: 900px) {
    .booking-content {
        grid-template-columns: 1fr;
    }
    
    .booking-summary {
        position: relative;
        top: 0;
    }
}
</style>

<div class="booking-wrapper">
    <div class="booking-header">
        <h1>Complete Your Booking</h1>
        <p>Fill in the details below to confirm your reservation</p>
    </div>

    <?php if (isset($error)): ?>
        <div class="error-message"><?php echo $error; ?></div>
    <?php endif; ?>

    <form method="post" action="">
        <!-- Hidden inputs to preserve room IDs -->
        <?php foreach ($room_ids as $rid): ?>
            <input type="hidden" name="rooms[]" value="<?php echo $rid; ?>">
        <?php endforeach; ?>
        
        <div class="booking-content">
            <!-- LEFT: Booking Details -->
            <div class="booking-form-section">
                <h2>Booking Details</h2>

                <div class="date-grid">
                    <div class="form-group">
                        <label>Check-In Date</label>
                        <input type="date" name="check_in" 
                            min="<?php echo date('Y-m-d'); ?>" 
                            value="<?php echo isset($_POST['check_in']) ? $_POST['check_in'] : date('Y-m-d'); ?>" 
                            required>
                    </div>

                    <div class="form-group">
                        <label>Check-Out Date</label>
                        <input type="date" name="check_out" 
                            min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" 
                            value="<?php echo isset($_POST['check_out']) ? $_POST['check_out'] : date('Y-m-d', strtotime('+1 day')); ?>" 
                            required>
                    </div>
                </div>

                <div class="form-group">
                    <label>Number of Guests</label>
                    <input type="number" name="guests" min="1" 
                        value="<?php echo isset($_POST['guests']) ? $_POST['guests'] : 1; ?>" 
                        required>
                </div>

                <?php if (!empty($available_discounts)): ?>
                <div class="form-group">
                    <label>Pilih Diskon (Opsional) 🎁</label>
                    <select name="discount_id" class="form-control" id="discount-select">
                        <option value="">-- Tidak Pakai Diskon --</option>
                        <?php foreach ($available_discounts as $disc): ?>
                            <?php 
                            $disc_label = $disc['discount_type'] == 'percentage' 
                                ? $disc['discount_value'] . '% OFF' 
                                : 'Rp ' . number_format($disc['discount_value']) . ' OFF';
                            ?>
                            <option value="<?php echo $disc['id']; ?>" 
                                    data-type="<?php echo $disc['discount_type']; ?>"
                                    data-value="<?php echo $disc['discount_value']; ?>"
                                    data-start="<?php echo $disc['start_date']; ?>"
                                    data-end="<?php echo $disc['end_date']; ?>"
                                    <?php echo (isset($_POST['discount_id']) && $_POST['discount_id'] == $disc['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($disc['discount_name']); ?> - <?php echo $disc_label; ?>
                                (<?php echo date('d/m/Y', strtotime($disc['start_date'])); ?> - <?php echo date('d/m/Y', strtotime($disc['end_date'])); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:#777; font-size:11px; display:block; margin-top:5px;" id="discount-message">
                        * Pilih tanggal check-in terlebih dahulu untuk melihat diskon yang berlaku
                    </small>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" 
                           value="<?php echo isset($_POST['full_name']) ? $_POST['full_name'] : ''; ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone"
                           value="<?php echo isset($_POST['phone']) ? $_POST['phone'] : ''; ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label>Additional Notes (Optional)</label>
                    <textarea name="note"><?php echo isset($_POST['note']) ? $_POST['note'] : ''; ?></textarea>
                </div>

            </div>

            <!-- RIGHT: Booking Summary -->
            <div class="booking-summary">
                <h2>Booking Summary</h2>

                <div class="selected-rooms-list">
                    <?php foreach ($selected_rooms as $room): ?>
                        <div class="summary-room-item">
                            <div class="summary-room-thumb">
                                <?php
                                $img = !empty($room['image']) ? 'uploads/'.$room['image'] :
                                    'https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&w=200&q=80';
                                ?>
                                <img src="<?php echo $img; ?>" alt="Room">
                            </div>
                            <div class="summary-room-info">
                                <h4><?php echo htmlspecialchars($room['title']); ?></h4>
                                <small><?php echo htmlspecialchars($room['city']); ?> · <?php echo htmlspecialchars($room['type']); ?></small>
                            </div>
                            <div class="summary-room-price">
                                Rp <?php echo number_format($room['price_per_night']); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="price-breakdown">
                    <div class="price-row">
                        <span><?php echo count($selected_rooms); ?> room(s) × <span id="nights-display">1</span> night(s)</span>
                        <span id="subtotal-display">Rp <?php echo number_format($total_price); ?></span>
                    </div>
                    <div class="price-row" id="discount-row" style="display:none; color:#28a745;">
                        <span>Diskon 🎁</span>
                        <span id="discount-display">- Rp 0</span>
                    </div>
                    <div class="price-row total">
                        <span>Total Price</span>
                        <span id="total-display">Rp <?php echo number_format($total_price); ?></span>
                    </div>
                    <small style="color:#777;font-size:11px;display:block;margin-top:8px;">
                        * Price will be calculated automatically
                    </small>
                </div>

                <script>
                // Auto-calculate price based on dates and discount
                const pricePerNight = <?php echo $total_price; ?>;
                const checkInInput = document.querySelector('input[name="check_in"]');
                const checkOutInput = document.querySelector('input[name="check_out"]');
                const discountSelect = document.getElementById('discount-select');
                const discountMessage = document.getElementById('discount-message');
                const nightsDisplay = document.getElementById('nights-display');
                const subtotalDisplay = document.getElementById('subtotal-display');
                const discountRow = document.getElementById('discount-row');
                const discountDisplay = document.getElementById('discount-display');
                const totalDisplay = document.getElementById('total-display');
                
                function validateDiscounts() {
                    if (!discountSelect || !checkInInput.value) return;
                    
                    const checkInDate = checkInInput.value;
                    let hasValidDiscount = false;
                    
                    // Check each discount option
                    for (let i = 1; i < discountSelect.options.length; i++) {
                        const option = discountSelect.options[i];
                        const startDate = option.dataset.start;
                        const endDate = option.dataset.end;
                        
                        // Check if check-in date is within discount period
                        if (checkInDate >= startDate && checkInDate <= endDate) {
                            option.disabled = false;
                            option.style.color = '';
                            hasValidDiscount = true;
                        } else {
                            option.disabled = true;
                            option.style.color = '#ccc';
                            // Reset selection if currently selected discount is invalid
                            if (option.selected) {
                                discountSelect.value = '';
                            }
                        }
                    }
                    
                    // Update message
                    if (discountMessage) {
                        if (hasValidDiscount) {
                            discountMessage.style.color = '#28a745';
                            discountMessage.textContent = '✓ Ada diskon yang tersedia untuk tanggal ini!';
                        } else {
                            discountMessage.style.color = '#777';
                            discountMessage.textContent = '✗ Tidak ada diskon untuk tanggal check-in yang dipilih';
                        }
                    }
                }
                
                function calculatePrice() {
                    const checkIn = new Date(checkInInput.value);
                    const checkOut = new Date(checkOutInput.value);
                    
                    if (checkIn && checkOut && checkOut > checkIn) {
                        const diffTime = Math.abs(checkOut - checkIn);
                        const nights = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        let subtotal = pricePerNight * nights;
                        
                        nightsDisplay.textContent = nights;
                        subtotalDisplay.textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
                        
                        // Apply discount if selected
                        let discountAmount = 0;
                        if (discountSelect && discountSelect.value) {
                            const selectedOption = discountSelect.options[discountSelect.selectedIndex];
                            const discountType = selectedOption.dataset.type;
                            const discountValue = parseFloat(selectedOption.dataset.value);
                            
                            if (discountType === 'percentage') {
                                discountAmount = (subtotal * discountValue) / 100;
                            } else {
                                discountAmount = discountValue;
                            }
                            
                            discountRow.style.display = 'flex';
                            discountDisplay.textContent = '- Rp ' + discountAmount.toLocaleString('id-ID');
                        } else {
                            discountRow.style.display = 'none';
                        }
                        
                        const finalTotal = subtotal - discountAmount;
                        totalDisplay.textContent = 'Rp ' + finalTotal.toLocaleString('id-ID');
                    } else {
                        nightsDisplay.textContent = '1';
                        subtotalDisplay.textContent = 'Rp ' + pricePerNight.toLocaleString('id-ID');
                        totalDisplay.textContent = 'Rp ' + pricePerNight.toLocaleString('id-ID');
                        discountRow.style.display = 'none';
                    }
                }
                
                // Update on date change
                checkInInput.addEventListener('change', function() {
                    validateDiscounts();
                    calculatePrice();
                });
                checkOutInput.addEventListener('change', calculatePrice);
                
                // Update on discount change
                if (discountSelect) {
                    discountSelect.addEventListener('change', calculatePrice);
                }
                
                // Calculate on page load
                validateDiscounts();
                calculatePrice();
                </script>

                <button type="submit" name="confirm_booking" class="btn-confirm-booking">
                    Confirm Booking
                </button>

                <a href="rooms.php" class="btn-back">← Back to Rooms</a>
            </div>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
