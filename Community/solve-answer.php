<?php
include '../partials/header.php'; // Database connection
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/solve-answer.css">

<body>
    <div class="container">
        <div class="header">
            <h1>Answers Forum</h1>
            <div>
                <button>Learn How It Works</button>
                <a href="<?= ROOT_URL ?>Community/ask-a-question.php">Ask a Question</a>
            </div>
        </div>

        <div class="search-bar">
            <input type="text" placeholder="Search answers...">
        </div>

        <div class="filters">
            <div class="filter active">All</div>
            <div class="filter">Most Helpful</div>
            <div class="filter">Unanswered</div>
            <div class="filter">Newest</div>
        </div>

        <div class="questions-container">
            <div class="question">
                <img src="<?= ROOT_URL ?>img/tests/earbuds.png" alt="Category Logo">
                <div class="answers-count">6 Answers</div>
                <div class="question-details">
                    <h3 class="question-title">HP Power Button not working</h3>
                    <p class="question-meta"><span class="accepted">✔️ Accepted</span> HP Laptop</p>
                </div>
                <div class="question-author">
                    <img src="<?= ROOT_URL ?>img/tests/ange.jpg" alt="Category Logo">
                    <p>Evan Gattuso - 9.5k</p>
                    <p>Answer edited May 29, 2024</p>
                </div>
            </div>
        </div>
    </div>
</body>

</html>

<?php
include '../partials/footer.php'; // Database connection
?>