<?php
include '../admin-sidebar.php'; // Include Sidebar

// Fetch all user details
$users_query = "SELECT id, email, firstName, lastName, userName, phoneNumber, avatar, isBanned, joined_at FROM usersmember ORDER BY id ASC";
$users_result = mysqli_query($connection, $users_query);

$total_users = mysqli_num_rows($users_result);

// Count active and banned users this made 
$active_query = "SELECT COUNT(*) as active_count FROM usersmember WHERE isBanned = 0";
$active_result = mysqli_query($connection, $active_query);
$active_count = mysqli_fetch_assoc($active_result)['active_count'];

$banned_count = $total_users - $active_count;

?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/manage-contents.css">
<div class="content">
    <div class="page-header">
        <h1>Users Management</h1>
        <div class="user-stats">
            <div class="stat-box">
                <span class="stat-value"><?= $total_users ?></span>
                <span class="stat-label">Total Users</span>
            </div>
            <div class="stat-box active">
                <span class="stat-value"><?= $active_count ?></span>
                <span class="stat-label">Active</span>
            </div>
            <div class="stat-box banned">
                <span class="stat-value"><?= $banned_count ?></span>
                <span class="stat-label">Banned</span>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['users-list-ban'])): ?>
        <div class="alert-message success" id="alert-message">
            <p>
                <?= $_SESSION['users-list-ban'];
                unset($_SESSION['users-list-ban']); ?>
            </p>
        </div>
    <?php endif ?>

    <div class="table-actions">
        <div class="search-container">
            <input type="text" id="userSearch" placeholder="Search users...">
        </div>
        <div class="filter-container">
            <select id="statusFilter">
                <option value="all">All Users</option>
                <option value="active">Active Users</option>
                <option value="banned">Banned Users</option>
            </select>
        </div>
    </div>

    <div class="table-container">
        <table class="styled-table" id="usersTable">
            <thead>
                <tr>
                    <th>Profile</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($users_result)): ?>
                    <tr class="<?= $user['isBanned'] ? 'banned-row' : '' ?>"
                        data-status="<?= $user['isBanned'] ? 'banned' : 'active' ?>">
                        <td>
                            <div class="user-avatar">
                                <img src="<?= ROOT_URL . 'img/users/' . $user['avatar'] ?>" alt="Profile" class="table-img">
                            </div>
                        </td>
                        <td><?= $user['userName'] ?></td>
                        <td><?= $user['firstName'] . ' ' . $user['lastName'] ?></td>
                        <td>
                            <a href="mailto:<?= $user['email'] ?>" class="user-email"><?= $user['email'] ?></a>
                        </td>
                        <td><?= $user['phoneNumber'] ?></td>
                        <td>
                            <span class="status-badge <?= $user['isBanned'] ? 'banned' : 'active' ?>">
                                <?= $user['isBanned'] ? 'Banned' : 'Active' ?>
                            </span>
                        </td>
                        <td class="action-buttons">
                            <!-- View Button -->
                            <button class="view-btn" onclick="viewUserDetails(<?= $user['id'] ?>)"
                                data-tooltip="View Details">
                                <i class="fa fa-eye"></i> View
                            </button>

                            <!-- Ban/Unban Form -->
                            <form action="<?= ROOT_URL ?>admin/Users/users-list-ban.php" method="POST"
                                style="display: inline;">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                <button type="submit" class="<?= $user['isBanned'] ? 'unban-btn' : 'ban-btn' ?>"
                                    onclick="return confirm('Are you sure you want to <?= $user['isBanned'] ? 'Unban' : 'Ban' ?> this user?');"
                                    data-tooltip="<?= $user['isBanned'] ? 'Unban User' : 'Ban User' ?>">
                                    <i class="fa <?= $user['isBanned'] ? 'fa-unlock' : 'fa-ban' ?>"></i>
                                    <?= $user['isBanned'] ? 'Unban' : 'Ban' ?>
                                </button>
                            </form>

                            <!-- Delete Form -->
                            <form action="<?= ROOT_URL ?>admin/Users/users-delete.php" method="POST"
                                style="display: inline;">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                <button type="submit" class="delete-btn"
                                    onclick="return confirm('Are you sure you want to delete user <?= $user['userName'] ?>?');"
                                    data-tooltip="Delete User">
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

<!-- User Details Modal -->
<div id="userModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="document.getElementById('userModal').style.display='none'">&times;</span>
        <div id="userDetails">Loading...</div>
    </div>
</div>

<script>
    //  Search functionality
    document.getElementById('userSearch').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('#usersTable tbody tr');
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
        
        // Apply status filter if active
        const statusFilter = document.getElementById('statusFilter').value;
        if (statusFilter !== 'all') {
            visibleRows = visibleRows.filter(row => row.dataset.status === statusFilter);
        }
        
        // Update pagination based on filtered results
        updatePaginationForFilteredResults(visibleRows);
    });

    // Updated Filter functionality
    document.getElementById('statusFilter').addEventListener('change', function() {
        const filter = this.value;
        const searchTerm = document.getElementById('userSearch').value.toLowerCase();
        const rows = document.querySelectorAll('#usersTable tbody tr');
        let visibleRows = [];

        rows.forEach(row => {
            const username = row.children[1].textContent.toLowerCase();
            const fullName = row.children[2].textContent.toLowerCase();
            const email = row.children[3].textContent.toLowerCase();
            const matchesSearch = searchTerm === '' || 
                                  username.includes(searchTerm) || 
                                  fullName.includes(searchTerm) || 
                                  email.includes(searchTerm);

            if (matchesSearch && (filter === 'all' || row.dataset.status === filter)) {
                row.style.display = '';
                visibleRows.push(row);
            } else {
                row.style.display = 'none';
            }
        });

        // Reset pagination for filtered results
        currentPage = 1;
        updatePaginationForFilteredResults(visibleRows);
    });

    // Helper function to get currently visible rows
    function getVisibleRows() {
        const statusFilter = document.getElementById('statusFilter').value;
        const searchTerm = document.getElementById('userSearch').value.toLowerCase();
        return Array.from(document.querySelectorAll('#usersTable tbody tr')).filter(row => {
            // Check if row matches search term
            const username = row.children[1].textContent.toLowerCase();
            const fullName = row.children[2].textContent.toLowerCase();
            const email = row.children[3].textContent.toLowerCase();
            const matchesSearch = searchTerm === '' || 
                                  username.includes(searchTerm) || 
                                  fullName.includes(searchTerm) || 
                                  email.includes(searchTerm);
            
            // Check if row matches status filter
            const matchesStatus = statusFilter === 'all' || row.dataset.status === statusFilter;
            
            return matchesSearch && matchesStatus;
        });
    }

    // Update pagination for filtered results
    function updatePaginationForFilteredResults(visibleRows) {
        const filteredTotalPages = Math.ceil(visibleRows.length / rowsPerPage);
        
        document.getElementById('prevPage').disabled = currentPage === 1;
        document.getElementById('nextPage').disabled = currentPage === filteredTotalPages || filteredTotalPages === 0;
        
        const pageNumbers = document.getElementById('pageNumbers');
        pageNumbers.innerHTML = '';
        
        for (let i = 1; i <= filteredTotalPages; i++) {
            const pageNumber = document.createElement('button');
            pageNumber.textContent = i;
            pageNumber.classList.add('page-number');
            if (i === currentPage) {
                pageNumber.classList.add('active');
            }
            pageNumber.addEventListener('click', () => {
                currentPage = i;
                showFilteredPage(currentPage);
            });
            pageNumbers.appendChild(pageNumber);
        }
        
        showFilteredPage(currentPage);
    }

    // Show page with filtered results
    function showFilteredPage(page) {
        const visibleRows = getVisibleRows();
        const start = (page - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        
        visibleRows.forEach((row, index) => {
            if (index >= start && index < end) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        
        // Update pagination controls
        const filteredTotalPages = Math.ceil(visibleRows.length / rowsPerPage);
        document.getElementById('prevPage').disabled = page === 1;
        document.getElementById('nextPage').disabled = page === filteredTotalPages || filteredTotalPages === 0;
    }

    // Pagination functionality
    const rowsPerPage = 10;
    let currentPage = 1;
    const rows = document.querySelectorAll('#usersTable tbody tr');
    const totalPages = Math.ceil(rows.length / rowsPerPage);

    function showPage(page) {
        // If we have active filters, use filtered pagination
        if (document.getElementById('statusFilter').value !== 'all' || 
            document.getElementById('userSearch').value !== '') {
            showFilteredPage(page);
            return;
        }
        
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
        const visibleRows = getVisibleRows();
        const totalFilteredPages = Math.ceil(visibleRows.length / rowsPerPage);
        
        if (currentPage < (document.getElementById('statusFilter').value !== 'all' || 
                           document.getElementById('userSearch').value !== '' ? 
                           totalFilteredPages : totalPages)) {
            currentPage++;
            showPage(currentPage);
        }
    });

    // Modal functionality
    function viewUserDetails(userId) {
        // Fetch user details using PHP via query string
        fetch(`get-user-details.php?user_id=${userId}`)
            .then(response => response.text())
            .then(data => {
                // Insert fetched details into modal
                document.getElementById('userDetails').innerHTML = data;
                document.getElementById('userModal').style.display = 'block';
            })
            .catch(error => console.error('Error fetching user details:', error));
    }

    document.querySelector('.close').addEventListener('click', () => {
        document.getElementById('userModal').style.display = 'none';
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

</body>

</html>