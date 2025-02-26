<?php
session_start([
    'cookie_lifetime' => 86400, // 1 day in seconds
]);

// Check if the user is logged in
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Get user ID from session
} else {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit();
}

include 'db.php';

// Fetch posts from users that the logged-in user follows
$stmt = $conn->prepare("
    SELECT posts.*, users.username, users.profile_pic 
    FROM posts 
    JOIN users ON posts.user_id = users.id 
    WHERE posts.user_id IN (
        SELECT followee_id 
        FROM follows 
        WHERE follower_id = :user_id
    )
    ORDER BY posts.created_at DESC
");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch the profile picture of the logged-in user from the database
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

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>Savage</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            font-family: sans-serif;
        }

        body {
            display: flex;
            justify-content: space-around;
            background-color: #111;
            color: #fff;
        }

        .main {
            background-color: #111;
            max-width: 400px;
            margin-bottom: 50px;
        }

        @media (max-width: 500px) {
            .main {
                max-width: none;
                width: 100%;
                height: auto;
            }

            body {
                display: block;
            }

            .menu {
                width: 100%;
            }
        }

        .linex {
            height: 0.02em;
            background-color: #333;
            width: 100%;
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

        .post {
            margin: 20px 15px 5px 15px;
            color: #777;
        }

        .post .user .profile-pic {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            margin-right: 10px;
        }

        .user {
            display: flex;
            margin-bottom: 5px;
        }

        .user-r strong {
            color: #fff;
            margin-bottom: 5px;
        }

        .user-r {
            color: #555;
            font-weight: 500;
            display: flex;
            flex-direction: column;
        }

        .post a {
            text-decoration: none;
        }

        .post .text {
            margin: 10px 0;
            color: #888;
        }

        .post .post-image {
            border-radius: 8px;
            width: 100%;
        }

        .post-actions {
            display: flex;
            align-items: center;
            margin: 8px 0;
            justify-content: space-between;
        }

        .like-btn {
            display: flex;
            align-items: center;
        }

        .like-btn p {
            margin-right: 20px;
            font-size: 20px;
        }

        .like-btn img {
            width: 30px;
            margin-right: 5px;
            height: 30px;
        }

        .view-btn button,
        .comment-btn {
            border: none;
            background: transparent;
            color: #555;
            cursor: pointer;
            font-size: 25px;
        }

        .view-btn button span:hover,
        .comment-btn span:hover {
            color: #777;
        }

        .view-btn button span,
        .comment-btn span {
            font-size: 25px;
        }

        .comments {
            margin: 10px;
        }

        .comments .comment {
            margin: 10px 5px;
        }

        .comment .user-c {
            display: flex;
            align-items: center;
        }

        .comments .comment img {
            width: 30px;
            height: 30px;
            margin-right: 10px;
            border-radius: 50%;
        }

        .comments .comment strong {
            color: #fff;
            margin-bottom: 5px;
        }

        .comment .user-c p {
            display: flex;
            flex-direction: column;
        }

        .comment .c-text {
            margin-left: 40px;
            margin-top: 5px;
            color: #999;
            font-size: 18px;
        }

        .comments form {
            margin: 5px 0;
            display: flex;
            justify-content: space-between;
        }

        .comments form input {
            padding: 10px 15px;
            background-color: #444;
            width: 85%;
            color: #fff;
            border: none;
            border-radius: 5px;
        }

        .comments form button {
            border: none;
            background-color: #007fff;
            cursor: pointer;
            border-radius: 5px;
            padding: 5px 10px;
            color: #fff;
        }

        .comments form button:hover {
            background-color: #005fff;
        }
    </style>
</head>

<body>
    <div class="main">
        <?php include("nav.php"); ?>

        <?php if (count($posts) > 0): ?>
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <a href="user.php?id=<?php echo $post["user_id"]; ?>">
                        <div class="user">
                            <img src="<?php echo $post['profile_pic']; ?>" alt="Profile Pic" class="profile-pic">
                            <div class="user-r">
                                <strong><?php echo $post['username']; ?></strong>
                                <small><?php echo $post['created_at']; ?></small>
                            </div>
                        </div>
                    </a>
                    <p class="text"><?php echo $post['content']; ?></p>
                    <?php if ($post['image']): ?>
                        <img src="<?php echo $post['image']; ?>" alt="Post Image" class="post-image">
                    <?php endif; ?>


                    <!-- Like Count -->
                    <?php
                    $stmt = $conn->prepare("SELECT COUNT(*) AS like_count FROM likes WHERE post_id = :post_id");
                    $stmt->bindParam(':post_id', $post['id']);
                    $stmt->execute();
                    $like_count = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>

                    <?php
                    $stmt = $conn->prepare("SELECT * FROM likes WHERE user_id = :user_id AND post_id = :post_id");
                    $stmt->bindParam(':user_id', $user_id);
                    $stmt->bindParam(':post_id', $post['id']);
                    $stmt->execute();
                    $is_liked = $stmt->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="post-actions">
                        <div class="like-btn">
                            <a href="like.php?post_id=<?php echo $post['id']; ?>">
                                <img src="<?php echo $is_liked ? 'assets/like-h.png' : 'assets/like.png'; ?>" />
                            </a>
                            <p><?php echo $like_count['like_count']; ?></p>
                            <button class="comment-btn" onclick="showComment()"><span class="material-symbols-rounded">
                                    comment
                                </span></button>
                        </div>
                        <a href="post.php?p=<?php echo $post['id']; ?>" class="view-btn">
                            <button type="button" class="view-btn"><span class="material-symbols-rounded">
                                    visibility
                                </span></button>
                        </a>
                    </div>

                    <?php
                    $stmt = $conn->prepare("
                    SELECT comments.*, users.username, users.profile_pic 
                    FROM comments 
                    JOIN users ON comments.user_id = users.id 
                    WHERE comments.post_id = :post_id 
                    ORDER BY comments.created_at ASC
                ");
                    $stmt->bindParam(':post_id', $post['id']);
                    $stmt->execute();
                    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <div style="display: none;" id="comment" class="comments">
                        <h4>Comments</h4>
                        <?php foreach ($comments as $comment): ?>
                            <div class="comment">
                                <div class="user-c">
                                    <img src="<?php echo $comment['profile_pic']; ?>" alt="Profile Pic" class="profile-pic">
                                    <p><strong><?php echo $comment['username']; ?></strong>
                                        <small><?php echo $comment['created_at']; ?></small>
                                    </p>
                                </div>
                                <p class="c-text"><?php echo $comment['comment']; ?></p>

                            </div>
                            <div class="linex"></div>
                        <?php endforeach; ?>
                        <form method="POST" action="comment.php" class="comment-form">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <input name="comment" placeholder="Add a comment..." required></input>
                            <button type="submit"><span class="material-symbols-outlined">
                                    send
                                </span></button>
                        </form>
                    </div>
                </div>
                <div class="linex"></div>

            <?php endforeach; ?>
        <?php else: ?>
            <p>No posts to display. Follow users to see their posts.</p>
        <?php endif; ?>
        <button id="installButton" style="display: none;">Install App</button>
        <div class="menu">
            <a href="/app" class="active"><span class="material-symbols-rounded">
                    home
                </span></a>
            <a href="search"><span class="material-symbols-rounded">
                    search
                </span></i></a>
            <a href="notification"><span class="material-symbols-rounded">
                    notifications
                </span></a>
            <a href="user" class="pic">
                <img src="<?php echo $profile_pic; ?>" />
            </a>
        </div>
    </div>
    <script>
        function showComment() {
            let div = document.getElementById("comment");
            if (div.style.display === "none" || div.style.display === "") {
                div.style.display = "block";
            } else {
                div.style.display = "none";
            }
        }
        if ("serviceWorker" in navigator) {
            navigator.serviceWorker.register("/app/service-worker.js", { scope: "/app/" })
                .then((registration) => {
                    console.log("Service Worker registered with scope:", registration.scope);
                })
                .catch((error) => {
                    console.error("Service Worker registration failed:", error);
                });
        }
    </script>

</body>

</html>