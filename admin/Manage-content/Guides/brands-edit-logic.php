<?php
require __DIR__ . '/../../../config/database.php';
require __DIR__ . '/../../../Helper/admin-activity-logger.php';

if (isset($_POST['submit'])) {
    // Filtering user input data before validation
    $id = filter_var($_POST['brand_id'], FILTER_SANITIZE_NUMBER_INT);
    $category_id = filter_var($_POST['category_id'], FILTER_SANITIZE_NUMBER_INT);
    $brand = filter_var($_POST['brand'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $image = $_FILES['image'];

    // Validation
    if (!$brand) {
        $_SESSION['edit-brand'] = "Please input a Brand";
    } elseif (!$description) {
        $_SESSION['edit-brand'] = "Please input a description";
    } else {

        // Check if the category name exists in any deleted record
        $checkDeletedQuery = "SELECT deleted_at FROM guide_brands WHERE brand COLLATE utf8mb4_general_ci = ? AND deleted_at IS NOT NULL";
        $stmt = mysqli_prepare($connection, $checkDeletedQuery);
        mysqli_stmt_bind_param($stmt, 's', $brand);
        mysqli_stmt_execute($stmt);
        $deletedResult = mysqli_stmt_get_result($stmt);
        $deletedData = mysqli_fetch_assoc($deletedResult);
        mysqli_stmt_close($stmt);

        if ($deletedData) {
            $_SESSION['edit-brand'] = "Cannot use this brand name. It was deleted on " . date('F j, Y, g:i a', strtotime($deletedData['deleted_at']));
        } else {
            // Check if category title already exists (case-insensitive)
            $checkBrandQuery = "SELECT * FROM guide_brands WHERE brand COLLATE utf8mb4_general_ci = ? AND id != ?";
            $stmt = mysqli_prepare($connection, $checkBrandQuery);
            mysqli_stmt_bind_param($stmt, 'si', $brand, $id);
            mysqli_stmt_execute($stmt);
            $checkBrandResult = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($checkBrandResult) > 0) {
                $_SESSION['edit-brand'] = "Brand already exists";
            } else {
                // Fetch the current image to delete later if updated
                $query = "SELECT image FROM guide_brands WHERE id = ? LIMIT 1";
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
                    $imageDestinationPath = $_SERVER['DOCUMENT_ROOT'] . '/System/img/logos/' . $imageName;

                    // Make sure the file is an image
                    $allowedFiles = ['png', 'jpg', 'jpeg'];
                    $extension = pathinfo($imageName, PATHINFO_EXTENSION);

                    if (in_array($extension, $allowedFiles)) {
                        // Check file size
                        if ($image['size'] < 3_000_000) {
                            // Move new image to folder
                            move_uploaded_file($imageTmpName, $imageDestinationPath);

                            // Delete old image if exists
                            if ($currentImage && file_exists($_SERVER['DOCUMENT_ROOT'] . "/System/img/logos/" . $currentImage)) {
                                unlink($_SERVER['DOCUMENT_ROOT'] . "/System/img/logos/" . $currentImage);
                            }

                            // Update database with new image
                            $query = "UPDATE guide_brands SET category = ?, description = ?, image = ? WHERE id = ? LIMIT 1";
                            $stmt = mysqli_prepare($connection, $query);
                            mysqli_stmt_bind_param($stmt, 'sssi', $brand, $description, $imageName, $id);
                        } else {
                            $_SESSION['edit-brand'] = "File size too big. Should be less than 3MB";
                        }
                    } else {
                        $_SESSION['edit-brand'] = "File should be PNG, JPG, or JPEG";
                    }
                } else {
                    // No new image uploaded, only update category and description
                    $query = "UPDATE guide_brands SET brand = ?, description = ? WHERE id = ? LIMIT 1";
                    $stmt = mysqli_prepare($connection, $query);
                    mysqli_stmt_bind_param($stmt, 'ssi', $brand, $description, $id);
                }

                // Execute Update Query
                if (isset($stmt) && mysqli_stmt_execute($stmt)) {
                    // Log the activity
                    $admin_id = $_SESSION['admin-id'];
                    $activity_type = 'UPDATE';
                    $description = "Updated brand: '$brand'";
                    $entity_type = 'guide_brand';
                    log_admin_activity($connection, $admin_id, $activity_type, $description, $entity_type, $id);

                    unset($_SESSION['edit-brand-data']); // Clear form data from session on success  
                    $_SESSION['edit-brand-success'] = "Brand '$brand' updated successfully";
                    header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/brands.php?category_id=' . $category_id);
                    die();
                } else {
                    $_SESSION['edit-brand'] = "Failed to update category.";
                }

                mysqli_stmt_close($stmt);
            }
        }
    }

    // Redirect back if there's an error
    if (isset($_SESSION['edit-brand'])) {
        $_SESSION['edit-brand-data'] = $_POST;
        header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/brands-edit.php?brand_id=' . $id . '&category_id=' . $category_id);
        die();
    }
} else {
    // If button wasn't clicked, redirect to manage brands page with category_id
    header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/brands.php?category_id=' . $category_id);
    die();
}
