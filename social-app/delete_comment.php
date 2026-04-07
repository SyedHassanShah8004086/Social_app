<?php
session_start();
include("config/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if(isset($_GET['comment_id'])){
    $comment_id = $_GET['comment_id'];

    // Delete only if the comment belongs to the logged-in user
    $conn->query("DELETE FROM comments WHERE id='$comment_id' AND user_id='$user_id'");
}

header("Location: home.php");
exit;
?>