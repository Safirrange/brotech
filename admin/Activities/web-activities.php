<?php
include '../admin-sidebar.php'; // Include Sidebar
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/admin-dashboard.css">
<div class="content">
    <h1>See activities on Your Webpage (View)</h1>
    <div class="card-container">

        <a href="<?= ROOT_URL ?>admin/Activities/user.php">
            <div class="card">
                <h3>User Activities</h3>
                <p>Publishing, Rating, Commenting, etc...</p>
            </div>
        </a>

        <a href="<?= ROOT_URL ?>admin/Activities/admin.php">
            <div class="card">
                <h3>Admin Activities</h3>
                <p>Viewing, Adding, Editing, etc...</p>
            </div>
        </a>

    </div>
</div>

</body>

</html>