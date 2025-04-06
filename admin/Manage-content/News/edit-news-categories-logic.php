<?php
require __DIR__ . '/../../../config/database.php';
require __DIR__ . '/../../../Helper/admin-activity-logger.php';

if (isset($_POST['submit'])) {
    // Filtering user input data before validation
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);
    $news_category = filter_var($_POST['news_category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validation
    if (!$news_category) {
        $_SESSION['edit-news-category'] = "Please input a News Category";
    } elseif (!$description) {
        $_SESSION['edit-news-category'] = "Please input a Description";
    } else {
        // Check if category title already exists (case-insensitive)
        $checkCategoryQuery = "SELECT * FROM news_category WHERE news_category COLLATE utf8mb4_general_ci = ? AND id != ?";
        $stmt = mysqli_prepare($connection, $checkCategoryQuery);
        mysqli_stmt_bind_param($stmt, 'si', $news_category, $id);
        mysqli_stmt_execute($stmt);
        $checkCategoryResult = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($checkCategoryResult) > 0) {
            $_SESSION['edit-news-category'] = "News category already exists";
        } else {

            //update category and description
            $query = "UPDATE news_category SET news_category = ?, description = ? WHERE id = ? LIMIT 1";
            $stmt = mysqli_prepare($connection, $query);
            mysqli_stmt_bind_param($stmt, 'ssi', $news_category, $description, $id);

            // Execute Update Query
            if (isset($stmt) && mysqli_stmt_execute($stmt)) {
                // Log admin activity
                log_admin_activity(
                    $connection,
                    $_SESSION['admin-id'],
                    'EDIT_NEWS_CATEGORY',
                    "Updated news category: $news_category",
                    'news_category',
                    $id
                );

                unset($_SESSION['edit-news-category-data']); // Clear form data from session on success  
                $_SESSION['edit-news-category-success'] = "News category '$news_category' updated successfully";
                header('location: ' . ROOT_URL . 'admin/Manage-content/News/news-categories.php');
                die();
            } else {
                $_SESSION['edit-news-category'] = "Failed to update news category.";
            }

            mysqli_stmt_close($stmt);
        }
    }

    // Redirect back if there's an error
    if (isset($_SESSION['edit-news-category'])) {
        $_SESSION['edit-news-category-data'] = $_POST;
        header('location: ' . ROOT_URL . 'admin/Manage-content/News/edit-news-categories.php?id=' . $id);
        die();
    }
} else {
    // If button wasn't clicked, redirect to manage categories page
    header('location: ' . ROOT_URL . 'admin/Manage-content/News/news-categories.php');
    die();
}
