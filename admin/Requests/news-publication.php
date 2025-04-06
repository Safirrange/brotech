<?php
include '../admin-sidebar.php'; // Include Sidebar

$notifQuery = "SELECT n.*, news.deleted_at as news_deleted_at
               FROM notifications n
               LEFT JOIN news ON n.entity_id = news.id
               WHERE n.entity_type = 'news'
               AND n.recipient = 'admin'
               AND news.deleted_at IS NULL
               ORDER BY n.created_at DESC";

$notifStmt = mysqli_prepare($connection, $notifQuery);
mysqli_stmt_execute($notifStmt);
$notifResult = mysqli_stmt_get_result($notifStmt);
?>

<?php if (isset($_SESSION['updated'])): ?>
    <div class="alert-message success">
        <p>
            <?= $_SESSION['updated'];
            unset($_SESSION['updated']);
            ?>
        </p>
    </div>
<?php endif ?>

<?php if (isset($_SESSION['failed'])): ?>
    <div class="alert-message error">
        <p>
            <?= $_SESSION['failed'];
            unset($_SESSION['failed']);
            ?>
        </p>
    </div>
<?php endif ?>


<table class="table">
    <thead>
        <tr>
            <th>Status</th>
            <th>Message</th>
            <th>Date</th>
            <th>Action</th>
            <th>Approval Status</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($notif = mysqli_fetch_assoc($notifResult)): ?>
            <tr class="<?= $notif['is_read'] ? 'table-light' : 'table-warning' ?>">
                <td>
                    <?= $notif['is_read'] ? "✅ Read" : "🔔 Unread" ?>
                </td>
                <td><?= $notif['message'] ?></td>
                <td><?= date('M d, Y H:i', strtotime($notif['created_at'])) ?></td>
                <td>
                    <?php if ($notif['news_deleted_at'] === null): ?>
                        <a href="<?= $notif['link'] ?>&notification_id=<?= $notif['id'] ?>" class="btn btn-primary btn-sm">View</a>
                    <?php else: ?>
                        <span class="text-muted">News Deleted</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php
                    if ($notif['status'] == 'approved') {
                        echo '<span style="color: green; font-weight: bold;">✅ Approved</span>';
                    } elseif ($notif['status'] == 'pending') {
                        echo '<span style="color: orange; font-weight: bold;">⏳ Pending</span>';
                    } elseif ($notif['status'] == 'rejected') {
                        echo '<span style="color: red; font-weight: bold;">❌ Rejected</span>';
                    } else {
                        echo '<span style="color: gray; font-weight: bold;">Unknown</span>';
                    }
                    ?>
                </td>

            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script src="<?=ROOT_URL?>js/alert.js"></script>