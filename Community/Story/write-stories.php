<?php
include '../partials/header.php'; // Database connection
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/write-story.css">


<body>
    <div class="container">
        <h1>Share Your Story</h1>
        <p>We all have repair stories. Some end well, some end not-so-well. Whatever the case, we want to hear about your experience!</p>

        <label for="title">Give your story a title</label>
        <input type="text" id="title" name="title" placeholder="Story title">

        <label>Add your images or video</label>
        <div class="media-upload">
            <div class="media-box">📷</div>
            <div class="media-box">📷</div>
            <div class="media-box">📹</div>
        </div>

        <label for="guide">Which repair guide did you use?</label>
        <input type="text" id="guide" name="guide" placeholder="Repair guide name">

        <label for="problem">The Problem — Why did you fix it?</label>
        <textarea id="problem" name="problem" rows="4"></textarea>

        <label for="repair">The Repair — How did it go?</label>
        <textarea id="repair" name="repair" rows="4"></textarea>

        <label for="advice">The Enlightenment — Got any advice?</label>
        <textarea id="advice" name="advice" rows="4"></textarea>

        <label for="helpful">Was there a part or tool that was particularly helpful?</label>
        <input type="text" id="helpful" name="helpful" placeholder="Part or tool name">

        <button type="submit">Submit</button>
    </div>
</body>

</html>

<?php
include '../partials/footer.php'; // Database connection
?>