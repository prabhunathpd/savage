<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include 'db.php';

// Fetch the current user's profile picture
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = :user_id");
$stmt->bindParam(':user_id', $user_id);
$stmt->execute();
$profile_pic = $stmt->fetchColumn();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['content'];
    $image = null;

    if ($_FILES['image']['name']) {
        $target_dir = "posts/";
        $target_file = $target_dir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
        $image = $target_file;
    }

    $stmt = $conn->prepare("INSERT INTO posts (user_id, content, image) VALUES (:user_id, :content, :image)");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':content', $content);
    $stmt->bindParam(':image', $image);
    $stmt->execute();

    header("Location: user.php");
    exit(); // Ensure no further code is executed after the redirect
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon">
    <link rel="manifest" href="/app/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#222222">
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
            width: 400px;
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
            width: 28px;
            height: 28px;
        }

        .menu a span {
            font-size: 30px;
        }

        .create h3 {
            margin: 20px 20px 15px 20px;
            color: #fff;
        }

        .create form {
            margin: 0 20px;
        }

        form textarea {
            width: 100%;
            height: 100px;
            border: 0;
            color: #fff;
            font-size: 16px;
            border-radius: 10px;
            padding: 15px;
            background-color: #222;
            resize: none;
        }

        form button {
            padding: 8px 10px;
            border: 0;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            margin-top: 10px;
            width: 100%;
            background-color: #007fff;
            color: #fff;
        }

        form button:hover {
            background-color: #005fff;
        }

        .file-upload {
            margin-top: 10px;
            text-align: center;
        }

        .file-upload label {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007fff;
            color: #fff;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
        }

        .file-upload label:hover {
            background-color: #005fff;
        }

        .file-upload input[type="file"] {
            display: none;
        }

        .image-preview {
            margin-top: 10px;
            text-align: center;
        }

        .image-preview img {
            max-width: 100%;
            max-height: 200px;
            border-radius: 10px;
            display: none;
        }
    </style>
</head>

<body>
    <div class="main">
        <div class="navbar">
            <div class="logo">
                <h3>Savage</h3>
            </div>
        </div>
        <div class="linex"></div>

        <div class="create">
            <h3>Create Post</h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <textarea name="content" placeholder="Write something..." required></textarea><br>
                <div class="image-preview">
                    <img id="preview" src="#" alt="Image Preview">
                </div>
                <div class="file-upload">
                    <label for="image-upload">Choose Image</label>
                    <input type="file" id="image-upload" name="image" accept="image/*">
                </div>

                <button type="submit"><span class="material-symbols-outlined">
                        edit_square
                    </span>&nbsp; Post</button>
            </form>
        </div>

        <div class="menu">
            <a href="/app" class="active"><span class="material-symbols-outlined">
                    home
                </span></a>
            <a href="search"><span class="material-symbols-outlined">
                    search
                </span></i></a>
            <a href="notification"><span class="material-symbols-outlined">
                    notifications
                </span></a>
            <a href="user" class="pic">
                <img src="<?php echo $profile_pic; ?>" />
            </a>
        </div>
    </div>
    <script>
        // JavaScript to handle image preview
        const imageUpload = document.getElementById('image-upload');
        const preview = document.getElementById('preview');

        imageUpload.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.src = '#';
                preview.style.display = 'none';
            }
        });
    </script>
</body>

</html>