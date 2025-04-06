<?php
include 'admin-sidebar.php'; // Include Sidebar

// Fetch maintenance status
$query = "SELECT is_enabled FROM maintenance_mode WHERE id = 1";
$result = mysqli_query($connection, $query);
$data = mysqli_fetch_assoc($result);
$isMaintenanceEnabled = $data['is_enabled'];
?>

<body>
    <h1>Settings</h1>

    <div>
        <!-- Display current status -->
        <p>Current Status: <strong><?= $isMaintenanceEnabled ? 'Maintenance Mode Enabled' : 'Maintenance Mode Disabled' ?></strong></p>

        <!-- Confirmation Form -->
        <form action="<?= ROOT_URL ?>admin/toggle-maintenance.php" method="POST" onsubmit="return confirmToggle();">
            <button type="submit">
                <?= $isMaintenanceEnabled ? 'Disable Maintenance Mode' : 'Enable Maintenance Mode' ?>
            </button>
        </form>
    </div>

    <script>
        function confirmToggle() {
            let action = "<?= $isMaintenanceEnabled ? 'disable' : 'enable' ?>";
            return confirm(`Are you sure you want to ${action} Maintenance Mode?`);
        }
    </script>
</body>