<?php
require __DIR__ . '/../config/database.php';

// Check if request is POST and ID is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

    // Check if admin exists
    $query = "SELECT * FROM admin WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            // Admin exists, now soft delete it
            $updateQuery = "UPDATE admin SET deleted_at = NOW() WHERE id = ?";
            $updateStmt = mysqli_prepare($connection, $updateQuery);

            if ($updateStmt) {
                mysqli_stmt_bind_param($updateStmt, 'i', $id);
                $updateSuccess = mysqli_stmt_execute($updateStmt);

                if ($updateSuccess) {
                    $_SESSION['delete-admin-success'] = "Admin deleted successfully!";
                    header('Location: ' . ROOT_URL . 'admin/admin.php');
                    exit();
                } else {
                    $_SESSION['delete-admin'] = "Error marking admin as deleted: " . mysqli_error($connection);
                }
                mysqli_stmt_close($updateStmt);
            }
        } else {
            $_SESSION['delete-admin'] = "Admin not found.";
        }
        mysqli_stmt_close($stmt);
    }

    // Redirect back if there was an error
    if (isset($_SESSION['delete-admin'])) {
        header('Location: ' . ROOT_URL . 'admin/admin.php');
        exit();
    }
    
} else {
    // Redirect to admin management page
    header('Location: ' . ROOT_URL . 'admin/admin.php');
    exit();
}