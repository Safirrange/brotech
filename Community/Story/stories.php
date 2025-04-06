<?php
include '../partials/header.php'; // Database connection
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/stories.css">

<body>
    <div class="stories-container">
        <div class="stories-header-container">
            <a href="<?= ROOT_URL ?>Community/Story/stories.php">Repair Stories</a>
            <p>Every broken device is a repair story waiting to happen. Check out these stories—written by people just like you—and get inspired to fix something. Or share your best repair story with the iFixit community. (Don't forget the pictures!)</p>
        </div>

        <div class="stories-button">
            <div class="left-part">
                <a href="">Most Recent</a>
                <a href="">Most Viewed</a>
            </div>
            <div class="right-part">
                <a href="<?= ROOT_URL ?>Community/Story/write-stories.php">Share your stories</a>
            </div>
        </div>

        <div class="all-stories-container">
            <a href="<?= ROOT_URL ?>Community/news-content.php?id=<?= $news['id'] ?>">
                <div class="stories">
                    <div class="story-img-container">
                        <img src="<?= ROOT_URL ?>img/tests/phone.png" alt="News Image">
                    </div>
                    <div class="story-title">
                        <h3>Story Title</h3>
                        <p>Read Story - Gadget Name</p>

                    </div>

                    <div class="story-author">
                        <h4>author name</h4>
                        <h4>published date</h4>
                    </div>
                </div>
            </a>
        </div>

    </div>

</body>

<?php
include '../partials/footer.php'; // Database connection
?>
