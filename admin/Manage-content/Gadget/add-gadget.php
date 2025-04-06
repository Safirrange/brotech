<?php
include '../../admin-sidebar.php'; // Database connection

// Get back form data if there was an error
$gadget = $_SESSION['add-gadget-category-data']['gadget_category'] ?? null;
$title = $_SESSION['add-gadget-category-data']['page_title'] ?? null;
unset($_SESSION['add-gadget-category-data']);
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/add-content.css">

<div class="add-content-container">
    <h2>Add a Category for Repair</h2>

    <!-- Error message -->
    <?php if (isset($_SESSION['add-gadget-category'])) : ?>
        <div class="alert-message error">
            <p>
                <?= $_SESSION['add-gadget-category'];
                unset($_SESSION['add-gadget-category']); ?>
            </p>
        </div>
    <?php endif ?>

    <form action="<?= ROOT_URL ?>admin/Manage-content/Gadget/add-gadget-logic.php" method="POST" enctype="multipart/form-data">
        <input type="text" value="<?= htmlspecialchars($gadget) ?>" name="gadget_category" placeholder="Gadget Type">

        <div class="upload-box-container">
            <label><b>Icon / Gadget image</b></label>
            <div class="upload-box" onclick="this.nextElementSibling.click()">
                <img class="preview-img" src="" alt="Preview" style="display: none;">
                <span class="upload-text">📹 Click to Upload</span>
            </div>
            <input type="file" name="gadget_img" accept=".png, .jpg, .jpeg" class="file-input" onchange="previewFile(this)" style="display: none;">
        </div>

        <input type="text" value="<?= htmlspecialchars($title) ?>" name="page_title" placeholder="Page Title">

        <div class="upload-box-container">
            <label><b>Page image</b></label>
            <div class="upload-box" onclick="this.nextElementSibling.click()">
                <img class="preview-img" src="" alt="Preview" style="display: none;">
                <span class="upload-text">📹 Click to Upload</span>
            </div>
            <input type="file" name="page_img" accept=".png, .jpg, .jpeg" class="file-input" onchange="previewFile(this)" style="display: none;">
        </div>

        <button class="btn" name="submit" type="submit">Add</button>
    </form>
</div>

<script src="<?= ROOT_URL ?>js/alert.js"></script>

</body>

</html>