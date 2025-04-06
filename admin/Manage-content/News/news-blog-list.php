<?php
include '../../admin-sidebar.php'; // Include your database connection

// Check if category ID is provided
if (isset($_GET['news_category_id'])) {
    $category_id = intval($_GET['news_category_id']);

    // Fetch the category name
    $category_query = "SELECT news_category FROM news_category WHERE id = $category_id AND deleted_at IS NULL";
    $category_result = mysqli_query($connection, $category_query);
    $category = mysqli_fetch_assoc($category_result);

    $category_name = $category['news_category'];

    // Determine the filter status
    $status_filter = $_GET['filter'] ?? 'all';
    $status_condition = '';

    switch ($status_filter) {
        case 'pending':
            $status_condition = "AND n.isVerifiedToPublish = 0";
            break;
        case 'approved':
            $status_condition = "AND n.isVerifiedToPublish = 1";
            break;
        case 'rejected':
            $status_condition = "AND n.isVerifiedToPublish = 2";
            break;
    }

    // Fetch all news related to the selected category
    $news_query = "
        SELECT n.*, u.username AS author_name 
        FROM news n
        INNER JOIN news_category_relations ncr ON n.id = ncr.news_id
        LEFT JOIN usersmember u ON n.newsAuthor = u.id
        WHERE ncr.category_id = $category_id 
        $status_condition
        AND n.deleted_at IS NULL
        ORDER BY n.publishedDate DESC
    ";
} else {
    // View all news without a specific category filter
    $status_filter = $_GET['filter'] ?? 'all';
    $status_condition = '';

    switch ($status_filter) {
        case 'pending':
            $status_condition = "WHERE n.isVerifiedToPublish = 0";
            break;
        case 'approved':
            $status_condition = "WHERE n.isVerifiedToPublish = 1";
            break;
        case 'rejected':
            $status_condition = "WHERE n.isVerifiedToPublish = 2";
            break;
        default:
            $status_condition = "WHERE 1=1"; // This ensures proper SQL syntax
    }

    // Fetch all news
    $news_query = "
        SELECT n.*, u.username AS author_name 
        FROM news n
        LEFT JOIN usersmember u ON n.newsAuthor = u.id
        $status_condition
        AND n.deleted_at IS NULL
        ORDER BY n.id DESC
    ";
}

$news_result = mysqli_query($connection, $news_query);
$total_news = mysqli_num_rows($news_result);
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/manage-contents.css">
<div class="content">
    <div class="page-header">
        <h1><?= isset($category_id) ? "News Articles in \"" . htmlspecialchars($category_name) . "\"" : "All News Articles" ?></h1>
        <div class="user-stats">
            <div class="stat-box">
                <span class="stat-value"><?= $total_news ?></span>
                <span class="stat-label">Total Articles</span>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['delete-news-success'])) : ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['delete-news-success'];
                unset($_SESSION['delete-news-success']); ?></p>
        </div>
    <?php endif ?>
    <?php if (isset($_SESSION['delete-news'])) : ?>
        <div class="alert-message error" id="alert-message">
            <p><?= $_SESSION['delete-news'];
                unset($_SESSION['delete-news']); ?></p>
        </div>
    <?php endif ?>

    <div class="table-actions">
        <div class="search-container">
            <input type="text" id="newsSearch" placeholder="Search articles...">
        </div>
        <div class="filter-buttons">
            <a href="<?= isset($category_id) ? "?news_category_id=$category_id&filter=all" : "?filter=all" ?>"
                class="filter-btn <?= $status_filter === 'all' ? 'active' : '' ?>">
                <i class="fa fa-list"></i> All
            </a>
            <a href="<?= isset($category_id) ? "?news_category_id=$category_id&filter=pending" : "?filter=pending" ?>"
                class="filter-btn <?= $status_filter === 'pending' ? 'active' : '' ?>">
                <i class="fa fa-clock-o"></i> Pending
            </a>
            <a href="<?= isset($category_id) ? "?news_category_id=$category_id&filter=approved" : "?filter=approved" ?>"
                class="filter-btn <?= $status_filter === 'approved' ? 'active' : '' ?>">
                <i class="fa fa-check"></i> Approved
            </a>
            <a href="<?= isset($category_id) ? "?news_category_id=$category_id&filter=rejected" : "?filter=rejected" ?>"
                class="filter-btn <?= $status_filter === 'rejected' ? 'active' : '' ?>">
                <i class="fa fa-times"></i> Rejected
            </a>
        </div>
        <div class="action-buttons">
            <a href="<?= ROOT_URL ?>admin/Manage-content/News/news-categories.php" class="back-btn">
                <i class="fa fa-arrow-left"></i> Back to Categories
            </a>
        </div>
    </div>

    <div class="table-container">
        <table class="styled-table" id="newsTable">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Published Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($news_result) > 0) : ?>
                    <?php while ($news = mysqli_fetch_assoc($news_result)) : ?>
                        <?php
                        $status_text = match ((int)$news['isVerifiedToPublish']) {
                            0 => '<span class="status pending">Pending</span>',
                            1 => '<span class="status approved">Published</span>',
                            2 => '<span class="status rejected">Rejected</span>',
                        };
                        ?>
                        <tr>
                            <td>
                                <a href="<?= ROOT_URL ?>admin/Requests/news-approval.php?news_id=<?= $news['id']; ?>"
                                    class="news-link">
                                    <?= htmlspecialchars($news['title']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($news['author_name']) ?></td>
                            <td><?= date('F j, Y', strtotime($news['publishedDate'])) ?></td>
                            <td><?= $status_text ?></td>
                            <td class="action-buttons">
                                <a href="<?= ROOT_URL ?>admin/Requests/news-approval.php?entity_id=<?= $news['id']; ?>"
                                    class="view-link-btn">
                                    <i class="fa fa-eye"></i> View
                                </a>
                                <form action="<?= ROOT_URL ?>admin/Manage-content/News/delete-news.php"
                                    method="POST"
                                    style="display:inline;">
                                    <input type="hidden" name="id" value="<?= $news['id'] ?>">
                                    <input type="hidden" name="title" value="<?= $news['title'] ?>">
                                    <button type="submit"
                                        class="delete-btn"
                                        onclick="return confirm('Are you sure you want to delete this news?');">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="5" class="no-results">No news articles found.</td>
                    </tr>
                <?php endif; ?>
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
        const searchTerm = document.getElementById('newsSearch').value.toLowerCase();
        const allRows = document.querySelectorAll('#newsTable tbody tr');

        // First make all rows visible for filtering
        allRows.forEach(row => {
            row.style.display = '';
        });

        // Apply search to get filtered rows
        filteredRows = Array.from(allRows).filter(row => {
            if (searchTerm) {
                const title = row.children[0].textContent.toLowerCase();
                const author = row.children[1].textContent.toLowerCase();
                return title.includes(searchTerm) || author.includes(searchTerm);
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
        const allRows = document.querySelectorAll('#newsTable tbody tr');
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
    document.getElementById('newsSearch').addEventListener('input', applyFiltersAndSearch);

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
        filteredRows = Array.from(document.querySelectorAll('#newsTable tbody tr'));
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