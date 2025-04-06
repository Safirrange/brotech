<?php
include '../../admin-sidebar.php'; // Database connection

// Check if category_id is provided in URL
if (!isset($_GET['category_id']) || empty($_GET['category_id'])) {
    header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/category.php');
    die();
} elseif (!isset($_GET['brand_id']) || empty($_GET['brand_id'])) {
    header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/brands.php?category_id=' . $_GET['category_id']);
    die();
}

$category_id = intval($_GET['category_id']);
// Fetch category details
$category_query = "SELECT * FROM guide_categories WHERE id = $category_id AND deleted_at IS NULL";
$category_result = mysqli_query($connection, $category_query);
$category = mysqli_fetch_assoc($category_result);


$brand_id = intval($_GET['brand_id']);
// Fetch category details
$brand_query = "SELECT * FROM guide_brands WHERE id = $brand_id AND deleted_at IS NULL";
$brand_result = mysqli_query($connection, $brand_query);
$brand = mysqli_fetch_assoc($brand_result);

$type = $_SESSION['add-types-data']['type'] ?? null;
$released_date = $_SESSION['add-types-data']['released_date'] ?? null;
unset($_SESSION['add-types-data']);

if (!$category || !$brand) {
    header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/category.php');
    die();
}
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/edit-contents.css">

<div class="add-content-container">
    <h2>Add a Type under <?= htmlspecialchars($brand['brand']) ?></h2>

    <!-- Error message -->
    <?php if (isset($_SESSION['add-type-error'])) : ?>
        <div class="alert-message error">
            <p>
                <?= $_SESSION['add-type-error'];
                unset($_SESSION['add-type-error']); ?>
            </p>
        </div>
    <?php endif ?>

    <form action="<?= ROOT_URL ?>admin/Manage-content/Guides/type-add-logic.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="category_id" value="<?= htmlspecialchars($category_id) ?>">
        <input type="hidden" name="brand_id" value="<?= htmlspecialchars($brand_id) ?>">
        <input type="hidden" name="brand_name" value="<?= htmlspecialchars($brand['brand']) ?>">
        <label>Type Name:</label>
        <input type="text" name="type" value="<?= htmlspecialchars($type) ?>">

        <label>Release Date:</label>
        <input type="number" name="released_date" max="<?= date('Y') ?>" placeholder="e.g., 2024" value="<?= htmlspecialchars($released_date) ?>">

        <label for="image">Upload Logo:</label>
        <input type="file" name="image" accept="image/*">

        <button type="submit" name="submit">Add Type</button>
    </form>
</div>

<script src="<?= ROOT_URL ?>js/alert.js"></script>

</body>

</html>