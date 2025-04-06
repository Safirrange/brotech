<?php
include '../../admin-sidebar.php'; // Database connection

// Get category_id from the URL
if (isset($_GET['gadget_category_id'])) {
    $gadget_category_id = intval($_GET['gadget_category_id']);

    // Fetch category details (for the page title)
    $category_query = "SELECT gadget_category FROM gadget_category WHERE id = $gadget_category_id";
    $category_result = mysqli_query($connection, $category_query);
    $category = mysqli_fetch_assoc($category_result);

    if (!$category) {
        header('location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget.php');
        die();
    }

    // Fetch all types under the selected category
    $types_query = "SELECT * FROM gadget_types WHERE gadget_category_id = $gadget_category_id AND deleted_at IS NULL";
    $types_result = mysqli_query($connection, $types_query);
} else {
    header('location: ' . ROOT_URL . 'admin/Manage-content/Gadget/gadget.php');
    die();
}

// Get current admin's ratings for each type
$admin_id = $_SESSION['admin-id'];

// Count total types
$total_types = mysqli_num_rows($types_result);
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/manage-contents.css">
<div class="content">
    <div class="page-header">
        <h1>Types under <?= htmlspecialchars($category['gadget_category']) ?></h1>
        <div class="user-stats">
            <div class="stat-box">
                <span class="stat-value"><?= $total_types ?></span>
                <span class="stat-label">Total Brands</span>
            </div>
        </div>
    </div>

    <!-- Success message -->
    <?php if (isset($_SESSION['add-gadget-types-success'])) : ?>
        <div class="alert-message success" id="alert-message">
            <p>
                <?= $_SESSION['add-gadget-types-success'];
                unset($_SESSION['add-gadget-types-success']); ?>
            </p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['edit-gadget-types-success'])) : ?>
        <div class="alert-message success" id="alert-message">
            <p>
                <?= $_SESSION['edit-gadget-types-success'];
                unset($_SESSION['edit-gadget-types-success']); ?>
            </p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['delete-gadget-types-success'])) : ?>
        <div class="alert-message success" id="alert-message">
            <p>
                <?= $_SESSION['delete-gadget-types-success'];
                unset($_SESSION['delete-gadget-types-success']); ?>
            </p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['delete-gadget-types'])) : ?>
        <div class="alert-message error" id="alert-message">
            <p>
                <?= $_SESSION['delete-gadget-types'];
                unset($_SESSION['delete-gadget-types']); ?>
            </p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['rating-success'])) : ?>
        <div class="alert-message success" id="alert-message">
            <p>
                <?= $_SESSION['rating-success'];
                unset($_SESSION['rating-success']); ?>
            </p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['rating-error'])) : ?>
        <div class="alert-message error" id="alert-message">
            <p>
                <?= $_SESSION['rating-error'];
                unset($_SESSION['rating-error']); ?>
            </p>
        </div>
    <?php endif ?>

    <div class="table-actions">
        <div class="search-container">
            <input type="text" id="typesSearch" placeholder="Search brands...">
        </div>
        <div class="action-buttons">
            <a href="<?= ROOT_URL ?>admin/Manage-content/Gadget/gadget-types-add.php?gadget_category_id=<?= $gadget_category_id ?>" class="add-btn">
                <i class="fa fa-plus"></i> Add Brand
            </a>
            <a href="<?= ROOT_URL ?>admin/Manage-content/Gadget/gadget.php" class="back-btn">
                <i class="fa fa-arrow-left"></i> Back to Categories
            </a>
        </div>
    </div>

    <div class="table-container">
        <table class="styled-table" id="typesTable">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Brand Name</th>
                    <th>Price</th>
                    <th>Longevity</th>
                    <th>Repairability</th>
                    <th>Functionality</th>
                    <th>Your Rating</th>
                    <th>Overall Rating</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($type = mysqli_fetch_assoc($types_result)) : ?>
                    <?php
                    // Check if admin has rated this gadget
                    $rating_query = "SELECT * FROM admin_gadget_ratings 
                                   WHERE admin_id = ? AND gadget_type_id = ?";
                    $stmt = mysqli_prepare($connection, $rating_query);
                    mysqli_stmt_bind_param($stmt, "ii", $admin_id, $type['id']);
                    mysqli_stmt_execute($stmt);
                    $rating_result = mysqli_stmt_get_result($stmt);
                    $admin_rating = mysqli_fetch_assoc($rating_result);

                    // Get overall rating (average of all admin ratings)
                    $overall_query = "SELECT AVG((price_rating + longevity_rating + 
                                     repairability_rating + functionality_rating)/4) as overall 
                                     FROM admin_gadget_ratings 
                                     WHERE gadget_type_id = ?";
                    $stmt_overall = mysqli_prepare($connection, $overall_query);
                    mysqli_stmt_bind_param($stmt_overall, "i", $type['id']);
                    mysqli_stmt_execute($stmt_overall);
                    $overall_result = mysqli_stmt_get_result($stmt_overall);
                    $overall = mysqli_fetch_assoc($overall_result);
                    ?>
                    <tr>
                        <td>
                            <div class="data-image">
                                <img src="<?= ROOT_URL ?>img/gadgets/<?= htmlspecialchars($type['gadget_img']) ?>" alt="<?= htmlspecialchars($type['type_name']) ?>" class="table-img">
                            </div>
                        </td>
                        <td><?= ($type['type_name']) ?></td>

                        <?php if ($admin_rating) : ?>
                            <td><?= htmlspecialchars($admin_rating['price_rating']) ?>/10</td>
                            <td><?= htmlspecialchars($admin_rating['longevity_rating']) ?>/10</td>
                            <td><?= htmlspecialchars($admin_rating['repairability_rating']) ?>/10</td>
                            <td><?= htmlspecialchars($admin_rating['functionality_rating']) ?>/10</td>
                            <td><?= number_format(($admin_rating['price_rating'] + $admin_rating['longevity_rating'] +
                                    $admin_rating['repairability_rating'] + $admin_rating['functionality_rating']) / 4, 1) ?>/10</td>
                        <?php else : ?>
                            <td colspan="5" class="text-center">
                                <button onclick="showRatingModal(<?= $type['id'] ?>, '<?= htmlspecialchars($type['type_name'], ENT_QUOTES) ?>')" class="rate-btn">
                                    <i class="fa fa-star"></i> Add Rating
                                </button>
                            </td>
                        <?php endif; ?>

                        <td><?= number_format($overall['overall'] ?? 0, 1) ?>/10</td>
                        <td class="action-buttons">
                            <a href="<?= ROOT_URL ?>admin/Manage-content/Gadget/gadget-types-edit.php?id=<?= $type['id'] ?>" class="edit-btn">
                                <i class="fa fa-edit"></i> Edit
                            </a>
                            <form action="<?= ROOT_URL ?>admin/Manage-content/Gadget/gadget-types-delete.php" method="POST" style="display:inline;">
                                <input type="hidden" name="id" value="<?= $type['id'] ?>">
                                <input type="hidden" name="gadget_category_id" value="<?= $gadget_category_id ?>">
                                <button type="submit" class="delete-btn" onclick="return confirm('Are you sure you want to delete <?= htmlspecialchars($type['type_name']) ?>?');">
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

<div id="ratingModal" class="modal" style="display:none;">
    <div class="modal-content">
        <h2>Add Your Rating</h2>
        <form id="adminRatingForm" method="POST" action="<?= ROOT_URL?>admin/Manage-content/Gadget/gadget-types-rate.php">
            <input type="hidden" name="gadget_type_id" id="gadget_type_id">
            <input type="hidden" name="gadget_category_id" value="<?= $gadget_category_id ?>">

            <label>Price Rating (1-10):</label>
            <input type="number" name="price_rating" min="1" max="10" required>

            <label>Longevity Rating (1-10):</label>
            <input type="number" name="longevity_rating" min="1" max="10" required>

            <label>Repairability Rating (1-10):</label>
            <input type="number" name="repairability_rating" min="1" max="10" required>

            <label>Functionality Rating (1-10):</label>
            <input type="number" name="functionality_rating" min="1" max="10" required>

            <button type="submit">Submit Rating</button>
            <button type="button" onclick="closeRatingModal()">Cancel</button>
        </form>
    </div>
</div>

<div id="ratingModal" class="modal" style="display:none;">
    <div class="modal-content">
        <h2>Add Rating for <span id="gadgetName"></span></h2>
        <form id="adminRatingForm" method="POST" action="gadget-types-rate.php">
            <input type="hidden" name="gadget_type_id" id="gadget_type_id">
            <input type="hidden" name="gadget_type_name" id="gadget_type_name">
            <!-- ...rest of your form fields... -->
        </form>
    </div>
</div>

<script>
    function showRatingModal(typeId, typeName) {
        document.getElementById('gadget_type_id').value = typeId;
        document.getElementById('gadget_type_name').value = typeName;
        document.getElementById('gadgetName').textContent = typeName;
        document.getElementById('ratingModal').style.display = 'block';
    }
</script>

<script>
    function closeRatingModal() {
        document.getElementById('ratingModal').style.display = 'none';
    }
</script>

<script>
    // Search functionality
    document.getElementById('typesSearch').addEventListener('input', function() {
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

</body>

</html>