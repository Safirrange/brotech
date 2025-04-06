<?php
require __DIR__ . '/../../../config/database.php';
require __DIR__ . '/../../../Helper/admin-activity-logger.php';

if (isset($_POST['submit'])) {
    // Get form data
    $category = filter_var($_POST['category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $image = $_FILES['image'];

    // Validations
    if (!$category) {
        $_SESSION['add-content-category'] = "Enter Appliance Category";
    } elseif (!$description) {
        $_SESSION['add-content-category'] = "Enter Description";
    } elseif (!$image['name']) {
        $_SESSION['add-content-category'] = "Please select an image";
    } else {
        // Check if category exists, including soft-deleted ones
        $checkCategoriesQuery = "SELECT id, deleted_at FROM guide_categories WHERE LOWER(category) = LOWER(?)";
        $stmt = mysqli_prepare($connection, $checkCategoriesQuery);
        mysqli_stmt_bind_param($stmt, 's', $category);
        mysqli_stmt_execute($stmt);
        $checkCategoriesResult = mysqli_stmt_get_result($stmt);
        $existingCategory = mysqli_fetch_assoc($checkCategoriesResult);

        if ($existingCategory) {
            if (!is_null($existingCategory['deleted_at'])) {
                // If category exists but was soft deleted, restore it
                $restoreQuery = "UPDATE guide_categories SET deleted_at = NULL WHERE id = ?";
                $restoreStmt = mysqli_prepare($connection, $restoreQuery);
                mysqli_stmt_bind_param($restoreStmt, 'i', $existingCategory['id']);
                $restoreResult = mysqli_stmt_execute($restoreStmt);

                if ($restoreResult) {
                    $_SESSION['add-content-category-success'] = "Category '$category' has been restored!";
                    header('Location: ' . ROOT_URL . 'admin/Manage-content/Guides/category.php');
                    exit();
                } else {
                    $_SESSION['add-content-category'] = "Error restoring category.";
                }
            } else {
                $_SESSION['add-content-category'] = "Category '$category' already exists!";
            }
        } else {
            // Process Image Upload
            $time = time(); // Make each image name unique
            $imageName = $time . '_' . $image['name'];
            $imageTmpName = $image['tmp_name'];
            $imageDestinationPath = $_SERVER['DOCUMENT_ROOT'] . '/System/img/homepage/' . $imageName;

            // Ensure it's an allowed image type
            $allowedFiles = ['png', 'jpg', 'jpeg'];
            $extension = pathinfo($imageName, PATHINFO_EXTENSION);
            if (in_array($extension, $allowedFiles)) {
                // Check file size
                if ($image['size'] < 3_000_000) {
                    move_uploaded_file($imageTmpName, $imageDestinationPath);

                    // Insert category into the database
                    $query = "INSERT INTO guide_categories (category, image, description) VALUES (?, ?, ?)";
                    $stmt = mysqli_prepare($connection, $query);

                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, 'sss', $category, $imageName, $description);
                        $result = mysqli_stmt_execute($stmt);

                        if ($result) {
                            // Get the newly inserted brand ID
                            $new_category_id = mysqli_insert_id($connection);

                            // Log the activity
                            $admin_id = $_SESSION['admin-id'];
                            $activity_type = 'CREATE';
                            $description = "Added new category: '$category'";
                            $entity_type = 'guide_category';
                            log_admin_activity($connection, $admin_id, $activity_type, $description, $entity_type, $new_category_id);

                            $_SESSION['add-content-category-success'] = "Category '$category' successfully added";
                            header('Location: ' . ROOT_URL . 'admin/Manage-content/Guides/category.php');
                            exit();
                        } else {
                            $_SESSION['add-content-category'] = "Couldn't add category.";
                        }
                    } else {
                        $_SESSION['add-content-category'] = "Error preparing statement: " . mysqli_error($connection);
                    }
                } else {
                    $_SESSION['add-content-category'] = "File size too big. Should be less than 3MB.";
                }
            } else {
                $_SESSION['add-content-category'] = "File should be PNG, JPG, or JPEG.";
            }
        }
    }

    // Redirect back if there was an error
    if (isset($_SESSION['add-content-category'])) {
        $_SESSION['add-content-category-data'] = $_POST;
        header('Location: ' . ROOT_URL . 'admin/Manage-content/Guides/add-category.php');
        exit();
    }
} else {
    header('Location: ' . ROOT_URL . 'admin/Manage-content/Guides/add-category.php');
    exit();
}
