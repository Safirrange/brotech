<?php
require __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT);

    // First, get current ban status
    $query = "SELECT isBanned FROM usersmember WHERE id = $id";
    $result = mysqli_query($connection, $query);
    $user = mysqli_fetch_assoc($result);

    // Toggle the ban status
    $new_status = $user['isBanned'] ? 0 : 1;

    $update_query = "UPDATE usersmember SET isBanned = $new_status WHERE id = $id";
    mysqli_query($connection, $update_query);

    $_SESSION['users-list-ban'] = "User status updated successfully.";
    header('location: ' . ROOT_URL . 'admin/Users/users-list.php');
    exit;
} else {
    header('location: ' . ROOT_URL . 'admin/Users/users-list.php');
    exit;
}
