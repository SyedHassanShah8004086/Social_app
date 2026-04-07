<?php
session_start();
include("config/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

if(isset($_POST['post'])){
    $content = $_POST['content'];
    $user_id = $_SESSION['user_id'];
    $created_at = date("Y-m-d H:i:s");

    // Handle image upload
    $image_name = NULL;
    if(isset($_FILES['post_image']) && $_FILES['post_image']['name'] != ''){
        $target_dir = "uploads/";
        if(!is_dir($target_dir)) mkdir($target_dir, 0777, true); // create folder if not exist
        $image_name = time() . "_" . basename($_FILES['post_image']['name']);
        $target_file = $target_dir . $image_name;

        $allowed_types = ['jpg','jpeg','png','gif'];
        $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        if(in_array($ext, $allowed_types)){
            move_uploaded_file($_FILES['post_image']['tmp_name'], $target_file);
        } else {
            $image_name = NULL; // invalid file type
        }
    }

    // Insert post
    $sql = "INSERT INTO posts (user_id, content, image, created_at) VALUES ('$user_id', '$content', '$image_name', '$created_at')";
    $conn->query($sql);

    header("Location: home.php");
}
?>