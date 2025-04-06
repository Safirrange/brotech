<?php
include '../partials/header.php';
?>

<body>

    <link rel="stylesheet" href="<?= ROOT_URL ?>css/Community.css">

    <div class="main-container">

        <h1 class="community-heading">Community</h1>

        <div class="community-card-container">
            <div class="community-card">
                <img src="<?= ROOT_URL ?>img/stock.jpg" alt="Step-by-Step Guides">
                <div class="community-card-content">
                    <h2>Solve/Answer Problems</h2>
                    <p>Get help from friendly fixers and share a helping hand with others around the world.</p>
                    <a href="<?= ROOT_URL ?>Community/solve-answer.php">Q&A Forums</a>
                </div>
            </div>

            <div class="community-card">
                <img src="<?= ROOT_URL ?>img/stock.jpg" alt="Step-by-Step Guides">
                <div class="community-card-content">
                    <h2>Write a Story</h2>
                    <p>Share your repair experience to others learn what to do(and what not to do).</p>
                    <?php if (isset($_SESSION['user-id'])): ?>
                        <a href="<?= ROOT_URL ?>Community/Story/stories.php">Write Story</a>
                    <?php else: ?>
                        <a href="#" class="login-required open-login-modal">Write Story</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="community-card">
                <img src="<?= ROOT_URL ?>img/stock.jpg" alt="Step-by-Step Guides">
                <div class="community-card-content">
                    <h2>Teach a Repair</h2>
                    <p>No one knows how to fix everything, but everyone knows how to fix something.</p>
                    <?php if (isset($_SESSION['user-id'])): ?>
                        <a href="<?= ROOT_URL ?>Guide/newGuide.php">Create a Guide</a>
                    <?php else: ?>
                        <a href="#" class="login-required open-login-modal">Create a Guide</a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="community-card">
                <img src="<?= ROOT_URL ?>img/stock.jpg" alt="Step-by-Step Guides">
                <div class="community-card-content">
                    <h2>Be Updated</h2>
                    <p>Read the latest news on the latest tech releases and never miss out.</p>
                    <a href="<?= ROOT_URL ?>Community/News/news.php">Read the News</a>
                </div>
            </div>

        </div>


        <div class="community-grid-container">
            <h1 class="community-heading">Latest Community Activity</h1>

            <div class="community-grids">
                <div class="community-grid">
                    <img src="<?= ROOT_URL ?>img/stock.jpg" alt="Apparel">
                    <div class="grid-user-profile">
                        <img src="<?= ROOT_URL ?>img/imgicon.png" alt="Apparel">
                        <div class="grid-user-details">
                            <span>Ben Schlichter</span>
                            Published a Guide
                        </div>
                    </div>
                </div>

                <div class="community-grid">
                    <img src="<?= ROOT_URL ?>img/stock.jpg" alt="Apparel">
                    <div class="grid-user-profile">
                        <img src="<?= ROOT_URL ?>img/imgicon.png" alt="Apparel">
                        <div class="grid-user-details">
                            <span>Ben Schlichter</span>
                            Published a Guide
                        </div>
                    </div>
                </div>

                <div class="community-grid">
                    <img src="<?= ROOT_URL ?>img/stock.jpg" alt="Apparel">
                    <div class="grid-user-profile">
                        <img src="<?= ROOT_URL ?>img/imgicon.png" alt="Apparel">
                        <div class="grid-user-details">
                            <span>Ben Schlichter</span>
                            Published a Guide
                        </div>
                    </div>
                </div>

                <div class="community-grid">
                    <img src="<?= ROOT_URL ?>img/stock.jpg" alt="Apparel">
                    <div class="grid-user-profile">
                        <img src="<?= ROOT_URL ?>img/imgicon.png" alt="Apparel">
                        <div class="grid-user-details">
                            <span>Ben Schlichter</span>
                            Published a Guide
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

</body>

<?php
include '../partials/footer.php';
?>