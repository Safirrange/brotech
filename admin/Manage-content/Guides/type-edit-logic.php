<?php
require __DIR__ . '/../../../config/database.php';
require __DIR__ . '/../../../Helper/admin-activity-logger.php';

if (isset($_POST['submit'])) {
    // Filtering user input data before validation
    $id = filter_var($_POST['type_id'], FILTER_SANITIZE_NUMBER_INT);
    $category_id = filter_var($_POST['category_id'], FILTER_SANITIZE_NUMBER_INT);
    $brand_id = filter_var($_POST['brand_id'], FILTER_SANITIZE_NUMBER_INT);
    $type = filter_var($_POST['type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $released_date = filter_var($_POST['released_date'], FILTER_VALIDATE_INT);
    $image = $_FILES['image'];

    // Validation
    if (!$type) {
        $_SESSION['edit-type-error'] = "Please input a Type";
    } elseif (!$released_date || $released_date > date('Y')) {
        $_SESSION['edit-type-error'] = "Please enter a valid Year Created";
    } elseif ($released_date < 2000) {
        $_SESSION['edit-type-error'] = "Please enter a Year after 2000";
    } else {

        // Check if the category name exists in any deleted record
        $checkDeletedQuery = "SELECT deleted_at FROM guide_types WHERE name COLLATE utf8mb4_general_ci = ? AND deleted_at IS NOT NULL";
        $stmt = mysqli_prepare($connection, $checkDeletedQuery);
        mysqli_stmt_bind_param($stmt, 's', $type);
        mysqli_stmt_execute($stmt);
        $deletedResult = mysqli_stmt_get_result($stmt);
        $deletedData = mysqli_fetch_assoc($deletedResult);
        mysqli_stmt_close($stmt);

        if ($deletedData) {
            $_SESSION['edit-type-error'] = "Cannot use this brand name. It was deleted on " . date('F j, Y, g:i a', strtotime($deletedData['deleted_at']));
        } else {
            // Check if category title already exists (case-insensitive)
            $checkTypeQuery = "SELECT * FROM guide_types WHERE name COLLATE utf8mb4_general_ci = ? AND id != ?";
            $stmt = mysqli_prepare($connection, $checkTypeQuery);
            mysqli_stmt_bind_param($stmt, 'si', $type, $id);
            mysqli_stmt_execute($stmt);
            $checkTypeResult = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($checkTypeResult) > 0) {
                $_SESSION['edit-type-error'] = "Type already exists";
            } else {
                // Fetch the current image to delete later if updated
                $query = "SELECT image FROM guide_types WHERE id = ? LIMIT 1";
                $stmt = mysqli_prepare($connection, $query);
                mysqli_stmt_bind_param($stmt, 'i', $id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $currentData = mysqli_fetch_assoc($result);
                $currentImage = $currentData['image'];
                mysqli_stmt_close($stmt);

                // Image Handling Logic
                if ($image['name']) { // If a new image is uploaded
                    $time = time(); // Unique file name
                    $imageName = $time . '_' . $image['name'];
                    $imageTmpName = $image['tmp_name'];
                    $imageDestinationPath = $_SERVER['DOCUMENT_ROOT'] . '/System/img/guides/' . $imageName;

                    // Make sure the file is an image
                    $allowedFiles = ['png', 'jpg', 'jpeg'];
                    $extension = pathinfo($imageName, PATHINFO_EXTENSION);

                    if (in_array($extension, $allowedFiles)) {
                        // Check file size
                        if ($image['size'] < 3_000_000) {
                            // Move new image to folder
                            move_uploaded_file($imageTmpName, $imageDestinationPath);

                            // Delete old image if exists
                            if ($currentImage && file_exists($_SERVER['DOCUMENT_ROOT'] . "/System/img/guides/" . $currentImage)) {
                                unlink($_SERVER['DOCUMENT_ROOT'] . "/System/img/guides/" . $currentImage);
                            }

                            // Update database with new image
                            $query = "UPDATE guide_types SET name = ?, released_date = ?, image = ? WHERE id = ? LIMIT 1";
                            $stmt = mysqli_prepare($connection, $query);
                            mysqli_stmt_bind_param($stmt, 'sisi', $type, $released_date, $imageName, $id);
                        } else {
                            $_SESSION['edit-type-error'] = "File size too big. Should be less than 3MB";
                        }
                    } else {
                        $_SESSION['edit-type-error'] = "File should be PNG, JPG, or JPEG";
                    }
                } else {
                    // No new image uploaded, only update category and description
                    $query = "UPDATE guide_types SET name = ?, released_date = ? WHERE id = ? LIMIT 1";
                    $stmt = mysqli_prepare($connection, $query);
                    mysqli_stmt_bind_param($stmt, 'sii', $type, $released_date, $id);
                }

                // Execute Update Query
                if (isset($stmt) && mysqli_stmt_execute($stmt)) {
                    // Log the activity
                    $admin_id = $_SESSION['admin-id']; // Assuming you store user ID in session
                    $activity_type = 'UPDATE';
                    $description = "Updated type: '$type'";
                    $entity_type = 'guide_type';
                    log_admin_activity($connection, $admin_id, $activity_type, $description, $entity_type, $id);

                    unset($_SESSION['edit-type-data']); // Clear form data from session on success  
                    $_SESSION['edit-type-success'] = "Type '$type' updated successfully";
                    header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/type.php?brand_id=' . $brand_id . '&category_id=' . $category_id);
                    die();
                } else {
                    $_SESSION['edit-type-error'] = "Failed to update category.";
                }

                mysqli_stmt_close($stmt);
            }
        }
    }

    // Redirect back if there's an error
    if (isset($_SESSION['edit-type-error'])) {
        $_SESSION['edit-type-data'] = $_POST;
        header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/type-edit.php?type_id=' . $id . '&brand_id=' . $brand_id . '&category_id=' . $category_id);
        die();
    }
} else {
    // If button wasn't clicked, redirect to manage brands page with category_id
    header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/type.php?brand_id=' . $brand_id . '&category_id=' . $category_id);
    die();
}
