<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

$user_id = $_SESSION['user_id'];

// Fetch the current user's details
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User not found!");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $bio = $_POST['bio'];
    $profile_pic = $user['profile_pic']; // Default to existing profile picture

    // Handle profile picture upload
    if ($_FILES['profile_pic']['name']) {
        $target_dir = "profile/";
        $target_file = $target_dir . basename($_FILES['profile_pic']['name']);
        if (move_uploaded_file($_FILES['profile_pic']['tmp_name'], $target_file)) {
            $profile_pic = $target_file;
        }
    }

    // Update user details in the database
    $stmt = $conn->prepare("UPDATE users SET username = :username, bio = :bio, profile_pic = :profile_pic WHERE id = :user_id");
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':bio', $bio);
    $stmt->bindParam(':profile_pic', $profile_pic);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();

    header("Location: user");
    exit();
}
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
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon">
    <link rel="manifest" href="/app/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#222222">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            width: 400px;
            margin-bottom: 50px;
        }

        @media (max-width: 500px) {
            .main {
                max-width: none;
                width: 100%;
                height: auto;
            }

            .edit-profile-container{
                width: 100%;
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
            margin: 8px 20px;
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
        .edit-profile-container {
            padding: 20px 20px 5px 20px;
            width: auto;
        }

        .edit-profile-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .edit-profile-container form {
            display: flex;
            flex-direction: column;
        }

        .edit-profile-container form label {
            margin-bottom: 5px;
        }

        .edit-profile-container form input{
            margin-bottom: 15px;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #333;
            color: #fff;
        }
        .edit-profile-container form textarea {
            margin-bottom: 15px;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #333;
            width: 100%;
            height: 100px;
            resize: none;
            color: #fff;
        }

        .edit-profile-container form textarea {
            resize: vertical;
            height: 100px;
        }

        .edit-profile-container button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #007fff;
            color: #fff;
            cursor: pointer;
        }

        .edit-profile-container button:hover {
            background-color: #005fff;
        }
        .logout{
            padding: 10px 20px;
            width: 90%;
            text-decoration: none;
            justify-content: center;
            display: flex;
            border-radius: 5px;
            background-color: #007fff;
            margin-top: 15px;
            margin: 15px 10px 0 20px;
            color: #fff;
            cursor: pointer;
        }
        .logout:hover{
            background-color: #005fff;
        }
    </style>
</head>

<body>
    <div class="main">
        <?php include("nav.php"); ?>
        <div class="edit-profile-container">
            <h2>Edit Profile</h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo $user['username']; ?>" required>

                <label for="bio">Bio</label>
                <textarea id="bio" name="bio"><?php echo $user['bio']; ?></textarea>

                <label for="profile_pic">Profile Picture</label>
                <input type="file" id="profile_pic" name="profile_pic" accept="image/*">

                <button type="submit">Save Changes</button>
            </form>
        </div>
        <a class="logout" href="logout">Logout</a>
        <div class="menu">
            <a href="/app"><span class="material-symbols-outlined">
                    home
                </span></a>
            <a href="search"><span class="material-symbols-outlined">
                    search
                </span></i></a>
            <a href="notification" ><span class="material-symbols-outlined">
                    notifications
                </span></a>
            <a href="user" class="pic">
                <img class="active" src="<?php echo $profile_pic; ?>" />
            </a>
        </div>
    </div>
</body>

</html>