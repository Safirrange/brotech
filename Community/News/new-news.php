<?php include '../../partials/header.php';
?>

<body>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/new-news.css">

    <div class="news-form-container">
        <h1>Publish News</h1>
        <form action="<?= ROOT_URL ?>Community/News/save-news.php" method="POST" enctype="multipart/form-data">

            <label for="headerImg">Header Image:</label>
            <input type="file" name="headerImg" id="headerImg" required>

            <label for="title">Title:</label>
            <input type="text" name="title" id="title" required>

            <label for="subTitle">Sub Title:</label>
            <input type="text" name="subTitle" id="subTitle" required>

            <label for="newsCategory">Category:</label>
            <select name="newsCategory[]" id="newsCategory" multiple required>
                <?php
                $categories_query = "SELECT id, news_category FROM news_category WHERE deleted_at IS NULL";
                $categories_result = mysqli_query($connection, $categories_query);
                while ($category = mysqli_fetch_assoc($categories_result)) :
                ?>
                    <option value="<?= $category['id'] ?>"><?= htmlspecialchars($category['news_category']) ?></option>
                <?php endwhile; ?>
            </select>
            <p>Hold CTRL (Windows) or CMD (Mac) to select multiple categories.</p>


            <label for="introduction">Introduction:</label>
            <textarea name="introduction" id="introduction" required></textarea>


            <div id="content-sections">
                <!-- Default First Section -->
                <div class="content-section">
                    <label>Section Image (Optional):</label>
                    <input type="file" name="newsImg[]">

                    <label>Image Title (Optional):</label>
                    <input type="text" name="newsImgTitle[]">

                    <label>Section Title (Optional):</label>
                    <input type="text" name="newsTitle[]">

                    <div class="paragraph-container">
                        <label>Content Paragraph:</label>
                        <textarea name="newsContent[0][]" required></textarea>
                    </div>
                    <button type="button" class="add-paragraph">+ Add More Paragraph</button>
                </div>
            </div>

            <button type="button" id="add-section">+ Add More Section</button>
            <button type="submit">Submit</button>
        </form>
    </div>

    <script>
        document.getElementById('add-section').addEventListener('click', function() {
            const sectionsContainer = document.getElementById('content-sections');
            const sectionCount = sectionsContainer.children.length;
            const section = document.querySelector('.content-section').cloneNode(true);

            // Clear inputs inside the cloned section
            section.querySelectorAll('input, textarea').forEach(input => {
                if (input.type === 'file') {
                    input.value = ''; // Reset file input
                    input.removeAttribute('name'); // Remove old name to avoid duplicate file inputs
                    input.setAttribute('name', `newsImg[${sectionCount}]`); // Assign a new unique name
                } else {
                    input.value = '';
                }
            });

            // Reset paragraph container
            const paragraphContainer = section.querySelector('.paragraph-container');
            paragraphContainer.innerHTML = `
        <label>Content Paragraph:</label>
        <textarea name="newsContent[${sectionCount}][]" required></textarea>
    `;

            sectionsContainer.appendChild(section);
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-paragraph')) {
                const section = e.target.closest('.content-section');
                const sectionIndex = Array.from(section.parentNode.children).indexOf(section);
                const paragraphContainer = section.querySelector('.paragraph-container');

                const newParagraph = document.createElement('div');
                newParagraph.innerHTML = `
            <label>Content Paragraph:</label>
            <textarea name="newsContent[${sectionIndex}][]" required></textarea>
        `;
                paragraphContainer.appendChild(newParagraph);
            }
        });
    </script>
</body>

<?php include '../../partials/footer.php'; ?>