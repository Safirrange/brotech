<?php
include '../../admin-sidebar.php';

if (isset($_GET['category_id'])) {
    $category_id = intval($_GET['category_id']);

    // Fetch category details
    $category_query = "SELECT category FROM guide_categories WHERE id = $category_id AND deleted_at IS NULL";
    $category_result = mysqli_query($connection, $category_query);
    $category = mysqli_fetch_assoc($category_result);

    if (!$category) {
        header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/category-guides.php');
        die();
    }

    // Fetch all brands under the selected category
    $brands_query = "SELECT * FROM guide_brands WHERE category_id = $category_id AND deleted_at IS NULL";
    $brands_result = mysqli_query($connection, $brands_query);

    $total_brands = mysqli_num_rows($brands_result);
} else {
    header('location: ' . ROOT_URL . 'admin/Manage-content/Guides/category-guides.php');
    die();
}
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/manage-contents.css">
<div class="content">
    <div class="page-header">
        <h1>Brands under <?= htmlspecialchars($category['category']) ?></h1>
        <div class="user-stats">
            <div class="stat-box">
                <span class="stat-value"><?= $total_brands ?></span>
                <span class="stat-label">Total <?= htmlspecialchars($category['category']) ?> Brands</span>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['add-content-brands-success'])): ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['add-content-brands-success'];
                unset($_SESSION['add-content-brands-success']); ?></p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['edit-brand-success'])): ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['edit-brand-success'];
                unset($_SESSION['edit-brand-success']); ?></p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['delete-brand-success'])): ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['delete-brand-success'];
                unset($_SESSION['delete-brand-success']); ?></p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['delete-brand-error'])): ?>
        <div class="alert-message error" id="alert-message">
            <p><?= $_SESSION['delete-brand-error'];
                unset($_SESSION['delete-brand-error']); ?></p>
        </div>
    <?php endif ?>

    <div class="table-actions">
        <div class="search-container">
            <input type="text" id="brandSearch" placeholder="Search brands...">
        </div>
        <div class="action-buttons">
            <a href="<?= ROOT_URL ?>admin/Manage-content/Guides/brands-add.php?category_id=<?= $category_id ?>"
                class="add-btn">
                <i class="fa fa-plus"></i> Add Brand
            </a>
            <a href="<?= ROOT_URL ?>admin/Manage-content/Guides/category.php"
                class="back-btn">
                <i class="fa fa-arrow-left"></i> Back to Categories
            </a>
        </div>
    </div>

    <div class="table-container">
        <table class="styled-table" id="brandsTable">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Brand Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($brand = mysqli_fetch_assoc($brands_result)): ?>
                    <tr>
                        <td>
                            <div class="data-image">
                                <img src="<?= ROOT_URL ?>img/logos/<?= htmlspecialchars($brand['image']) ?>"
                                    alt="<?= htmlspecialchars($brand['brand']) ?>"
                                    class="table-img">
                            </div>
                        </td>
                        <td>
                            <a href="<?= ROOT_URL ?>admin/Manage-content/Guides/type.php?brand_id=<?= $brand['id'] ?>&category_id=<?= $category_id ?>"
                                class="category-link">
                                <?= htmlspecialchars($brand['brand']) ?>
                            </a>
                        </td>
                        <td class="action-buttons">
                            <a href="<?= ROOT_URL ?>admin/Manage-content/Guides/brands-edit.php?category_id=<?= $category_id ?>&brand_id=<?= $brand['id'] ?>"
                                class="edit-btn">
                                <i class="fa fa-edit"></i> Edit
                            </a>

                            <form action="<?= ROOT_URL ?>admin/Manage-content/Guides/brands-delete.php"
                                method="POST"
                                style="display: inline;">
                                <input type="hidden" name="brand_id" value="<?= $brand['id'] ?>">
                                <input type="hidden" name="category_id" value="<?= $category_id ?>">
                                <button type="submit"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($brand['brand']) ?>?');">
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
    // Search functionality
    document.getElementById('brandSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#brandsTable tbody tr');
        let visibleRows = [];

        rows.forEach(row => {
            const brandName = row.children[1].textContent.toLowerCase();
            if (brandName.includes(searchTerm)) {
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
    const rows = document.querySelectorAll('#brandsTable tbody tr');
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
        document.querySelectorAll('.alert-message').forEach(alert => {
            alert.style.display = 'none';
        });
    }, 5000);
</script>

<script src="<?= ROOT_URL ?>js/alert.js"></script>
</body>

</html>