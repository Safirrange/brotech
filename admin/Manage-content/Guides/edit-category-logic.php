<?php
require __DIR__ . '/../../../config/database.php';
require __DIR__ . '/../../../Helper/admin-activity-logger.php';

if (isset($_POST['submit'])) {
    // Filtering user input data before validation
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $category = filter_var($_POST['category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $image = $_FILES['image'];

    // Validation
    if (!$category) {
        $_SESSION['edit-content-category'] = "Please input a Repair Category";
    } elseif (!$description) {
        $_SESSION['edit-content-category'] = "Please input a description";
    } else {

        // Check if the category name exists in any deleted record
        $checkDeletedQuery = "SELECT deleted_at FROM guide_categories WHERE category COLLATE utf8mb4_general_ci = ? AND deleted_at IS NOT NULL";
        $stmt = mysqli_prepare($connection, $checkDeletedQuery);
        mysqli_stmt_bind_param($stmt, 's', $category);
        mysqli_stmt_execute($stmt);
        $deletedResult = mysqli_stmt_get_result($stmt);
        $deletedData = mysqli_fetch_assoc($deletedResult);
        mysqli_stmt_close($stmt);

        if ($deletedData) {
            $_SESSION['edit-content-category'] = "Cannot use this category name. It was deleted on " . date('F j, Y, g:i a', strtotime($deletedData['deleted_at']));
        } else {

            // Check if category title already exists (case-insensitive)
            $checkCategoryMetaQuery = "SELECT * FROM guide_categories WHERE category COLLATE utf8mb4_general_ci = ? AND id != ?";
            $stmt = mysqli_prepare($connection, $checkCategoryMetaQuery);
            mysqli_stmt_bind_param($stmt, 'si', $category, $id);
            mysqli_stmt_execute($stmt);
            $checkCategoryMetaResult = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($checkCategoryMetaResult) > 0) {
                $_SESSION['edit-content-category'] = "Category already exists";
            } else {
                // Fetch the current image to delete later if updated
                $query = "SELECT image FROM guide_categories WHERE id = ? LIMIT 1";
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
                    $imageDestinationPath = $_SERVER['DOCUMENT_ROOT'] . '/System/img/homepage/' . $imageName;

                    // Make sure the file is an image
                    $allowedFiles = ['png', 'jpg', 'jpeg'];
                    $extension = pathinfo($imageName, PATHINFO_EXTENSION);

                    if (in_array($extension, $allowedFiles)) {
                        // Check file size
                        if ($image['size'] < 3_000_000) {
                            // Move new image to folder
                            move_uploaded_file($imageTmpName, $imageDestinationPath);

                            // Delete old image if exists
                            if ($currentImage && file_exists($_SERVER['DOCUMENT_ROOT'] . "/System/img/homepage/" . $currentImage)) {
                                unlink($_SERVER['DOCUMENT_ROOT'] . "/System/img/homepage/" . $currentImage);
                            }

                            // Update database with new image
                            $query = "UPDATE guide_categories SET category = ?, description = ?, image = ? WHERE id = ? LIMIT 1";
                            $stmt = mysqli_prepare($connection, $query);
                            mysqli_stmt_bind_param($stmt, 'sssi', $category, $description, $imageName, $id);
                        } else {
                            $_SESSION['edit-content-category'] = "File size too big. Should be less than 3MB";
                        }
                    } else {
                        $_SESSION['edit-content-category'] = "File should be PNG, JPG, or JPEG";
                    }
                } else {
                    // No new image uploaded, only update category and description
                    $query = "UPDATE guide_categories SET category = ?, description = ? WHERE id = ? LIMIT 1";
                    $stmt = mysqli_prepare($connection, $query);
                    mysqli_stmt_bind_param($stmt, 'ssi', $category, $description, $id);
                }

                // Execute Update Query
                if (isset($stmt) && mysqli_stmt_execute($stmt)) {
                    // Log the activity
                    $admin_id = $_SESSION['admin-id']; // Assuming you store user ID in session
                    $activity_type = 'UPDATE';
                    $description = "Updated type: '$category'";
                    $entity_type = 'guide_category';
                    log_admin_activity($connection, $admin_id, $activity_type, $description, $entity_type, $id);
                    unset($_SESSION['edit-content-category-data']); // Clear form data from session on success  
                    $_SESSION['edit-content-category-success'] = "Category '$category' updated successfully";
                    header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/category.php');
                    die();
                } else {
                    $_SESSION['edit-content-category'] = "Failed to update category.";
                }


                mysqli_stmt_close($stmt);
            }
        }
    }
    // Redirect back if there's an error
    if (isset($_SESSION['edit-content-category'])) {
        $_SESSION['edit-content-category-data'] = $_POST;
        header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/edit-category.php?id=' . $id);
        die();
    }
} else {
    // If button wasn't clicked, redirect to manage categories page
    header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/category.php');
    die();
}
