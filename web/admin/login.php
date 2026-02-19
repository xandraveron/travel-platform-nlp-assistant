<?php
session_start();
require_once __DIR__ . "/../connect_db.php";

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);

        if (empty($username) || empty($password)) {
            $error_message = "Username and password are required!";
        } else {
            $stmt = $conectare->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $row = $result->fetch_assoc();

                if (password_verify($password, $row['password'])) {
                    $_SESSION['loggedin'] = true;
                    $_SESSION['username'] = $username;
                    $_SESSION['admin_id'] = $row['id'];
                    $_SESSION['userType'] = $row['user_type'];

                    if ($row['user_type'] == 'admin') {
                        header("Location: index_admin.php");
                        exit();
                    } else {
                        $error_message = "You are not authorized to access this page.";
                    }
                } else {
                    $error_message = "Invalid password!";
                }
            } else {
                $error_message = "Invalid username!";
            }

            $stmt->close();
        }
    } else {
        $error_message = "Username and password are required!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet" type="text/css" href="signup.css">
</head>
<body>
    <div class="align">
        <h2>Admin Login</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
            <label>Username:</label><br>
            <input type="text" name="username" required><br>
            <label>Password:</label><br>
            <input type="password" name="password" required><br><br>
            <input type="submit" value="Login">
            <p><?php echo $error_message; ?></p>
        </form>
    </div>
</body>
</html>

