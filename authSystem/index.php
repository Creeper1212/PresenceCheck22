<?php
session_start();
require '../Dashboard/config.php';
require_once 'session_auth.php';


//Check if the session is valid.  If not, redirect to login.php
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}


//Rest of the Dashboard code here.
//Example:
echo "<h2>Welcome, " . $_SESSION['username'] . "!</h2>";
echo "<a href='logout.php'>Logout</a>";


if ($_SESSION['is_admin']) {
    echo "<a href='admin_users.php'>Admin Users</a>";
}


function registerUser ($username, $password) {
    global $userdb_conn;
    $stmt = $userdb_conn->prepare("INSERT INTO users(username, password) VALUES(?, ?)");
    if (!$stmt) {
        die("Prepare failed: " . $userdb_conn->error);
    }
    $stmt->bind_param("ss", $username, $password);
    if ($stmt->execute()) {
        header("Location: ../index.php");
        exit;
    } else {
        die("Error: " . $stmt->error);
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrieren</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background: linear-gradient(135deg, #ffffff, #f8f9fa);
        }
        .card {
            max-width: 400px;
            padding: 2rem;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            border: none;
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card">
            <h2 class="card-title text-center mb-4 text-danger">
                <i class="bi bi-person-plus me-2"></i>Add Admin User
            </h2>
            <form action="index.php" method="POST">
                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger" role="alert">
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required autofocus>
                    <label for="username">Username</label>
                </div>
                <div class="form-floating mb-3">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">Password</label>
                </div>
                <button type="submit" class="btn btn-danger w-100" name="submit">Create</button>
            </form>
            </div>
    </div>
</body>
</html>
