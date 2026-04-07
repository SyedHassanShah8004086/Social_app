<?php
session_start();
include("config/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

if(isset($_POST['comment'])){
    $user_id = $_SESSION['user_id'];
    $post_id = $_POST['post_id'];
    $comment = $_POST['comment_text'];
    $created_at = date("Y-m-d H:i:s");

    $sql = "INSERT INTO comments (user_id, post_id, comment, created_at) 
            VALUES ('$user_id','$post_id','$comment','$created_at')";
    $conn->query($sql);

    header("Location: home.php");
}
?>