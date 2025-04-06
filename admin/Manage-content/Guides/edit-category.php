<?php
include '../../admin-sidebar.php'; // Database connection

if (isset($_GET['id'])) {
    // Use a prepared statement to safely fetch the category
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM guide_categories WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $results = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        $category = $_SESSION['edit-content-category-data']['category'] ?? $results['category'];
        $description = $_SESSION['edit-content-category-data']['description'] ?? $results['description'];
        $image = $_SESSION['edit-content-category-data']['image'] ?? $results['image'];
    } else {
        // Handle error in preparing the statement
        header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/category.php');
        die();
    }
} else {
    header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/category.php');
    die();
}

unset($_SESSION['edit-content-category-data']);
?>

<!-- START OF ADD-CATEGORY FORM -->
<link rel="stylesheet" href="<?= ROOT_URL ?>css/add-category-guides.css">

<div class="add-content-container">
    <h2>Edit a Category for Repair</h2>

    <!-- Error message -->
    <?php if (isset($_SESSION['edit-content-category'])) : ?>
        <div class="alert-message error">
            <p>
                <?= $_SESSION['edit-content-category'];
                unset($_SESSION['edit-content-category']); ?>
            </p>
        </div>
    <?php endif ?>

    <form action="<?= ROOT_URL ?>admin/Manage-content/Guides/edit-category-logic.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= htmlspecialchars($results['id']) ?>">
        <input type="text" value="<?= htmlspecialchars($category) ?>" name="category" placeholder="Repair Type">
        <textarea rows="4" name="description" placeholder="Description"><?= htmlspecialchars($description) ?></textarea>

        <div class="upload-box-container">
            <label><b>Icon</b></label>
            <div class="upload-box" onclick="this.nextElementSibling.click()">
                <img class="preview-img" src="<?= ROOT_URL ?>img/homepage/<?= htmlspecialchars($image) ?>"
                    alt="Preview"
                    style="<?= $image ? 'display: block;' : 'display: none;' ?>">
                <span class="upload-text" style="<?= $image ? 'display: none;' : 'display: block;' ?>">📹 Click to Upload</span>
            </div>
            <input type="file" name="image" accept=".png, .jpg, .jpeg" class="file-input" onchange="previewFile(this)" style="display: none;">
        </div>


        <button class="btn" name="submit" type="submit">Update</button>
    </form>
</div>

<script src="<?= ROOT_URL ?>js/alert.js"></script>

</body>

</html>