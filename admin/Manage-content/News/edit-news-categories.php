<?php
include '../../admin-sidebar.php'; // Database connection

if (isset($_GET['id'])) {
    // Use a prepared statement to safely fetch the category
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM news_category WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $results = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        $news_category = $_SESSION['edit-news-category-data']['news_category'] ?? $results['news_category'];
        $news_description = $_SESSION['edit-news-category-data']['description'] ?? $results['description'];
    } else {
        // Handle error in preparing the statement
        header('location: ' . ROOT_URL . 'admin/Manage-content/News/news-categories.php');
        die();
    }
} else {
    header('location: ' . ROOT_URL . 'admin/Manage-content/News/news-categories.php');
    die();
}

unset($_SESSION['edit-news-category-data']);
?>

<!-- START OF ADD-CATEGORY FORM -->
<link rel="stylesheet" href="<?= ROOT_URL ?>css/add-category-guides.css">

<div class="add-content-container">
    <h2>Edit a Category for News</h2>

    <!-- Error message -->
    <?php if (isset($_SESSION['edit-news-category'])) : ?>
        <div class="alert-message error">
            <p>
                <?= $_SESSION['edit-news-category'];
                unset($_SESSION['edit-news-category']); ?>
            </p>
        </div>
    <?php endif ?>

    <form action="<?= ROOT_URL ?>admin/Manage-content/News/edit-news-categories-logic.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= htmlspecialchars($results['id']) ?>">
        <input type="text" value="<?= ($news_category) ?>" name="news_category" placeholder=" News Category">
        <input type="text" value="<?= ($news_description) ?>" name="description" placeholder="Description">

        <button class="btn" name="submit" type="submit">Update</button>
    </form>
</div>

<script src="<?= ROOT_URL ?>js/alert.js"></script>

</body>

</html>