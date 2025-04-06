<?php
require __DIR__ . '/../../../config/database.php';
require __DIR__ . '/../../../Helper/admin-activity-logger.php';

if (isset($_POST['submit'])) {
    // Filtering user input data before validation
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $gadget_category = filter_var($_POST['gadget_category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $gadget_img = $_FILES['gadget_img'];

    // Validation
    if (!$gadget_category) {
        $_SESSION['edit-gadget-category'] = "Please input a Repair Category";
    } else {
        // Check if category title already exists (case-insensitive)
        $checkGadgetCategoryQuery = "SELECT * FROM gadget_category WHERE gadget_category COLLATE utf8mb4_general_ci = ? AND id != ?";
        $stmt = mysqli_prepare($connection, $checkGadgetCategoryQuery);
        mysqli_stmt_bind_param($stmt, 'si', $gadget_category, $id);
        mysqli_stmt_execute($stmt);
        $checkGadgetCategoryResult = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($checkGadgetCategoryResult) > 0) {
            $_SESSION['edit-gadget-category'] = "Gadget category already exists";
        } else {
            // Fetch the current image to delete later if updated
            $query = "SELECT gadget_img FROM gadget_category WHERE id = ? LIMIT 1";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, 'i', $id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $currentData = mysqli_fetch_assoc($result);
            $currentImage = $currentData['gadget_img'];
            mysqli_stmt_close($stmt);

            // Image Handling Logic
            if ($gadget_img['name']) { // If a new image is uploaded
                $time = time(); // Unique file name
                $imageName = $time . '_' . $gadget_img['name'];
                $imageTmpName = $gadget_img['tmp_name'];
                $imageDestinationPath = $_SERVER['DOCUMENT_ROOT'] . '/System/img/repairability/' . $imageName;

                // Make sure the file is an image
                $allowedFiles = ['png', 'jpg', 'jpeg'];
                $extension = pathinfo($imageName, PATHINFO_EXTENSION);

                if (in_array($extension, $allowedFiles)) {
                    // Check file size
                    if ($gadget_img['size'] < 3_000_000) {
                        // Move new image to folder
                        move_uploaded_file($imageTmpName, $imageDestinationPath);

                        // Delete old image if exists
                        if ($currentImage && file_exists($_SERVER['DOCUMENT_ROOT'] . "/System/img/repairability/" . $currentImage)) {
                            unlink($_SERVER['DOCUMENT_ROOT'] . "/System/img/repairability/" . $currentImage);
                        }

                        // Update database with new image
                        $query = "UPDATE gadget_category SET gadget_category = ?, gadget_img = ? WHERE id = ? LIMIT 1";
                        $stmt = mysqli_prepare($connection, $query);
                        mysqli_stmt_bind_param($stmt, 'ssi', $gadget_category, $imageName, $id);
                    } else {
                        $_SESSION['edit-gadget-category'] = "File size too big. Should be less than 3MB";
                    }
                } else {
                    $_SESSION['edit-gadget-category'] = "File should be PNG, JPG, or JPEG";
                }
            } else {
                // No new image uploaded, only update category and description
                $query = "UPDATE gadget_category SET gadget_category = ? WHERE id = ? LIMIT 1";
                $stmt = mysqli_prepare($connection, $query);
                mysqli_stmt_bind_param($stmt, 'si', $gadget_category, $id);
            }

            // Execute Update Query
            if (isset($stmt) && mysqli_stmt_execute($stmt)) {
                // Log admin activity
                log_admin_activity(
                    $connection,
                    $_SESSION['admin-id'],
                    'EDIT_GADGET_CATEGORY',
                    "Updated gadget category: $gadget_category",
                    'gadget_category',
                    $id
                );

                unset($_SESSION['edit-gadget-category-data']); // Clear form data from session on success  
                $_SESSION['edit-gadget-category-success'] = "Gadget category '$gadget_category' updated successfully";
                header('location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget.php');
                die();
            } else {
                $_SESSION['edit-gadget-category'] = "Failed to update gadget category.";
            }

            mysqli_stmt_close($stmt);
        }
    }

    // Redirect back if there's an error
    if (isset($_SESSION['edit-gadget-category'])) {
        $_SESSION['edit-gadget-category-data'] = $_POST;
        header('location: ' . ROOT_URL . 'admin/Manage-content/Gadget/edit-gadget.php?id=' . $id);
        die();
    }
} else {
    // If button wasn't clicked, redirect to manage categories page
    header('location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget.php');
    die();
}
