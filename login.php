<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Vérifiez le mot de passe haché dans la base de données
    $stmt = $pdo->prepare('SELECT password FROM virtual_users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && hash('sha256', $password) === $user['password']) {
        $_SESSION['email'] = $email;
        $_SESSION['imap_password'] = $password;  // Stocke le mot de passe non haché pour l'IMAP
        header('Location: mailbox.php');
        exit();
    } else {
        $error = 'Invalid email or password';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php if (isset($error)) { echo '<p style="color:red;">' . $error . '</p>'; } ?>
    <form method="post">
        <label>Email:</label>
        <input type="email" name="email" required><br>
        <label>Password:</label>
        <input type="password" name="password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
