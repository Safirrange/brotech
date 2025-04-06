<?php
include '../../admin-sidebar.php';

// Fetch all categories that are not deleted
$categories_query = "SELECT * FROM gadget_category WHERE deleted_at IS NULL ORDER BY gadget_category ASC";
$categories_result = mysqli_query($connection, $categories_query);

$total_categories = mysqli_num_rows($categories_result);
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/manage-contents.css">
<div class="content">
    <div class="page-header">
        <h1>Latest Technology Gadgets</h1>
        <div class="user-stats">
            <div class="stat-box">
                <span class="stat-value"><?= $total_categories ?></span>
                <span class="stat-label">Total Gadgets</span>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['add-gadget-category-success'])): ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['add-gadget-category-success'];
                unset($_SESSION['add-gadget-category-success']); ?></p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['edit-gadget-category-success'])): ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['edit-gadget-category-success'];
                unset($_SESSION['edit-gadget-category-success']); ?></p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['delete-gadget-category-success'])): ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['delete-gadget-category-success'];
                unset($_SESSION['delete-gadget-category-success']); ?></p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['delete-gadget-category'])): ?>
        <div class="alert-message error" id="alert-message">
            <p><?= $_SESSION['delete-gadget-category'];
                unset($_SESSION['delete-gadget-category']); ?></p>
        </div>
    <?php endif ?>

    <div class="table-actions">
        <div class="search-container">
            <input type="text" id="gadgetSearch" placeholder="Search gadgets...">
        </div>
        <div class="action-buttons">
            <a href="<?= ROOT_URL ?>admin/Manage-content/Gadget/add-gadget.php" class="add-btn">
                <i class="fa fa-plus"></i> Add Category
            </a>
        </div>
    </div>

    <div class="table-container">
        <table class="styled-table" id="gadgetsTable">
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
                                <img src="<?= ROOT_URL ?>img/repairability/<?= htmlspecialchars($category['gadget_img']) ?>"
                                    alt="<?= htmlspecialchars($category['gadget_category']) ?>"
                                    class="table-img">
                            </div>
                        </td>
                        <td>
                            <a href="<?= ROOT_URL ?>admin/Manage-content/Gadget/gadget-types.php?gadget_category_id=<?= $category['id'] ?>"
                                class="category-link">
                                <?= ($category['gadget_category']) ?>
                            </a>
                        </td>
                        <td class="action-buttons">
                            <a href="<?= ROOT_URL ?>admin/Manage-content/Gadget/edit-gadget.php?id=<?= $category['id'] ?>"
                                class="edit-btn">
                                <i class="fa fa-edit"></i> Edit
                            </a>

                            <form action="<?= ROOT_URL ?>admin/Manage-content/Gadget/delete-gadget.php"
                                method="POST"
                                style="display: inline;">
                                <input type="hidden" name="id" value="<?= $category['id'] ?>">
                                <button type="submit"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($category['gadget_category']) ?>?');">
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
    document.getElementById('gadgetSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#gadgetsTable tbody tr');
        let visibleRows = [];

        rows.forEach(row => {
            const gadgetName = row.children[1].textContent.toLowerCase();
            if (gadgetName.includes(searchTerm)) {
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
    const rows = document.querySelectorAll('#gadgetsTable tbody tr');
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