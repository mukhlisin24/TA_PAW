<?php
session_start();
require 'koneksi.php';
include "header.php";
if ($_SESSION['role'] !== 'customer') {
    echo "<script>alert('Hanya customer yang bisa akses!');
        window.location='owner_dashboard.php';
      </script>";
    exit;

}

// Read filters
$city = isset($_GET['city']) ? trim($_GET['city']) : '';
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$guests = isset($_GET['guests']) ? (int) $_GET['guests'] : 0;
$max_price = isset($_GET['max_price']) ? (int) $_GET['max_price'] : 0;

// Build query
$conditions = [];
if ($city !== '') {
    $c = mysqli_real_escape_string($conn, $city);
    $conditions[] = "city LIKE '%$c%'";
}
if ($type !== '') {
    $t = mysqli_real_escape_string($conn, $type);
    $conditions[] = "type = '$t'";
}
if ($guests > 0) {
    $conditions[] = "capacity >= $guests";
}
if ($max_price > 0) {
    $conditions[] = "price_per_night <= $max_price";
}

$sql = "SELECT * FROM rooms";
if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}
$sql .= " ORDER BY price_per_night ASC";

$rooms = mysqli_query($conn, $sql);
?>
<style>
    .page-wrapper {
        display: flex;
        gap: 20px;
        padding: 20px 5%;
        background: #f5f5f7;
        min-height: 80vh;
        font-family: "Poppins", sans-serif;
    }

    /* SIDEBAR FILTER */
    .filter-sidebar {
        width: 260px;
        background: #fff;
        border-radius: 14px;
        padding: 14px 14px 16px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        align-self: flex-start;
    }

    .filter-sidebar h3 {
        margin: 0 0 8px;
        font-size: 16px;
    }

    .filter-sidebar small {
        font-size: 11px;
        color: #777;
    }

    .filter-group {
        margin-top: 10px;
    }

    .filter-group label {
        font-size: 12px;
        display: block;
        margin-bottom: 4px;
    }

    .filter-group input,
    .filter-group select {
        width: 100%;
        padding: 6px 8px;
        border-radius: 8px;
        border: 1px solid #ddd;
        font-size: 13px;
    }

    .filter-actions {
        margin-top: 12px;
        display: flex;
        gap: 6px;
    }

    .btn-filter {
        flex: 1;
        padding: 7px 0;
        border-radius: 999px;
        border: none;
        font-size: 13px;
        cursor: pointer;
    }

    .btn-filter-primary {
        background: #c59d5f;
        color: #fff;
    }

    .btn-filter-reset {
        background: #f2f0ea;
        color: #8a6d34;
    }

    /* MAIN LIST */
    .rooms-main {
        flex: 1;
    }

    .rooms-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .rooms-header h2 {
        margin: 0;
        font-size: 20px;
    }

    .rooms-header small {
        font-size: 12px;
        color: #777;
    }

    .sort-info {
        font-size: 12px;
        color: #777;
    }

    /* ROOMS LIST */
    .rooms-list {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .room-item {
        background: #fff;
        border-radius: 14px;
        display: flex;
        gap: 12px;
        padding: 10px;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.04);
    }

    .room-thumb img {
        width: 150px;
        height: 110px;
        border-radius: 10px;
        object-fit: cover;
    }

    .room-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .room-title-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 8px;
    }

    .room-title-row h3 {
        margin: 0;
        font-size: 16px;
    }

    .room-location {
        font-size: 12px;
        color: #777;
    }

    .room-capacity {
        font-size: 12px;
        color: #777;
    }

    .room-price-lg {
        font-size: 16px;
        font-weight: 600;
        color: #c59d5f;
    }

    .room-meta-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 6px;
    }

    .room-actions-row {
        display: flex;
        gap: 8px;
        margin-top: 6px;
    }

    .btn-small-primary,
    .btn-small-outline {
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        cursor: pointer;
        text-decoration: none;
        border: none;
    }

    .btn-small-primary {
        background: #c59d5f;
        color: #fff;
    }

    .btn-small-outline {
        background: #f3eee4;
        color: #8a6d34;
    }

    .select-checkbox {
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 12px;
    }

    /* BOOK SELECTED BAR */
    .selected-bar {
        position: sticky;
        bottom: 0;
        margin-top: 15px;
        padding-top: 8px;
    }

    .selected-inner {
        background: #111;
        color: #fff;
        padding: 8px 12px;
        border-radius: 999px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 13px;
    }

    .selected-inner button {
        border: none;
        border-radius: 999px;
        padding: 6px 14px;
        background: #c59d5f;
        color: #fff;
        font-size: 13px;
        cursor: pointer;
    }

    /* RESPONSIVE */
    @media(max-width:900px) {
        .page-wrapper {
            flex-direction: column;
            padding: 15px;
        }

        .filter-sidebar {
            width: 100%;
            order: 1;
        }

        .rooms-main {
            order: 2;
        }

        .room-item {
            flex-direction: column;
        }

        .room-thumb img {
            width: 100%;
            height: 180px;
        }
    }
</style>

<div class="page-wrapper">

    <!-- LEFT FILTER SIDEBAR -->
    <aside class="filter-sidebar">
        <h3>Filter Rooms</h3>
        <small>Adjust filters to find your perfect stay.</small>

        <form method="get" action="rooms.php">
            <div class="filter-group">
                <label>City</label>
                <input type="text" name="city" value="<?php echo htmlspecialchars($city); ?>"
                    placeholder="Jakarta, Bali...">
            </div>

            <div class="filter-group">
                <label>Room Type</label>
                <select name="type">
                    <option value="">Any Type</option>
                    <?php
                    $types = ['Standard', 'Deluxe', 'Suite', 'Family', 'Premium'];
                    foreach ($types as $t) {
                        $sel = ($type === $t) ? 'selected' : '';
                        echo "<option value=\"$t\" $sel>$t</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="filter-group">
                <label>Guests</label>
                <input type="number" name="guests" min="1" value="<?php echo $guests > 0 ? $guests : ''; ?>"
                    placeholder="2">
            </div>

            <div class="filter-group">
                <label>Max Price / Night</label>
                <input type="number" name="max_price" min="0" value="<?php echo $max_price > 0 ? $max_price : ''; ?>"
                    placeholder="1500000">
            </div>

            <div class="filter-actions">
                <button type="submit" class="btn-filter btn-filter-primary">Apply</button>
                <a href="rooms.php" class="btn-filter btn-filter-reset"
                    style="text-align:center;line-height:23px;">Reset</a>
            </div>
        </form>
    </aside>

    <!-- MAIN ROOMS CONTENT -->
    <section class="rooms-main">
        <div class="rooms-header">
            <div>
                <h2>Available Rooms</h2>
                <small>
                    <?php
                    $count = mysqli_num_rows($rooms);
                    echo $count . " room(s) found";
                    if ($city)
                        echo " · in " . htmlspecialchars($city);
                    ?>
                </small>
            </div>
            <div class="sort-info">
                Sorted by price (lowest first)
            </div>
        </div>

        <form method="post" action="create_booking.php">
            <div class="rooms-list">
                <?php if ($count > 0): ?>
                    <?php while ($r = mysqli_fetch_assoc($rooms)): ?>
                        <div class="room-item">
                            <div class="room-thumb">
                                <?php
                                $img = !empty($r['image']) ? 'uploads/' . $r['image'] :
                                    'https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&w=800&q=80';
                                ?>
                                <img src="<?php echo $img; ?>" alt="Room">
                            </div>
                            <div class="room-body">
                                <div class="room-title-row">
                                    <div>
                                        <h3><?php echo htmlspecialchars($r['title']); ?></h3>
                                        <div class="room-location">
                                            <?php echo htmlspecialchars($r['city']); ?> ·
                                            <?php echo htmlspecialchars($r['type']); ?>
                                        </div>
                                        <div class="room-capacity">
                                            Up to <?php echo (int) $r['capacity']; ?> guests
                                        </div>
                                    </div>
                                    <div class="room-price-lg">
                                        Rp <?php echo number_format($r['price_per_night']); ?><br>
                                        <span style="font-size:11px;color:#777;">per night</span>
                                    </div>
                                </div>

                                <div class="room-meta-row">
                                    <div class="select-checkbox">
                                        <input type="checkbox" name="rooms[]" value="<?php echo $r['id']; ?>">
                                        <span>Select</span>
                                    </div>
                                    <div class="room-actions-row">
                                        <a href="room_detail.php?id=<?php echo $r['id']; ?>" class="btn-small-outline">View
                                            Details</a>
                                        <button type="submit" name="book_single" value="<?php echo $r['id']; ?>"
                                            class="btn-small-primary">
                                            Book This
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No rooms match your filters.</p>
                <?php endif; ?>
            </div>

            <!-- Multi-room booking bar -->
            <div class="selected-bar">
                <div class="selected-inner">
                    <span>Select multiple rooms and book together.</span>
                    <button type="submit" name="book_selected">Book Selected</button>
                </div>
            </div>
        </form>
    </section>

</div>