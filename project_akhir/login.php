<?php
session_start();
require 'koneksi.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $password = $_POST['password'];

    if (empty($name)) {
        $errors['name'] = "Nama wajib diisi!";
    }

    if (empty($password)) {
        $errors['password'] = "Password wajib diisi!";
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT * FROM users WHERE name = ? LIMIT 1");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user) {
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];

                if (isset($_SESSION['redirect_to'])) {
                    $redir = $_SESSION['redirect_to'];
                    unset($_SESSION['redirect_to']);
                    header("Location: $redir");
                    exit;
                }

                if ($user['role'] === 'admin') {
                    header("Location: owner_dashboard.php");
                } else {
                    header("Location: customer_dashboard.php");
                }
                exit;

            } else {
                $errors['general'] = "Password salah!";
            }
        } else {
            $errors['general'] = "User tidak ditemukan!";
        }
    }
}
?>
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="assets/log.css">
</head>

<body>
    <div class="overlay"></div>

    <div class="login-wrapper">
        <h2>Login</h2>
        <?php if (!empty($errors['general'])): ?>
            <div class="form-error general-error"><?= htmlspecialchars($errors['general']) ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Name</label>
            <input type="text" name="name" value="<?= htmlspecialchars($name ?? '') ?>">
            <?php if (!empty($errors['name'])): ?>
                <span class="form-error"><?= $errors['name'] ?></span>
            <?php endif; ?>

            <label>Password</label>
            <input type="password" name="password">
            <?php if (!empty($errors['password'])): ?>
                <span class="form-error"><?= $errors['password'] ?></span>
            <?php endif; ?>

            <button type="submit">Login</button>

            <p style="margin-top:10px;">
                Belum punya akun?
                <a href="register.php">Register</a>
            </p>
        </form>
    </div>

</body>

</html>