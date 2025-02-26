<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

$user_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'];
$comment = $_POST['comment'];

// Insert comment
$stmt = $conn->prepare("INSERT INTO comments (user_id, post_id, comment) VALUES (:user_id, :post_id, :comment)");
$stmt->bindParam(':user_id', $user_id);
$stmt->bindParam(':post_id', $post_id);
$stmt->bindParam(':comment', $comment);
$stmt->execute();

// Insert notification for the post owner
$stmt_notification = $conn->prepare("
    INSERT INTO notifications (user_id, type, source_user_id, post_id)
    SELECT posts.user_id, 'comment', :source_user_id, :post_id
    FROM posts
    WHERE posts.id = :post_id
");
$stmt_notification->bindParam(':source_user_id', $user_id);
$stmt_notification->bindParam(':post_id', $post_id);
$stmt_notification->execute();

header("Location: " . $_SERVER['HTTP_REFERER']);
?>