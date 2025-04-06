<?php
include '../admin-sidebar.php'; // Include Sidebar

// Fetch activities with user details
$query = "SELECT ua.*, u.username, u.avatar 
          FROM user_activities ua 
          JOIN usersmember u ON ua.user_id = u.id 
          ORDER BY ua.created_at DESC 
          LIMIT 100";
$activities = mysqli_query($connection, $query);

// Count total activities
$total_query = "SELECT COUNT(*) as total FROM user_activities";
$total_result = mysqli_query($connection, $total_query);
$total_activities = mysqli_fetch_assoc($total_result)['total'];
?>

<link rel="stylesheet" href="<?= ROOT_URL ?>css/manage-contents.css">

<div class="content">
    <div class="page-header">
        <h1>User Activities</h1>
        <div class="activity-stats">
            <div class="stat-box">
                <span class="stat-value"><?= $total_activities ?></span>
                <span class="stat-label">Total Activities</span>
            </div>
        </div>
    </div>

    <div class="table-actions">
        <div class="search-container">
            <input type="text" id="activitySearch" placeholder="Search activities...">
        </div>

        <div class="filter-container">
            <select id="activityFilter">
                <option value="all">All Activities</option>
                <option value="create_news">News</option>
                <option value="gadget_rating">Gadget</option>
            </select>
        </div>
    </div>

    <div class="table-container">
        <table class="styled-table" id="activitiesTable">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Activity</th>
                    <th>Description</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($activity = mysqli_fetch_assoc($activities)) : ?>
                    <?php
                    $activity_type = strtolower($activity['activity_type']);
                    $activity_class = in_array($activity_type, ['create_news', 'gadget_rating']) ?
                        "activity-$activity_type" : "activity-other";
                    ?>
                    <tr data-activity="<?= $activity_type ?>">
                        <td>
                            <div class="user-avatar">
                                <img src="<?= ROOT_URL . 'img/users/' . $activity['avatar'] ?>" alt="" class="table-img">
                                <?= htmlspecialchars($activity['username']) ?>
                            </div>
                        </td>
                        <td><?= htmlspecialchars($activity['activity_type']) ?></td>
                        <td><?= htmlspecialchars($activity['activity_description']) ?></td>
                        <td><?= date('F j, Y, g:i a', strtotime($activity['created_at'])) ?></td>
                    </tr>
                <?php endwhile ?>
            </tbody>
        </table>
    </div>

    <div class="pagination">
        <button id="prevPage">Previous</button>
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
        const searchTerm = document.getElementById('activitySearch').value.toLowerCase();
        const filter = document.getElementById('activityFilter').value;
        const allRows = document.querySelectorAll('#activitiesTable tbody tr');

        // First make all rows visible for filtering
        allRows.forEach(row => {
            row.style.display = '';
        });

        // Apply search to get filtered rows
        filteredRows = Array.from(allRows).filter(row => {

            if (filter !== 'all' && row.dataset.activity !== filter) {
                return false;
            }

            if (searchTerm) {
                const username = row.children[0].textContent.toLowerCase();
                const activity = row.children[1].textContent.toLowerCase();
                const description = row.children[2].textContent.toLowerCase();

                if (!username.includes(searchTerm) &&
                    !activity.includes(searchTerm) &&
                    !description.includes(searchTerm)) {
                    return false;
                }
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
        const allRows = document.querySelectorAll('#activitiesTable tbody tr');
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
    document.getElementById('activitySearch').addEventListener('input', applyFiltersAndSearch);
    document.getElementById('activityFilter').addEventListener('change', applyFiltersAndSearch);

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
        filteredRows = Array.from(document.querySelectorAll('#activitiesTable tbody tr'));
        updatePagination();
    });
</script>