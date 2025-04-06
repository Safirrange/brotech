<?php
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../Helper/admin-activity-logger.php';

if (!isset($_SESSION['admin-id'])) {
    header('Location: ' . ROOT_URL . 'admin/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entity_id = intval($_POST['entity_id']);
    $title = filter_var($_POST['title'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $admin_id = $_SESSION['admin-id'];
    $status = '';

    // Get the user who submitted the news
    $userQuery = "SELECT newsAuthor FROM news WHERE id = ?";
    $userStmt = mysqli_prepare($connection, $userQuery);
    mysqli_stmt_bind_param($userStmt, 'i', $entity_id);
    mysqli_stmt_execute($userStmt);
    $userResult = mysqli_stmt_get_result($userStmt);
    $user = mysqli_fetch_assoc($userResult);
    $user_id = $user['newsAuthor'];

    if (isset($_POST['approve'])) {
        // Approve the news
        $updateQuery = "UPDATE news SET isVerifiedToPublish = 1 WHERE id = ?";
        $status = 'approved';
        $message = "Your news article '$title' has been approved!";
        $type = "news_approval";

        $stmt = mysqli_prepare($connection, $updateQuery);
        mysqli_stmt_bind_param($stmt, 'i', $entity_id);

        if (mysqli_stmt_execute($stmt)) {
            // Log admin activity with news title
            log_admin_activity(
                $connection,
                $admin_id,
                'APPROVE_NEWS',
                "Approved news article: $title",
                'news',
                $entity_id,
                ROOT_URL . "Community/News/view-news.php?id=" . $entity_id
            );

            // Update user's original notification to 'read'
            $updateOriginalNotif = "UPDATE notifications SET status = 'approved', is_read = 1 
                                  WHERE entity_id = ? AND type = 'news_submission'";
            $origNotifStmt = mysqli_prepare($connection, $updateOriginalNotif);
            mysqli_stmt_bind_param($origNotifStmt, 'i', $entity_id);
            mysqli_stmt_execute($origNotifStmt);

            // Insert new notification for user
            if (!isset($_POST['approve']) && !isset($_POST['reject'])) {
                // Now, Insert a New Notification for the User (Admin to User Notification)
                $insertNotifQuery = "INSERT INTO notifications (entity_id, user_id, message, type, status, created_at, recipient) 
                                     VALUES (?, ?, ?, ?, ?, NOW(), 'user')";
                $insertNotifStmt = mysqli_prepare($connection, $insertNotifQuery);
                mysqli_stmt_bind_param($insertNotifStmt, 'iisss', $entity_id, $user_id, $message, $type, $status);
                mysqli_stmt_execute($insertNotifStmt);
            }
        }
        mysqli_stmt_close($stmt);
    } elseif (isset($_POST['reject']) && !empty($_POST['rejection_reason'])) {
        // Reject the news
        $rejectionReason = mysqli_real_escape_string($connection, $_POST['rejection_reason']);
        $updateQuery = "UPDATE news SET isVerifiedToPublish = 2, rejection_reason = ? WHERE id = ?";
        $status = 'rejected';
        $message = "Your news article was rejected. Reason: " . $rejectionReason;
        $type = "news_rejection";

        $stmt = mysqli_prepare($connection, $updateQuery);
        mysqli_stmt_bind_param($stmt, 'si', $rejectionReason, $entity_id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_execute($stmt)) {
            // Log admin activity
            log_admin_activity(
                $connection,
                $admin_id,
                'REJECT_NEWS',
                "Rejected news article: {$title}",
                'news',
                $entity_id,
                ROOT_URL . "Community/News/edit-news.php?id=" . $entity_id
            );
        }
    } else {
        $_SESSION['try-again'] = "Something went wrong, Please try again.";
        header('Location: ' . ROOT_URL . 'admin/Requests/news-approval.php');
        exit();
    }

    // Check if there's an existing notification for this news and user (User to Admin Notification)
    $checkNotifQuery = "SELECT id FROM notifications WHERE entity_id = ? AND user_id = ?";
    $checkNotifStmt = mysqli_prepare($connection, $checkNotifQuery);
    mysqli_stmt_bind_param($checkNotifStmt, 'ii', $entity_id, $user_id);
    mysqli_stmt_execute($checkNotifStmt);
    $checkNotifResult = mysqli_stmt_get_result($checkNotifStmt);

    if ($checkNotifResult->num_rows > 0) {
        // Update the existing notification status (User to Admin Notification)
        $notifRow = mysqli_fetch_assoc($checkNotifResult);
        $notifID = $notifRow['id'];

        $notifQuery = "UPDATE notifications SET status = ? WHERE id = ?";
        $notifStmt = mysqli_prepare($connection, $notifQuery);
        mysqli_stmt_bind_param($notifStmt, 'si', $status, $notifID);
        mysqli_stmt_execute($notifStmt);
    }

    // Now, Insert a New Notification for the User (Admin to User Notification)
    $link = ROOT_URL . "Community/News/news-content.php?id=" . $entity_id;
    $entity_type = "news";
    $insertNotifQuery = "INSERT INTO notifications (entity_id, entity_type, user_id, message, link, type, status, created_at, recipient) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 'user')";
    $insertNotifStmt = mysqli_prepare($connection, $insertNotifQuery);
    mysqli_stmt_bind_param($insertNotifStmt, 'isissss', $entity_id, $entity_type, $user_id, $message, $link, $type, $status);
    mysqli_stmt_execute($insertNotifStmt);


    // Redirect back to the news publication page
    $_SESSION['updated'] = "Approval status successfully updated.";
    header('Location: ' . ROOT_URL . 'admin/Requests/news-publication.php');
    exit();
}
