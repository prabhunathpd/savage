<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

$user_id = $_SESSION['user_id'];

// Fetch notifications
$stmt = $conn->prepare("
    SELECT notifications.*, users.username 
    FROM notifications 
    JOIN users ON notifications.source_user_id = users.id 
    WHERE notifications.user_id = :user_id 
    ORDER BY notifications.created_at DESC
");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$profile_pic = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#222222">
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>Savage</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: sans-serif;
        }

        body {
            display: flex;
            justify-content: space-around;
            color: #fff;
            background-color: #111;
        }

        .main {
            background-color: #111;
            width: 400px;
            margin-bottom: 50px;
        }

        .linex {
            height: 0.02em;
            background-color: #333;
            width: 100%;
            margin-bottom: 10px;
        }

        .navbar {
            display: flex;
            color: #fff;
            align-items: center;
            justify-content: space-between;
            padding: 8px 20px;
        }

        .navbar .logo h3 {
            font-size: 44px;
        }

        .navbar .post-btn a {
            display: flex;
            text-decoration: none;
            color: #fff;
            align-items: center;
        }

        .navbar .post-btn span {
            font-size: 40px;
        }

        .menu {
            display: flex;
            justify-content: space-evenly;
            position: fixed;
            width: 100%;
            left: 0;
            padding: 10px 0;
            background-color: #222;
            bottom: 0;
        }

        .menu a {
            text-decoration: none;
            color: #555;
        }

        .menu a.active {
            color: #fff;
        }

        .menu .pic img {
            width: 30px;
            border-radius: 50%;
            height: 30px;
        }

        .menu img.active {
            border-radius: 50%;
            border: 2px #fff solid;
            width: 28px;
            height: 28px;
        }

        .menu a span {
            font-size: 30px;
        }

        .notification-container {
            margin: 20px;
        }

        .notification-container h3 {
            color: #999;
            margin: 10px 0;
        }

        .notification {
            background-color: #222;
            padding: 15px 20px;
            margin-bottom: 15px;
            border-radius: 10px;
        }

        .notification p {
            display: flex;
            align-items: center;
        }

        .notification p a {
            text-decoration: none;
            color: #fff;
        }

        .notification img {
            margin-right: 15px;
        }

        .notification p a:hover {
            color: #888;
        }

        .notification .username {
            font-weight: bold;
            margin-right: 10px;
        }

        .notification .time {
            color: #666;
            font-size: 0.9em;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="main">
        <?php include("nav.php"); ?>
        <div class="notification-container">
            <h3>Notifications</h3>
            <?php if (count($notifications) > 0): ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="notification">
                        <?php if ($notification['type'] === 'like'): ?>
                            <p>
                                <img src="assets/like-h.png" width="30px" />
                                <span class="username">
                                    <a href="user.php?id=<?php echo $notification['source_user_id'] ?>">
                                        <?php echo $notification['username']; ?>
                                    </a>
                                </span>
                                liked your post.
                            </p>
                        <?php elseif ($notification['type'] === 'comment'): ?>
                            <p>
                                <img src="assets/comment.png" width="30px" />
                                <span class="username"><a href="user.php?id=<?php echo $notification['source_user_id'] ?>">
                                        <?php echo $notification['username']; ?>
                                    </a></span>
                                commented on your post.
                            </p>
                        <?php elseif ($notification['type'] === 'follow'): ?>
                            <p>
                                <img src="assets/follow.png" width="30px" />
                                <span class="username"><a href=user.php?id=<?php echo $notification['source_user_id'] ?>">
                                        <?php echo $notification['username']; ?>
                                    </a></span>
                                started following you.
                            </p>
                        <?php endif; ?>
                        <p class="time"><?php $timestamp = $notification['created_at'];
                        echo date("H:i  ‎ ‎   d F Y", strtotime($timestamp)); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No notifications to display.</p>
            <?php endif; ?>
        </div>
        <div class="menu">
            <a href="/app"><span class="material-symbols-outlined">
                    home
                </span></a>
            <a href="search"><span class="material-symbols-outlined">
                    search
                </span></i></a>
            <a href="notification" class="active"><span class="material-symbols-outlined">
                    notifications
                </span></a>
            <a href="user" class="pic">
                <img src="<?php echo $profile_pic; ?>" />
            </a>
        </div>
    </div>
</body>

</html>