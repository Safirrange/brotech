<?php
require __DIR__ . '/../../../config/database.php';
require __DIR__ . '/../../../Helper/admin-activity-logger.php';

// Check if request is POST and ID is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type_id'])) {
    $id = filter_var($_POST['type_id'], FILTER_SANITIZE_NUMBER_INT);
    $brand_id = filter_var($_POST['brand_id'], FILTER_SANITIZE_NUMBER_INT);
    $category_id = filter_var($_POST['category_id'], FILTER_SANITIZE_NUMBER_INT);

    // Check if category exists
    $query = "SELECT * FROM guide_types WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            $row = mysqli_fetch_assoc($result); // Fetch the row

            // Category exists, now soft delete it
            $updateQuery = "UPDATE guide_types SET deleted_at = NOW() WHERE id = ?";
            $updateStmt = mysqli_prepare($connection, $updateQuery);

            if ($updateStmt) {
                mysqli_stmt_bind_param($updateStmt, 'i', $id);
                $updateSuccess = mysqli_stmt_execute($updateStmt);

                if ($updateSuccess) {
                    // Log the activity
                    $admin_id = $_SESSION['admin-id'];
                    $activity_type = 'DELETE';
                    $description = "Deleted " . $row['name']; // Use fetched name
                    $entity_type = 'guide_type';
                    log_admin_activity($connection, $admin_id, $activity_type, $description, $entity_type, $id);

                    $_SESSION['delete-type-success'] = "Type deleted successfully!";
                    header('Location: ' . ROOT_URL . 'admin/Manage-content/Guides/type.php?brand_id=' . $brand_id . '&category_id=' . $category_id);
                    exit();
                } else {
                    $_SESSION['delete-type-error'] = "Error marking type as deleted: " . mysqli_error($connection);
                }
                mysqli_stmt_close($updateStmt);
            }
        } else {
            $_SESSION['delete-type-error'] = "Type not found.";
        }
        mysqli_stmt_close($stmt);
    }

    // Redirect back if there was an error
    if (isset($_SESSION['delete-type-error'])) {
        header('Location: ' . ROOT_URL . 'admin/Manage-content/Guides/type.php?brand_id=' . $brand_id . '&category_id=' . $category_id);
        exit();
    }
} else {
    // Redirect to category management page
    header('Location: ' . ROOT_URL . 'admin/Manage-content/Guides/type.php?brand_id=' . $brand_id . '&category_id=' . $category_id);
    exit();
}
