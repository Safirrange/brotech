<?php
include '../partials/header.php'; // Database connection
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/ask-a-question.css">
<body>
    <div class="container">
        <h1>Ask a Question</h1>
        <p class="subtext">Be specific and include what you've already tried. Better questions get more answers.</p>

        <label for="device">Choose a Device</label>
        <input type="text" id="device" name="device" placeholder="Find Device">

        <label for="title">Question Title</label>
        <input type="text" id="title" name="title" placeholder="Why is my power adapter overheating?">

        <label for="description">Question Description</label>
        <textarea id="description" name="description" rows="5"></textarea>

        <button type="submit">Preview Your Question</button>

        <p style="text-align: center; margin-top: 20px; color: green;">
            ← Nevermind, I'll keep searching.
        </p>
    </div>
</body>

<?php
include '../partials/footer.php'; // Database connection
?>
