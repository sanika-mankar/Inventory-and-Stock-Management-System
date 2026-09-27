<?php

session_start();

require_once "config.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = trim($_POST["password"] ?? "");

    $username_safe = mysqli_real_escape_string($conn, $username);

    $sql = "SELECT id, username, password, full_name
            FROM users
            WHERE username = '$username_safe'
            LIMIT 1";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die("Query Error: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($result) == 0) {

        $error = "User not found!";

    } else {

        $user = mysqli_fetch_assoc($result);

        if ($password === $user["password"]) {

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["full_name"] = $user["full_name"];

            header("Location: dashboard.php");
            exit();

        } else {

            $error = "Invalid password!";

        }
    }
}
?>
   

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Management System - Login</title>
</head>

<body>

<h2>Inventory Management System</h2>

<?php if ($error != ""): ?>
    <p><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="POST">

    <label>Username:</label><br>
    <input type="text" name="username" required><br><br>

    <label>Password:</label><br>
    <input type="password" name="password" required><br><br>

    <button type="submit">Login</button>

</form>

</body>
</html>