<?php
include '../admin-sidebar.php'; // Include Sidebar
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/admin-dashboard.css">
<div class="content">
    <h1>Manage Contents on Your Webpage (View / Add / Edit / Delete)</h1>
    <div class="card-container">

        <a href="<?= ROOT_URL ?>admin/Manage-content/Guides/category.php">
            <div class="card">
                <h3>Repair Guides</h3>
                <p>Appliances, Technology, Electronics, etc...</p>
            </div>
        </a>


        <a href="<?= ROOT_URL ?>admin/Manage-content/News/news-categories.php">
            <div class="card">
                <h3>News Category</h3>
                <p>how To's, Teardown, Tech Hacks, etc...</p>
            </div>
        </a>


        <a href="<?= ROOT_URL ?>admin/Manage-content/Gadget/gadget.php">
            <div class="card">
                <h3>Gadget Categories</h3>
                <p>Phone, Tablets, Earbuds, etc...</p>
            </div>
        </a>

    </div>
</div>

</body>

</html>