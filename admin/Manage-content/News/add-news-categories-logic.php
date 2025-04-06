<?php
require __DIR__ . '/../../../config/database.php';

if (isset($_POST['submit'])) {
    // Get form data
    $news_category = filter_var($_POST['news_category'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $description = filter_var($_POST['description'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    // Validations
    if (!$news_category) {
        $_SESSION['add-news-category'] = "Enter News Category";
    } elseif (!$description) {
        $_SESSION['add-news-category'] = "Enter Description";
    } else {
        // Check if category exists, including soft-deleted ones
        $checkCategoriesQuery = "SELECT id, deleted_at FROM news_category WHERE LOWER(news_category) = LOWER(?)";
        $stmt = mysqli_prepare($connection, $checkCategoriesQuery);
        mysqli_stmt_bind_param($stmt, 's', $news_category);
        mysqli_stmt_execute($stmt);
        $checkCategoriesResult = mysqli_stmt_get_result($stmt);
        $existingCategory = mysqli_fetch_assoc($checkCategoriesResult);

        if ($existingCategory) {
            if (!is_null($existingCategory['deleted_at'])) {
                // If category exists but was soft deleted, restore it
                $restoreQuery = "UPDATE news_category SET deleted_at = NULL WHERE id = ?";
                $restoreStmt = mysqli_prepare($connection, $restoreQuery);
                mysqli_stmt_bind_param($restoreStmt, 'i', $existingCategory['id']);
                $restoreResult = mysqli_stmt_execute($restoreStmt);

                if ($restoreResult) {
                    $_SESSION['add-news-category-success'] = "News category '$news_category' has been restored!";
                    header('Location: ' . ROOT_URL . 'admin/Manage-content/News/news-categories.php');
                    exit();
                } else {
                    $_SESSION['add-news-category'] = "Error restoring category.";
                }
            } else {
                $_SESSION['add-news-category'] = "News category '$news_category' already exists!";
            }
        } else {

            // Insert category into the database
            $query = "INSERT INTO news_category (news_category, description) VALUES (?, ?)";
            $stmt = mysqli_prepare($connection, $query);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'ss', $news_category, $description);
                $result = mysqli_stmt_execute($stmt);

                if ($result) {
                    $_SESSION['add-news-category-success'] = "News category '$news_category' successfully added";
                    header('Location: ' . ROOT_URL . 'admin/Manage-content/News/news-categories.php');
                    exit();
                } else {
                    $_SESSION['add-news-category'] = "Couldn't add category.";
                }
            } else {
                $_SESSION['add-news-category'] = "Error preparing statement: " . mysqli_error($connection);
            }
        }
    }

    // Redirect back if there was an error
    if (isset($_SESSION['add-news-category'])) {
        $_SESSION['add-news-category-data'] = $_POST;
        header('Location: ' . ROOT_URL . 'admin/Manage-content/News/add-news-categories.php');
        exit();
    }
} else {
    header('Location: ' . ROOT_URL . 'admin/Manage-content/News/add-news-categories.php');
    exit();
}
