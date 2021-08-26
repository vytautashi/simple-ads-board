<?php
session_start();
require_once('inc/mysql_conn.php');
require_once('inc/helper.php');

if (Account::auth()) {
    Redirect::to_admin_panel();
}


$error_msg = register();

function register()
{
    if (empty($_POST)) {
        return '';
    }

    $conn     = mysql_conn();
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (strlen($email) < 1 || strlen($password) < 1) {
        return 'Email address or/and password is too short';
    }

    if (!Account::create_account($email, $password, $conn)) {
        return sprintf('User with email: %s already exists', $email);
    }

    if (!Account::login($email, $password, $conn)) {
        return 'Account successfully created, but failed to login';
    }

    $_SESSION['email'] = $email;
    Redirect::to_admin_panel();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Registration</title>
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
        <h1>Registration</h1>
        <p class="err"><?= htmlentities($error_msg ?? '') ?></p>
        <form method="post">
            <label for="email">Email:</label><br>
            <input type="email" name="email" id="email" required><br><br>
            <label for="password">Password:</label><br>
            <input type="password" name="password" id="password" required><br><br>
            <input type="submit" name="register" value="Submit">
        </form>
        <p>or</p>
        <a href="/login">Login</a>
    </main>
</body>

</html>