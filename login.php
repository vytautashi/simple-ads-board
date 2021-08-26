<?php
session_start();
require_once('inc/mysql_conn.php');
require_once('inc/helper.php');

if (Account::auth()) {
    Redirect::to_admin_panel();
}


$error_msg = login();

function login()
{
    if (empty($_POST)) {
        return '';
    }

    $conn     = mysql_conn();
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!Account::login($email, $password, $conn)) {
        return 'Wrong password or email';
    }

    $_SESSION['email'] = $email;
    Redirect::to_admin_panel();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link type="text/css" rel="stylesheet" href="/styles.css">
</head>

<body>
    <nav>
        <ul>
            <li><a href="/">Advertisements</a></li>
            <li><a href="/my-ads">My ads</a></li>
            <li><a href="/about">About</a></li>
        </ul>
    </nav>
    <main>
        <h1>Login</h1>
        <p class="err"><?= htmlentities($error_msg ?? '') ?></p>
        <form method="post">
            <label for="email">Email:</label><br>
            <input type="email" name="email" id="email" value="<?= htmlentities($_POST['email'] ?? '')  ?>" required><br><br>
            <label for="password">Password:</label><br>
            <input type="password" name="password" id="password" required><br><br>
            <input type="submit" name="register" value="Submit">
        </form>
        <p>or</p>
        <a href="/register">Register</a>
    </main>
</body>

</html>