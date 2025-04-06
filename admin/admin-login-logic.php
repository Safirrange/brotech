<?php
require __DIR__ . '/../config/database.php';

if (isset($_POST['submit'])) {
    // Get form data
    $admin = filter_var($_POST['user'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $pass = filter_var($_POST['pass'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validations
    if (!$admin) {
        $_SESSION['admin-login'] = "Username required";
    } elseif (!$pass) {
        $_SESSION['admin-login'] = "Password required";
    } else {
        // Fetch user from the database securely using prepared statements
        // Add check for deleted_at being NULL (not deleted accounts)
        $fetchAdminQuery = "SELECT * FROM admin WHERE user = ? AND deleted_at IS NULL";
        $stmt = mysqli_prepare($connection, $fetchAdminQuery);
        mysqli_stmt_bind_param($stmt, 's', $admin);
        mysqli_stmt_execute($stmt);
        $fetchAdminResult = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($fetchAdminResult) == 1) {
            // Convert the record into the assoc array
            $adminRecord = mysqli_fetch_assoc($fetchAdminResult);
            $dbPass = $adminRecord['pass'];

            // Compare form password with database password
            if (password_verify($pass, $dbPass)) {
                // Set session for access control
                $_SESSION['admin-id'] = $adminRecord['id'];
                // Set session if user is an admin
                header('location: ' . ROOT_URL . 'admin/admin-dashboard.php');
                die();
            } else {
                // If username/email doesn't match with the password
                $_SESSION['admin-login'] = "Invalid user or password";
            }
        } else {
            // Check if admin exists but is deleted
            $checkDeletedAdmin = "SELECT deleted_at FROM admin WHERE user = ? AND deleted_at IS NOT NULL";
            $stmtDeleted = mysqli_prepare($connection, $checkDeletedAdmin);
            mysqli_stmt_bind_param($stmtDeleted, 's', $admin);
            mysqli_stmt_execute($stmtDeleted);
            $deletedResult = mysqli_stmt_get_result($stmtDeleted);
            
            if (mysqli_num_rows($deletedResult) >= 1) {
                $_SESSION['admin-login'] = "This account has been deactivated";
            } else {
                $_SESSION['admin-login'] = "Admin not found!";
            }
            mysqli_stmt_close($stmtDeleted);
        }
        mysqli_stmt_close($stmt);
    }

    // If any problem, redirect back to the signin page with login data
    if (isset($_SESSION['admin-login'])) {
        $_SESSION['admin-login-data'] = $_POST;
        header('Location: ' . ROOT_URL . 'admin/login.php');
        die();
    }

} else {
    // If button wasn't clicked then bounce back to the signin page
    header('Location: ' . ROOT_URL . 'admin/login.php');
    die();
}
?>
