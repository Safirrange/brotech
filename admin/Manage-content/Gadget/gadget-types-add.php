<?php
include '../../admin-sidebar.php'; // Database connection

// Check if category_id is provided in URL
if (!isset($_GET['gadget_category_id']) || empty($_GET['gadget_category_id'])) {
    header('location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget.php');
    die();
}

$gadget_category_id = intval($_GET['gadget_category_id']);
// Fetch category details
$gadget_category_query = "SELECT * FROM gadget_category WHERE id = $gadget_category_id AND deleted_at IS NULL";
$gadget_category_result = mysqli_query($connection, $gadget_category_query);
$gadget_category = mysqli_fetch_assoc($gadget_category_result);

$type_name = $_SESSION['add-gadget-types-data']['type_name'] ?? null;
$pros = $_SESSION['add-gadget-types-data']['pros'] ?? [];
$cons = $_SESSION['add-gadget-types-data']['cons'] ?? [];
$price_rating = $_SESSION['add-gadget-types-data']['price_rating'] ?? null;
$longevity_rating = $_SESSION['add-gadget-types-data']['longevity_rating'] ?? null;
$repairability_rating = $_SESSION['add-gadget-types-data']['repairability_rating'] ?? null;
$functionality_rating = $_SESSION['add-gadget-types-data']['functionality_rating'] ?? null;
$year_created = $_SESSION['add-gadget-types-data']['year_created'] ?? null;
$price = $_SESSION['add-gadget-types-data']['price'] ?? null;
unset($_SESSION['add-gadget-types-data']);

if (!$gadget_category) {
    header('location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget-types.php');
    die();
}
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/edit-contents.css">

<div class="add-content-container">
    <h2>Add a Type under <?= htmlspecialchars($gadget_category['gadget_category']) ?></h2>

    <!-- Error message -->
    <?php if (isset($_SESSION['add-gadget-types'])) : ?>
        <div class="alert-message error">
            <p>
                <?= $_SESSION['add-gadget-types'];
                unset($_SESSION['add-gadget-types']); ?>
            </p>
        </div>
    <?php endif ?>

    <form action="<?= ROOT_URL ?>admin/Manage-content/Gadget/gadget-types-add-logic.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="gadget_category_id" value="<?= htmlspecialchars($gadget_category_id) ?>">

        <label>Type Name:</label>
        <input type="text" name="type_name" value="<?= htmlspecialchars($type_name) ?>">

        <!-- Pros Section -->
        <label>Pros (Add multiple pros):</label>
        <div id="pros-container">
            <?php foreach ($pros as $pro) : ?>
                <input type="text" name="pros[]" placeholder="Enter a pro" value="<?= htmlspecialchars($pro) ?>">
            <?php endforeach; ?>
            <?php if (empty($pros)) : ?>
                <input type="text" name="pros[]" placeholder="Enter a pro">
            <?php endif; ?>
        </div>
        <button type="button" onclick="addPro()">Add More Pros</button>

        <label>Cons (Add multiple cons):</label>
        <div id="cons-container">
            <?php foreach ($cons as $con) : ?>
                <input type="text" name="cons[]" placeholder="Enter a con" value="<?= htmlspecialchars($con) ?>">
            <?php endforeach; ?>
            <?php if (empty($cons)) : ?>
                <input type="text" name="cons[]" placeholder="Enter a con">
            <?php endif; ?>
        </div>
        <button type="button" onclick="addCon()">Add More Cons</button>

        <label>Ratings (1 - 10):</label>
        <label>Price:</label>
        <input type="number" name="price_rating" value="<?= htmlspecialchars($price_rating) ?>">

        <label>Longevity:</label>
        <input type="number" name="longevity_rating" value="<?= htmlspecialchars($longevity_rating) ?>">

        <label>Repairability:</label>
        <input type="number" name="repairability_rating" value="<?= htmlspecialchars($repairability_rating) ?>">

        <label>Functionality:</label>
        <input type="number" name="functionality_rating" value="<?= htmlspecialchars($functionality_rating) ?>">

        <label>Year Created:</label>
        <input type="number" name="year_created" max="<?= date('Y') ?>" placeholder="e.g., 2024" value="<?= htmlspecialchars($year_created) ?>">

        <label>Price:</label>
        <input type="number" name="price" step="0.01" placeholder="e.g., 999.99" value="<?= htmlspecialchars($price) ?>">

        <label>Upload Gadget Image:</label>
        <input type="file" name="gadget_img" accept="image/*">

        <button type="submit" name="submit">Add Type</button>
    </form>
</div>

<script>
    function addPro() {
        const prosContainer = document.getElementById('pros-container');
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'pros[]';
        input.placeholder = 'Enter a pro';
        prosContainer.appendChild(input);
    }

    function addCon() {
        const consContainer = document.getElementById('cons-container');
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'cons[]';
        input.placeholder = 'Enter a con';
        consContainer.appendChild(input);
    }
</script>


<script src="<?= ROOT_URL ?>js/alert.js"></script>

</body>

</html>