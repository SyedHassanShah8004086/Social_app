<?php
session_start();
include("config/db.php");

// Check login
if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch logged-in user info securely
$stmt = $conn->prepare("SELECT username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Handle deletion of own post securely
if(isset($_GET['delete_post'])){
    $post_id = (int)$_GET['delete_post'];
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
    header("Location: profile.php");
    exit;
}

// Fetch all posts by this user
// Fetch all posts by this user along with username and avatar
$posts = $conn->query("
    SELECT posts.*, users.username, users.avatar 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    WHERE posts.user_id='$user_id'
    ORDER BY posts.created_at DESC
");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($user['username']); ?>'s Profile</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background: #f0f2f5; }
.post-card { border-radius: 15px; box-shadow: 0 3px 10px rgba(0,0,0,0.1); background: #fff; margin-bottom: 20px; padding:15px;}
.post-image { max-height: 400px; object-fit: cover; border-radius: 10px; margin-top: 10px; }
.comment-card { margin-left: 20px; font-size: 0.9rem; margin-top:5px;}
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark">
    <div class="container">
        <a href="home.php" class="navbar-brand">Home</a>
        <span class="navbar-brand"><?php echo htmlspecialchars($user['username']); ?></span>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</nav>

<div class="container mt-4" style="max-width: 600px;">
    <a href="edit_profile.php" class="btn btn-sm btn-info mb-3">Edit Profile</a>

    <!-- User Info -->
    <div class="card p-3 mb-3">
        <h4><?php echo htmlspecialchars($user['username']); ?></h4>
        <p>Email: <?php echo htmlspecialchars($user['email']); ?></p>
    </div>

    <h5>Your Posts</h5>

    <?php if($posts->num_rows > 0): ?>
        <?php while($row = $posts->fetch_assoc()): ?>
            <div class="card post-card">
                <div class="d-flex justify-content-between">
                    <h5><?php echo htmlspecialchars($user['username']); ?></h5>
                    <div>
                        <a href="edit_post.php?post_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning me-2">Edit</a>
                        <a href="profile.php?delete_post=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this post?')">Delete</a>
                    </div>
                </div>

                <p><?php echo nl2br(htmlspecialchars($row['content'])); ?></p>

                <?php if($row['image'] && file_exists("uploads/".$row['image'])): ?>
                    <img src="uploads/<?php echo htmlspecialchars($row['image']); ?>" class="img-fluid post-image" alt="Post Image">
                <?php endif; ?>

                <?php
                // Optimized like query
                $stmtLike = $conn->prepare("SELECT COUNT(*) as cnt FROM likes WHERE post_id = ?");
                $stmtLike->bind_param("i", $row['id']);
                $stmtLike->execute();
                $like_count = $stmtLike->get_result()->fetch_assoc()['cnt'];

                $stmtLiked = $conn->prepare("SELECT COUNT(*) as cnt FROM likes WHERE post_id = ? AND user_id = ?");
                $stmtLiked->bind_param("ii", $row['id'], $user_id);
                $stmtLiked->execute();
                $liked = $stmtLiked->get_result()->fetch_assoc()['cnt'] > 0;
                ?>

                <a href="like.php?post_id=<?php echo $row['id']; ?>" class="btn btn-sm <?php echo $liked ? 'btn-primary' : 'btn-outline-primary'; ?>">
                    ❤️ Like (<?php echo $like_count; ?>)
                </a>
                <small class="text-muted d-block mt-2"><?php echo htmlspecialchars($row['created_at']); ?></small>

                <!-- Comment Form -->
                <form action="comment.php" method="POST" class="mt-2">
                    <input type="hidden" name="post_id" value="<?php echo $row['id']; ?>">
                    <input type="text" name="comment_text" class="form-control mb-2" placeholder="Write a comment..." required>
                    <button type="submit" name="comment" class="btn btn-sm btn-secondary">Comment</button>
                </form>

                <!-- Show Comments -->
                <?php
                $stmtComments = $conn->prepare("SELECT comments.id, comments.comment, comments.user_id, users.username 
                                                FROM comments 
                                                JOIN users ON comments.user_id = users.id 
                                                WHERE post_id = ? 
                                                ORDER BY created_at ASC");
                $stmtComments->bind_param("i", $row['id']);
                $stmtComments->execute();
                $comments = $stmtComments->get_result();
                while($c = $comments->fetch_assoc()):
                ?>
                    <div class="comment-card">
                        <strong><?php echo htmlspecialchars($c['username']); ?>:</strong> <?php echo htmlspecialchars($c['comment']); ?>
                        <?php if($c['user_id'] == $user_id): ?>
                            <a href="delete_comment.php?comment_id=<?php echo $c['id']; ?>" class="text-danger" onclick="return confirm('Delete this comment?')">x</a>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-muted">You haven't posted anything yet.</p>
    <?php endif; ?>

</div>

</body>
</html>