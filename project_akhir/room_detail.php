<?php
require 'koneksi.php';
include 'header.php';

if (!isset($_GET['id'])) {
    header("Location: rooms.php");
    exit;
}

$room_id = intval($_GET['id']);
$query = mysqli_query($conn, "SELECT * FROM rooms WHERE id = $room_id");
$room = mysqli_fetch_assoc($query);

if (!$room) {
    echo "<h2>Room not found.</h2>";
    exit;
}

// Set default image if empty
$room_image = !empty($room['image']) ? 'uploads/' . $room['image'] : 'https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&w=800&q=80';
?>

<style>
body {
    font-family: 'Poppins', sans-serif;
    background: #f7f7f7;
}

/* --- PAGE WRAPPER --- */
.detail-wrapper {
    max-width: 1200px;
    margin: 40px auto;
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 30px;
}

/* --- IMAGE GALLERY --- */
.gallery {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 10px;
}

.gallery img {
    width: 100%;
    border-radius: 14px;
    object-fit: cover;
    height: 300px;
}

.gallery-small {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.gallery-small img {
    height: 145px;
}

/* --- DETAILS SECTION --- */
.details {
    background: white;
    padding: 25px;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.06);
}

.details h1 {
    margin-bottom: 10px;
    font-size: 28px;
}

/* --- AMENITIES --- */
.amenities {
    margin-top: 25px;
}

.amenities h2 {
    font-size: 20px;
    margin-bottom: 10px;
}

.amenities-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
}

.amenity-item {
    background: #fafafa;
    padding: 10px;
    border-radius: 10px;
    font-size: 14px;
}

/* --- BOOKING SIDEBAR --- */
.sidebar {
    position: sticky;
    top: 20px;
    background: white;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 6px 20px rgba(0,0,0,0.06);
}

.sidebar-price {
    font-size: 26px;
    font-weight: 600;
    color: #c59d5f;
    margin-bottom: 20px;
}

.sidebar input {
    width: 100%;
    padding: 12px;
    margin-bottom: 10px;
    border-radius: 8px;
    border: 1px solid #ddd;
}

.btn-book {
    display: block;
    width: 100%;
    background: #c59d5f;
    color: white;
    padding: 14px;
    text-align: center;
    font-size: 16px;
    border-radius: 40px;
    text-decoration: none;
    font-weight: 600;
}
.btn-book:hover { background: #b08d4f; }

.description-box {
    margin-top: 20px;
    padding: 20px;
    background: #fafafa;
    border-radius: 12px;
    line-height: 1.7;
}
</style>

<div class="detail-wrapper">

    <!-- LEFT SECTION -->
    <div>

        <!-- IMAGE GALLERY -->
        <div class="gallery">
            <img src="<?php echo htmlspecialchars($room_image); ?>" alt="Main Room Image">

            <div class="gallery-small">
                <img src="<?php echo htmlspecialchars($room_image); ?>" alt="Room View">
                <img src="<?php echo htmlspecialchars($room_image); ?>" alt="Room Detail">
            </div>
        </div>

        <!-- ROOM DETAILS -->
        <div class="details">
            <h1><?php echo htmlspecialchars($room['title']); ?></h1>
            <p><?php echo htmlspecialchars($room['city']); ?> · <?php echo htmlspecialchars($room['type']); ?></p>

            <div class="description-box">
                <h2>Description</h2>
                <p><?php echo nl2br(htmlspecialchars($room['description'])); ?></p>
            </div>

            <div class="amenities">
                <h2>Amenities</h2>
                <div class="amenities-grid">
                    <div class="amenity-item">Fast WiFi</div>
                    <div class="amenity-item">Air Conditioning</div>
                    <div class="amenity-item">Private Bathroom</div>
                    <div class="amenity-item">TV + Netflix</div>
                    <div class="amenity-item">Parking</div>
                    <div class="amenity-item">24/7 Security</div>
                </div>
            </div>
        </div>

    </div>

    <!-- RIGHT SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-price">
            Rp <?php echo number_format($room['price_per_night'], 0, ',', '.'); ?> / night
        </div>

        <!-- DATE PICKER -->
        <form action="create_booking.php" method="POST">

            <input type="hidden" name="book_single" value="<?php echo htmlspecialchars($room['id']); ?>">

            <label>Check-In</label>
            <input type="date" name="check_in" required>

            <label>Check-Out</label>
            <input type="date" name="check_out" required>

            <label>Guests</label>
            <input type="number" name="guests" min="1" value="1" required>

            <button class="btn-book" type="submit">Book Now</button>
        </form>

    </div>
</div>

<?php include 'footer.php'; ?>
