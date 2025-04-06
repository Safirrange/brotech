<?php
require '../../../config/database.php';

if (isset($_GET['type_id'])) {
    $type_id = intval($_GET['type_id']);

    // Fetch type and specs details
    $specs_query = "SELECT *
                   FROM guide_types 
                   WHERE id = ? AND deleted_at IS NULL";

    $stmt = mysqli_prepare($connection, $specs_query);
    mysqli_stmt_bind_param($stmt, "i", $type_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($specs = mysqli_fetch_assoc($result)) {
?>

        <link rel="stylesheet" href="<?= ROOT_URL ?>css/manage-contents.css">
        <div class="specs-details">
            <h2><?= htmlspecialchars($specs['name']) ?> Specifications</h2>

            <div class="specs-grid">
                <div class="specs-group">
                    <h3>Display</h3>
                    <p><?= htmlspecialchars($specs['display'] ?? 'N/A') ?></p>
                </div>

                <div class="specs-group">
                    <h3>Platform</h3>
                    <p><strong>OS:</strong> <?= htmlspecialchars($specs['os'] ?? 'N/A') ?></p>
                    <p><strong>Chipset:</strong> <?= htmlspecialchars($specs['chipset'] ?? 'N/A') ?></p>
                    <p><strong>CPU:</strong> <?= htmlspecialchars($specs['cpu'] ?? 'N/A') ?></p>
                    <p><strong>GPU:</strong> <?= htmlspecialchars($specs['gpu'] ?? 'N/A') ?></p>
                </div>

                <div class="specs-group">
                    <h3>Memory</h3>
                    <p><strong>RAM:</strong> <?= htmlspecialchars($specs['ram'] ?? 'N/A') ?></p>
                    <p><strong>Storage:</strong> <?= htmlspecialchars($specs['storage'] ?? 'N/A') ?></p>
                </div>

                <div class="specs-group">
                    <h3>Camera</h3>
                    <p><strong>Main:</strong> <?= htmlspecialchars($specs['main_camera'] ?? 'N/A') ?></p>
                    <p><strong>Selfie:</strong> <?= htmlspecialchars($specs['selfie_camera'] ?? 'N/A') ?></p>
                </div>

                <div class="specs-group">
                    <h3>Battery</h3>
                    <p><?= htmlspecialchars($specs['battery'] ?? 'N/A') ?></p>
                </div>

                <div class="specs-group">
                    <h3>Suggested Retail Price (SRP)</h3>
                    <p><?= htmlspecialchars($specs['battery'] ?? 'N/A') ?></p>
                </div>
            </div>

        </div>
<?php
    } else {
        echo "<p>No specifications found for this device.</p>";
    }
} else {
    echo "<p>Invalid request</p>";
}
?>