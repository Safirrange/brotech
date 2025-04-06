<?php
require __DIR__ . '/../../../config/database.php';
require __DIR__ . '/../../../Helper/admin-activity-logger.php';

// Handle form submission
if (isset($_POST['submit'])) {
    $gadget_category_id = filter_var($_POST['gadget_category_id'], FILTER_SANITIZE_NUMBER_INT);
    $type_name = filter_var($_POST['type_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $price_rating = filter_var($_POST['price_rating'], FILTER_VALIDATE_INT);
    $longevity_rating = filter_var($_POST['longevity_rating'], FILTER_VALIDATE_INT);
    $repairability_rating = filter_var($_POST['repairability_rating'], FILTER_VALIDATE_INT);
    $functionality_rating = filter_var($_POST['functionality_rating'], FILTER_VALIDATE_INT);
    $pros = isset($_POST['pros']) && is_array($_POST['pros']) ? $_POST['pros'] : [];
    $cons = isset($_POST['cons']) && is_array($_POST['cons']) ? $_POST['cons'] : [];
    $year_created = filter_var($_POST['year_created'], FILTER_VALIDATE_INT);
    $price = filter_var($_POST['price'], FILTER_VALIDATE_FLOAT);
    $gadget_img = $_FILES['gadget_img'];

    // Validate inputs
    if (!$type_name) {
        $_SESSION['add-gadget-types'] = "Please input a Gadget Type/Brand";
    } elseif (empty(array_filter($pros))) {
        $_SESSION['add-gadget-types']  = "Please enter at least one Pro.";
    } elseif (empty(array_filter($cons))) {
        $_SESSION['add-gadget-types']  = "Please enter at least one Con.";
    } elseif (!$year_created || $year_created > date('Y')) {
        $_SESSION['add-gadget-types']  = "Please enter a valid Year Created.";
    } elseif ($year_created < 2000) {
        $_SESSION['add-gadget-types']  = "Please enter a Year after 2000.";
    } elseif ($price === false) {
        $_SESSION['add-gadget-types']  = "Please enter a valid Price.";
    } elseif ($price < 0) {
        $_SESSION['add-gadget-types']  = "Please enter a postive Price.";
    } elseif (!$price_rating) {
        $_SESSION['add-gadget-types']  = "Please enter a Price Rating";
    } elseif ($price_rating < 1 || $price_rating > 10) {
        $_SESSION['add-gadget-types']  = "Please enter a Price Rating between 1- 10.";
    } elseif (!$longevity_rating) {
        $_SESSION['add-gadget-types']  = "Please enter a Longevity Rating";
    } elseif ($longevity_rating < 1 || $longevity_rating > 10) {
        $_SESSION['add-gadget-types']  = "Please enter a Longevity Rating between 1- 10.";
    } elseif (!$repairability_rating) {
        $_SESSION['add-gadget-types']  = "Please enter a Repairability Rating";
    } elseif ($repairability_rating < 1 || $repairability_rating > 10) {
        $_SESSION['add-gadget-types']  = "Please enter a Repairability Rating between 1- 10.";
    } elseif (!$functionality_rating) {
        $_SESSION['add-gadget-types']  = "Please enter a Functionality Rating";
    } elseif ($functionality_rating < 1 || $functionality_rating > 10) {
        $_SESSION['add-gadget-types']  = "Please enter a Functionality Rating between 1- 10.";
    } elseif (empty($gadget_img['name'])) {
        $_SESSION['add-gadget-types']  = "Please select an image.";
    } else {
        // Check if type already exists in the category
        $check_query = "SELECT id FROM gadget_types WHERE gadget_category_id = ? AND type_name = ?";
        $check_stmt = mysqli_prepare($connection, $check_query);
        mysqli_stmt_bind_param($check_stmt, "is", $gadget_category_id, $type_name);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);

        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $_SESSION['add-gadget-types'] = "Gadget Type '$type_name' already exists in this category.";
        } else {
            $time = time(); // Make each image name unique
            $imageName = $time . '_' . $gadget_img['name'];
            $imageTmpName = $gadget_img['tmp_name'];
            $imageDestinationPath = $_SERVER['DOCUMENT_ROOT'] . '/System/img/gadgets/' . $imageName;

            // Ensure it's an allowed image type
            $allowedFiles = ['png', 'jpg', 'jpeg'];
            $extension = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));

            if (in_array($extension, $allowedFiles)) {
                // Check file size
                if ($gadget_img['size'] < 3_000_000) {
                    move_uploaded_file($imageTmpName, $imageDestinationPath);

                    // Convert pros and cons arrays to JSON strings
                    $prosJson = json_encode($pros);
                    $consJson = json_encode($cons);

                    // Insert new type into database
                    $query = "INSERT INTO gadget_types (gadget_category_id, type_name, pros, cons, year_created, price, gadget_img) 
                              VALUES (?, ?, ?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($connection, $query);

                    if ($stmt) {
                        mysqli_stmt_bind_param($stmt, "isssdis", $gadget_category_id, $type_name, $prosJson, $consJson, $year_created, $price, $imageName);
                        $result = mysqli_stmt_execute($stmt);

                        if ($result) {
                            // Get the new gadget type ID
                            $new_type_id = mysqli_insert_id($connection);
                            
                            // Add initial admin rating
                            $rating_query = "INSERT INTO admin_gadget_ratings 
                                           (gadget_type_id, admin_id, price_rating, longevity_rating, 
                                            repairability_rating, functionality_rating) 
                                           VALUES (?, ?, ?, ?, ?, ?)";
                            
                            $rating_stmt = mysqli_prepare($connection, $rating_query);
                            mysqli_stmt_bind_param(
                                $rating_stmt, 
                                "iiiiii",
                                $new_type_id,
                                $_SESSION['admin-id'],
                                $price_rating,
                                $longevity_rating,
                                $repairability_rating,
                                $functionality_rating
                            );
                            $rating_result = mysqli_stmt_execute($rating_stmt);

                            if ($rating_result) {
                                // Calculate and update verified rating
                                $verified_rating = ($price_rating + $longevity_rating + $repairability_rating + $functionality_rating) / 4;
                                
                                $update_query = "UPDATE gadget_types SET verified_rating = ? WHERE id = ?";
                                $update_stmt = mysqli_prepare($connection, $update_query);
                                mysqli_stmt_bind_param($update_stmt, "di", $verified_rating, $new_type_id);
                                mysqli_stmt_execute($update_stmt);
                                mysqli_stmt_close($update_stmt);

                                // Log admin activity
                                log_admin_activity(
                                    $connection,
                                    $_SESSION['admin-id'],
                                    'ADD_GADGET_TYPE',
                                    "Added new gadget type: $type_name",
                                    'gadget_types',
                                    $new_type_id
                                );

                                $_SESSION['add-gadget-types-success'] = "Gadget type '$type_name' successfully added.";
                                header('Location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget-types.php?gadget_category_id=' . $gadget_category_id);
                                exit();
                            } else {
                                $_SESSION['add-gadget-types'] = "Error adding ratings.";
                            }
                            mysqli_stmt_close($rating_stmt);
                        } else {
                            $_SESSION['add-gadget-types'] = "Couldn't add gadget type.";
                        }
                        mysqli_stmt_close($stmt);
                    } else {
                        $_SESSION['add-gadget-types'] = "Error preparing statement: " . mysqli_error($connection);
                    }
                } else {
                    $_SESSION['add-gadget-types'] = "File size too big. Should be less than 3MB.";
                }
            } else {
                $_SESSION['add-gadget-types'] = "File should be PNG, JPG, or JPEG.";
            }
        }
        mysqli_stmt_close($check_stmt);
    }

    if (isset($_SESSION['add-gadget-types'])) {
        $_SESSION['add-gadget-types-data'] = $_POST;
        header('Location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget-types-add.php?gadget_category_id=' . $gadget_category_id);
        exit();
    }
} else {
    header('Location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget-types-add.php');
    exit();
}
