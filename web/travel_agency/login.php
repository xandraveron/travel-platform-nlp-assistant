<?php

session_start();
require_once __DIR__ . "/../connect_db.php";

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!empty($username) && !empty($password)) {
        $stmt = $conectare->prepare("SELECT agency_id, password FROM agencies WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($agency_id, $hashed_password);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['agency_id'] = $agency_id;
                $_SESSION['username'] = $username;
                header("Location: index_agency.php");
                exit();
            } else {
                $error_message = "Incorrect password.";
            }
        } else {
            $error_message = "No agency found with that username.";
        }

        $stmt->close();
    } else {
        $error_message = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agency Login</title>
    <link rel="stylesheet" href="signup.css">
</head>
<body>
    <form action="login.php" method="post">
        <h2>Agency Login</h2>
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
        <button type="submit">Login</button>
        <p><?php echo $error_message; ?></p>
    </form>
</body>
</html>

