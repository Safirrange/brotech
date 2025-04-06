<?php
include '../../admin-sidebar.php'; // Database connection

// Get back form data if there was an error
$category = $_SESSION['add-content-category-data']['category'] ?? null;
$description = $_SESSION['add-content-category-data']['description'] ?? null;
unset($_SESSION['add-content-category-data']);
?>

<!-- START OF ADD-CATEGORY FORM -->
<link rel="stylesheet" href="<?= ROOT_URL ?>css/add-content.css">

<div class="add-content-container">
    <h2>Add a Category for Repair</h2>

    <!-- Error message -->
    <?php if (isset($_SESSION['add-content-category'])) : ?>
        <div class="alert-message error">
            <p>
                <?= $_SESSION['add-content-category'];
                unset($_SESSION['add-content-category']); ?>
            </p>
        </div>
    <?php endif ?>

    <form action="<?= ROOT_URL ?>admin/Manage-content/Guides/add-category-logic.php" method="POST" enctype="multipart/form-data">
        <input type="text" value="<?= htmlspecialchars($category) ?>" name="category" placeholder="Repair Type">
        <textarea rows="4" name="description" placeholder="Description"><?= htmlspecialchars($description) ?></textarea>

        <div class="upload-box-container">
            <label><b>Icon</b></label>
            <div class="upload-box" onclick="this.nextElementSibling.click()">
                <img class="preview-img" src="" alt="Preview" style="display: none;">
                <span class="upload-text">📹 Click to Upload</span>
            </div>
            <input type="file" name="image" accept=".png, .jpg, .jpeg" class="file-input" onchange="previewFile(this)" style="display: none;">
        </div>

        <button class="btn" name="submit" type="submit">Add</button>
    </form>
</div>

<script src="<?= ROOT_URL ?>js/alert.js"></script>

</body>

</html>