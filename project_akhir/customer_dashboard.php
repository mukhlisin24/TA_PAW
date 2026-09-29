<?php
// include "header.php";
require 'koneksi.php';
session_start();

/* ========== AUTH CHECK ========== */
if (!isset($_SESSION['role'])) {
    echo "<script>window.location='login.php';</script>";
    exit;
}
if ($_SESSION['role'] !== 'customer') {
    // if someone tries to access but not a customer, send to owner or login
    echo "<script>window.location='owner_dashboard.php';</script>";
    exit;
}

// Get logged in user id
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    // fallback: try to find by email, if you store it in session
    if (isset($_SESSION['email'])) {
        $email = mysqli_real_escape_string($conn, $_SESSION['email']);
        $u = mysqli_query($conn, "SELECT id FROM users WHERE email='$email' LIMIT 1");
        if ($u && mysqli_num_rows($u) > 0) {
            $row_u = mysqli_fetch_assoc($u);
            $user_id = $row_u['id'];
            $_SESSION['user_id'] = $user_id;
        }
    }
    if (!$user_id) {
        echo "<script>window.location='login.php';</script>";
        exit;
    }
}

/* ========== HANDLE CANCEL BOOKING ========== */
$flash_message = "";
$flash_type = ""; // success / error

// Check for booking success message from session
if (isset($_SESSION['booking_success'])) {
    $flash_message = $_SESSION['booking_success'];
    $flash_type = "success";
    unset($_SESSION['booking_success']);
}

if (isset($_GET['cancel_booking'])) {
    $cancel_id = (int) $_GET['cancel_booking'];

    // only allow cancel if this booking belongs to this user and is pending
    $check = mysqli_query($conn, "SELECT * FROM bookings WHERE id='$cancel_id' AND user_id='$user_id' AND status='pending'");
    if ($check && mysqli_num_rows($check) > 0) {
        mysqli_query($conn, "UPDATE bookings SET status='cancelled' WHERE id='$cancel_id'");
        $flash_message = "Booking berhasil dibatalkan.";
        $flash_type = "success";
    } else {
        $flash_message = "Booking tidak dapat dibatalkan.";
        $flash_type = "error";
    }
}

/* ========== HANDLE PROFILE UPDATE ========== */
if (isset($_POST['save_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);

    $q = "UPDATE users SET name='$name', phone='$phone' WHERE id='$user_id'";
    if (mysqli_query($conn, $q)) {
        $_SESSION['name'] = $name;
        $flash_message = "Profil berhasil diperbarui.";
        $flash_type = "success";
    } else {
        $flash_message = "Gagal memperbarui profil: " . mysqli_error($conn);
        $flash_type = "error";
    }
}

/* ========== HANDLE PASSWORD CHANGE ========== */
if (isset($_POST['change_password'])) {
    $current_pass = mysqli_real_escape_string($conn, $_POST['current_password']);
    $new_pass = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_pass = mysqli_real_escape_string($conn, $_POST['confirm_password']);

    if ($new_pass !== $confirm_pass) {
        $flash_message = "Konfirmasi password baru tidak sama.";
        $flash_type = "error";
    } else {
        // NOTE: this assumes password stored in plain text (like your current setup).
        $res_user = mysqli_query($conn, "SELECT password FROM users WHERE id='$user_id'");
        $data_user = mysqli_fetch_assoc($res_user);

        if ($data_user) {
            if (password_verify($current_pass, $data_user['password'])) {

                $hashed_new_password = password_hash($new_pass, PASSWORD_DEFAULT);

                $q = "UPDATE users SET password='$hashed_new_password' WHERE id='$user_id'";

                if (mysqli_query($conn, $q)) {
                    $flash_message = "Password berhasil diubah.";
                    $flash_type = "success";
                } else {
                    $flash_message = "Gagal mengubah password: " . mysqli_error($conn);
                    $flash_type = "error";
                }

            } else {
                $flash_message = "Password saat ini salah.";
                $flash_type = "error";
            }
        }
    }
}

/* ========== DASHBOARD DATA ========== */
$owner_name = $_SESSION['name'] ?? "Customer";
$owner_email = $_SESSION['email'] ?? "user@example.com";
$initials = strtoupper(substr($owner_name, 0, 1));

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'overview';

// Stats
$total_bookings_q = mysqli_query($conn, "SELECT COUNT(*) AS c FROM bookings WHERE user_id='$user_id'");
$total_bookings = mysqli_fetch_assoc($total_bookings_q)['c'] ?? 0;

$upcoming_q = mysqli_query($conn, "
    SELECT COUNT(*) AS c 
    FROM bookings 
    WHERE user_id='$user_id' 
      AND check_in >= CURDATE()
      AND status IN ('pending','confirmed')
");
$upcoming_bookings = mysqli_fetch_assoc($upcoming_q)['c'] ?? 0;

$completed_q = mysqli_query($conn, "
    SELECT COUNT(*) AS c 
    FROM bookings 
    WHERE user_id='$user_id' 
      AND check_out < CURDATE()
      AND status = 'confirmed'
");
$completed_bookings = mysqli_fetch_assoc($completed_q)['c'] ?? 0;

$cancelled_q = mysqli_query($conn, "
    SELECT COUNT(*) AS c 
    FROM bookings 
    WHERE user_id='$user_id' 
      AND status = 'cancelled'
");
$cancelled_bookings = mysqli_fetch_assoc($cancelled_q)['c'] ?? 0;

// Upcoming bookings list
$upcoming_list = mysqli_query($conn, "
    SELECT 
        b.*, 
        GROUP_CONCAT(r.title SEPARATOR ', ') AS room_title,
        GROUP_CONCAT(r.city SEPARATOR ', ') AS city
    FROM bookings b
    LEFT JOIN booking_rooms br ON br.booking_id = b.id
    LEFT JOIN rooms r ON r.id = br.room_id
    WHERE b.user_id='$user_id'
      AND b.check_in >= CURDATE()
      AND b.status IN ('pending', 'confirmed')
    GROUP BY b.id
    ORDER BY b.check_in ASC
    LIMIT 6
");


// Past bookings - show all completed or past bookings
$past_list = mysqli_query($conn, "
    SELECT 
        b.*, 
        GROUP_CONCAT(r.title SEPARATOR ', ') AS room_title,
        GROUP_CONCAT(r.city SEPARATOR ', ') AS city
    FROM bookings b
    LEFT JOIN booking_rooms br ON br.booking_id = b.id
    LEFT JOIN rooms r ON r.id = br.room_id
    WHERE b.user_id='$user_id'
      AND (b.check_out < CURDATE() OR b.status IN ('cancelled', 'completed'))
    GROUP BY b.id
    ORDER BY b.check_in DESC
    LIMIT 6
");

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Customer Dashboard</title>
    <link rel="stylesheet" href="./assets/style.css">
    <link rel="stylesheet" href="assets/customer_panel.css">

</head>

<body>

    <div class="admin-layout">
        <!-- SIDEBAR -->
        <aside class="admin-sidebar">
            <div class="admin-profile">
                <div class="admin-avatar"><?php echo $initials; ?></div>
                <div class="admin-name"><?php echo htmlspecialchars($owner_name); ?></div>
                <div class="admin-role">Customer</div>
                <div class="admin-email"><?php echo htmlspecialchars($owner_email); ?></div>
            </div>

            <div class="sidebar-section-title">Menu</div>
            <ul class="sidebar-menu">
                <li>
                    <a href="customer_dashboard.php?tab=overview"
                        class="<?php echo ($active_tab == 'overview') ? 'active' : ''; ?>">
                        <span class="icon">🏠</span> Dashboard
                    </a>
                </li>
                <li>
                    <a href="customer_dashboard.php?tab=bookings"
                        class="<?php echo ($active_tab == 'bookings') ? 'active' : ''; ?>">
                        <span class="icon">📅</span> My Bookings
                    </a>
                </li>
                <li>
                    <a href="customer_dashboard.php?tab=profile"
                        class="<?php echo ($active_tab == 'profile') ? 'active' : ''; ?>">
                        <span class="icon">👤</span> Profile
                    </a>
                </li>
                <li>
                    <a href="customer_dashboard.php?tab=password"
                        class="<?php echo ($active_tab == 'password') ? 'active' : ''; ?>">
                        <span class="icon">🔑</span> Change Password
                    </a>
                </li>
                <li>
                    <a href="index.php">
                        <span class="icon">🌍</span> Front Website
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <span class="icon">🚪</span> Logout
                    </a>
                </li>
            </ul>

            <div class="sidebar-footer">
                Hotel Customer Panel<br>
                <span style="opacity:.8;">Enjoy your stay ✨</span>
            </div>
        </aside>

        <!-- MAIN -->
        <main class="admin-main">
            <div class="admin-header-top">
                <h2><span class="icon">👋</span> Customer Dashboard</h2>
                <div class="breadcrumb">
                    <?php
                    if ($active_tab == 'overview')
                        echo "Dashboard / Customer";
                    elseif ($active_tab == 'bookings')
                        echo "My Bookings / Customer";
                    elseif ($active_tab == 'profile')
                        echo "Profile / Customer";
                    else
                        echo "Change Password / Customer";
                    ?>
                </div>
            </div>

            <?php if ($flash_message): ?>
                <div class="alert <?php echo $flash_type == 'success' ? 'alert-success' : 'alert-error'; ?>">
                    <?php echo $flash_message; ?>
                </div>
            <?php endif; ?>

            <!-- STATS FOR ALL TABS -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-label">Total Bookings</div>
                    <div class="stat-value"><?php echo $total_bookings; ?></div>
                    <div class="stat-icon">📦</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Upcoming</div>
                    <div class="stat-value"><?php echo $upcoming_bookings; ?></div>
                    <div class="stat-icon">⏳</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Completed</div>
                    <div class="stat-value"><?php echo $completed_bookings; ?></div>
                    <div class="stat-icon">✅</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Cancelled</div>
                    <div class="stat-value"><?php echo $cancelled_bookings; ?></div>
                    <div class="stat-icon">❌</div>
                </div>
            </div>

            <?php if ($active_tab == 'overview'): ?>

                <!-- UPCOMING BOOKINGS -->
                <div class="card-panel">
                    <h3>Upcoming Bookings</h3>
                    <?php if (mysqli_num_rows($upcoming_list) > 0): ?>
                        <?php while ($b = mysqli_fetch_assoc($upcoming_list)): ?>
                            <?php
                            $status_color = 'bg-pending';
                            if ($b['status'] == 'confirmed')
                                $status_color = 'bg-confirmed';
                            if ($b['status'] == 'cancelled')
                                $status_color = 'bg-cancelled';
                            ?>
                            <div class="booking-item">
                                <div class="booking-main">
                                    <strong><?php echo htmlspecialchars($b['room_title'] ?? 'Room'); ?></strong>
                                    <div><?php echo $b['check_in']; ?> → <?php echo $b['check_out']; ?></div>
                                    <div>
                                        <span class="badge <?php echo $status_color; ?>">
                                            <?php echo strtoupper($b['status']); ?>
                                        </span>
                                        &nbsp; | Booking: #<?php echo $b['id']; ?>
                                    </div>
                                </div>
                                <div class="booking-actions">
                                    <a href="booking_detail.php?booking_id=<?php echo $b['id']; ?>" class="btn-view"
                                        style="color:#0275d8; text-decoration:none; font-weight:600;">
                                        View
                                    </a>



                                    <?php if ($b['status'] == 'pending'): ?>
                                        <a href="customer_dashboard.php?tab=overview&cancel_booking=<?php echo $b['id']; ?>"
                                            style="color:#d9534f;" onclick="return confirm('Batalkan booking ini?');">
                                            Cancel
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Belum ada booking yang akan datang.</p>
                    <?php endif; ?>
                </div>

                <!-- PAST BOOKINGS -->
                <div class="card-panel">
                    <h3>Past Bookings</h3>
                    <?php if (mysqli_num_rows($past_list) > 0): ?>
                        <?php while ($b = mysqli_fetch_assoc($past_list)): ?>
                            <?php
                            $status_color = 'bg-pending';
                            if ($b['status'] == 'confirmed')
                                $status_color = 'bg-confirmed';
                            if ($b['status'] == 'cancelled')
                                $status_color = 'bg-cancelled';
                            ?>
                            <div class="booking-item">
                                <div class="booking-main">
                                    <strong><?php echo htmlspecialchars($b['room_title'] ?? 'Room'); ?></strong>
                                    <div><?php echo $b['check_in']; ?> → <?php echo $b['check_out']; ?></div>
                                    <div>
                                        <span class="badge <?php echo $status_color; ?>">
                                            <?php echo strtoupper($b['status']); ?>
                                        </span>
                                        &nbsp; | Booking: #<?php echo $b['id']; ?>
                                    </div>
                                </div>
                                <div class="booking-actions">
                                    <a href="booking_detail.php?id=<?php echo $b['id']; ?>">View</a>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Belum ada riwayat booking.</p>
                    <?php endif; ?>
                </div>

            <?php elseif ($active_tab == 'bookings'): ?>

                <!-- FULL BOOKINGS LIST -->
                <div class="card-panel">
                    <h3>All My Bookings</h3>
                    <?php
                    $all_bookings = mysqli_query($conn, "
                        SELECT
                            b.*,
                            (
                                SELECT r.title
                                FROM booking_rooms br2
                                LEFT JOIN rooms r ON r.id = br2.room_id
                                WHERE br2.booking_id = b.id
                                LIMIT 1
                            ) AS room_title,
                            (
                                SELECT r.city
                                FROM booking_rooms br2
                                LEFT JOIN rooms r ON r.id = br2.room_id
                                WHERE br2.booking_id = b.id
                                LIMIT 1
                            ) AS city
                        FROM bookings b
                        WHERE b.user_id='$user_id'
                        ORDER BY b.created_at DESC
                    ");
                    if (mysqli_num_rows($all_bookings) > 0):
                        while ($b = mysqli_fetch_assoc($all_bookings)):
                            $status_color = 'bg-pending';
                            if ($b['status'] == 'confirmed')
                                $status_color = 'bg-confirmed';
                            if ($b['status'] == 'cancelled')
                                $status_color = 'bg-cancelled';
                            ?>
                            <div class="booking-item">
                                <div class="booking-main">
                                    <strong><?php echo htmlspecialchars($b['room_title'] ?? 'Room'); ?></strong>
                                    <div><?php echo $b['check_in']; ?> → <?php echo $b['check_out']; ?></div>
                                    <div>
                                        <span class="badge <?php echo $status_color; ?>">
                                            <?php echo strtoupper($b['status']); ?>
                                        </span>
                                        &nbsp; | Booking: #<?php echo $b['id']; ?>
                                        &nbsp; | Total: Rp <?php echo number_format($b['total_amount']); ?>
                                    </div>
                                </div>
                                <div class="booking-actions">
                                    <a href="booking_detail.php?id=<?php echo $b['id']; ?>">View</a>
                                    <?php if ($b['status'] == 'pending'): ?>
                                        <a href="customer_dashboard.php?tab=bookings&cancel_booking=<?php echo $b['id']; ?>"
                                            style="color:#d9534f;" onclick="return confirm('Batalkan booking ini?');">
                                            Cancel
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php
                        endwhile;
                    else:
                        ?>
                        <p>Belum ada booking.</p>
                    <?php endif; ?>
                </div>

            <?php elseif ($active_tab == 'profile'): ?>

                <!-- PROFILE FORM -->
                <?php
                $user_res = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
                $user = mysqli_fetch_assoc($user_res);
                ?>
                <div class="form-panel">
                    <h3>Profile</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Nama Lengkap</label>
                            <input type="text" name="name" class="form-control"
                                value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Email (tidak bisa diubah)</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>"
                                disabled>
                        </div>
                        <div class="form-group">
                            <label>No. Telepon</label>
                            <input type="text" name="phone" class="form-control"
                                value="<?php echo htmlspecialchars($user['phone']); ?>">
                        </div>

                        <button type="submit" name="save_profile" class="btn-primary">
                            Simpan Profil
                        </button>
                    </form>
                </div>

            <?php elseif ($active_tab == 'password'): ?>

                <!-- CHANGE PASSWORD FORM -->
                <div class="form-panel">
                    <h3>Change Password</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label>Password Saat Ini</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Password Baru</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Konfirmasi Password Baru</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>

                        <button type="submit" name="change_password" class="btn-primary">
                            Ubah Password
                        </button>
                    </form>
                </div>

            <?php endif; ?>

        </main>
    </div>

</body>

</html>