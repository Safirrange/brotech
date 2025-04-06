<?php
include '../../admin-sidebar.php'; // Database connection

// Get category_id from the URL
if (isset($_GET['brand_id']) && isset($_GET['category_id'])) {
    $brand_id = intval($_GET['brand_id']);
    $category_id = intval($_GET['category_id']);

    // Fetch category details (for the page title)
    $brand_query = "SELECT * FROM guide_brands WHERE id = $brand_id AND deleted_at IS NULL";
    $brand_result = mysqli_query($connection, $brand_query);
    $brand = mysqli_fetch_assoc($brand_result);

    if (!$brand) {
        header('Location: ' . ROOT_URL . 'admin/Manage-content/Guides/brands-guides.php?category_id=' . $category_id);
        exit();
    }

    // Fetch all types under the selected brand
    $types_query = "SELECT * FROM guide_types WHERE brand_id = $brand_id AND deleted_at IS NULL";
    $types_result = mysqli_query($connection, $types_query);

    $total_types = mysqli_num_rows($types_result);
} else {
    header('Location: ' . ROOT_URL . 'admin/Manage-content/Guides/brands-guides.php?category_id=' . $category_id);
    exit();
}
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/manage-contents.css">

<div class="content">
    <div class="page-header">
        <h1>Types under <?= htmlspecialchars($brand['brand']) ?></h1>

        <div class="user-stats">
            <div class="stat-box">
                <span class="stat-value"><?= $total_types ?></span>
                <span class="stat-label">Total <?= htmlspecialchars($brand['brand']) ?> Phones</span>
            </div>
        </div>
    </div>

    <!-- Success message -->
    <?php if (isset($_SESSION['add-type-success'])) : ?>
        <div class="alert-message success" id="alert-message">
            <p>
                <?= $_SESSION['add-type-success'];
                unset($_SESSION['add-type-success']); ?>
            </p>
        </div>
    <?php endif ?>

    <!-- Success message -->
    <?php if (isset($_SESSION['edit-type-success'])) : ?>
        <div class="alert-message success" id="alert-message">
            <p>
                <?= $_SESSION['edit-type-success'];
                unset($_SESSION['edit-type-success']); ?>
            </p>
        </div>
    <?php endif ?>

    <!-- Success message -->
    <?php if (isset($_SESSION['delete-type-success'])) : ?>
        <div class="alert-message success" id="alert-message">
            <p>
                <?= $_SESSION['delete-type-success'];
                unset($_SESSION['delete-type-success']); ?>
            </p>
        </div>
    <?php endif ?>

    <!-- Error message -->
    <?php if (isset($_SESSION['delete-type-error'])) : ?>
        <div class="alert-message error" id="alert-message">
            <p>
                <?= $_SESSION['delete-type-error'];
                unset($_SESSION['delete-type-error']); ?>
            </p>
        </div>
    <?php endif ?>

    <div class="table-actions">
        <div class="search-container">
            <input type="text" id="typeSearch" placeholder="Search types...">
        </div>

        <div class="action-buttons">
            <a href="<?= ROOT_URL ?>admin/Manage-content/Guides/type-add.php?brand_id=<?= $brand_id ?>&category_id=<?= $category_id ?>"
                class="add-btn">
                <i class="fa fa-plus"></i> Add Type
            </a>
            <a href="<?= ROOT_URL ?>admin/Manage-content/Guides/brands.php?category_id=<?= $category_id ?>"
                class="back-btn">
                <i class="fa fa-arrow-left"></i> Back to Brands
            </a>
        </div>
    </div>

    <div class="table-container">
        <table class="styled-table" id="typesTable">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Type</th>
                    <th>Released Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($type = mysqli_fetch_assoc($types_result)) : ?>
                    <tr>
                        <td>
                            <div class="data-image">
                                <img src="<?= ROOT_URL ?>img/guides/<?= htmlspecialchars($type['image']) ?>"
                                    alt="<?= htmlspecialchars($type['name']) ?>"
                                    class="table-img">
                        </td>
                        <td>
                            <!-- Display the type name as a link to its details page -->
                            <a href="<?= ROOT_URL ?>admin/Manage-content/Guides/type.php?brand_id=<?= $brand_id ?>&category_id=<?= $category_id ?>"
                                class="category-link">
                                <?= htmlspecialchars($type['name']) ?>
                            </a>
                        </td>
                        <td>
                            <?= htmlspecialchars($type['released_date']) ?>
                        </td>
                        <td class="action-buttons">
                            <!-- View Button -->
                            <button class="view-btn" onclick="viewTypeDetails(<?= $type['id'] ?>)">
                                <i class="fa fa-eye"></i> View Specs
                            </button>

                            <a href="<?= ROOT_URL ?>admin/Manage-content/Guides/type-edit.php?category_id=<?= $category_id ?>&brand_id=<?= $brand_id ?>&type_id=<?= $type['id'] ?>"
                                class="edit-btn">
                                <i class="fa fa-edit"></i> Edit
                            </a>

                            <form action="<?= ROOT_URL ?>admin/Manage-content/Guides/type-delete.php"
                                method="POST"
                                style="display: inline;">
                                <input type="hidden" name="type_id" value="<?= $type['id'] ?>">
                                <input type="hidden" name="brand_id" value="<?= $brand_id ?>">
                                <input type="hidden" name="category_id" value="<?= $category_id ?>">
                                <button type="submit"
                                    class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($type['name']) ?>?');">
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
<!-- Type Details Modal -->
<div id="typeModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('typeModal').style.display='none'">&times;</span>
        <div id="typeDetails">Loading...</div>
    </div>
</div>

<script>
    // Search functionality
    document.getElementById('typeSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#typesTable tbody tr');
        let visibleRows = [];

        rows.forEach(row => {
            const typeName = row.children[1].textContent.toLowerCase();
            if (typeName.includes(searchTerm)) {
                row.style.display = '';
                visibleRows.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        // Reset pagination for filtered results
        currentPage = 1;
        if (searchTerm === '') {
            showPage(1);
        } else {
            const filteredTotalPages = Math.ceil(visibleRows.length / rowsPerPage);
            updatePaginationControls();
        }
    });

    // Pagination functionality
    const rowsPerPage = 5;
    let currentPage = 1;
    const rows = document.querySelectorAll('#typesTable tbody tr');
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

    // Initialize pagination
    showPage(1);

    // Modal functionality for specs
    function viewTypeDetails(typeId) {
        const modal = document.getElementById('typeModal');
        const detailsContainer = document.getElementById('typeDetails');

        detailsContainer.innerHTML = 'Loading...';
        modal.style.display = 'block';

        fetch(`specs.php?type_id=${typeId}`)
            .then(response => response.text())
            .then(data => {
                detailsContainer.innerHTML = data;
            })
            .catch(error => {
                detailsContainer.innerHTML = 'Error loading specs: ' + error;
            });
    }

    // Modal close handlers
    document.querySelector('.close').addEventListener('click', () => {
        document.getElementById('typeModal').style.display = 'none';
    });

    window.addEventListener('click', (event) => {
        const modal = document.getElementById('typeModal');
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Alert message auto-dismiss
    setTimeout(() => {
        document.querySelectorAll('.alert-message').forEach(alert => {
            alert.style.display = 'none';
        });
    }, 5000);
</script>

<script src="<?= ROOT_URL ?>js/alert.js"></script>