<?php
require __DIR__ . '/../../../config/database.php';
require __DIR__ . '/../../../Helper/admin-activity-logger.php';

// Handle form submission
if (isset($_POST['submit'])) {
    $category_id = filter_var($_POST['categoryId'], FILTER_SANITIZE_NUMBER_INT);
    $brand = filter_var($_POST['brand'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $image = $_FILES['image'];

    // Validate inputs
    if (!$brand) {
        $_SESSION['add-content-brands'] = "Please input a Brand";
    } elseif (!$description) {
        $_SESSION['add-content-brands'] = "Please input a description";
    } elseif (!$image['name']) {
        $_SESSION['add-content-brands'] = "Please select an image";
    } else {
        // Check if type already exists in the category
        $check_query = "SELECT id FROM guide_brands WHERE category_id = ? AND brand = ?";
        $check_stmt = mysqli_prepare($connection, $check_query);
        mysqli_stmt_bind_param($check_stmt, "is", $category_id, $type);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $_SESSION['add-content-brands'] = "Brand '$brand' already exists in this category.";
        } else {
            $time = time(); // Make each image name unique
            $imageName = $time . '_' . $image['name'];
            $imageTmpName = $image['tmp_name'];
            $imageDestinationPath = $_SERVER['DOCUMENT_ROOT'] . '/System/img/logos/' . $imageName;

            // Ensure it's an allowed image type
            $allowedFiles = ['png', 'jpg', 'jpeg'];
            $extension = pathinfo($imageName, PATHINFO_EXTENSION);
            if (in_array($extension, $allowedFiles)) {
                // Check file size
                if ($image['size'] < 3_000_000) {
                    move_uploaded_file($imageTmpName, $imageDestinationPath);

                    // Insert new type into database
                    $query = "INSERT INTO guide_brands (category_id, brand, description, image) VALUES (?, ?, ?, ?)";
                    $stmt = mysqli_prepare($connection, $query);
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "isss", $category_id, $brand, $description, $imageName);
                        $result = mysqli_stmt_execute($stmt);

                        if ($result) {
                            // Get the newly inserted brand ID
                            $new_brand_id = mysqli_insert_id($connection);
                            
                            // Log the activity
                            $admin_id = $_SESSION['admin-id'];
                            $activity_type = 'CREATE';
                            $description = "Added new brand: '$brand'";
                            $entity_type = 'guide_brand';
                            log_admin_activity($connection, $admin_id, $activity_type, $description, $entity_type, $new_brand_id);

                            $_SESSION['add-content-brands-success'] = "Brand '$brand' successfully added";
                            header('Location: ' . ROOT_URL . 'admin/Manage-content/Guides/brands.php?category_id=' . $category_id);
                            exit();
                        } else {
                            $_SESSION['add-content-brands'] = "Couldn't add brand.";
                        }
                    } else {
                        $_SESSION['add-content-brands'] = "Error preparing statement: " . mysqli_error($connection);
                    }
                } else {
                    $_SESSION['add-content-brands'] = "File size too big. Should be less than 3MB.";
                }
            } else {
                $_SESSION['add-content-brands'] = "File should be PNG, JPG, or JPEG.";
            }
        }
        mysqli_stmt_close($check_stmt);
    }

    // Redirect back to the add page with the same category ID if there's an error
    if (isset($_SESSION['add-content-brands'])) {
        $_SESSION['add-content-brands-data'] = $_POST;
        header('Location: ' . ROOT_URL . 'admin/Manage-content/Guides/brands-add.php?category_id=' . $category_id);
        exit();
    }
} else {
    header('Location: ' . ROOT_URL . 'admin/Manage-content/Guides/brands-add.php?category_id=' . $category_id);
    exit();
}
