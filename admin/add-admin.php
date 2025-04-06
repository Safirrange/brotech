<?php
include 'admin-sidebar.php'; // Include sidebar

// Retrieve form data from session if available
$full_name = $_SESSION['add-admin-data']['full_name'] ?? null;
$username = $_SESSION['add-admin-data']['user'] ?? null;
$email = $_SESSION['add-admin-data']['email'] ?? null;
$phone = $_SESSION['add-admin-data']['phone'] ?? null;
$createPassword = $_SESSION['add-admin-data']['createPassword'] ?? null;
$confirmPassword = $_SESSION['add-admin-data']['confirmPassword'] ?? null;

// Clear session data after retrieval
unset($_SESSION['add-admin-data']);
?>


<link rel="stylesheet" href="<?= ROOT_URL ?>css/manage-contents.css">

<div class="admin-content-container">
    <div class="admin-content-header">
        <h2>Add New Administrator</h2>
        <p>Create a new admin account with appropriate permissions</p>
    </div>

    <div class="admin-form-container">
        <?php if (isset($_SESSION['add-admin'])): ?>
            <div class="alert-message <?= strpos($_SESSION['add-admin'], 'success') !== false ? 'success' : 'error' ?>">
                <p>
                    <?= $_SESSION['add-admin'];
                    unset($_SESSION['add-admin']);
                    ?>
                </p>
            </div>
        <?php endif ?>

        <form action="<?= ROOT_URL ?>admin/add-admin-logic.php" enctype="multipart/form-data" method="POST" class="admin-form" id="admin-form">
            <div class="form-row">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="user" value="<?= htmlspecialchars($username) ?>" placeholder="Username" required>
                    <small class="form-text">Choose a unique username for this admin</small>
                </div>

                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?= htmlspecialchars($full_name) ?>" placeholder="Full Name" required>
                    <small class="form-text">Enter admin's complete name</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="Email Address" required>
                    <small class="form-text">Admin will receive notifications at this email</small>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="<?= htmlspecialchars($phone) ?>" placeholder="Phone Number" required>
                    <small class="form-text">Include country code if applicable</small>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="createPassword">Create Password</label>
                    <input type="password" id="createPassword" name="createPassword" value="<?= htmlspecialchars($createPassword) ?>" placeholder="Create Password" required>
                    <small class="form-text">Min. 8 characters with numbers and symbols</small>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirm Password</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" value="<?= htmlspecialchars($confirmPassword) ?>" placeholder="Confirm Password" required>
                    <small class="form-text">Enter the same password as above</small>
                </div>
            </div>

            <div class="form-group">
                <label for="avatar">User Avatar</label>
                <div class="file-input-container">
                    <input type="file" id="avatar" name="avatar" accept=".png, .jpg, .jpeg" onchange="previewImage(this)">
                    <label for="avatar" class="file-label">Choose File</label>
                    <span id="file-name">No file chosen</span>
                </div>
                <small class="form-text">Recommended size: 300x300px (Max: 2MB)</small>
                <div id="image-preview"></div>
            </div>

            <div class="form-actions">
                <button type="reset" class="btn btn-secondary">Cancel</button>
                <button type="submit" name="submit" class="btn btn-primary">Add Administrator</button>
            </div>
        </form>
    </div>
</div>

<script>
// Preview selected image
function previewImage(input) {
    const fileNameDisplay = document.getElementById('file-name');
    const preview = document.getElementById('image-preview');
    
    // Update file name display
    if (input.files && input.files[0]) {
        fileNameDisplay.textContent = input.files[0].name;
        
        // Create preview image
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" alt="Avatar Preview" class="preview-img">`;
        };
        reader.readAsDataURL(input.files[0]);
        
        // File validation
        validateFile(input);
    } else {
        fileNameDisplay.textContent = 'No file chosen';
        preview.innerHTML = '';
    }
}

// Validate file size and type
function validateFile(input) {
    const file = input.files[0];
    const fileNameDisplay = document.getElementById('file-name');
    const preview = document.getElementById('image-preview');
    
    // Check file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
        alert('File size exceeds 2MB limit');
        input.value = '';
        fileNameDisplay.textContent = 'No file chosen';
        preview.innerHTML = '';
        return false;
    }
    
    // Check file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
    if (!allowedTypes.includes(file.type)) {
        alert('Only JPG, JPEG, and PNG files are allowed');
        input.value = '';
        fileNameDisplay.textContent = 'No file chosen';
        preview.innerHTML = '';
        return false;
    }
    
    return true;
}

// Form validation
document.getElementById('admin-form').addEventListener('submit', function(e) {
    const username = document.getElementById('username');
    const email = document.getElementById('email');
    const createPassword = document.getElementById('createPassword');
    const confirmPassword = document.getElementById('confirmPassword');
    let isValid = true;
    
    // Simple validation
    if (username.value.trim().length < 3) {
        alert('Username must be at least 3 characters');
        username.focus();
        isValid = false;
    }
    
    if (!validateEmail(email.value)) {
        alert('Please enter a valid email address');
        email.focus();
        isValid = false;
    }
    
    if (createPassword.value.length < 8) {
        alert('Password must be at least 8 characters long');
        createPassword.focus();
        isValid = false;
    }
    
    if (confirmPassword.value !== createPassword.value) {
        alert('Passwords do not match');
        confirmPassword.focus();
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
    }
});

// Email validation helper
function validateEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}
</script>

</body>
</html>