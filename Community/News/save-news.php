<?php
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../../Helper/activity_logger.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Upload header image
    $headerImg = $_FILES['headerImg'];
    $headerImgName = time() . '_' . $headerImg['name'];
    move_uploaded_file($headerImg['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . "/System/img/news/" . $headerImgName);

    // Get main news info
    $title = mysqli_real_escape_string($connection, $_POST['title']);
    $subTitle = mysqli_real_escape_string($connection, $_POST['subTitle']);
    $introduction = mysqli_real_escape_string($connection, $_POST['introduction']);
    $author = $_SESSION['user-id'];

    // Insert into news table
    $query = "INSERT INTO news (headerImg, title, subTitle, introduction, newsAuthor) 
              VALUES (?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'ssssi', $headerImgName, $title, $subTitle, $introduction, $author);
    mysqli_stmt_execute($stmt);
    $entity_id = mysqli_insert_id($connection); // Get inserted news ID

    // Fetch selected categories (ensure it's an array)
    $newsCategories = $_POST['newsCategory'] ?? [];

    // Store categories in the news_category_relations table
    foreach ($newsCategories as $category_id) {
        $category_id = intval($category_id); // Ensure it's an integer
        $categoryQuery = "INSERT INTO news_category_relations (news_id, category_id) VALUES (?, ?)";
        $categoryStmt = mysqli_prepare($connection, $categoryQuery);
        mysqli_stmt_bind_param($categoryStmt, 'ii', $entity_id, $category_id);
        mysqli_stmt_execute($categoryStmt);
    }

    // Loop through sections
    foreach ($_POST['newsTitle'] as $sectionIndex => $sectionTitle) {
        // Upload section image (if exists)
        $sectionImgName = null;
        if (!empty($_FILES['newsImg']['name'][$sectionIndex])) {
            $sectionImg = $_FILES['newsImg']['name'][$sectionIndex];
            $sectionImgName = time() . '_' . $sectionImg;
            move_uploaded_file($_FILES['newsImg']['tmp_name'][$sectionIndex], $_SERVER['DOCUMENT_ROOT'] . "/System/img/news/" . $sectionImgName);
        }

        // Get image title
        $sectionImgTitle = !empty($_POST['newsImgTitle'][$sectionIndex]) ? $_POST['newsImgTitle'][$sectionIndex] : null;

        // Ensure title is set correctly
        $sectionTitle = !empty($sectionTitle) ? $sectionTitle : null;

        // Insert section (ONLY ONCE PER SECTION)
        $sectionQuery = "INSERT INTO news_sections (news_id, newsImg, newsImgTitle, newsTitle) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($connection, $sectionQuery);
        mysqli_stmt_bind_param($stmt, 'isss', $entity_id, $sectionImgName, $sectionImgTitle, $sectionTitle);
        mysqli_stmt_execute($stmt);
        $section_id = mysqli_insert_id($connection); // Get inserted section ID

        // Now insert paragraphs under this section
        foreach ($_POST['newsContent'][$sectionIndex] as $paragraph) {
            $paragraph = mysqli_real_escape_string($connection, $paragraph);
            $paragraphQuery = "INSERT INTO news_paragraphs (section_id, paragraphContent) VALUES (?, ?)";
            $stmt = mysqli_prepare($connection, $paragraphQuery);
            mysqli_stmt_bind_param($stmt, 'is', $section_id, $paragraph);
            mysqli_stmt_execute($stmt);
        }
    }
    // Log the activity
    $activity_link = ROOT_URL . "Community/News/view-news.php?id=" . $entity_id;
    log_user_activity(
        $connection,
        $_SESSION['user-id'],
        'CREATE_NEWS',
        "Created news article: $title",
        'news',
        $entity_id,
        $activity_link
    );

    // Insert notification for each admin
    $notificationMessage = "A new news article needs approval.";
    $notificationLink = ROOT_URL . "admin/Requests/news-approval.php?entity_id=" . $entity_id;
    $entityType = "news"; // Enum type for news approvals
    $type = "news_submission"; // Enum type for news approvals
    $createdAt = date('Y-m-d H:i:s'); // Timestamp

    // This will need to change when you loop through all admin IDs
    $notifQuery = "INSERT INTO notifications (user_id, entity_id, entity_type, message, type, is_read, created_at, link) 
                       VALUES (?, ?, ?, ?, ?, 0, ?, ?)";
    $notifStmt = mysqli_prepare($connection, $notifQuery);
    mysqli_stmt_bind_param($notifStmt, 'iisssss', $author, $entity_id, $entityType, $notificationMessage, $type, $createdAt, $notificationLink);
    mysqli_stmt_execute($notifStmt);

    // Success message and redirect
    $notification = "News successfully sent for approval!";
    $notificationType = "success";
} else {
    header('Location: ' . ROOT_URL . 'Community/News/new-news.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notification</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
</head>

<body>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Display the notification
        document.addEventListener('DOMContentLoaded', () => {
            Swal.fire({
                title: "Notification",
                text: "<?= addslashes($notification ?? '') ?>",
                icon: "<?= $notificationType ?? 'info' ?>",
                confirmButtonText: "OK",
                timer: 3000, // Automatically close after 3 seconds
                timerProgressBar: true
            }).then(() => {
                window.location.href = "<?= ROOT_URL ?>users/user-dashboard.php";
            });
        });
    </script>
</body>

</html>