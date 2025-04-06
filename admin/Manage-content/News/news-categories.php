<?php
include '../../admin-sidebar.php'; // Database connection

// Fetch all categories that are not deleted
$categories_query = "SELECT id, news_category, description FROM news_category WHERE deleted_at IS NULL ORDER BY news_category ASC";
$categories_result = mysqli_query($connection, $categories_query);

// Count total categories
$total_categories = mysqli_num_rows($categories_result);
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/manage-contents.css">
<div class="content">
    <div class="page-header">
        <h1>News Categories Management</h1>
        <div class="user-stats">
            <div class="stat-box">
                <span class="stat-value"><?= $total_categories ?></span>
                <span class="stat-label">Total Categories</span>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['add-news-category-success'])) : ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['add-news-category-success'];
                unset($_SESSION['add-news-category-success']); ?></p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['edit-news-category-success'])) : ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['edit-news-category-success'];
                unset($_SESSION['edit-news-category-success']); ?></p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['delete-news-category-success'])) : ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['delete-news-category-success'];
                unset($_SESSION['delete-news-category-success']); ?></p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['delete-news-category'])) : ?>
        <div class="alert-message error" id="alert-message">
            <p><?= $_SESSION['delete-news-category'];
                unset($_SESSION['delete-news-category']); ?></p>
        </div>
    <?php endif ?>

    <div class="table-actions">
        <div class="search-container">
            <input type="text" id="newsCategorySearch" placeholder="Search categories...">
        </div>
        <div class="action-buttons">
            <a href="<?= ROOT_URL ?>admin/Manage-content/News/add-news-categories.php" class="add-btn">
                <i class="fa fa-plus"></i> Add Category
            </a>
            <a href="<?= ROOT_URL ?>admin/Manage-content/News/news-blog-list.php" class="back-btn">
                <i class="fa fa-newspaper-o"></i> Show All News
            </a>
        </div>
    </div>

    <div class="table-container">
        <table class="styled-table" id="newsCategoriesTable">
            <thead>
                <tr>
                    <th>Category Name</th>
                    <th>Description</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($category = mysqli_fetch_assoc($categories_result)) : ?>
                    <tr>
                        <td>
                            <a href="<?= ROOT_URL ?>admin/Manage-content/News/news-blog-list.php?news_category_id=<?= $category['id'] ?>"
                                class="category-link">
                                <?= htmlspecialchars($category['news_category']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars_decode($category['description']) ?></td>
                        <td class="action-buttons">
                            <a href="<?= ROOT_URL ?>admin/Manage-content/News/edit-news-categories.php?id=<?= $category['id'] ?>"
                                class="edit-btn">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <form action="<?= ROOT_URL ?>admin/Manage-content/News/delete-news-categories.php"
                                method="POST"
                                style="display:inline;">
                                <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                <input type="hidden" name="news_category" value="<?= $category['news_category'] ?>">
                                <button type="submit"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($category['news_category']) ?>?');">
                                    <i class="fa fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <button id="prevPage" disabled>Previous</button>
        <div id="pageNumbers"></div>
        <button id="nextPage">Next</button>
    </div>
</div>

<script>
    // Define variables for pagination
    const rowsPerPage = 8;
    let currentPage = 1;
    let filteredRows = [];

    // Function to apply filters and search
    function applyFiltersAndSearch() {
        const searchTerm = document.getElementById('newsCategorySearch').value.toLowerCase();
        const allRows = document.querySelectorAll('#newsCategoriesTable tbody tr');

        // First make all rows visible for filtering
        allRows.forEach(row => {
            row.style.display = '';
        });

        // Apply search to get filtered rows
        filteredRows = Array.from(allRows).filter(row => {
            if (searchTerm) {
                const categoryName = row.children[0].textContent.toLowerCase();
                const description = row.children[1].textContent.toLowerCase();
                return categoryName.includes(searchTerm) || description.includes(searchTerm);
            }
            return true;
        });

        // Reset to first page and update pagination
        currentPage = 1;
        updatePagination();
    }

    // Function to handle pagination display
    function updatePagination() {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);

        // Update pagination controls
        document.getElementById('prevPage').disabled = currentPage <= 1;
        document.getElementById('nextPage').disabled = currentPage >= totalPages || totalPages === 0;

        // Generate page number buttons
        const pageNumbers = document.getElementById('pageNumbers');
        pageNumbers.innerHTML = '';

        for (let i = 1; i <= totalPages; i++) {
            const pageNumber = document.createElement('button');
            pageNumber.textContent = i;
            pageNumber.classList.add('page-number');
            if (i === currentPage) {
                pageNumber.classList.add('active');
            }
            pageNumber.addEventListener('click', () => {
                currentPage = i;
                showCurrentPage();
            });
            pageNumbers.appendChild(pageNumber);
        }

        // Show the current page
        showCurrentPage();
    }

    // Function to show the current page of results
    function showCurrentPage() {
        // First hide all rows
        const allRows = document.querySelectorAll('#newsCategoriesTable tbody tr');
        allRows.forEach(row => {
            row.style.display = 'none';
        });

        // Calculate which filtered rows to show on current page
        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = Math.min(startIndex + rowsPerPage, filteredRows.length);

        // Show only the rows for the current page
        for (let i = startIndex; i < endIndex; i++) {
            filteredRows[i].style.display = '';
        }

        // Update button states
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        document.getElementById('prevPage').disabled = currentPage <= 1;
        document.getElementById('nextPage').disabled = currentPage >= totalPages || totalPages === 0;

        // Update active page number
        document.querySelectorAll('.page-number').forEach((btn, index) => {
            btn.classList.toggle('active', index + 1 === currentPage);
        });
    }

    // Event listeners
    document.getElementById('newsCategorySearch').addEventListener('input', applyFiltersAndSearch);

    document.getElementById('prevPage').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            showCurrentPage();
        }
    });

    document.getElementById('nextPage').addEventListener('click', () => {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            showCurrentPage();
        }
    });

    // Initialize the page
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize filteredRows with all rows initially
        filteredRows = Array.from(document.querySelectorAll('#newsCategoriesTable tbody tr'));
        updatePagination();
    });

    // Keep the alert message auto-dismiss
    setTimeout(() => {
        const alertMessage = document.getElementById('alert-message');
        if (alertMessage) {
            alertMessage.style.display = 'none';
        }
    }, 5000);
</script>

<script src="<?= ROOT_URL ?>js/alert.js"></script>
