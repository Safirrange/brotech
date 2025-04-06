<?php
require '../../config/database.php';

if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']); // Prevent SQL Injection

    $query = "SELECT * FROM usersmember WHERE id = $user_id LIMIT 1";
    $result = mysqli_query($connection, $query);

    if ($user = mysqli_fetch_assoc($result)) {

        $avatar = !empty($user['avatar']) ? ROOT_URL."img/users/{$user['avatar']}" : "img/default-avatar.png";
        // Output user details
        echo "<img src='$avatar' alt='User Avatar' class='user-avatar'>";
        echo "<h2>{$user['userName']} ID: ({$user['id']})</h2>";
        echo "<p>Email: {$user['email']}</p>";
        echo "<p>Name: {$user['firstName']} {$user['lastName']}</p>";
        echo "<p>Number: {$user['phoneNumber']}</p>";
        echo "<p>Joined: {$user['joined_at']}</p>";
    } else {
        echo "<p>User not found.</p>";
    }
} else {
    echo "<p>Invalid request.</p>";
}
