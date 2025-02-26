<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

$follower_id = $_SESSION['user_id'];
$followee_id = $_GET['followee_id'];

// Check if the follow relationship already exists
$stmt = $conn->prepare("SELECT * FROM follows WHERE follower_id = :follower_id AND followee_id = :followee_id");
$stmt->bindParam(':follower_id', $follower_id);
$stmt->bindParam(':followee_id', $followee_id);
$stmt->execute();
$is_following = $stmt->fetch(PDO::FETCH_ASSOC);

if ($is_following) {
    // Unfollow
    $stmt = $conn->prepare("DELETE FROM follows WHERE follower_id = :follower_id AND followee_id = :followee_id");
} else {
    // Follow
    $stmt = $conn->prepare("INSERT INTO follows (follower_id, followee_id) VALUES (:follower_id, :followee_id)");

    // Insert notification for the followee
    $stmt_notification = $conn->prepare("
        INSERT INTO notifications (user_id, type, source_user_id)
        VALUES (:followee_id, 'follow', :follower_id)
    ");
    $stmt_notification->bindParam(':followee_id', $followee_id);
    $stmt_notification->bindParam(':follower_id', $follower_id);
    $stmt_notification->execute();
}
$stmt->bindParam(':follower_id', $follower_id);
$stmt->bindParam(':followee_id', $followee_id);
$stmt->execute();

header("Location: " . $_SERVER['HTTP_REFERER']);
?>