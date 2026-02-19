<?php
require_once __DIR__ . "/../connect_db.php";
$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = trim($_POST['password']);
    $user_type = 'admin';

    if ($username && $email && $phone && $password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conectare->prepare("INSERT INTO users (username, password, email, phone, user_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $hashed_password, $email, $phone, $user_type);

        if ($stmt->execute()) {
            $message = "Admin added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Admin</title>
    <style>
        body { font-family: Arial; background-color: #f9f9f9; padding: 20px; }
        form { background: #fff; padding: 20px; border-radius: 8px; width: 400px; margin: auto; box-shadow: 0 0 10px #ccc; }
        input { width: 100%; padding: 10px; margin: 10px 0; }
        button { padding: 10px 20px; background: #307681; color: #fff; border: none; border-radius: 4px; }
        .msg { margin-top: 10px; color: green; font-weight: bold; }
    </style>
</head>
<body>

<h2 style="text-align:center;">Create New Admin</h2>
<form method="post" action="">
    <input type="text" name="username" placeholder="Admin Username" required>
    <input type="email" name="email" placeholder="Admin Email" required>
    <input type="text" name="phone" placeholder="Phone Number" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Add Admin</button>
    <?php if ($message): ?>
        <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
</form>

</body>
</html>
