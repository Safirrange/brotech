<?php
include '../../partials/header.php'; // Database connection

// Get news ID from URL
$news_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch main news details
$query = "SELECT 
            n.id, 
            n.headerImg, 
            n.title, 
            n.subTitle, 
            n.introduction, 
            n.publishedDate, 
            u.userName AS author_name,
            u.firstName AS first_name,
            u.lastName AS last_name,
            u.avatar AS author_avatar,
            GROUP_CONCAT(nc.news_category SEPARATOR ', ') AS categories
          FROM news n
          LEFT JOIN usersmember u ON n.newsAuthor = u.id
          LEFT JOIN news_category_relations ncr ON n.id = ncr.news_id
          LEFT JOIN news_category nc ON ncr.category_id = nc.id
          WHERE n.id = ? AND n.isVerifiedToPublish = 1
          AND n.deleted_at IS NULL
          GROUP BY n.id";

$stmt = mysqli_prepare($connection, $query);
mysqli_stmt_bind_param($stmt, 'i', $news_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$news = mysqli_fetch_assoc($result);

// Fetch all content sections related to this news
$sections_query = "SELECT * FROM news_sections WHERE news_id = ? ORDER BY id ASC";
$sections_stmt = mysqli_prepare($connection, $sections_query);
mysqli_stmt_bind_param($sections_stmt, 'i', $news_id);
mysqli_stmt_execute($sections_stmt);
$sections_result = mysqli_stmt_get_result($sections_stmt);
?>

<body>
<link rel="stylesheet" href="<?= ROOT_URL ?>css/news-contents.css">
    <?php if ($news): ?>

        <div class="news-header">
            <div class="news-tags">
                <?php
                $categories = explode(', ', $news['categories'] ?? 'Uncategorized');
                foreach ($categories as $category): ?>
                    <span class="news-tag"><?= htmlspecialchars($category) ?></span>
                <?php endforeach; ?>
            </div>
            <img src="<?= ROOT_URL ?>img/news/<?= htmlspecialchars($news['headerImg']) ?>" alt="News Image">
            <h1><?= htmlspecialchars($news['title']) ?></h1>
            <h3><?= htmlspecialchars($news['subTitle']) ?></h3>
        </div>

        <div class="news-content-container">

            <div class="news-meta">

                <div class="author-info">
                    <img src="<?= ROOT_URL ?>img/users/<?= htmlspecialchars($news['author_avatar']) ?>" alt="Author">
                    <div>
                        <strong>Article by: <?= htmlspecialchars($news['first_name']) ?>
                            <?= htmlspecialchars($news['last_name']) ?> </strong><br>
                        <span><?= date("F j, Y", strtotime($news['publishedDate'])) ?> - <a href="#">Tech Reviews</a></span>
                    </div>
                </div>

                <div class="share-container">
                    <button class="share-button" onclick="toggleShare()">Share <i class="fas fa-share"></i></button>
                    <ul class="share-dropdown" id="shareDropdown">
                        <li><a href="#"><i class="fab fa-facebook"></i> Facebook</a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i> Twitter</a></li>
                        <li><a href="#"><i class="fab fa-reddit"></i> Reddit</a></li>
                        <li><a href="#"><i class="fab fa-linkedin"></i> LinkedIn</a></li>
                        <li><a href="#"><i class="fas fa-envelope"></i> Email</a></li>
                        <li><a href="#"><i class="fas fa-link"></i> Copy Link</a></li>
                    </ul>
                </div>

            </div>

            <div class="news-body">
                <p class="news-intro"><?= nl2br(htmlspecialchars($news['introduction'])) ?></p>
                <?php while ($section = mysqli_fetch_assoc($sections_result)): ?>
                    <div class="news-section">
                        <?php if (!empty($section['newsImg'])): ?>
                            <div class="news-section-img">
                                <img src="<?= ROOT_URL ?>img/news/<?= htmlspecialchars($section['newsImg']) ?>" alt="<?= htmlspecialchars($section['newsImgTitle']) ?>">
                                <?php if (!empty($section['newsImgTitle'])): ?>
                                    <p class="news-img-title"><?= htmlspecialchars($section['newsImgTitle']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($section['newsTitle'])): ?>
                            <h2><?= htmlspecialchars($section['newsTitle']) ?></h2>
                        <?php endif; ?>

                        <?php
                        // Fetch paragraphs for this section
                        $paragraphs_query = "SELECT paragraphContent FROM news_paragraphs WHERE section_id = ? ORDER BY id ASC";
                        $paragraphs_stmt = mysqli_prepare($connection, $paragraphs_query);
                        mysqli_stmt_bind_param($paragraphs_stmt, 'i', $section['id']);
                        mysqli_stmt_execute($paragraphs_stmt);
                        $paragraphs_result = mysqli_stmt_get_result($paragraphs_stmt);
                        ?>

                        <?php while ($paragraph = mysqli_fetch_assoc($paragraphs_result)): ?>
                            <p><?= nl2br(htmlspecialchars($paragraph['paragraphContent'])) ?></p>
                        <?php endwhile; ?>
                    </div>
                <?php endwhile; ?>
            </div>


        <?php else: ?>
            <div class="news-not-found">
                <h2>News Not Found</h2>
                <p>Sorry, the article you are looking for does not exist, is not yet published or had been rejected.</p>
                <a href="<?= ROOT_URL ?>Community/News/news.php">Back to News</a>
            </div>
        <?php endif; ?>
        </div>
</body>

<script>
    function toggleShare() {
        var dropdown = document.getElementById("shareDropdown");
        dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
    }
    document.addEventListener("click", function(event) {
        var isClickInside = document.querySelector(".share-container").contains(event.target);
        if (!isClickInside) {
            document.getElementById("shareDropdown").style.display = "none";
        }
    });
</script>

<?php include '../../partials/footer.php'; ?>