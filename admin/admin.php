<?php
include 'admin-sidebar.php'; // Database connection

$users_query = "SELECT * FROM admin WHERE deleted_at IS NULL ORDER BY date_added ASC";
$users_result = mysqli_query($connection, $users_query);

$total_admins = mysqli_num_rows($users_result);

?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/manage-contents.css">
<div class="content">
    <div class="page-header">
        <h1>Admin Management</h1>
        <div class="user-stats">
            <div class="stat-box">
                <span class="stat-value"><?= $total_admins ?></span>
                <span class="stat-label">Total Admins</span>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['add-admin-success'])) : ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['add-admin-success'];
                unset($_SESSION['add-admin-success']); ?></p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['edit-admin-success'])) : ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['edit-admin-success'];
                unset($_SESSION['edit-admin-success']); ?></p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['delete-admin-success'])) : ?>
        <div class="alert-message success" id="alert-message">
            <p><?= $_SESSION['delete-admin-success'];
                unset($_SESSION['delete-admin-success']); ?></p>
        </div>
    <?php endif ?>

    <?php if (isset($_SESSION['delete-admin'])) : ?>
        <div class="alert-message error" id="alert-message">
            <p><?= $_SESSION['delete-admin'];
                unset($_SESSION['delete-admin']); ?></p>
        </div>
    <?php endif ?>

    <div class="table-actions">
        <div class="search-container">
            <input type="text" id="adminSearch" placeholder="Search admins...">
        </div>
        <div class="action-buttons">
            <a href="<?= ROOT_URL ?>admin/add-admin.php" class="add-btn">
                <i class="fa fa-plus"></i> Add Admin
            </a>
        </div>
    </div>

    <div class="table-container">
        <table class="styled-table" id="adminsTable">
            <thead>
                <tr>
                    <th>Profile</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($users_result)) : ?>
                    <tr>
                        <td>
                            <div class="user-avatar">
                                <img src="<?= ROOT_URL . 'img/users/' . $user['avatar'] ?>"
                                    alt="Profile"
                                    class="table-img">
                            </div>
                        </td>
                        <td><?= htmlspecialchars($user['user']) ?></td>
                        <td><?= htmlspecialchars($user['full_name']) ?></td>
                        <td>
                            <a href="mailto:<?= htmlspecialchars($user['email']) ?>" class="user-email">
                                <?= htmlspecialchars($user['email']) ?>
                            </a>
                        </td>
                        <td><?= htmlspecialchars($user['phone']) ?></td>
                        <td class="action-buttons">
                            <!-- View Button -->
                            <button class="view-btn" onclick="viewAdminDetails(<?= $user['id'] ?>)"
                                data-tooltip="View Details">
                                <i class="fa fa-eye"></i> View
                            </button>

                            <!-- Delete Form -->
                            <form action="<?= ROOT_URL ?>admin/admin-delete.php" method="POST" style="display: inline;">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                <button type="submit" class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete admin <?= htmlspecialchars($user['user']) ?>?');"
                                    data-tooltip="Delete Admin">
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

<!-- Admin Details Modal -->
<div id="adminModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <div id="adminDetails">
            <!-- Admin details will be loaded here -->
        </div>
    </div>
</div>

<script>
    //  Search functionality
    document.getElementById('adminSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#adminsTable tbody tr');
        let visibleRows = [];

        rows.forEach(row => {
            const username = row.children[1].textContent.toLowerCase();
            const fullName = row.children[2].textContent.toLowerCase();
            const email = row.children[3].textContent.toLowerCase();

            if (username.includes(searchTerm) || fullName.includes(searchTerm) || email.includes(searchTerm)) {
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

    // Updated Filter functionality
    document.getElementById('statusFilter').addEventListener('change', function() {
        const filter = this.value;
        const rows = document.querySelectorAll('#usersTable tbody tr');
        let visibleRows = [];

        rows.forEach(row => {
            if (filter === 'all' || row.dataset.status === filter) {
                row.style.display = '';
                visibleRows.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        // Reset pagination for filtered results
        currentPage = 1;
        showPage(1);
    });

    // Pagination functionality
    const rowsPerPage = 10;
    let currentPage = 1;
    const rows = document.querySelectorAll('#adminsTable tbody tr');
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

    // Modal functionality
    function viewAdminDetails(adminId) {
        const modal = document.getElementById('adminModal');
        const adminDetails = document.getElementById('adminDetails');

        adminDetails.innerHTML = `<h2>Admin Details (ID: ${adminId})</h2>
                                <p>Loading admin details...</p>`;

        modal.style.display = 'block';
    }

    document.querySelector('.close').addEventListener('click', () => {
        document.getElementById('adminModal').style.display = 'none';
    });

    // Pagination event listeners
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