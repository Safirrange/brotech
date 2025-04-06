<?php
include '../../admin-sidebar.php';

// Fetch all categories
$categories_query = "SELECT id, category, image FROM guide_categories WHERE deleted_at IS NULL ORDER BY category ASC";
$categories_result = mysqli_query($connection, $categories_query);

$total_categories = mysqli_num_rows($categories_result);

?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/manage-contents.css">
<div class="content">
    <div class="page-header">
        <h1>Categories Management</h1>
        <div class="user-stats">
            <div class="stat-box">
                <span class="stat-value"><?= $total_categories ?></span>
                <span class="stat-label">Total Categories</span>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['add-content-category-success'])): ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['add-content-category-success'];
                unset($_SESSION['add-content-category-success']); ?></p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['edit-content-category-success'])): ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['edit-content-category-success'];
                unset($_SESSION['edit-content-category-success']); ?></p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['delete-content-category-success'])): ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['delete-content-category-success'];
                unset($_SESSION['delete-content-category-success']); ?></p>
        </div>
    <?php endif ?>

    <div class="table-actions">
        <div class="search-container">
            <input type="text" id="categorySearch" placeholder="Search categories...">
        </div>
        <a href="<?= ROOT_URL ?>admin/Manage-content/Guides/add-category.php" class="add-btn">
            <i class="fa fa-plus"></i> Add Category
        </a>
    </div>

    <div class="table-container">
        <table class="styled-table" id="categoriesTable">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Category Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($category = mysqli_fetch_assoc($categories_result)): ?>
                    <tr>
                        <td>
                            <div class="data-image">
                                <img src="<?= ROOT_URL ?>img/homepage/<?= htmlspecialchars($category['image']) ?>"
                                    alt="<?= htmlspecialchars($category['category']) ?>"
                                    class="table-img">
                            </div>
                        </td>
                        <td>
                            <a href="<?= ROOT_URL ?>admin/Manage-content/Guides/brands.php?category_id=<?= $category['id'] ?>"
                                class="category-link">
                                <?= htmlspecialchars($category['category']) ?>
                            </a>
                        </td>
                        <td class="action-buttons">
                            <a href="<?= ROOT_URL ?>admin/Manage-content/Guides/edit-category.php?id=<?= $category['id'] ?>"
                                class="edit-btn">
                                <i class="fa fa-edit"></i> Edit
                            </a>

                            <form action="<?= ROOT_URL ?>admin/Manage-content/Guides/delete-category.php"
                                method="POST"
                                style="display: inline;">
                                <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                <button type="submit"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($category['category']) ?>?');">
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
    document.getElementById('categorySearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#categoriesTable tbody tr');
        let visibleRows = [];

        rows.forEach(row => {
            const categoryName = row.children[1].textContent.toLowerCase();
            if (categoryName.includes(searchTerm)) {
                row.style.display = '';
                visibleRows.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        // Reset pagination for filtered results
        currentPage = 1;
        if (searchTerm === '') {
            // If search is cleared, show original pagination
            showPage(1);
        } else {
            // Update pagination based on filtered results
            const filteredTotalPages = Math.ceil(visibleRows.length / rowsPerPage);
            updatePaginationControls();
        }
    });

    // Pagination functionality
    const rowsPerPage = 5;
    let currentPage = 1;
    const rows = document.querySelectorAll('#categoriesTable tbody tr');
    const totalPages = Math.ceil(rows.length / rowsPerPage);

    function showPage(page) {
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;

        rows.forEach((row, index) => {
            if (index >= start && index < end) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        updatePaginationControls();
    }

    function updatePaginationControls() {
        document.getElementById('prevPage').disabled = currentPage === 1;
        document.getElementById('nextPage').disabled = currentPage === totalPages;

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
                showPage(currentPage);
            });
            pageNumbers.appendChild(pageNumber);
        }
    }

    document.getElementById('prevPage').addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            showPage(currentPage);
        }
    });

    document.getElementById('nextPage').addEventListener('click', () => {
        if (currentPage < totalPages) {
            currentPage++;
            showPage(currentPage);
        }
    });

    // Initialize
    showPage(1);

    // Alert message auto-dismiss
    setTimeout(() => {
        const alertMessage = document.getElementById('alert-message');
        if (alertMessage) {
            alertMessage.style.display = 'none';
        }
    }, 5000);
</script>

<script src="<?= ROOT_URL ?>js/alert.js"></script>