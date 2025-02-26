<?php include 'db.php'; ?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="assets/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <link rel="manifest" href="/app/manifest.json">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="theme-color" content="#222222">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <title>Signup - Savage</title>
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
            justify-content: center;
        }

        .linex {
            height: 0.02em;
            background-color: #333;
            width: 100%;
            margin-bottom: 10px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 2.5rem;
        }

        form {
            padding: 30px;
            margin-top: 200px;
            color: #fff;
            border-radius: 10px;
            width: 100%;
            max-width: 400px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 10px 15px;
            margin-bottom: 20px;
            background-color: #333;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #007bff;
        }

        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #0056b3;
        }

        span{
            display: flex;
            justify-content: center;
        }
        span a{
            text-decoration: none;
            color: #007bff;
        }
        /* Responsive Design */
        @media (max-width: 480px) {
            form {
                padding: 20px;
            }

            h2 {
                font-size: 2rem;
            }

            input[type="text"],
            input[type="password"],
            button[type="submit"] {
                padding: 10px;
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <div class="main">
        <div class="login">

            <form method="POST" action="">
                <h2>Signup</h2>
                <input type="text" name="username" placeholder="Username" required><br>
                <input type="password" name="password" placeholder="Password" required><br>
                <button type="submit" name="signup">Signup</button>
            </form>
            <span>Already have Account?<a href="login">&nbsp; Login</a></span>
        </div>
    </div>
    <?php
    if (isset($_POST['signup'])) {
        $username = $_POST['username'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (:username, :password)");
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':password', $password);

        if ($stmt->execute()) {
            echo "Signup successful! <a href='login.php'>Login here</a>";
        } else {
            echo "Signup failed!";
        }
    }
    ?>
</body>

</html>