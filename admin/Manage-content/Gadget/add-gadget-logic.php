<?php
require __DIR__ . '/../../../config/database.php';
require __DIR__ . '/../../../Helper/admin-activity-logger.php';

if (isset($_POST['submit'])) {
    // Get form data
    $gadget_category = filter_var($_POST['gadget_category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $page_title = filter_var($_POST['page_title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $gadget_image = $_FILES['gadget_img'];
    $page_image = $_FILES['page_img'];

    // Validations
    if (!$gadget_category) {
        $_SESSION['add-gadget-category'] = "Enter Gadget Category";
    } elseif (!$page_title) {
        $_SESSION['add-gadget-category'] = "Enter Page Title";
    } elseif (empty($gadget_image['name'])) {
        $_SESSION['add-gadget-category'] = "Please select a gadget image";
    } elseif (empty($page_image['name'])) {
        $_SESSION['add-gadget-category'] = "Please select a page image";
    } else {
        // Check if category exists, including soft-deleted ones
        $checkCategoriesQuery = "SELECT id, deleted_at FROM gadget_category WHERE LOWER(gadget_category) = LOWER(?)";
        $stmt = mysqli_prepare($connection, $checkCategoriesQuery);
        mysqli_stmt_bind_param($stmt, 's', $gadget_category);
        mysqli_stmt_execute($stmt);
        $checkCategoriesResult = mysqli_stmt_get_result($stmt);
        $existingCategory = mysqli_fetch_assoc($checkCategoriesResult);

        if ($existingCategory) {
            if (!is_null($existingCategory['deleted_at'])) {
                $restoreQuery = "UPDATE gadget_category SET deleted_at = NULL WHERE id = ?";
                $restoreStmt = mysqli_prepare($connection, $restoreQuery);
                mysqli_stmt_bind_param($restoreStmt, 'i', $existingCategory['id']);
                $restoreResult = mysqli_stmt_execute($restoreStmt);

                if ($restoreResult) {
                    $_SESSION['add-gadget-category-success'] = "Category '$gadget_category' has been restored!";
                    header('Location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget.php');
                    exit();
                } else {
                    $_SESSION['add-gadget-category'] = "Error restoring category.";
                }
            } else {
                $_SESSION['add-gadget-category'] = "Category '$gadget_category' already exists!";
            }
        } else {
            // Process Image Uploads
            $time = time();
            $allowedFiles = ['png', 'jpg', 'jpeg'];

            $uploadImages = [
                'gadget_img' => $gadget_image,
                'page_img' => $page_image
            ];

            $uploadedPaths = [];

            foreach ($uploadImages as $key => $image) {
                $imageName = $time . '_' . $image['name'];
                $imageTmpName = $image['tmp_name'];
                $imageDestinationPath = $_SERVER['DOCUMENT_ROOT'] . '/System/img/repairability/' . $imageName;

                $extension = pathinfo($imageName, PATHINFO_EXTENSION);
                if (in_array($extension, $allowedFiles)) {
                    if ($image['size'] < 3_000_000) {
                        move_uploaded_file($imageTmpName, $imageDestinationPath);
                        $uploadedPaths[$key] = $imageName;
                    } else {
                        $_SESSION['add-gadget-category'] = "$key file size too big. Should be less than 3MB.";
                    }
                } else {
                    $_SESSION['add-gadget-category'] = "$key file should be PNG, JPG, or JPEG.";
                }
            }

            // Insert category into the database
            $query = "INSERT INTO gadget_category (gadget_category, gadget_img, page_img, page_title) VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($connection, $query);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ssss', $gadget_category, $uploadedPaths['gadget_img'], $uploadedPaths['page_img'], $page_title);
                $result = mysqli_stmt_execute($stmt);

                if ($result) {
                    // Get the new category ID
                    $new_category_id = mysqli_insert_id($connection);

                    // Log admin activity
                    log_admin_activity(
                        $connection,
                        $_SESSION['admin-id'],
                        'ADD_GADGET_CATEGORY',
                        "Added new gadget category: $gadget_category",
                        'gadget_category',
                        $new_category_id
                    );

                    $_SESSION['add-gadget-category-success'] = "Category '$gadget_category' successfully added";
                    header('Location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget.php');
                    exit();
                } else {
                    $_SESSION['add-gadget-category'] = "Couldn't add category.";
                }
            } else {
                $_SESSION['add-gadget-category'] = "Error preparing statement: " . mysqli_error($connection);
            }
        }
    }

    if (isset($_SESSION['add-gadget-category'])) {
        $_SESSION['add-gadget-category-data'] = $_POST;
        header('Location: ' . ROOT_URL . 'admin/Manage-content/Gadget/add-gadget.php');
        exit();
    }
} else {
    header('Location: ' . ROOT_URL . 'admin/Manage-content/Gadget/add-gadget.php');
    exit();
}
