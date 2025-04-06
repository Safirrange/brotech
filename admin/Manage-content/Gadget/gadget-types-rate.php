<?php
require __DIR__ . '/../../../config/database.php';
require __DIR__ . '/../../../Helper/admin-activity-logger.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['admin-id'])) {
    $admin_id = $_SESSION['admin-id'];
    $gadget_type_id = filter_var($_POST['gadget_type_id'], FILTER_SANITIZE_NUMBER_INT);
    $gadget_type_name = filter_var($_POST['gadget_type_name'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $price_rating = filter_var($_POST['price_rating'], FILTER_SANITIZE_NUMBER_INT);
    $longevity_rating = filter_var($_POST['longevity_rating'], FILTER_SANITIZE_NUMBER_INT);
    $repairability_rating = filter_var($_POST['repairability_rating'], FILTER_SANITIZE_NUMBER_INT);
    $functionality_rating = filter_var($_POST['functionality_rating'], FILTER_SANITIZE_NUMBER_INT);

    // Insert rating
    $query = "INSERT INTO admin_gadget_ratings (
        gadget_type_id, admin_id, price_rating, longevity_rating, 
        repairability_rating, functionality_rating
    ) VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param(
        $stmt,
        "iiiiii",
        $gadget_type_id,
        $admin_id,
        $price_rating,
        $longevity_rating,
        $repairability_rating,
        $functionality_rating
    );

    if (mysqli_stmt_execute($stmt)) {
        // Update verified rating in gadget_types
        updateVerifiedRating($connection, $gadget_type_id);

        // Log activity with gadget name
        log_admin_activity(
            $connection,
            $admin_id,
            'GADGET_RATING',
            "Rated gadget type: $gadget_type_name",
            'gadget_types',
            $gadget_type_id
        );

        $_SESSION['rating-success'] = "Rating submitted successfully for $gadget_type_name";
    } else {
        $_SESSION['rating-error'] = "Failed to submit rating";
    }

    header('Location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget-types.php?gadget_category_id=' . $_POST['gadget_category_id']);
    exit();
}

function updateVerifiedRating($connection, $gadget_type_id)
{
    $query = "UPDATE gadget_types SET verified_rating = (
        SELECT AVG((price_rating + longevity_rating + repairability_rating + functionality_rating)/4)
        FROM admin_gadget_ratings
        WHERE gadget_type_id = ?
    ) WHERE id = ?";

    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, "ii", $gadget_type_id, $gadget_type_id);
    mysqli_stmt_execute($stmt);
}
