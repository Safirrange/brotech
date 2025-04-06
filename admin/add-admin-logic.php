<?php
require __DIR__ . '/../config/database.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
require '../PHPMailer/src/Exception.php';

// get add-admin form data if add-admin button was clicked
if (isset($_POST['submit'])) {

    $user = filter_var($_POST['user'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $createPassword = filter_var($_POST['createPassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $confirmPassword = filter_var($_POST['confirmPassword'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $full_name = filter_var($_POST['full_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
    $phone = filter_var($_POST['phone'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $avatar = $_FILES['avatar'];

    //validate input values
    if (!$user) {
        $_SESSION['add-admin'] = "Please enter your User";
    } elseif (!$full_name) {
        $_SESSION['add-admin'] = "Please enter your Full Name";
    } elseif (!$phone) {
        $_SESSION['add-admin'] = "Please enter your Phone Number";
    } elseif (!$email) {
        $_SESSION['add-admin'] = "Please enter a valid Email";
    } elseif (!$createPassword) {
        $_SESSION['add-admin'] = "Please create a password";
    } elseif (!$confirmPassword) {
        $_SESSION['add-admin'] = "Please confirm you password";
    } elseif (strlen($createPassword) < 8) {
        $_SESSION['add-admin'] = "Password should be 8+ characters";
    } elseif (strlen($confirmPassword) > 20) {
        $_SESSION['add-admin'] = "Password should not be more than 20 characters";
    } elseif (strlen($confirmPassword) < 8) {
        $_SESSION['add-admin'] = "Please confirm your password";
    } else {

        // Check if admin exists (including soft-deleted ones)
        $checkAdminQuery = "SELECT id, deleted_at FROM admin WHERE user = ? OR email = ?";
        $stmt = mysqli_prepare($connection, $checkAdminQuery);
        mysqli_stmt_bind_param($stmt, 'ss', $user, $email);
        mysqli_stmt_execute($stmt);
        $checkAdminResult = mysqli_stmt_get_result($stmt);
        $existingAdmin = mysqli_fetch_assoc($checkAdminResult);

        if ($existingAdmin) {
            if (!is_null($existingAdmin['deleted_at'])) {
                // Restore the soft-deleted admin
                $restoreQuery = "UPDATE admin SET 
                    deleted_at = NULL,
                    pass = ?,
                    full_name = ?,
                    email = ?,
                    phone = ?,
                    avatar = ?
                    WHERE id = ?";
                $restoreStmt = mysqli_prepare($connection, $restoreQuery);
                mysqli_stmt_bind_param($restoreStmt, 'sssssi', 
                    $hashedPassword, 
                    $full_name, 
                    $email, 
                    $phone, 
                    $avatarName,
                    $existingAdmin['id']
                );
                $restoreResult = mysqli_stmt_execute($restoreStmt);

                if ($restoreResult) {
                    // Handle avatar upload for restored admin
                    if (move_uploaded_file($avatarTmpName, $avatarDestinationPath)) {
                        $_SESSION['add-admin-success'] = "Admin account has been restored!";
                        header('Location: ' . ROOT_URL . 'admin/admin.php');
                        exit();
                    } else {
                        $_SESSION['add-admin'] = "Error uploading avatar.";
                    }
                } else {
                    $_SESSION['add-admin'] = "Error restoring admin account.";
                }
                mysqli_stmt_close($restoreStmt);
            } else {
                $_SESSION['add-admin'] = "An admin with this username or email already exists!";
            }
            mysqli_stmt_close($stmt);
        } else {
            // Continue with new admin creation
            // check if passwords don't match
            if ($createPassword !== $confirmPassword) {
                $_SESSION['add-admin'] = "Passwords don't match";
            } elseif (!$avatar['name']) {
                $_SESSION['add-admin'] = "Please add avatar";
            } else {
                //hash password
                $hashedPassword = password_hash($createPassword, PASSWORD_DEFAULT);

                // Work on Avatar
                $time = time(); // make each image name unique using current timestamp
                $avatarName = $time . $avatar['name'];
                $avatarTmpName = $avatar['tmp_name'];
                $avatarDestinationPath = '../img/users/' . $avatarName;

                //make sure file is an image
                $allowedFiles = ['png', 'jpg', 'jpeg'];
                $extention = explode('.', $avatarName);
                $extention = end($extention);

                if (in_array($extention, $allowedFiles)) {
                    //make sure image is not too large (2mb+)
                    if ($avatar['size'] < 2000000) {
                        //upload avatar
                        move_uploaded_file($avatarTmpName, $avatarDestinationPath);
                    } else {
                        $_SESSION['add-admin'] = "File size too big. Should be less than 2mb";
                    }
                } else {
                    $_SESSION['add-admin'] = "File should be png, jpg, or jpeg";
                }

                // insert new admin into the database
                $insertUserQuery = "INSERT INTO admin (user, pass, full_name, email, phone, avatar, date_added) 
                VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)";
                $stmt = mysqli_prepare($connection, $insertUserQuery);
                mysqli_stmt_bind_param($stmt, 'ssssss', $user, $hashedPassword, $full_name, $email, $phone, $avatarName);
                $insertUserResult = mysqli_stmt_execute($stmt);
                
                if ($insertUserResult) {
                    $_SESSION['add-admin-success'] = "New admin added successfully!";
                    header('Location: ' . ROOT_URL . 'admin/admin.php');
                    die();
                } else {
                    $_SESSION['add-admin'] = "Database error: " . mysqli_error($connection);
                }
            }
        }
    }

    //redirect back to add-admin page if there was any problem
    if (isset($_SESSION['add-admin'])) {
        // pass form data back to add-admin page
        $_SESSION['add-admin-data'] = $_POST;
        header('Location: ' . ROOT_URL . 'admin/add-admin.php');
        die();
    }
} else {
    // if button wasn't clicked then bounce back to the add-admin page
    header('Location: ' . ROOT_URL . 'admin/add-admin.php');
    die();
}
