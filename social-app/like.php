<?php
session_start();
include("config/db.php");

if(!isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = $_GET['post_id'];

// Check if already liked
$check = "SELECT * FROM likes WHERE user_id='$user_id' AND post_id='$post_id'";
$result = $conn->query($check);

if($result->num_rows > 0){
    // Unlike
    $conn->query("DELETE FROM likes WHERE user_id='$user_id' AND post_id='$post_id'");
} else {
    // Like
    $conn->query("INSERT INTO likes (user_id, post_id) VALUES ('$user_id','$post_id')");
}

header("Location: home.php");
?>