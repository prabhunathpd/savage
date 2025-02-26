<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id']; // Get user ID from session
} else {
    // Redirect to login page if the user is not logged in
    header("Location: login.php");
    exit();
}

include 'db.php';

$search_query = isset($_GET['q']) ? $_GET['q'] : '';
$results = [];

if (!empty($search_query)) {
    $stmt = $conn->prepare("SELECT id, username, profile_pic FROM users WHERE username LIKE :query");
    $stmt->bindValue(':query', "%$search_query%");
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
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
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon">
    <link rel="manifest" href="/app/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#222222">
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

        .search-container {
            margin: 0 auto;
            color: #888;
            padding: 20px;
        }

        .search-container form {
            margin: 10px 0;
            display: flex;
        }

        .search-container form input {
            padding: 8px 15px;
            font-size: 16px;
            margin-right: 10px;
            width: 65vw;
            border: none;
            background-color: #555;
            color: #fff;
            border-radius: 5px;
        }

        .search-container form button {
            border: none;
            cursor: pointer;
            border-radius: 5px;
            padding: 8px 15px;
            font-size: 16px;
            color: #fff;
            background-color: #007fff;
        }

        .search-container form button:hover {
            background-color: #005fff;
        }

        .search-results {
            margin-top: 20px;
        }

        .user-result {
            display: flex;
            align-items: center;
            cursor: pointer;
            background-color: #222;
            border-radius: 10px;
            margin-bottom: 10px;
        }
        a{
            text-decoration: none;
        }

        .user-result img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin: 10px 15px;
        }

        .user-result  p{
            text-decoration: none;
            color: #fff;
            font-weight: 500;
            font-size: 18px;
        }

        .user-result:hover {
            background-color: #333;
        }
    </style>
</head>

<body>
    <div class="main">
        <?php include("nav.php"); ?>

        <div class="search-container">
            <h3>Search Users</h3>
            <form method="GET" action="search.php">
                <input type="text" name="q" placeholder="Search by username"
                    value="<?php echo htmlspecialchars($search_query); ?>">
                <button type="submit">Search</button>
            </form>

            <div class="search-results">
                <?php if (!empty($search_query)): ?>
                    <?php if (count($results) > 0): ?>
                        <?php foreach ($results as $user): ?>
                            <a href="user.php?id=<?php echo $user['id']; ?>">
                                <div class="user-result">
                                    <img src="<?php echo $user['profile_pic']; ?>" alt="Profile Pic">
                                    <p><?php echo $user['username']; ?></p>
                                </div>
                            </a>
                            <div class="linex"></div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No users found.</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        <div class="menu">
            <a href="/app"><span class="material-symbols-outlined">
                    home
                </span></a>
            <a href="search" class="active"><span class="material-symbols-outlined">
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
</body>

</html>