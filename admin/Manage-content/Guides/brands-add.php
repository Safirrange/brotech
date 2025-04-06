<?php
include '../../admin-sidebar.php'; // Database connection

// Check if category_id is provided in URL
if (!isset($_GET['category_id']) || empty($_GET['category_id'])) {
    header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/category.php');
    die();
}

$category_id = intval($_GET['category_id']);
// Fetch category details
$category_query = "SELECT id, category FROM guide_categories WHERE id = $category_id AND deleted_at IS NULL";
$category_result = mysqli_query($connection, $category_query);
$category = mysqli_fetch_assoc($category_result);

$brand = $_SESSION['add-content-brands-data']['brand'] ?? null;
$description = $_SESSION['add-content-brands-data']['description'] ?? null;
unset($_SESSION['add-content-brands-data']);

if (!$category) {
    header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/category.php');
    die();
}
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/edit-contents.css">

<div class="add-content-container">
    <h2>Add a Type under <?= htmlspecialchars($category['category']) ?></h2>

    <!-- Error message -->
    <?php if (isset($_SESSION['add-content-brands'])) : ?>
        <div class="alert-message error">
            <p>
                <?= $_SESSION['add-content-brands'];
                unset($_SESSION['add-content-brands']); ?>
            </p>
        </div>
    <?php endif ?>

    <form action="<?= ROOT_URL ?>admin/Manage-content/Guides/brands-add-logic.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="categoryId" value="<?= htmlspecialchars($category_id) ?>">
        <label>Type Name:</label>
        <input type="text" name="brand" value="<?= htmlspecialchars($brand) ?>">

        <label>Description:</label>
        <input type="text" name="description" value="<?= htmlspecialchars($description) ?>">

        <label for="image">Upload Logo:</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit" name="submit">Add Type</button>
    </form>
</div>

<script src="<?= ROOT_URL ?>js/alert.js"></script>

</body>

</html>