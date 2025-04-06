<?php
include '../admin-sidebar.php'; // Include your database connection
// Ensure the admin is logged in
if (!isset($_SESSION['admin-id'])) {
    header('Location: ' . ROOT_URL . 'admin/login.php');
    exit();
}

// Check if news_id is provided
if (!isset($_GET['entity_id']) || empty($_GET['entity_id'])) {
    $_SESSION['failed'] = "Something went wrong, Please try again.";
    header('Location: ' . ROOT_URL . 'admin/Requests/news-publication.php');
    exit();
}

$entity_id = intval($_GET['entity_id']);

if (isset($_GET['notification_id'])) {
    $notif_id = intval($_GET['notification_id']);
    $query = "UPDATE notifications SET is_read = 1 WHERE id = ?";
    $stmt = mysqli_prepare($connection, $query);
    mysqli_stmt_bind_param($stmt, 'i', $notif_id);
    mysqli_stmt_execute($stmt);
}

// Fetch news details
$query = "SELECT n.*, u.username, u.firstName, u.lastName 
          FROM news n
          JOIN usersmember u ON n.newsAuthor = u.id
          WHERE n.id = ?";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 'i', $entity_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result->num_rows === 0) {
    $_SESSION['failed'] = "Something went wrong, Please try again.";
    header('Location: ' . ROOT_URL . 'admin/Requests/news-publication.php');
    exit();
}

$news = mysqli_fetch_assoc($result);

// Fetch news categories
$categoryQuery = "SELECT c.news_category FROM news_category_relations nc
                  JOIN news_category c ON nc.category_id = c.id
                  WHERE nc.news_id = ?";
$categoryStmt = mysqli_prepare($connection, $categoryQuery);
mysqli_stmt_bind_param($categoryStmt, 'i', $news_id);
mysqli_stmt_execute($categoryStmt);
$categoryResult = mysqli_stmt_get_result($categoryStmt);
$categories = [];
while ($row = mysqli_fetch_assoc($categoryResult)) {
    $categories[] = $row['news_category'];
}

// Fetch news sections and paragraphs
$sectionsQuery = "SELECT * FROM news_sections WHERE news_id = ?";
$sectionsStmt = mysqli_prepare($connection, $sectionsQuery);
mysqli_stmt_bind_param($sectionsStmt, 'i', $entity_id);
mysqli_stmt_execute($sectionsStmt);
$sectionsResult = mysqli_stmt_get_result($sectionsStmt);
$sections = [];
while ($row = mysqli_fetch_assoc($sectionsResult)) {
    $row['paragraphs'] = [];
    $sections[$row['id']] = $row;
}

// Fetch paragraphs
$paragraphQuery = "SELECT * FROM news_paragraphs WHERE section_id IN (" . implode(',', array_keys($sections)) . ")";
$paragraphResult = mysqli_query($connection, $paragraphQuery);
while ($row = mysqli_fetch_assoc($paragraphResult)) {
    $sections[$row['section_id']]['paragraphs'][] = $row['paragraphContent'];
}
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/admin-style.css">

<body>

    <?php if (isset($_SESSION['try-again'])): ?>
        <div class="alert-message error">
            <p>
                <?= $_SESSION['try-again'];
                unset($_SESSION['try-again']);
                ?>
            </p>
        </div>
    <?php endif ?>

    <div class="container">
        <h2>Review News Submission</h2>
        <p><strong>Title:</strong> <?= htmlspecialchars($news['title']) ?></p>
        <p><strong>Subtitle:</strong> <?= htmlspecialchars($news['subTitle']) ?></p>
        <p><strong>Author Username:</strong> <?= htmlspecialchars($news['username']) ?></p>
        <p><strong>Author Name:</strong> <?= htmlspecialchars($news['firstName']) ?> <?= htmlspecialchars($news['lastName']) ?></p>
        <p><strong>Categories:</strong> <?= implode(', ', $categories) ?></p>
        <p><strong>Introduction:</strong> <?= nl2br(htmlspecialchars($news['introduction'])) ?></p>

        <img src="<?= ROOT_URL ?>img/news/<?= $news['headerImg'] ?>" alt="Header Image" width="400">

        <hr>

        <h3>News Sections</h3>
        <?php foreach ($sections as $section): ?>
            <h4><?= htmlspecialchars($section['newsTitle']) ?></h4>
            <?php if ($section['newsImg']): ?>
                <img src="<?= ROOT_URL ?>img/news/<?= $section['newsImg'] ?>" alt="Section Image" width="300">
                <p><em><?= htmlspecialchars($section['newsImgTitle']) ?></em></p>
            <?php endif; ?>
            <?php foreach ($section['paragraphs'] as $paragraph): ?>
                <p><?= nl2br(htmlspecialchars($paragraph)) ?></p>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <hr>

        <?php
        if ($news['isVerifiedToPublish'] == 1) {
            echo '<div class="alert alert-success">✅ This news has already been approved.</div>';
        } elseif ($news['isVerifiedToPublish'] == 2) {
            echo '<div class="alert alert-danger">❌ This news has been rejected. Reason: ' . htmlspecialchars($news['rejection_reason']) . '</div>';
        } else {
        ?>
            <!-- Approval Form -->
            <form id="approvalForm" method="POST" action="<?= ROOT_URL ?>admin/Requests/news-approval-processing.php">
                <input type="hidden" name="entity_id" value="<?= $entity_id ?>">
                <input type="hidden" name="title" value="<?= htmlspecialchars($news['title']) ?>">
                <button type="submit" name="approve" class="btn btn-success">Approve</button>
                <button type="button" id="rejectBtn" class="btn btn-danger">Reject</button>
            </form>

            <!-- Rejection Form -->
            <div id="rejectionBox" style="display: none; margin-top: 10px;">
                <form method="POST" action="<?= ROOT_URL ?>admin/Requests/news-approval-processing.php">
                    <input type="hidden" name="entity_id" value="<?= $entity_id ?>">
                    <input type="hidden" name="title" value="<?= htmlspecialchars($news['title']) ?>">
                    <textarea name="rejection_reason" placeholder="Enter rejection reason..." required></textarea>
                    <button type="submit" name="reject" class="btn btn-warning">Submit Rejection</button>
                </form>
            </div>

            <!-- JavaScript to Show Rejection Box -->
            <script>
                document.getElementById('rejectBtn').addEventListener('click', function() {
                    document.getElementById('rejectionBox').style.display = 'block';
                });
            </script>
        <?php
        }
        ?>


    </div>

</body>

</html>