<?php
include '../../partials/header.php'; // Database connection

$category_id = isset($_GET['category']) ? intval($_GET['category']) : 0;

if ($category_id > 0) {
    // Fetch category name
    $category_name_query = "SELECT news_category FROM news_category WHERE id = ? AND deleted_at IS NULL";
    $stmt = mysqli_prepare($connection, $category_name_query);
    mysqli_stmt_bind_param($stmt, 'i', $category_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $category_name = mysqli_fetch_assoc($result)['news_category'] ?? 'All News';

    // Fetch news for the selected category
    $news_query = "
        SELECT n.id, n.headerImg, n.title, n.introduction, n.publishedDate, u.username AS author_name 
        FROM news n
        JOIN usersmember u ON n.newsAuthor = u.id
        JOIN news_category_relations ncr ON n.id = ncr.news_id
        WHERE ncr.category_id = ? AND n.isVerifiedToPublish = 1
        AND deleted_at IS NULL
        ORDER BY n.publishedDate DESC";

    $stmt = mysqli_prepare($connection, $news_query);
    mysqli_stmt_bind_param($stmt, 'i', $category_id);
} else {
    // No category selected, fetch all news
    $category_name = "All News";

    $news_query = "
        SELECT n.id, n.headerImg, n.title, n.introduction, n.publishedDate, u.username AS author_name 
        FROM news n
        JOIN usersmember u ON n.newsAuthor = u.id
        WHERE n.isVerifiedToPublish = 1
        AND deleted_at IS NULL
        ORDER BY n.publishedDate DESC";

    $stmt = mysqli_prepare($connection, $news_query);
}

mysqli_stmt_execute($stmt);
$news_result = mysqli_stmt_get_result($stmt);
?>


<body>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/news.css">

    <div class="news-container">
        <div class="news-header-container">
            <a href="<?= ROOT_URL ?>Community/News/news.php">
                <h1>BroTech News</h1>
            </a>
            <a href="<?= ROOT_URL ?>Community/News/news-categories.php">All Categories</a>

            <?php if (isset($_SESSION['user-id'])): ?>
                <a href="<?= ROOT_URL ?>Community/News/new-news.php">Publish a News</a>
            <?php else: ?>
                <a href="#" class="login-required open-login-modal">Publish a News</a>
            <?php endif; ?>
        </div>

        <div class="all-news-container">
            <?php if (mysqli_num_rows($news_result) > 0) : ?>
                <?php while ($news = mysqli_fetch_assoc($news_result)) : ?>
                    <a href="<?= ROOT_URL ?>Community/News/news-content.php?id=<?= $news['id'] ?>">
                        <div class="news">
                            <div class="news-img-container">
                                <img src="<?= ROOT_URL ?>img/news/<?= htmlspecialchars($news['headerImg']) ?>" alt="News Image">
                            </div>
                            <div class="news-content">
                                <h3><?= htmlspecialchars($news['title']) ?></h3>
                                <p><?= htmlspecialchars($news['introduction']) ?></p>
                                <h4><?= date("F j, Y", strtotime($news['publishedDate'])) ?> by <?= htmlspecialchars($news['author_name']) ?></h4>
                            </div>
                        </div>
                    </a>
                <?php endwhile; ?>
            <?php else : ?>
                <div class="no-news">
                    <h2>No news available</h2>
                    <p>There are currently no articles published. Check back later.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</body>

<?php include '../../partials/footer.php'; ?>