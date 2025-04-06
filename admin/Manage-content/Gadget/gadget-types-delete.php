<?php
require __DIR__ . '/../../../config/database.php';
require __DIR__ . '/../../../Helper/admin-activity-logger.php';

// Check if request is POST and ID is set
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $gadget_category_id = filter_var($_POST['gadget_category_id'], FILTER_SANITIZE_NUMBER_INT);

    // Check if category exists
    $query = "SELECT * FROM gadget_types WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            // Category exists, now soft delete it
            $updateQuery = "UPDATE gadget_types SET deleted_at = NOW() WHERE id = ?";
            $updateStmt = mysqli_prepare($connection, $updateQuery);

            if ($updateStmt) {
                mysqli_stmt_bind_param($updateStmt, 'i', $id);
                $updateSuccess = mysqli_stmt_execute($updateStmt);

                if ($updateSuccess) {
                    // Get gadget type name for logging
                    $type_query = "SELECT type_name FROM gadget_types WHERE id = ?";
                    $type_stmt = mysqli_prepare($connection, $type_query);
                    mysqli_stmt_bind_param($type_stmt, 'i', $id);
                    mysqli_stmt_execute($type_stmt);
                    $type_result = mysqli_stmt_get_result($type_stmt);
                    $type_data = mysqli_fetch_assoc($type_result);
                    $type_name = $type_data['type_name'];
                    mysqli_stmt_close($type_stmt);

                    // Log admin activity
                    log_admin_activity(
                        $connection,
                        $_SESSION['admin-id'],
                        'DELETE_GADGET_TYPE',
                        "Deleted gadget type: $type_name",
                        'gadget_types',
                        $id
                    );

                    $_SESSION['delete-gadget-types-success'] = "Gadget type deleted successfully!";
                    header('Location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget-types.php?gadget_category_id=' . $gadget_category_id);
                    exit();
                } else {
                    $_SESSION['delete-gadget-types'] = "Error marking gadget type as deleted: " . mysqli_error($connection);
                }
                mysqli_stmt_close($updateStmt);
            }
        } else {
            $_SESSION['delete-gadget-types'] = "Gadget type not found.";
        }
        mysqli_stmt_close($stmt);
    }

    // Redirect back if there was an error
    if (isset($_SESSION['delete-gadget-types'])) {
        header('Location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget-types.php?gadget_category_id=' . $gadget_category_id);
        exit();
    }
} else {
    // Redirect to category management page
    header('Location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget-types.php?gadget_category_id=' . $gadget_category_id);
    exit();
}
