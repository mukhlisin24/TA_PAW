<?php
// include "header.php";
require 'koneksi.php';
session_start();

/* ========== AUTH CHECK ========== */
if (!isset($_SESSION['role'])) {
    echo "<script>window.location='login.php';</script>";
    exit;
}
if ($_SESSION['role'] !== 'admin') {
    echo "<script>window.location='customer_dashboard.php';</script>";
    exit;
}

/* ===================== DELETE ROOM ===================== */
if (isset($_GET['delete_room'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_room']);

    $q = mysqli_query($conn, "SELECT image FROM rooms WHERE id='$id'");
    if ($q && mysqli_num_rows($q) > 0) {
        $data = mysqli_fetch_assoc($q);
        if (!empty($data['image'])) {
            $file_path = __DIR__ . "/uploads/" . $data['image'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
    }

    mysqli_query($conn, "DELETE FROM rooms WHERE id='$id'");
    echo "<script>alert('Kamar berhasil dihapus!'); window.location='owner_dashboard.php?tab=rooms';</script>";
    exit;
}

/* ===================== UPDATE BOOKING STATUS ===================== */
if (isset($_GET['action']) && isset($_GET['id_book'])) {
    $status = $_GET['action'] == 'approve' ? 'confirmed' : 'cancelled';
    $id_book = mysqli_real_escape_string($conn, $_GET['id_book']);
    mysqli_query($conn, "UPDATE bookings SET status='$status' WHERE id='$id_book'");
    echo "<script>window.location='owner_dashboard.php?tab=bookings';</script>";
    exit;
}

/* ===================== DELETE DISCOUNT ===================== */
if (isset($_GET['delete_discount'])) {
    $id = mysqli_real_escape_string($conn, $_GET['delete_discount']);
    mysqli_query($conn, "DELETE FROM discounts WHERE id='$id'");
    echo "<script>alert('Diskon berhasil dihapus!'); window.location='owner_dashboard.php?tab=discounts';</script>";
    exit;
}

/* ===================== TOGGLE DISCOUNT STATUS ===================== */
if (isset($_GET['toggle_discount'])) {
    $id = mysqli_real_escape_string($conn, $_GET['toggle_discount']);
    $status = isset($_GET['status']) && $_GET['status'] == '1' ? 0 : 1;
    mysqli_query($conn, "UPDATE discounts SET is_active='$status' WHERE id='$id'");
    echo "<script>window.location='owner_dashboard.php?tab=discounts';</script>";
    exit;
}

/* ===================== SAVE DISCOUNT (ADD / UPDATE) ===================== */
if (isset($_POST['save_discount'])) {
    $discount_name = mysqli_real_escape_string($conn, $_POST['discount_name']);
    $discount_type = mysqli_real_escape_string($conn, $_POST['discount_type']);
    $discount_value = (float) $_POST['discount_value'];
    $start_date = mysqli_real_escape_string($conn, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($conn, $_POST['end_date']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!empty($_POST['id_update'])) {
        $id_up = mysqli_real_escape_string($conn, $_POST['id_update']);
        $query = "UPDATE discounts 
                    SET discount_name='$discount_name',
                        discount_type='$discount_type',
                        discount_value='$discount_value',
                        start_date='$start_date',
                        end_date='$end_date',
                        is_active='$is_active'
                  WHERE id='$id_up'";
    } else {
        $query = "INSERT INTO discounts (discount_name, discount_type, discount_value, start_date, end_date, is_active)
                  VALUES ('$discount_name', '$discount_type', '$discount_value', '$start_date', '$end_date', '$is_active')";
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data diskon berhasil disimpan!'); window.location='owner_dashboard.php?tab=discounts';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan: " . mysqli_error($conn) . "');</script>";
    }
}

/* ===================== EDIT MODE ===================== */
$edit_mode = false;
$kamar_edit = [];

if (isset($_GET['edit_room'])) {
    $edit_mode = true;
    $id_edit = mysqli_real_escape_string($conn, $_GET['edit_room']);
    $res = mysqli_query($conn, "SELECT * FROM rooms WHERE id='$id_edit'");
    $kamar_edit = mysqli_fetch_assoc($res);
}

/* ===================== EDIT DISCOUNT MODE ===================== */
$edit_discount_mode = false;
$discount_edit = [];

if (isset($_GET['edit_discount'])) {
    $edit_discount_mode = true;
    $id_edit = mysqli_real_escape_string($conn, $_GET['edit_discount']);
    $res = mysqli_query($conn, "SELECT * FROM discounts WHERE id='$id_edit'");
    $discount_edit = mysqli_fetch_assoc($res);
}

/* ===================== SAVE ROOM (ADD / UPDATE) ===================== */
if (isset($_POST['save_room'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $capacity = (int) $_POST['capacity'];
    $price = (float) $_POST['price_per_night'];
    $desc = mysqli_real_escape_string($conn, $_POST['description']);

    $image_query = "";
    $img_val = "";

    if (!empty($_FILES['image']['name'])) {
        $nama_file = time() . '_' . preg_replace('/\s+/', '_', $_FILES['image']['name']);
        $tmp_file = $_FILES['image']['tmp_name'];
        $upload_dir_fs = __DIR__ . "/uploads/";

        if (!is_dir($upload_dir_fs)) {
            mkdir($upload_dir_fs, 0777, true);
        }

        if (move_uploaded_file($tmp_file, $upload_dir_fs . $nama_file)) {
            $image_query = ", image='$nama_file'";
            $img_val = $nama_file;

            if ($edit_mode && !empty($kamar_edit['image'])) {
                $old_path = $upload_dir_fs . $kamar_edit['image'];
                if (file_exists($old_path)) {
                    unlink($old_path);
                }
            }
        }
    } else if (!$edit_mode) {
        $img_val = "";
    }

    if (!empty($_POST['id_update'])) {
        $id_up = mysqli_real_escape_string($conn, $_POST['id_update']);
        $query = "UPDATE rooms 
                    SET title='$title',
                        city='$city',
                        type='$type',
                        capacity='$capacity',
                        price_per_night='$price',
                        description='$desc'
                        $image_query
                  WHERE id='$id_up'";
    } else {
        $query = "INSERT INTO rooms (title, city, type, capacity, price_per_night, description, image)
                  VALUES ('$title', '$city', '$type', '$capacity', '$price', '$desc', '$img_val')";
    }

    if (mysqli_query($conn, $query)) {
        echo "<script>alert('Data kamar berhasil disimpan!'); window.location='owner_dashboard.php?tab=rooms';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan: " . mysqli_error($conn) . "');</script>";
    }
}

/* ===================== DASHBOARD STATS ===================== */
$total_rooms = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM rooms"))[0] ?? 0;
$total_bookings = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings"))[0] ?? 0;
$pending_bookings = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE status='pending'"))[0] ?? 0;
$confirmed_bookings = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE status='confirmed'"))[0] ?? 0;

/* ===================== REVENUE STATS ===================== */
$row_total_rev = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT SUM(total_amount) AS s 
     FROM bookings 
     WHERE payment_status='paid' AND status='confirmed'"
));
$total_revenue = (float) ($row_total_rev['s'] ?? 0);

$row_month_rev = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT SUM(total_amount) AS s 
     FROM bookings 
     WHERE payment_status='paid' AND status='confirmed'
       AND YEAR(created_at) = YEAR(CURDATE())
       AND MONTH(created_at) = MONTH(CURDATE())"
));
$monthly_revenue = (float) ($row_month_rev['s'] ?? 0);

$row_today_rev = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT SUM(total_amount) AS s 
     FROM bookings 
     WHERE payment_status='paid' AND status='confirmed'
       AND DATE(created_at) = CURDATE()"
));
$today_revenue = (float) ($row_today_rev['s'] ?? 0);

$row_pending_amt = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT SUM(total_amount) AS s 
     FROM bookings 
     WHERE payment_status='unpaid' AND status='pending'"
));
$pending_amount = (float) ($row_pending_amt['s'] ?? 0);

$row_avg_rev = mysqli_fetch_assoc(mysqli_query(
    $conn,
    "SELECT AVG(total_amount) AS a 
     FROM bookings 
     WHERE payment_status='paid' AND status='confirmed'"
));
$avg_revenue = (float) ($row_avg_rev['a'] ?? 0);

/* ===================== CALENDAR (CURRENT MONTH) ===================== */
$firstDayOfMonth = date('Y-m-01');
$lastDayOfMonth = date('Y-m-t');

$calendarRevenue = [];
$maxDayRevenue = 0;

$cal_q = mysqli_query(
    $conn,
    "SELECT DATE(created_at) AS d, SUM(total_amount) AS revenue
     FROM bookings
     WHERE payment_status='paid' AND status='confirmed'
       AND DATE(created_at) BETWEEN '$firstDayOfMonth' AND '$lastDayOfMonth'
     GROUP BY DATE(created_at)"
);

while ($row = mysqli_fetch_assoc($cal_q)) {
    $date = $row['d'];
    $rev = (float) $row['revenue'];
    $calendarRevenue[$date] = $rev;
    if ($rev > $maxDayRevenue)
        $maxDayRevenue = $rev;
}

/* ===================== DATE RANGE REVENUE CHART ===================== */
$from_date = isset($_GET['from']) && $_GET['from'] !== '' ? $_GET['from'] : $firstDayOfMonth;
$to_date = isset($_GET['to']) && $_GET['to'] !== '' ? $_GET['to'] : date('Y-m-d');

if (strtotime($to_date) < strtotime($from_date)) {
    $to_date = $from_date;
}

$range_labels = [];
$range_revenue = [];
$range_bookings = [];

$range_q = mysqli_query(
    $conn,
    "SELECT 
        DATE(created_at) AS d,
        SUM(total_amount) AS revenue,
        COUNT(*) AS bookings
     FROM bookings
     WHERE payment_status='paid' AND status='confirmed'
       AND DATE(created_at) BETWEEN '$from_date' AND '$to_date'
     GROUP BY DATE(created_at)
     ORDER BY d ASC"
);

while ($row = mysqli_fetch_assoc($range_q)) {
    $d = $row['d'];
    $range_labels[] = date('d M', strtotime($d));
    $range_revenue[] = (float) $row['revenue'];
    $range_bookings[] = (int) $row['bookings'];
}

/* ===================== SIDEBAR DATA ===================== */
$owner_name = isset($_SESSION['name']) ? $_SESSION['name'] : "Administrator";
$owner_email = isset($_SESSION['email']) ? $_SESSION['email'] : "admin@hotel.com";
$initials = strtoupper(substr($owner_name, 0, 1));
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'rooms';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Owner Dashboard</title>
    <link rel="stylesheet" href="./assets/style.css">
    <link rel="stylesheet" href="assets/owner.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

    <div class="admin-layout">

        <aside class="admin-sidebar">
            <div class="admin-profile">
                <div class="admin-avatar"><?php echo $initials; ?></div>
                <div class="admin-name"><?php echo htmlspecialchars($owner_name); ?></div>
                <div class="admin-role">Owner / Admin</div>
                <div class="admin-email"><?php echo htmlspecialchars($owner_email); ?></div>
            </div>

            <div class="sidebar-section-title">Menu</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="owner_dashboard.php?tab=rooms"
                        class="<?php echo ($active_tab == 'rooms' || $active_tab == '') ? 'active' : ''; ?>">
                        <span class="icon">🏨</span> Manage Rooms
                    </a>
                </li>
                <li>
                    <a href="owner_dashboard.php?tab=bookings"
                        class="<?php echo ($active_tab == 'bookings') ? 'active' : ''; ?>">
                        <span class="icon">📅</span> Bookings
                    </a>
                </li>
                <li>
                    <a href="owner_dashboard.php?tab=revenue"
                        class="<?php echo ($active_tab == 'revenue') ? 'active' : ''; ?>">
                        <span class="icon">💰</span> Revenue
                    </a>
                </li>
                <li>
                    <a href="owner_dashboard.php?tab=discounts"
                        class="<?php echo ($active_tab == 'discounts') ? 'active' : ''; ?>">
                        <span class="icon">🎁</span> Discounts
                    </a>
                </li>
                <li>
                    <a href="index.php">
                        <span class="icon">🏠</span> Front Website
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <span class="icon">🚪</span> Logout
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                Hotel Web Admin Panel<br>
                <span style="opacity:.8;">Logged in as admin</span>
            </div>
        </aside>

        <main class="admin-main">

            <div class="admin-header-top">
                <h2><span class="icon">📊</span>Owner Dashboard</h2>
                <div class="breadcrumb">
                    <?php
                    if ($active_tab == 'rooms')
                        echo "Rooms / Admin";
                    elseif ($active_tab == 'bookings')
                        echo "Bookings / Admin";
                    elseif ($active_tab == 'revenue')
                        echo "Revenue / Admin";
                    elseif ($active_tab == 'discounts')
                        echo "Discounts / Admin";
                    elseif ($active_tab == 'add_discount')
                        echo "Add Discount / Admin";
                    else
                        echo "Add Room / Admin";
                    ?>
                </div>
            </div>

            <!-- ROOMS TAB -->
            <?php if ($active_tab == 'rooms'): ?>

                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-label">Total Rooms</div>
                        <div class="stat-value"><?php echo $total_rooms; ?></div>
                        <div class="stat-icon">🏨</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Bookings</div>
                        <div class="stat-value"><?php echo $total_bookings; ?></div>
                        <div class="stat-icon">📦</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Pending</div>
                        <div class="stat-value"><?php echo $pending_bookings; ?></div>
                        <div class="stat-icon">⏳</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Confirmed</div>
                        <div class="stat-value"><?php echo $confirmed_bookings; ?></div>
                        <div class="stat-icon">✅</div>
                    </div>
                </div>

                <?php
                $limit = 6;
                $page = isset($_GET['p']) ? (int) $_GET['p'] : 1;
                if ($page < 1)
                    $page = 1;

                $start = ($page - 1) * $limit;

                $total_rooms_query = mysqli_query($conn, "SELECT COUNT(*) AS total FROM rooms");
                $total_rooms_data = mysqli_fetch_assoc($total_rooms_query);
                $total_rooms_count = (int) $total_rooms_data['total'];

                $total_pages = $total_rooms_count > 0 ? ceil($total_rooms_count / $limit) : 1;

                $rooms = mysqli_query($conn, "
                SELECT *
                FROM rooms
                ORDER BY id DESC
                LIMIT $start, $limit
            ");
                ?>

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3 style="margin:0;">Daftar Kamar</h3>
                    <button onclick="window.location.href='owner_dashboard.php?tab=add'" class="btn-primary">
                        <span>＋</span> Tambah Kamar
                    </button>
                </div>

                <div class="room-grid">
                    <?php
                    if (mysqli_num_rows($rooms) > 0):
                        while ($row = mysqli_fetch_assoc($rooms)):
                            $has_image = false;
                            $img_src = "";

                            if (isset($row['image']) && !empty($row['image'])) {
                                $img_file = __DIR__ . "/uploads/" . $row['image'];
                                $img_src = "uploads/" . $row['image'];
                                $has_image = file_exists($img_file);
                            }
                            ?>
                            <div class="room-card">
                                <?php if ($has_image): ?>
                                    <img src="<?php echo $img_src; ?>" class="card-img-top" alt="Foto">
                                <?php else: ?>
                                    <div class="card-img-top">📷</div>
                                <?php endif; ?>

                                <div class="card-body">
                                    <div class="room-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                    <div class="room-meta">
                                        <?php echo htmlspecialchars($row['city']); ?> &middot;
                                        <?php echo htmlspecialchars($row['type']); ?> &middot;
                                        Kapasitas: <?php echo (int) $row['capacity']; ?>
                                    </div>
                                    <div class="room-price">
                                        Rp <?php echo number_format($row['price_per_night']); ?> / malam
                                    </div>

                                    <div class="card-actions">
                                        <a href="owner_dashboard.php?tab=rooms&p=<?php echo $page; ?>&delete_room=<?php echo $row['id']; ?>"
                                            class="link-del" onclick="return confirm('Yakin hapus kamar ini?');">Delete</a>
                                        <a href="owner_dashboard.php?tab=add&edit_room=<?php echo $row['id']; ?>"
                                            class="link-edit">View / Edit</a>
                                    </div>
                                </div>
                            </div>
                            <?php
                        endwhile;
                    else:
                        ?>
                        <p>Tidak ada kamar terdaftar.</p>
                    <?php endif; ?>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="owner_dashboard.php?tab=rooms&p=<?php echo $page - 1; ?>" class="page-btn">← Prev</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="owner_dashboard.php?tab=rooms&p=<?php echo $i; ?>"
                                class="page-number <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $total_pages): ?>
                            <a href="owner_dashboard.php?tab=rooms&p=<?php echo $page + 1; ?>" class="page-btn">Next →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- ADD ROOM TAB -->
            <?php elseif ($active_tab == 'add'): ?>

                <div class="form-panel">
                    <h3 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">
                        <?php echo $edit_mode ? "Edit Kamar" : "Tambah Kamar Baru"; ?>
                    </h3>

                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_update" value="<?php echo $edit_mode ? $kamar_edit['id'] : ''; ?>">

                        <?php if ($edit_mode && !empty($kamar_edit['image'])): ?>
                            <div style="text-align:center; margin-bottom:15px;">
                                <img src="uploads/<?php echo $kamar_edit['image']; ?>"
                                    style="height:150px; border-radius:8px; object-fit:cover;">
                            </div>
                        <?php endif; ?>

                        <div class="form-group">
                            <label>Foto Kamar</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>

                        <div class="form-group">
                            <label>Nama Kamar</label>
                            <input type="text" name="title" class="form-control" required
                                value="<?php echo $edit_mode ? htmlspecialchars($kamar_edit['title']) : ''; ?>">
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                            <div class="form-group">
                                <label>Kota</label>
                                <input type="text" name="city" class="form-control" required
                                    value="<?php echo $edit_mode ? htmlspecialchars($kamar_edit['city']) : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Tipe Kamar</label>
                                <select name="type" class="form-control">
                                    <?php
                                    $types = ['Standard', 'Deluxe', 'Suite', 'Family', 'Premium'];
                                    foreach ($types as $t) {
                                        $selected = ($edit_mode && $kamar_edit['type'] == $t) ? 'selected' : '';
                                        echo "<option value='$t' $selected>$t</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Harga per Malam (Rp)</label>
                            <input type="number" name="price_per_night" class="form-control" required
                                value="<?php echo $edit_mode ? $kamar_edit['price_per_night'] : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Kapasitas</label>
                            <input type="number" name="capacity" class="form-control" required
                                value="<?php echo $edit_mode ? $kamar_edit['capacity'] : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label>Deskripsi</label>
                            <textarea name="description" class="form-control" rows="3"><?php
                            echo $edit_mode ? htmlspecialchars($kamar_edit['description']) : '';
                            ?></textarea>
                        </div>

                        <button type="submit" name="save_room" class="btn-primary"
                            style="width:100%; justify-content:center;">
                            <?php echo $edit_mode ? "Simpan Perubahan" : "Simpan Kamar"; ?>
                        </button>
                        <a href="owner_dashboard.php?tab=rooms"
                            style="display:block; text-align:center; margin-top:15px; text-decoration:none; color:#777;">
                            Kembali
                        </a>
                    </form>
                </div>

                <!-- BOOKINGS TAB -->
            <?php elseif ($active_tab == 'bookings'): ?>

                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-label">Total Bookings</div>
                        <div class="stat-value"><?php echo $total_bookings; ?></div>
                        <div class="stat-icon">📦</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Pending</div>
                        <div class="stat-value"><?php echo $pending_bookings; ?></div>
                        <div class="stat-icon">⏳</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Confirmed</div>
                        <div class="stat-value"><?php echo $confirmed_bookings; ?></div>
                        <div class="stat-icon">✅</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Revenue (Paid)</div>
                        <div class="stat-value">Rp <?php echo number_format($total_revenue); ?></div>
                        <div class="stat-icon">💰</div>
                    </div>
                </div>

                <h3 style="margin-bottom:15px;">Daftar Pesanan</h3>
                <table class="booking-table">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Tamu</th>
                            <th>Check In / Out</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query_book = "SELECT b.*, u.name as guest_name
                                   FROM bookings b
                                   JOIN users u ON b.user_id = u.id
                                   ORDER BY b.created_at DESC";
                        $books = mysqli_query($conn, $query_book);
                        if (mysqli_num_rows($books) > 0):
                            while ($b = mysqli_fetch_assoc($books)):
                                $status_color = 'bg-pending';
                                if ($b['status'] == 'confirmed')
                                    $status_color = 'bg-confirmed';
                                if ($b['status'] == 'cancelled')
                                    $status_color = 'bg-cancelled';
                                ?>
                                <tr>
                                    <td>#<?php echo htmlspecialchars($b['booking_code']); ?></td>
                                    <td><?php echo htmlspecialchars($b['guest_name']); ?></td>
                                    <td><?php echo $b['check_in']; ?> - <?php echo $b['check_out']; ?></td>
                                    <td><span
                                            class="badge <?php echo $status_color; ?>"><?php echo strtoupper($b['status']); ?></span>
                                    </td>
                                    <td>
                                        <?php if ($b['status'] == 'pending'): ?>
                                            <a href="owner_dashboard.php?tab=bookings&action=approve&id_book=<?php echo $b['id']; ?>"
                                                style="color:green; font-weight:bold;">Terima</a> |
                                            <a href="owner_dashboard.php?tab=bookings&action=reject&id_book=<?php echo $b['id']; ?>"
                                                style="color:red; font-weight:bold;"
                                                onclick="return confirm('Tolak pesanan ini?');">Tolak</a> |
                                        <?php endif; ?>
                                        <a href="owner_booking_detail.php?id=<?php echo $b['id']; ?>"
                                            style="color:#0275d8; font-weight:bold;">View</a>
                                    </td>
                                </tr>
                            <?php endwhile; else: ?>
                            <tr>
                                <td colspan="5" style="text-align:center;">Belum ada pesanan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- REVENUE TAB -->
            <?php elseif ($active_tab == 'revenue'): ?>

                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-label">Total Revenue</div>
                        <div class="stat-value">Rp <?php echo number_format($total_revenue); ?></div>
                        <div class="stat-icon">💰</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">This Month</div>
                        <div class="stat-value">Rp <?php echo number_format($monthly_revenue); ?></div>
                        <div class="stat-icon">📅</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Today</div>
                        <div class="stat-value">Rp <?php echo number_format($today_revenue); ?></div>
                        <div class="stat-icon">☀️</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Pending Amount</div>
                        <div class="stat-value">Rp <?php echo number_format($pending_amount); ?></div>
                        <div class="stat-icon">⏳</div>
                    </div>
                </div>

                <!-- CALENDAR (CURRENT MONTH) -->
                <?php
                $monthTitle = date('F Y', strtotime($firstDayOfMonth));
                $firstTs = strtotime($firstDayOfMonth);
                $startWeekday = (int) date('w', $firstTs); // 0=Sun
                $daysInMonth = (int) date('t', $firstTs);
                ?>
                <div class="revenue-calendar-card">
                    <div class="calendar-header">
                        <div class="calendar-title"><?php echo $monthTitle; ?></div>
                        <div class="calendar-legend">
                            <span style="margin-right:6px;">Legend:</span>
                            <span class="legend-dot level-0"></span> Low / 0 &nbsp;
                            <span class="legend-dot level-1"></span> Low &nbsp;
                            <span class="legend-dot level-2"></span> Medium &nbsp;
                            <span class="legend-dot level-3"></span> High &nbsp;
                            <span class="legend-dot level-4"></span> Very High
                        </div>
                    </div>

                    <div class="calendar-weekdays">
                        <div>Su</div>
                        <div>Mo</div>
                        <div>Tu</div>
                        <div>We</div>
                        <div>Th</div>
                        <div>Fr</div>
                        <div>Sa</div>
                    </div>
                    <div class="calendar-days">
                        <?php
                        for ($i = 0; $i < $startWeekday; $i++) {
                            echo '<div class="day-cell empty"></div>';
                        }
                        for ($day = 1; $day <= $daysInMonth; $day++) {
                            $dateStr = date('Y-m-d', strtotime("$firstDayOfMonth +" . ($day - 1) . " days"));
                            $rev = $calendarRevenue[$dateStr] ?? 0;
                            $levelClass = 'level-0';
                            if ($rev > 0 && $maxDayRevenue > 0) {
                                $ratio = $rev / $maxDayRevenue;
                                if ($ratio <= 0.25)
                                    $levelClass = 'level-1';
                                elseif ($ratio <= 0.50)
                                    $levelClass = 'level-2';
                                elseif ($ratio <= 0.75)
                                    $levelClass = 'level-3';
                                else
                                    $levelClass = 'level-4';
                            }
                            echo '<div class="day-cell ' . $levelClass . '">';
                            echo '<div class="day-num">' . $day . '</div>';
                            if ($rev > 0) {
                                echo '<div class="day-amount">Rp ' . number_format($rev) . '</div>';
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>

                <!-- DATE RANGE CHART + SUMMARY -->
                <div class="analytics-row">
                    <div class="chart-card">
                        <h3>
                            <span>Revenue by Date</span>
                            <form method="get" class="date-range-form">
                                <input type="hidden" name="tab" value="revenue">
                                <span>From</span>
                                <input type="date" name="from" value="<?php echo htmlspecialchars($from_date); ?>">
                                <span>To</span>
                                <input type="date" name="to" value="<?php echo htmlspecialchars($to_date); ?>">
                                <button type="submit" class="btn-primary btn-small">Apply</button>
                            </form>
                        </h3>
                        <canvas id="rangeRevenueChart"></canvas>
                    </div>

                    <div class="revenue-card">
                        <h3>Summary</h3>
                        <ul class="revenue-list">
                            <li>
                                <span class="revenue-label">Average per Booking</span>
                                <span class="revenue-value">Rp <?php echo number_format($avg_revenue); ?></span>
                            </li>
                            <li>
                                <span class="revenue-label">Total Bookings</span>
                                <span class="revenue-value"><?php echo $total_bookings; ?></span>
                            </li>
                            <li>
                                <span class="revenue-label">Confirmed Bookings</span>
                                <span class="revenue-value"><?php echo $confirmed_bookings; ?></span>
                            </li>
                            <li>
                                <span class="revenue-label">Pending Bookings</span>
                                <span class="revenue-value"><?php echo $pending_bookings; ?></span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- DISCOUNTS TAB -->
            <?php elseif ($active_tab == 'discounts'): ?>

                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3 style="margin:0;">Daftar Diskon</h3>
                    <button onclick="window.location.href='owner_dashboard.php?tab=add_discount'" class="btn-primary">
                        <span>＋</span> Tambah Diskon
                    </button>
                </div>

                <table class="booking-table">
                    <thead>
                        <tr>
                            <th>Nama Diskon</th>
                            <th>Tipe</th>
                            <th>Nilai</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $discounts = mysqli_query($conn, "SELECT * FROM discounts ORDER BY created_at DESC");
                        if (mysqli_num_rows($discounts) > 0):
                            while ($d = mysqli_fetch_assoc($discounts)):
                                $status_badge = $d['is_active'] == 1 ? '<span class="badge bg-confirmed">ACTIVE</span>' : '<span class="badge bg-cancelled">INACTIVE</span>';
                                $value_display = $d['discount_type'] == 'percentage' ? $d['discount_value'] . '%' : 'Rp ' . number_format($d['discount_value']);
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($d['discount_name']); ?></td>
                                    <td><?php echo strtoupper($d['discount_type']); ?></td>
                                    <td><?php echo $value_display; ?></td>
                                    <td><?php echo $d['start_date']; ?> s/d <?php echo $d['end_date']; ?></td>
                                    <td><?php echo $status_badge; ?></td>
                                    <td>
                                        <a href="owner_dashboard.php?tab=add_discount&edit_discount=<?php echo $d['id']; ?>"
                                            style="color:#0275d8; font-weight:bold;">Edit</a> |
                                        <a href="owner_dashboard.php?tab=discounts&toggle_discount=<?php echo $d['id']; ?>&status=<?php echo $d['is_active']; ?>"
                                            style="color:<?php echo $d['is_active'] == 1 ? '#f0ad4e' : '#5cb85c'; ?>; font-weight:bold;">
                                            <?php echo $d['is_active'] == 1 ? 'Nonaktifkan' : 'Aktifkan'; ?>
                                        </a> |
                                        <a href="owner_dashboard.php?tab=discounts&delete_discount=<?php echo $d['id']; ?>"
                                            style="color:red; font-weight:bold;"
                                            onclick="return confirm('Hapus diskon ini?');">Hapus</a>
                                    </td>
                                </tr>
                            <?php endwhile;
                        else: ?>
                            <tr>
                                <td colspan="6" style="text-align:center;">Belum ada diskon.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

                <!-- ADD/EDIT DISCOUNT TAB -->
            <?php elseif ($active_tab == 'add_discount'): ?>

                <div class="form-panel">
                    <h3 style="margin-top:0; border-bottom:1px solid #eee; padding-bottom:10px;">
                        <?php echo $edit_discount_mode ? "Edit Diskon" : "Tambah Diskon Baru"; ?>
                    </h3>

                    <form method="POST">
                        <input type="hidden" name="id_update"
                            value="<?php echo $edit_discount_mode ? $discount_edit['id'] : ''; ?>">

                        <div class="form-group">
                            <label>Nama Diskon</label>
                            <input type="text" name="discount_name" class="form-control" required
                                value="<?php echo $edit_discount_mode ? htmlspecialchars($discount_edit['discount_name']) : ''; ?>"
                                placeholder="Contoh: Diskon Tahun Baru">
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                            <div class="form-group">
                                <label>Tipe Diskon</label>
                                <select name="discount_type" class="form-control" required>
                                    <option value="percentage" <?php echo ($edit_discount_mode && $discount_edit['discount_type'] == 'percentage') ? 'selected' : ''; ?>>Persentase (%)
                                    </option>
                                    <option value="fixed" <?php echo ($edit_discount_mode && $discount_edit['discount_type'] == 'fixed') ? 'selected' : ''; ?>>Nominal Tetap (Rp)
                                    </option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Nilai Diskon</label>
                                <input type="number" name="discount_value" class="form-control" required step="0.01" min="0"
                                    value="<?php echo $edit_discount_mode ? $discount_edit['discount_value'] : ''; ?>"
                                    placeholder="Contoh: 10 atau 50000">
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                            <div class="form-group">
                                <label>Tanggal Mulai</label>
                                <input type="date" name="start_date" class="form-control" required
                                    value="<?php echo $edit_discount_mode ? $discount_edit['start_date'] : ''; ?>">
                            </div>
                            <div class="form-group">
                                <label>Tanggal Berakhir</label>
                                <input type="date" name="end_date" class="form-control" required
                                    value="<?php echo $edit_discount_mode ? $discount_edit['end_date'] : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label style="display:flex; align-items:center; gap:8px; cursor:pointer;">
                                <input type="checkbox" name="is_active" value="1" <?php echo ($edit_discount_mode && $discount_edit['is_active'] == 1) || !$edit_discount_mode ? 'checked' : ''; ?>>
                                <span>Aktifkan diskon ini</span>
                            </label>
                        </div>

                        <button type="submit" name="save_discount" class="btn-primary"
                            style="width:100%; justify-content:center;">
                            <?php echo $edit_discount_mode ? "Simpan Perubahan" : "Simpan Diskon"; ?>
                        </button>
                        <a href="owner_dashboard.php?tab=discounts"
                            style="display:block; text-align:center; margin-top:15px; text-decoration:none; color:#777;">
                            Kembali
                        </a>
                    </form>
                </div>

            <?php endif; ?>

        </main>
    </div>

    <script>
        (function () {
            const activeTab = "<?php echo $active_tab; ?>";
            if (activeTab !== 'revenue') return;

            const labels = <?php echo json_encode($range_labels); ?>;
            const revenues = <?php echo json_encode($range_revenue); ?>;
            const bookings = <?php echo json_encode($range_bookings); ?>;

            const canvas = document.getElementById('rangeRevenueChart');
            if (!canvas || labels.length === 0) return;

            const ctx = canvas.getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Revenue (Rp)',
                            data: revenues,
                            yAxisID: 'y',
                            borderWidth: 1,
                            backgroundColor: 'rgba(177,125,0,0.7)'
                        },
                        {
                            label: 'Bookings',
                            data: bookings,
                            yAxisID: 'y1',
                            type: 'line',
                            borderWidth: 2,
                            fill: false,
                            tension: 0.3,
                            borderColor: 'rgba(0,0,0,0.8)',
                            pointRadius: 3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            position: 'left',
                            ticks: {
                                callback: function (value) {
                                    return 'Rp ' + value.toLocaleString();
                                }
                            }
                        },
                        y1: {
                            beginAtZero: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false
                            }
                        }
                    },
                    plugins: {
                        legend: { display: true }
                    }
                }
            });
        })();
    </script>

</body>

</html>