<?php
include '../../admin-sidebar.php';

if (isset($_GET['id'])) {
    $id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
    $query = "SELECT * FROM gadget_types WHERE id = ? AND deleted_at IS NULL";
    $stmt = mysqli_prepare($connection, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $type = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        // Decode JSON strings to arrays
        $pros = json_decode($type['pros'], true) ?? [];
        $cons = json_decode($type['cons'], true) ?? [];

        // Get form data from session if it exists (in case of validation error)
        $type_name = $_SESSION['edit-gadget-types-data']['type_name'] ?? $type['type_name'];
        $year_created = $_SESSION['edit-gadget-types-data']['year_created'] ?? $type['year_created'];
        $price = $_SESSION['edit-gadget-types-data']['price'] ?? $type['price'];

        unset($_SESSION['edit-gadget-types-data']);

        // After fetching type details
        $admin_id = $_SESSION['admin-id'];
        $rating_query = "SELECT * FROM admin_gadget_ratings WHERE gadget_type_id = ? AND admin_id = ?";
        $stmt = mysqli_prepare($connection, $rating_query);
        mysqli_stmt_bind_param($stmt, "ii", $id, $admin_id);
        mysqli_stmt_execute($stmt);
        $rating_result = mysqli_stmt_get_result($stmt);
        $admin_rating = mysqli_fetch_assoc($rating_result);

        // Use these variables in your form
        $price_rating = $admin_rating['price_rating'] ?? 0;
        $longevity_rating = $admin_rating['longevity_rating'] ?? 0;
        $repairability_rating = $admin_rating['repairability_rating'] ?? 0;
        $functionality_rating = $admin_rating['functionality_rating'] ?? 0;
    } else {
        header('location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget-types.php');
        die();
    }
} else {
    header('location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget-types.php');
    die();
}
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/edit-contents.css">

<div class="add-content-container">
    <h2>Edit Type: <?= htmlspecialchars($type['type_name']) ?></h2>

    <?php if (isset($_SESSION['edit-gadget-types'])) : ?>
        <div class="alert-message error">
            <p>
                <?= $_SESSION['edit-gadget-types'];
                unset($_SESSION['edit-gadget-types']); ?>
            </p>
        </div>
    <?php endif ?>

    <form action="<?= ROOT_URL ?>admin/Manage-content/Gadget/gadget-types-edit-logic.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= $type['id'] ?>">
        <input type="hidden" name="gadget_category_id" value="<?= $type['gadget_category_id'] ?>">
        <input type="hidden" name="previous_image" value="<?= $type['gadget_img'] ?>">

        <label>Type Name:</label>
        <input type="text" name="type_name" value="<?= htmlspecialchars($type_name) ?>">

        <!-- Pros Section -->
        <label>Pros:</label>
        <div id="pros-container">
            <?php foreach ($pros as $pro) : ?>
                <input type="text" name="pros[]" value="<?= htmlspecialchars($pro) ?>">
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="addPro()">Add More Pros</button>

        <!-- Cons Section -->
        <label>Cons:</label>
        <div id="cons-container">
            <?php foreach ($cons as $con) : ?>
                <input type="text" name="cons[]" value="<?= htmlspecialchars($con) ?>">
            <?php endforeach; ?>
        </div>
        <button type="button" onclick="addCon()">Add More Cons</button>

        <label>Ratings (1-10):</label>
        <label>Price:</label>
        <input type="number" name="price_rating" min="1" max="10" value="<?= htmlspecialchars($price_rating) ?>">

        <label>Longevity:</label>
        <input type="number" name="longevity_rating" min="1" max="10" value="<?= htmlspecialchars($longevity_rating) ?>">

        <label>Repairability:</label>
        <input type="number" name="repairability_rating" min="1" max="10" value="<?= htmlspecialchars($repairability_rating) ?>">

        <label>Functionality:</label>
        <input type="number" name="functionality_rating" min="1" max="10" value="<?= htmlspecialchars($functionality_rating) ?>">

        <label>Year Created:</label>
        <input type="number" name="year_created" max="<?= date('Y') ?>" value="<?= htmlspecialchars($year_created) ?>">

        <label>Price:</label>
        <input type="number" name="price" step="0.01" value="<?= htmlspecialchars($price) ?>">

        <div class="current-image">
            <p>Current Image:</p>
            <img src="<?= ROOT_URL ?>img/gadgets/<?= $type['gadget_img'] ?>" alt="Current Gadget Image">
        </div>

        <label>Upload New Image (leave empty to keep current):</label>
        <input type="file" name="gadget_img" accept="image/*">

        <button type="submit" name="submit">Update Type</button>
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