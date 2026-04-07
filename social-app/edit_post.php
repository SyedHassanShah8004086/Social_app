<?php
session_start();
include("config/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if(!isset($_GET['post_id'])){
    header("Location: home.php");
    exit;
}

$post_id = $_GET['post_id'];

// Fetch post
$post = $conn->query("SELECT * FROM posts WHERE id='$post_id' AND user_id='$user_id'")->fetch_assoc();
if(!$post){
    header("Location: home.php");
    exit;
}

// Handle update
if(isset($_POST['update_post'])){
    $content = $_POST['content'];
    
    // Check for image upload
    $image_name = $post['image'];
    if(isset($_FILES['post_image']) && $_FILES['post_image']['name'] != ''){
        $image_name = time().'_'.$_FILES['post_image']['name'];
        move_uploaded_file($_FILES['post_image']['tmp_name'], 'uploads/'.$image_name);
    }

    $conn->query("UPDATE posts SET content='$content', image='$image_name' WHERE id='$post_id' AND user_id='$user_id'");
    header("Location: home.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Post</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5" style="max-width: 600px;">
    <h3>Edit Post</h3>
    <form method="POST" enctype="multipart/form-data">
        <textarea name="content" class="form-control mb-2" required><?php echo htmlspecialchars($post['content']); ?></textarea>
        <input type="file" name="post_image" class="form-control mb-2" accept="image/*">
        <?php if($post['image']): ?>
            <img src="uploads/<?php echo $post['image']; ?>" class="img-fluid mb-2" style="max-height:200px;" alt="Post Image">
        <?php endif; ?>
        <button type="submit" name="update_post" class="btn btn-primary">Update Post</button>
        <a href="home.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>