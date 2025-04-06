<?php
include '../../admin-sidebar.php'; // Database connection

if (isset($_GET['type_id'])) {
    // Use a prepared statement to safely fetch the category
    $id = filter_var($_GET['type_id'], FILTER_SANITIZE_NUMBER_INT);
    $brand_id = filter_var($_GET['brand_id'], FILTER_SANITIZE_NUMBER_INT);
    $category_id = filter_var($_GET['category_id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM guide_types WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $results = mysqli_fetch_assoc($result);

        mysqli_stmt_close($stmt);

        $type = $_SESSION['edit-type-data']['type'] ?? $results['name'];
        $released_date = $_SESSION['edit-type-data']['released_date'] ?? $results['released_date'];
        $image = $_SESSION['edit-type-data']['image'] ?? $results['image'];
    } else {
        // Handle error in preparing the statement
        header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/type.php?brand_id=' . $brand_id . '&category_id=' . $category_id);
        die();
    }
} else {
    header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/type.php?brand_id=' . $brand_id . '&category_id=' . $category_id);
    die();
}

unset($_SESSION['edit-type-data']);
?>

<!-- START OF ADD-CATEGORY FORM -->
<link rel="stylesheet" href="<?= ROOT_URL ?>css/add-category-guides.css">

<div class="add-content-container">
    <h2>Edit <?= $type ?> for Repair Guide</h2>

    <!-- Error message -->
    <?php if (isset($_SESSION['edit-type-error'])) : ?>
        <div class="alert-message error">
            <p>
                <?= $_SESSION['edit-type-error'];
                unset($_SESSION['edit-type-error']); ?>
            </p>
        </div>
    <?php endif ?>

    <form action="<?= ROOT_URL ?>admin/Manage-content/Guides/type-edit-logic.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="type_id" value="<?= htmlspecialchars($results['id']) ?>">
        <input type="hidden" name="category_id" value="<?= $category_id ?>">
        <input type="hidden" name="brand_id" value="<?= $brand_id ?>">
        <input type="text" value="<?= htmlspecialchars($type) ?>" name="type" placeholder="Type">

        <label>Year Created:</label>
        <input type="number" name="released_date" max="<?= date('Y') ?>" value="<?= htmlspecialchars($released_date) ?>">

        <div class="upload-box-container">
            <label><b>Icon</b></label>
            <div class="upload-box" onclick="this.nextElementSibling.click()">
                <img class="preview-img" src="<?= ROOT_URL ?>img/guides/<?= htmlspecialchars($image) ?>"
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