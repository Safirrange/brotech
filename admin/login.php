<?php
require __DIR__ . '/../config/database.php';

// Get back form data if there was a registration error
$admin = $_SESSION['admin-login-data']['user'] ?? null;
$pass = $_SESSION['admin-login-data']['pass'] ?? null;

// Delete signup session
unset($_SESSION['admin-login-data']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BROTECH - Admin Login</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/admin-login.css">
</head>

<body>
    <div class="login-container">
        <h2>BROTECH - Admin Login</h2>
        <?php if (isset($_SESSION['admin-login'])): ?>
            <div class="alert-message">
                <p>
                    <?= $_SESSION['admin-login'];
                    unset($_SESSION['admin-login']);
                    ?>
                </p>
            </div>
        <?php endif ?>
        <form action="<?= ROOT_URL ?>admin/admin-login-logic.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="user" value="<?= $admin ?>" placeholder="Username">
            <input type="password" name="pass" value="<?= $pass ?>" placeholder="Password">
            <button name = "submit" type="submit">Login</button>
        </form>
    </div>
</body>

</html>