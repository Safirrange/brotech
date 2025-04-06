<?php
include '../admin-sidebar.php'; // Include Sidebar
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/admin-dashboard.css">
<style>
    .red-dot {
        display: inline-block;
        width: 10px;
        height: 10px;
        background-color: red;
        border-radius: 50%;
        margin-left: 5px;
    }

    .notif-badge {
        background-color: red;
        color: white;
        border-radius: 12px;
        padding: 2px 6px;
        font-size: 12px;
        margin-left: 5px;
    }
</style>

<div class="content">
    <h1>Requests</h1>
    <div class="card-container">
        <?php
        // Check for pending professional applications
        $professionalRequestQuery = "SELECT COUNT(*) AS pending_count FROM notifications WHERE status = 'pending' AND type = 'professional'";
        $professionalRequestResult = mysqli_query($connection, $professionalRequestQuery);
        $professionalRequestData = mysqli_fetch_assoc($professionalRequestResult);
        $pendingProfessionalRequests = $professionalRequestData['pending_count'];

        // Check for pending news publications
        $newsRequestQuery = "SELECT COUNT(*) AS pending_count FROM notifications WHERE status = 'pending' AND entity_type = 'news'";
        $newsRequestResult = mysqli_query($connection, $newsRequestQuery);
        $newsRequestData = mysqli_fetch_assoc($newsRequestResult);
        $pendingNewsRequests = $newsRequestData['pending_count'];

        // Check for pending story publications
        $storyRequestQuery = "SELECT COUNT(*) AS pending_count FROM notifications WHERE status = 'pending' AND type = 'story'";
        $storyRequestResult = mysqli_query($connection, $storyRequestQuery);
        $storyRequestData = mysqli_fetch_assoc($storyRequestResult);
        $pendingStoryRequests = $storyRequestData['pending_count'];
        ?>

        <!-- Professional Applications Card -->
        <a href="<?= ROOT_URL ?>admin/professional-applications.php">
            <div class="card">
                <h3>Professionals
                    <?php if ($pendingProfessionalRequests > 0): ?>
                        <span class="red-dot"></span>
                        <span class="notif-badge"><?= $pendingProfessionalRequests ?></span>
                    <?php endif; ?>
                </h3>
                <p>Approve applications of users to become professionals</p>
            </div>
        </a>

        <!-- News Publication Card -->
        <a href="<?= ROOT_URL ?>admin/Requests/news-publication.php">
            <div class="card">
                <h3>Publish News
                    <?php if ($pendingNewsRequests > 0): ?>
                        <span class="red-dot"></span>
                        <span class="notif-badge"><?= $pendingNewsRequests ?></span>
                    <?php endif; ?>
                </h3>
                <p>Approve to publish news written by our users.</p>
            </div>
        </a>

        <!-- Story Publication Card -->
        <a href="<?= ROOT_URL ?>admin/story-publication.php">
            <div class="card">
                <h3>Publish Stories
                    <?php if ($pendingStoryRequests > 0): ?>
                        <span class="red-dot"></span>
                        <span class="notif-badge"><?= $pendingStoryRequests ?></span>
                    <?php endif; ?>
                </h3>
                <p>Approve to publish stories written by our users.</p>
            </div>
        </a>
    </div>
</div>

</body>

</html>