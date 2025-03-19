<?php
require '../Dashboard/config.php'; // Adjust this path to your database config file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // Validate input
    if (empty($username) || empty($password)) {
        $error = "Username and password are required.";
    } else {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $userdb_conn->prepare("SELECT * FROM users WHERE username=?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            // Verify password
            if (password_verify($password, $user["password"])) {
                // Start the session
                session_start();

                // Set session variables
                $_SESSION["username"] = $user["username"];
                $_SESSION["ip"] = $_SERVER['REMOTE_ADDR'];
                $_SESSION["user_agent"] = $_SERVER['HTTP_USER_AGENT'];

                // Always redirect to homepage.php after login
                header("Location: homepage.php");
                exit();
            } else {
                $error = "Invalid credentials.";
            }
        } else {
            $error = "User not found.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form action="login.php" method="POST">
        <h1>Login</h1>
        <div class="inputs_container">
            <input type="text" placeholder="Username" name="username" autocomplete="off" required>
            <input type="password" placeholder="Password" name="password" autocomplete="off" required>
        </div>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <button name="submit">Login</button>
    </form>
</body>
</html>