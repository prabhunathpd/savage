<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

$logged_in_user_id = $_SESSION['user_id'];
$profile_user_id = isset($_GET['id']) ? $_GET['id'] : $logged_in_user_id;

// Fetch the profile user's details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $profile_user_id);
$stmt->execute();
$profile_user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$profile_user) {
    die("User not found!");
}

// Fetch followers count
$stmt = $conn->prepare("SELECT COUNT(*) AS followers_count FROM follows WHERE followee_id = :user_id");
$stmt->bindParam(':user_id', $profile_user_id);
$stmt->execute();
$followers = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch following count
$stmt = $conn->prepare("SELECT COUNT(*) AS following_count FROM follows WHERE follower_id = :user_id");
$stmt->bindParam(':user_id', $profile_user_id);
$stmt->execute();
$following = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if the logged-in user is following the profile user
$stmt = $conn->prepare("SELECT * FROM follows WHERE follower_id = :follower_id AND followee_id = :followee_id");
$stmt->bindParam(':follower_id', $logged_in_user_id);
$stmt->bindParam(':followee_id', $profile_user_id);
$stmt->execute();
$is_following = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT COUNT(*) AS post_count FROM posts WHERE user_id = :user_id");
$stmt->bindParam(':user_id', $profile_user_id);
$stmt->execute();
$post_count = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle follow/unfollow action
if (isset($_POST['follow'])) {
    if ($is_following) {
        // Unfollow
        $stmt = $conn->prepare("DELETE FROM follows WHERE follower_id = :follower_id AND followee_id = :followee_id");
        $stmt->bindParam(':follower_id', $logged_in_user_id);
        $stmt->bindParam(':followee_id', $profile_user_id);
        $stmt->execute();
    } else {
        // Follow
        $stmt = $conn->prepare("INSERT INTO follows (follower_id, followee_id) VALUES (:follower_id, :followee_id)");
        $stmt->bindParam(':follower_id', $logged_in_user_id);
        $stmt->bindParam(':followee_id', $profile_user_id);
        $stmt->execute();
    }
    header("Location: user.php?id=$profile_user_id");
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon">
    <link rel="manifest" href="/app/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#222222">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title><?php echo $profile_user['username']; ?> - Savage</title>
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
            width: 30px;
            height: 30px;
        }

        .menu a span {
            font-size: 30px;
        }

        .profile-container {
            margin: 20px;
            display: flex;
        }

        .profile-container .stats p {
            font-size: 24px;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .profile-container .profile-pic {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin-right: 25px;
        }

        .profile-container .stats-f {
            display: flex;
            color: #999;
        }

        .profile-container .stats-f div {
            display: flex;
            flex-direction: column;
            margin-right: 20px;
            text-align: left;
            font-weight: 600;
            font-size: 1em;
        }

        .stats-f div span {
            font-weight: 400;
        }

        .bio {
            color: #999997;
            margin: 0 20px;
        }

        h4 {
            color: #f1f1f1;
            margin: 5px 20px;
            font-size: 25px;
            text-align: center;

        }

        .posts {
            margin: 10px 20px;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
        }

        .post {
            width: 150px;
            height: 150px;
            overflow: hidden;
            margin-bottom: 15px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .post img {
            width: auto;
            height: 100%;
            object-fit: cover;
        }

        .follow-btn {
            display: flex;
            justify-content: space-around;
        }

        .follow-btn .follow {
            padding: 8px 15px;
            cursor: pointer;
            margin: 15px 0 5px 0;
            background-color: #007fff;
            color: #fff;
            width: 300px;
            border-radius: 5px;
            border: none;
        }

        .follow-btn .follow:hover {
            background-color: #005fff;
        }

        .follow-btn .unfollow {
            padding: 8px 15px;
            cursor: pointer;
            margin: 15px 0 5px 0;
            background-color: #444;
            color: #fff;
            width: 300px;
            border-radius: 5px;
            border: none;
        }

        .follow-btn .unfollow:hover {
            background-color: #333;
        }

        .edit-profile-btn {
            margin: 10px 20px;
            text-align: center;
        }

        .edit-profile-btn .edit-btn {
            padding: 8px 15px;
            cursor: pointer;
            background-color: #007fff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            width: 100%;
        }

        .edit-profile-btn .edit-btn:hover {
            background-color: #005fff;
        }
    </style>
</head>

<body>
    <div class="main">
        <?php include("nav.php"); ?>
        <div class="profile-container">


            <?php if ($profile_user['profile_pic']): ?>
                <img src="<?php echo $profile_user['profile_pic']; ?>" alt="Profile Picture" class="profile-pic">
            <?php else: ?>
                <p>No profile picture uploaded.</p>
            <?php endif; ?>

            <div class="stats">
                <p><?php echo $profile_user['username']; ?></p>

                <div class="stats-f">
                    <div> <?php echo $post_count['post_count']; ?><span>Posts</span>
                    </div>
                    <div> <?php echo $followers['followers_count']; ?><span>Followers</span></div>
                    <div> <?php echo $following['following_count']; ?><span>Following</span></div>
                </div>
            </div>
        </div>
        <div class="bio">
            <?php echo $profile_user['bio'] ? nl2br($profile_user['bio']) : "No bio available."; ?>
        </div>

        <!-- Follow/Unfollow Button (only for other users) -->
        <?php if ($profile_user_id !== $logged_in_user_id): ?>
            <div class="follow-btn">
                <form method="POST" action="">

                    <?php echo $is_following ? '<button type="submit" name="follow" class="unfollow">Unfollow</button>' : '<button type="submit" name="follow" class="follow">Follow</button>'; ?>
                </form>
            </div>
        <?php endif; ?>
        <!-- Edit Profile Button (only for the logged-in user's profile) -->
        <?php if ($profile_user_id === $logged_in_user_id): ?>
            <div class="edit-profile-btn">
                <a href="edit_profile">
                    <button type="button" class="edit-btn">Edit Profile</button>
                </a>
            </div>
        <?php endif; ?>
        <h4>Posts</h4>
        <div class="linex"></div>
        <div class="posts">

            <?php
            $stmt = $conn->prepare("
            SELECT posts.*, users.username, users.profile_pic 
            FROM posts 
            JOIN users ON posts.user_id = users.id 
            WHERE posts.user_id = :user_id 
            ORDER BY posts.created_at DESC
        ");
            $stmt->bindParam(':user_id', $profile_user_id);
            $stmt->execute();
            $user_posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($user_posts) > 0): ?>
                <?php foreach ($user_posts as $post): ?>
                    <a href="post.php?p=<?php echo $post['id'] ?>">
                        <div class="post">
                            <?php if ($post['image']): ?>
                                <img src="<?php echo $post['image']; ?>" alt="Post Image" width="300">
                            <?php endif; ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No posts to display.</p>
            <?php endif; ?>
        </div>
    
    <div class="menu">
        <a href="/app"><span class="material-symbols-rounded">
                home
            </span></a>
        <a href="search"><span class="material-symbols-rounded">
                search
            </span></i></a>
        <a href="notification"><span class="material-symbols-rounded">
                notifications
            </span></a>
        <a href="user" class="pic">
            <img class="active" src="<?php echo $profile_user['profile_pic']; ?>" />
        </a>
    </div>
    </div>
    </div>
</body>

</html>