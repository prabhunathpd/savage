<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

$user_id = $_SESSION['user_id'];
$post_id = $_GET['post_id'];

// Check if the user already liked the post
$stmt = $conn->prepare("SELECT * FROM likes WHERE user_id = :user_id AND post_id = :post_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':post_id', $post_id);
$stmt->execute();
$is_liked = $stmt->fetch(PDO::FETCH_ASSOC);

if ($is_liked) {
    // Unlike the post
    $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = :user_id AND post_id = :post_id");
} else {
    // Like the post
    $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (:user_id, :post_id)");

    // Insert notification for the post owner
    $stmt_notification = $conn->prepare("
        INSERT INTO notifications (user_id, type, source_user_id, post_id)
        SELECT posts.user_id, 'like', :source_user_id, :post_id
        FROM posts
        WHERE posts.id = :post_id
    ");
    $stmt_notification->bindParam(':source_user_id', $user_id);
    $stmt_notification->bindParam(':post_id', $post_id);
    $stmt_notification->execute();
}
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':post_id', $post_id);
$stmt->execute();

header("Location: " . $_SERVER['HTTP_REFERER']);
?>