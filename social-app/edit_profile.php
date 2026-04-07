<?php
session_start();
include("config/db.php");

// Check login
if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current user info
$user = $conn->query("SELECT * FROM users WHERE id='$user_id'")->fetch_assoc();

// Handle profile update
if(isset($_POST['update_profile'])){
    $username = $_POST['username'];
    $email = $_POST['email'];
    $avatar = $user['avatar']; // keep existing if no new upload

    // Handle profile picture upload
    if(isset($_FILES['avatar']) && $_FILES['avatar']['name'] != ''){
        $avatar = time().'_'.$_FILES['avatar']['name'];
        move_uploaded_file($_FILES['avatar']['tmp_name'], 'uploads/'.$avatar);
    }

    // Update database
    $conn->query("UPDATE users SET username='$username', email='$email', avatar='$avatar' WHERE id='$user_id'");

    // Update session username
    $_SESSION['username'] = $username;

    header("Location: profile.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f0f2f5; }
</style>
</head>
<body>
<div class="container mt-5" style="max-width: 500px;">
    <h3>Edit Profile</h3>
    <form method="POST" enctype="multipart/form-data">
        <div class="mb-2">
            <label>Username</label>
            <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <div class="mb-2">
            <label>Email</label>
            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        </div>
        <div class="mb-2">
            <label>Profile Picture</label>
            <input type="file" name="avatar" class="form-control" accept="image/*">
            <?php if(!empty($user['avatar'])): ?>
                <img src="uploads/<?php echo $user['avatar']; ?>" class="img-fluid mt-2" style="max-height:150px;" alt="Profile Picture">
            <?php endif; ?>
        </div>
        <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
        <a href="profile.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>