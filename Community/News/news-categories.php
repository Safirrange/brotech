<?php
include '../../partials/header.php'; // Database connection

// Fetch categories and count the number of news articles in each category
$news_categories_query = "
    SELECT nc.id, nc.news_category, nc.description, 
       COUNT(CASE WHEN n.isVerifiedToPublish = 1 THEN ncr.news_id END) AS news_count
FROM news_category nc
LEFT JOIN news_category_relations ncr ON nc.id = ncr.category_id
LEFT JOIN news n ON ncr.news_id = n.id
WHERE nc.deleted_at IS NULL
GROUP BY nc.id
ORDER BY nc.news_category ASC";

$news_categories_result = mysqli_query($connection, $news_categories_query);
?>

<body>

    <link rel="stylesheet" href="<?= ROOT_URL ?>css/new-cat.css">

    <div class="category-title-container">
        <h1>All Categories</h1>
    </div>

    <div class="categories-container">
        <?php while ($news_category = mysqli_fetch_assoc($news_categories_result)) : ?>
            <div class="category-card">
                <h2><?= htmlspecialchars($news_category['news_category']) ?></h2>
                <p><?= htmlspecialchars_decode($news_category['description']) ?></p>
                <a href="<?= ROOT_URL ?>Community/News/news.php?category=<?= $news_category['id'] ?>">
                    <?= $news_category['news_count'] > 0 ? $news_category['news_count'] . ' Stories' : 'No Stories Yet' ?>
                </a>

            </div>
        <?php endwhile; ?>
    </div>

</body>

<?php
include '../../partials/footer.php';
?>