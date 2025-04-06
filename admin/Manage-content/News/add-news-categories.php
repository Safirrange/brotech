<?php
include '../../admin-sidebar.php'; // Database connection

// Get back form data if there was an error
$news_category = $_SESSION['add-news-category-data']['news_category'] ?? null;
$description = $_SESSION['add-news-category-data']['description'] ?? null;
unset($_SESSION['add-news-category-data']);
?>

<!-- START OF ADD-CATEGORY FORM -->
<link rel="stylesheet" href="<?= ROOT_URL ?>css/add-category-guides.css">

<div class="add-content-container">
    <h2>Add a Category for News</h2>

    <!-- Error message -->
    <?php if (isset($_SESSION['add-news-category'])) : ?>
        <div class="alert-message error">
            <p>
                <?= $_SESSION['add-news-category'];
                unset($_SESSION['add-news-category']); ?>
            </p>
        </div>
    <?php endif ?>

    <form action="<?= ROOT_URL ?>admin/Manage-content/News/add-news-categories-logic.php" method="POST" enctype="multipart/form-data">
        <input type="text" value="<?= htmlspecialchars($news_category) ?>" name="news_category" placeholder="News Type">
        <textarea rows="4" name="description" placeholder="Description"><?= htmlspecialchars($description) ?></textarea>    
        <button class="btn" name="submit" type="submit">Add</button>
    </form>
</div>

<script src="<?=ROOT_URL?>js/alert.js"></script>

</body>

</html>