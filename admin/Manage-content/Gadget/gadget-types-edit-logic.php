<?php
require __DIR__ . '/../../../config/database.php';
require __DIR__ . '/../../../Helper/admin-activity-logger.php';

if (isset($_POST['submit'])) {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $gadget_category_id = filter_var($_POST['gadget_category_id'], FILTER_SANITIZE_NUMBER_INT);
    $type_name = filter_var($_POST['type_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $pros = isset($_POST['pros']) && is_array($_POST['pros']) ? $_POST['pros'] : [];
    $cons = isset($_POST['cons']) && is_array($_POST['cons']) ? $_POST['cons'] : [];
    $year_created = filter_var($_POST['year_created'], FILTER_VALIDATE_INT);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $price_rating = filter_var($_POST['price_rating'], FILTER_VALIDATE_INT);
    $longevity_rating = filter_var($_POST['longevity_rating'], FILTER_VALIDATE_INT);
    $repairability_rating = filter_var($_POST['repairability_rating'], FILTER_VALIDATE_INT);
    $functionality_rating = filter_var($_POST['functionality_rating'], FILTER_VALIDATE_INT);
    $previous_image = $_POST['previous_image'];
    $gadget_img = $_FILES['gadget_img'];

    // Validation
    if (!$type_name) {
        $_SESSION['edit-gadget-types'] = "Please input a Gadget Type/Brand";
    } elseif (empty(array_filter($pros))) {
        $_SESSION['edit-gadget-types'] = "Please enter at least one Pro";
    } elseif (empty(array_filter($cons))) {
        $_SESSION['edit-gadget-types'] = "Please enter at least one Con";
    } elseif (!$year_created || $year_created > date('Y')) {
        $_SESSION['edit-gadget-types'] = "Please enter a valid Year Created";
    } elseif ($year_created < 2000) {
        $_SESSION['edit-gadget-types'] = "Please enter a Year after 2000";
    } elseif ($price === false || $price < 0) {
        $_SESSION['edit-gadget-types'] = "Please enter a valid positive Price";
    } elseif (!$price_rating || $price_rating < 1 || $price_rating > 10) {
        $_SESSION['edit-gadget-types'] = "Please enter a Price Rating between 1-10";
    } elseif (!$longevity_rating || $longevity_rating < 1 || $longevity_rating > 10) {
        $_SESSION['edit-gadget-types'] = "Please enter a Longevity Rating between 1-10";
    } elseif (!$repairability_rating || $repairability_rating < 1 || $repairability_rating > 10) {
        $_SESSION['edit-gadget-types'] = "Please enter a Repairability Rating between 1-10";
    } elseif (!$functionality_rating || $functionality_rating < 1 || $functionality_rating > 10) {
        $_SESSION['edit-gadget-types'] = "Please enter a Functionality Rating between 1-10";
    } else {
        // Handle image update if new image is uploaded
        if ($gadget_img['name']) {
            $time = time();
            $imageName = $time . '_' . $gadget_img['name'];
            $imageTmpName = $gadget_img['tmp_name'];
            $imageDestinationPath = $_SERVER['DOCUMENT_ROOT'] . '/System/img/gadgets/' . $imageName;

            $allowedFiles = ['png', 'jpg', 'jpeg'];
            $extension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

            if (!in_array($extension, $allowedFiles)) {
                $_SESSION['edit-gadget-types'] = "File should be PNG, JPG, or JPEG";
            } elseif ($gadget_img['size'] > 3000000) {
                $_SESSION['edit-gadget-types'] = "File size too big. Should be less than 3MB";
            } else {
                // Delete old image if it exists
                if ($previous_image) {
                    $oldImagePath = $_SERVER['DOCUMENT_ROOT'] . '/System/img/gadgets/' . $previous_image;
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                // Upload new image
                move_uploaded_file($imageTmpName, $imageDestinationPath);
            }
        } else {
            // Keep existing image
            $imageName = $previous_image;
        }

        if (!isset($_SESSION['edit-gadget-types'])) {
            // Convert pros and cons arrays to JSON
            $prosJson = json_encode($pros);
            $consJson = json_encode($cons);

            // Start transaction
            mysqli_begin_transaction($connection);
            
            // Update gadget type
            $query = "UPDATE gadget_types SET 
                        type_name = ?, 
                        pros = ?, 
                        cons = ?, 
                        year_created = ?,
                        price = ?,
                        gadget_img = ?
                    WHERE id = ?";

            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param(
                $stmt,
                "sssidsi",
                $type_name,
                $prosJson,
                $consJson,
                $year_created,
                $price,
                $imageName,
                $id
            );
            
            $gadget_updated = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if ($gadget_updated) {
                // Check if admin rating exists
                $check_rating_query = "SELECT * FROM admin_gadget_ratings 
                                      WHERE admin_id = ? AND gadget_type_id = ?";
                $check_stmt = mysqli_prepare($connection, $check_rating_query);
                mysqli_stmt_bind_param($check_stmt, "ii", $_SESSION['admin-id'], $id);
                mysqli_stmt_execute($check_stmt);
                $rating_result = mysqli_stmt_get_result($check_stmt);
                mysqli_stmt_close($check_stmt);

                if (mysqli_num_rows($rating_result) > 0) {
                    // Update existing rating
                    $rating_query = "UPDATE admin_gadget_ratings SET 
                                    price_rating = ?,
                                    longevity_rating = ?,
                                    repairability_rating = ?,
                                    functionality_rating = ?
                                    WHERE admin_id = ? AND gadget_type_id = ?";
                } else {
                    // Insert new rating
                    $rating_query = "INSERT INTO admin_gadget_ratings 
                                    (price_rating, longevity_rating, repairability_rating, 
                                     functionality_rating, admin_id, gadget_type_id) 
                                    VALUES (?, ?, ?, ?, ?, ?)";
                }

                $rating_stmt = mysqli_prepare($connection, $rating_query);
                mysqli_stmt_bind_param(
                    $rating_stmt,
                    "iiiiii",
                    $price_rating,
                    $longevity_rating,
                    $repairability_rating,
                    $functionality_rating,
                    $_SESSION['admin-id'],
                    $id
                );
                
                $rating_updated = mysqli_stmt_execute($rating_stmt);
                mysqli_stmt_close($rating_stmt);

                if ($rating_updated) {
                    // Calculate and update verified rating
                    $overall_query = "UPDATE gadget_types SET verified_rating = (
                        SELECT AVG((price_rating + longevity_rating + repairability_rating + functionality_rating)/4) 
                        FROM admin_gadget_ratings 
                        WHERE gadget_type_id = ?
                    ) WHERE id = ?";
                    
                    $overall_stmt = mysqli_prepare($connection, $overall_query);
                    mysqli_stmt_bind_param($overall_stmt, "ii", $id, $id);
                    $overall_updated = mysqli_stmt_execute($overall_stmt);
                    mysqli_stmt_close($overall_stmt);

                    if ($overall_updated) {
                        // Log admin activity
                        log_admin_activity(
                            $connection,
                            $_SESSION['admin-id'],
                            'EDIT_GADGET_TYPE',
                            "Updated gadget type and ratings for: $type_name",
                            'gadget_types',
                            $id
                        );

                        mysqli_commit($connection);
                        $_SESSION['edit-gadget-types-success'] = "Gadget $type_name type and ratings updated successfully";
                        header('location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget-types.php?gadget_category_id=' . $gadget_category_id);
                        die();
                    } else {
                        mysqli_rollback($connection);
                        $_SESSION['edit-gadget-types'] = "Error updating overall rating";
                    }
                } else {
                    mysqli_rollback($connection);
                    $_SESSION['edit-gadget-types'] = "Error updating ratings";
                }
            } else {
                mysqli_rollback($connection);
                $_SESSION['edit-gadget-types'] = "Error updating gadget type";
            }
        }
    }

    if (isset($_SESSION['edit-gadget-types'])) {
        $_SESSION['edit-gadget-types-data'] = $_POST;
        header('location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget-types-edit.php?id=' . $id);
        die();
    }
} else {
    header('location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget-types.php');
    die();
}
