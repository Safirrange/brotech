<?php
require __DIR__ . '/../config/database.php';

// Get current status
$query = "SELECT is_enabled FROM maintenance_mode WHERE id = 1";
$result = mysqli_query($connection, $query);
$data = mysqli_fetch_assoc($result);

$currentStatus = $data['is_enabled'];
$newStatus = $currentStatus == 1 ? 0 : 1;  // Toggle between 1 (Enabled) and 0 (Disabled)

// Update maintenance mode status
$updateQuery = "UPDATE maintenance_mode SET is_enabled = ? WHERE id = 1";
$stmt = $connection->prepare($updateQuery);
$stmt->bind_param('i', $newStatus);
$stmt->execute();
$stmt->close();
$connection->close();

// Redirect back to settings page
header('Location: ' . ROOT_URL . 'admin/settings.php');
exit();