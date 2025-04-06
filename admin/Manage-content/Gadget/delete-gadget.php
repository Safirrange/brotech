<?php
require __DIR__ . '/../../../config/database.php';
require __DIR__ . '/../../../Helper/admin-activity-logger.php';

// Check if request is POST and ID is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

    // Check if category exists
    $query = "SELECT * FROM gadget_category WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            // Category exists, now soft delete it
            $updateQuery = "UPDATE gadget_category SET deleted_at = NOW() WHERE id = ?";
            $updateStmt = mysqli_prepare($connection, $updateQuery);

            if ($updateStmt) {
                mysqli_stmt_bind_param($updateStmt, 'i', $id);
                $updateSuccess = mysqli_stmt_execute($updateStmt);

                if ($updateSuccess) {
                    // Log admin activity
                    log_admin_activity(
                        $connection,
                        $_SESSION['admin-id'],
                        'DELETE_GADGET_CATEGORY',
                        "Deleted gadget category ID: $id",
                        'gadget_category',
                        $id
                    );

                    $_SESSION['delete-gadget-category-success'] = "Category deleted successfully!";
                    header('Location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget.php');
                    exit();
                } else {
                    $_SESSION['delete-gadget-category'] = "Error marking category as deleted: " . mysqli_error($connection);
                }
                mysqli_stmt_close($updateStmt);
            }
        } else {
            $_SESSION['delete-gadget-category'] = "Category not found.";
        }
        mysqli_stmt_close($stmt);
    }

    // Redirect back if there was an error
    if (isset($_SESSION['delete-gadget-category'])) {
        header('Location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget.php');
        exit();
    }
    
} else {
    // Redirect to category management page
    header('Location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget.php');
    exit();
}
