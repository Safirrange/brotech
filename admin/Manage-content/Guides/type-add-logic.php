<?php
require __DIR__ . '/../../../config/database.php';
require __DIR__ . '/../../../Helper/admin-activity-logger.php';

// Handle form submission
if (isset($_POST['submit'])) {
    $category_id = filter_var($_POST['category_id'], FILTER_SANITIZE_NUMBER_INT);
    $brand_id = filter_var($_POST['brand_id'], FILTER_SANITIZE_NUMBER_INT);
    $brand_name = filter_var($_POST['brand_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $type = filter_var($_POST['type'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $released_date = filter_var($_POST['released_date'], FILTER_VALIDATE_INT);
    $image = $_FILES['image'];

    // Validate inputs
    if (!$type) {
        $_SESSION['add-type-error'] = "Please input a type";
    } elseif (!$released_date || $released_date > date('Y')) {
        $_SESSION['add-type-error']  = "Please enter a valid Year Created.";
    } elseif ($released_date < 2000) {
        $_SESSION['add-type-error']  = "Please enter a Year after 2000.";
    } elseif (!$image['name']) {
        $_SESSION['add-type-error'] = "Please select an image";
    } else {
        // Check if type already exists in the brands
        $check_query = "SELECT id FROM guide_types WHERE category_id = ? AND brand_id = ? AND name = ?";
        $check_stmt = mysqli_prepare($connection, $check_query);
        mysqli_stmt_bind_param($check_stmt, "iis", $category_id, $brand_id, $type);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $_SESSION['add-type-error'] = "'$brand_name' '$type' already exists in this brand.";
        } else {
            $time = time(); // Make each image name unique
            $imageName = $time . '_' . $image['name'];
            $imageTmpName = $image['tmp_name'];
            $imageDestinationPath = $_SERVER['DOCUMENT_ROOT'] . '/System/img/guides/' . $imageName;

            // Ensure it's an allowed image type
            $allowedFiles = ['png', 'jpg', 'jpeg'];
            $extension = pathinfo($imageName, PATHINFO_EXTENSION);
            if (in_array($extension, $allowedFiles)) {
                // Check file size
                if ($image['size'] < 3_000_000) {
                    move_uploaded_file($imageTmpName, $imageDestinationPath);

                    // Insert new type into database
                    $query = "INSERT INTO guide_types (category_id, brand_id, name, image, released_date) VALUES (?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($connection, $query);
                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "iissi", $category_id, $brand_id, $type, $imageName, $released_date);
                        $result = mysqli_stmt_execute($stmt);

                        if ($result) {
                            // Get the newly inserted type ID
                            $new_type_id = mysqli_insert_id($connection);
                            
                            // Log the activity
                            $admin_id = $_SESSION['admin-id'];
                            $activity_type = 'CREATE';
                            $description = "Added new type: '$type' for brand '$brand_name'";
                            $entity_type = 'guide_type';
                            log_admin_activity($connection, $admin_id, $activity_type, $description, $entity_type, $new_type_id);

                            $_SESSION['add-type-success'] = "$brand_name '$type' successfully added";
                            header('Location: ' . ROOT_URL . 'admin/Manage-content/Guides/type.php?brand_id=' . $brand_id . '&category_id=' . $category_id);
                            exit();
                        } else {
                            $_SESSION['add-type-error'] = "Couldn't add brand.";
                        }
                    } else {
                        $_SESSION['add-type-error'] = "Error preparing statement: " . mysqli_error($connection);
                    }
                } else {
                    $_SESSION['add-type-error'] = "File size too big. Should be less than 3MB.";
                }
            } else {
                $_SESSION['add-type-error'] = "File should be PNG, JPG, or JPEG.";
            }
        }
        mysqli_stmt_close($check_stmt);
    }

    // Redirect back to the add page with the same category ID if there's an error
    if (isset($_SESSION['add-type-error'])) {
        $_SESSION['add-types-data'] = $_POST;
        header('Location: ' . ROOT_URL . 'admin/Manage-content/Guides/type-add.php?brand_id=' . $brand_id . '&category_id=' . $category_id);
        exit();
    }
} else {
    header('Location: ' . ROOT_URL . 'admin/Manage-content/Guides/type-add.php?brand_id=' . $brand_id . '&category_id=' . $category_id);
    exit();
}
