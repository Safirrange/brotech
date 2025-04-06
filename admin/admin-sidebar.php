<?php
require __DIR__ . '/../config/database.php';

// Define restricted folders
$restricted_folders = ['admin'];

// Get the directory name of the current script
$current_directory = basename(dirname($_SERVER['SCRIPT_FILENAME']));

// Restrict access if the script is inside a restricted folder
if (in_array($current_directory, $restricted_folders)) {
    // If the user is accessing admin files without admin privileges
    if (!isset($_SESSION['admin-id'])) {
        header('Location: ' . ROOT_URL . 'admin/login.php'); // Redirect to admin login
        exit();
    }
}

// Count pending requests
$requestQuery = "SELECT COUNT(*) AS pending_count FROM notifications WHERE status = 'pending'";
$requestResult = mysqli_query($connection, $requestQuery);
$requestData = mysqli_fetch_assoc($requestResult);
$pendingRequests = $requestData['pending_count'];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/admin-sidebar.css">
    <link rel="stylesheet" href="<?= ROOT_URL ?>css/admin-dashboard.css">

</head>


<div class="sidebar">
    <h2>Admin Panel</h2>
    <ul>
        <li><a href="<?= ROOT_URL ?>admin/admin-dashboard.php">Dashboard</a></li>
        <li><a href="<?= ROOT_URL ?>admin/Users/users-list.php">Users</a></li>
        <li><a href="<?= ROOT_URL ?>admin/Users/professionals-lists.php">Professionals</a></li>
        <li><a href="<?= ROOT_URL ?>admin/Manage-content/manage-content.php">Manage Content</a></li>
        <li><a href="<?= ROOT_URL ?>admin/admin.php">Admins </a></li>
        <li>
            <a href="<?= ROOT_URL ?>admin/Requests/requests.php">
                Requests <span id="requestCount" class="request-badge" style="<?= ($pendingRequests > 0) ? '' : 'display: none;' ?>">
                    <?= ($pendingRequests > 0) ? $pendingRequests : '' ?>
                </span>
            </a>
        </li>
        <li><a href="<?= ROOT_URL ?>admin/Activities/web-activities.php"> Web-Activities </a></li>
        <li><a href="<?= ROOT_URL ?>admin/settings.php">Settings</a></li>
        <li><a href="<?= ROOT_URL ?>admin/logout.php">Logout</a></li>
    </ul>
</div>

<script>
    // Set active class based on current page
    document.addEventListener("DOMContentLoaded", function() {
        const currentPage = window.location.pathname;
        const menuItems = document.querySelectorAll('.sidebar ul li a');
        menuItems.forEach(item => {
            if (item.getAttribute('href') === currentPage ||
                currentPage.endsWith(item.getAttribute('href').split('/').pop())) {
                item.classList.add('current-page');
            }
        });
    });
</script>