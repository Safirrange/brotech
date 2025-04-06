<?php
include '../../partials/header.php';

// Ensure user is logged in
if (!isset($_SESSION['user-id'])) {
    header('Location: ' . ROOT_URL . 'signin.php');
    exit();
}

// Check if news_id is provided and valid
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['alert-user'] = "Invalid news article ID.";
    header('Location: ' . ROOT_URL . 'users/user-dashboard.php');
    exit();
}

$news_id = intval($_GET['id']);
$user_id = $_SESSION['user-id'];

// First check if the user has access to this news article
$access_query = "SELECT COUNT(*) as count 
                 FROM news 
                 WHERE id = ? AND newsAuthor = ?";
$access_stmt = mysqli_prepare($connection, $access_query);
mysqli_stmt_bind_param($access_stmt, 'ii', $news_id, $user_id);
mysqli_stmt_execute($access_stmt);
$access_result = mysqli_stmt_get_result($access_stmt);
$access_row = mysqli_fetch_assoc($access_result);

// Now fetch the news details
$query = "SELECT n.*, u.username, u.firstName, u.lastName 
          FROM news n
          JOIN usersmember u ON n.newsAuthor = u.id
          WHERE n.id = ? AND n.newsAuthor = ?";
$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 'ii', $news_id, $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($access_row['count'] == 0 || $result->num_rows === 0) {
    echo "You don't have access to this news or news doesn't exist. <a href='http://localhost/System/Community/News/news.php' style='display: inline-block; padding: 10px 15px; background-color: blue; color: white; text-decoration: none; border-radius: 5px;'> Back to News </a>";
    die();  
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
mysqli_stmt_bind_param($sectionsStmt, 'i', $news_id);
mysqli_stmt_execute($sectionsStmt);
$sectionsResult = mysqli_stmt_get_result($sectionsStmt);
$sections = [];
while ($row = mysqli_fetch_assoc($sectionsResult)) {
    $row['paragraphs'] = [];
    $sections[$row['id']] = $row;
}

if (!empty($sections)) {
    // Fetch paragraphs
    $paragraphQuery = "SELECT * FROM news_paragraphs WHERE section_id IN (" . implode(',', array_keys($sections)) . ")";
    $paragraphResult = mysqli_query($connection, $paragraphQuery);
    while ($row = mysqli_fetch_assoc($paragraphResult)) {
        $sections[$row['section_id']]['paragraphs'][] = $row['paragraphContent'];
    }
}
?>

<body>

    <link rel="stylesheet" href="<?= ROOT_URL ?>css/view-news.css">

    <?php if ($news['deleted_at'] !== null):
        $deletedDate = date('F j, Y', strtotime($news['deleted_at'])); ?>

        <div class='status-banner deleted'>
            ⛔ This news article has been deleted on <?= $deletedDate ?>
        </div>

    <?php else : ?>
        <div class="container">
            <!-- Approval Status Banner -->
            <?php if ($news['isVerifiedToPublish'] == 1): ?>
                <div class="status-banner success">
                    ✅ This news article has been approved and published
                </div>
            <?php elseif ($news['isVerifiedToPublish'] == 2): ?>
                <div class="status-banner error">
                    ❌ This news article was rejected
                    <p class="rejection-reason">Reason: <?= htmlspecialchars($news['rejection_reason']) ?></p>
                </div>
            <?php else: ?>
                <div class="status-banner pending">
                    ⏳ This news article is pending approval
                </div>
            <?php endif; ?>

            <div class="news-preview">
                <h1><?= htmlspecialchars($news['title']) ?></h1>
                <h3><?= htmlspecialchars($news['subTitle']) ?></h3>

                <div class="meta-info">
                    <p>Author: <?= htmlspecialchars($news['firstName']) ?> <?= htmlspecialchars($news['lastName']) ?></p>
                    <p>Categories: <?= implode(', ', $categories) ?></p>
                    <p>Date Submitted: <?= date('F j, Y', strtotime($news['publishedDate'])) ?></p>
                </div>

                <div class="header-image">
                    <img src="<?= ROOT_URL ?>img/news/<?= $news['headerImg'] ?>" alt="Header Image">
                </div>

                <div class="introduction">
                    <?= nl2br(htmlspecialchars($news['introduction'])) ?>
                </div>

                <div class="sections">
                    <?php foreach ($sections as $section): ?>
                        <div class="section">
                            <h2><?= htmlspecialchars($section['newsTitle']) ?></h2>

                            <?php if ($section['newsImg']): ?>
                                <div class="section-image">
                                    <img src="<?= ROOT_URL ?>img/news/<?= $section['newsImg'] ?>" alt="Section Image">
                                    <?php if ($section['newsImgTitle']): ?>
                                        <p class="image-caption"><?= htmlspecialchars($section['newsImgTitle']) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <?php foreach ($section['paragraphs'] as $paragraph): ?>
                                <p><?= nl2br(htmlspecialchars($paragraph)) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="actions">
        <a href="<?= ROOT_URL ?>users/user-dashboard.php" class="btn">Back to My Dashboard</a>
    </div>


</body>

<?php include '../../partials/footer.php'; ?>