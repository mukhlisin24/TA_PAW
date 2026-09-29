<?php
session_start();
require 'koneksi.php';

$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $password = trim($_POST['password']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = 'customer';

    if (empty($name)) {
        $errors['name'] = "Nama wajib diisi!";
    } else if (strlen($name) < 3) {
        $errors['name'] = "Username minimal 3 karakter!";
    }

    if (empty($password)) {
        $errors['password'] = "Password wajib diisi!";
    } else {
        if (strlen($password) < 6) {
            $errors['password'] = "Password minimal 6 karakter!";
        }
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            $errors['password'] = "Password harus kombinasi huruf dan angka!";
        }
    }

    if (empty($email)) {
        $errors['email'] = "Email wajib diisi!";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Format email tidak valid!";
    }

    if (empty($phone)) {
        $errors['phone'] = "Nomor telepon wajib diisi!";
    } else if (!preg_match("/^[0-9]{10,15}$/", $phone)) {
        $errors['phone'] = "Nomor telepon hanya boleh angka dan panjang 10–15 digit!";
    }

    if (empty($errors)) {
        $stmtCheck = $conn->prepare("SELECT id FROM users WHERE name = ? LIMIT 1");
        $stmtCheck->bind_param("s", $name);
        $stmtCheck->execute();
        $result = $stmtCheck->get_result();

        if ($result->num_rows > 0) {
            $errors['name'] = "Username sudah digunakan!";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $hash, $role);

            if ($stmt->execute()) {
                $success = "Registrasi berhasil! Silakan login.";
                header("Refresh: 2; url=login.php");
            } else {
                $errors['general'] = "Gagal menyimpan data!";
            }
        }
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="assets/log.css">
</head>

<body>
    <div class="overlay"></div>

    <div class="login-wrapper">
        <h2>Register</h2>

        <?php if (!empty($success)): ?>
            <p style="color:green;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form method="post">
            <label>Name</label>
            <input type="text" name="name" value="<?= isset($name) ? htmlspecialchars($name) : '' ?>">
            <?php if (!empty($errors['name'])): ?>
                <span class="form-error"><?= $errors['name'] ?></span>
            <?php endif; ?>

            <label>Password</label>
            <input type="password" name="password">
            <?php if (!empty($errors['password'])): ?>
                <span class="form-error"><?= $errors['password'] ?></span>
            <?php endif; ?>

            <label>Email</label>
            <input type="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
            <?php if (!empty($errors['email'])): ?>
                <span class="form-error"><?= $errors['email'] ?></span>
            <?php endif; ?>

            <label>Phone</label>
            <input type="text" name="phone" value="<?= isset($phone) ? htmlspecialchars($phone) : '' ?>">
            <?php if (!empty($errors['phone'])): ?>
                <span class="form-error"><?= $errors['phone'] ?></span>
            <?php endif; ?>

            <button type="submit">Daftar</button>

            <p style="margin-top:10px;">
                Sudah punya akun? <a href="login.php">Login di sini</a>
            </p>
        </form>
    </div>
    </main>
</body>

</html>