<?php
session_start();
include("config/db.php");

// Check login
if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch all posts with user info
$posts = $conn->query("
    SELECT posts.*, users.username, users.avatar 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    ORDER BY posts.created_at DESC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Home Feed</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f0f2f5; }
.post-card { border-radius: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); background: #fff; margin-bottom: 20px; padding:15px;}
.post-image { max-height: 400px; object-fit: cover; border-radius: 10px; margin-top: 10px; }
.comment-card { margin-left: 50px; font-size: 0.9rem; margin-top:5px; }
.avatar { width: 40px; height: 40px; border-radius: 50%; object-fit: cover; margin-right:10px; }
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <span class="navbar-brand">Welcome <?php echo $_SESSION['username']; ?></span>
        <a href="profile.php" class="btn btn-info">Profile</a>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<div class="container mt-4" style="max-width: 600px;">

    <!-- Create Post -->
    <div class="card p-3 mb-3 shadow post-card">
        <form action="post.php" method="POST" enctype="multipart/form-data">
            <textarea name="content" class="form-control mb-2" placeholder="What's on your mind?" required></textarea>
            <input type="file" name="post_image" class="form-control mb-2" accept="image/*">
            <button type="submit" name="post" class="btn btn-primary w-100">Post</button>
        </form>
    </div>

    <!-- Show Posts -->
    <?php if($posts->num_rows > 0): ?>
        <?php while($row = $posts->fetch_assoc()): ?>
            <div class="card post-card">
                <div class="d-flex align-items-center mb-2">
                    <img src="uploads/<?php echo $row['avatar'] ?? 'default-avatar.png'; ?>" class="avatar">
                    <strong><?php echo $row['username']; ?></strong>
                    <?php if($row['user_id'] == $user_id): ?>
                        <div class="ms-auto">
                            <a href="edit_post.php?post_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning me-2">Edit</a>
                            <a href="home.php?delete_post=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this post?')">Delete</a>
                        </div>
                    <?php endif; ?>
                </div>

                <p><?php echo $row['content']; ?></p>
                <?php if($row['image']): ?>
                    <img src="uploads/<?php echo $row['image']; ?>" class="img-fluid post-image" alt="Post Image">
                <?php endif; ?>

                <?php
                $post_id = $row['id'];
                $like_count = $conn->query("SELECT * FROM likes WHERE post_id='$post_id'")->num_rows;
                $liked = $conn->query("SELECT * FROM likes WHERE post_id='$post_id' AND user_id='$user_id'")->num_rows;
                ?>

                <a href="like.php?post_id=<?php echo $post_id; ?>" class="btn btn-sm <?php echo $liked ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    ❤️ Like (<?php echo $like_count; ?>)
                </a>
                <small class="text-muted d-block mt-2"><?php echo $row['created_at']; ?></small>

                <!-- Comment Form -->
                <form action="comment.php" method="POST" class="mt-2">
                    <input type="hidden" name="post_id" value="<?php echo $post_id; ?>">
                    <input type="text" name="comment_text" class="form-control mb-2" placeholder="Write a comment..." required>
                    <button type="submit" name="comment" class="btn btn-sm btn-secondary">Comment</button>
                </form>

                <!-- Show Comments -->
                <?php
                $comments = $conn->query("
                    SELECT comments.*, users.username, users.avatar 
                    FROM comments 
                    JOIN users ON comments.user_id = users.id 
                    WHERE post_id='$post_id'
                    ORDER BY created_at ASC
                ");
                while($c = $comments->fetch_assoc()):
                ?>
                    <div class="d-flex align-items-center comment-card">
                        <img src="uploads/<?php echo $c['avatar'] ?? 'default-avatar.png'; ?>" class="avatar" style="width:30px; height:30px;">
                        <strong><?php echo $c['username']; ?>:</strong> <span class="ms-1"><?php echo $c['comment']; ?></span>
                        <?php if($c['user_id'] == $user_id): ?>
                            <a href="delete_comment.php?comment_id=<?php echo $c['id']; ?>" class="text-danger ms-auto" onclick="return confirm('Delete this comment?')">x</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-muted">No posts yet.</p>
    <?php endif; ?>

</div>

</body>
</html>
