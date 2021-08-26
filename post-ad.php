<?php
session_start();
require_once('inc/mysql_conn.php');
require_once('inc/helper.php');

if (!Account::auth()) {
    Redirect::to_login();
}


$error_msg = post();

function post()
{
    $email              = $_SESSION['email'] ?? '';
    $post_token_session = $_SESSION['post_token'] ?? null;
    $post_token         = $_POST['post_token'] ?? '';
    $title              = $_POST['title'] ?? '';
    $description        = $_POST['description'] ?? '';

    $_SESSION['post_token'] = Common::generate_token();

    if (empty($_POST)) {
        return '';
    }

    if ($post_token !== $post_token_session) {
        return 'Token has been expired, try submit form again';
    }

    if (strlen($title) < 1 || strlen($description) < 1) {
        return 'Advertisement title or/and description is too short';
    }

    $conn = mysql_conn();

    $account = Account::get_account($email, $conn);
    if ($account === null) {
        return 'Failed to find account in database';
    }

    $advertisement_id = Advertisement::create_advertisement($title, $description, $account['id'], $conn);
    if ($advertisement_id === null) {
        return 'Failed to create new advertisement in database';
    }

    Redirect::to_admin_panel();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Post ad</title>
    <link type="text/css" rel="stylesheet" href="/styles.css">
</head>

<body>
    <nav>
        <ul>
            <li><a href="/">Advertisements</a></li>
            <li><a href="/my-ads" class="active">My ads</a></li>
            <li><a href="/about">About</a></li>
        </ul>
    </nav>
    <div class="subnav">
        <ul>
            <li><a href="/post-ad" class="active">Post ad</a></li>
            <li><a href="/logout">(<?= htmlentities($_SESSION['email'] ?? '') ?>)logout</a></li>
        </ul>
    </div>
    <main>
        <h1>Post ad</h1>
        <p class="err"><?= htmlentities($error_msg ?? '') ?></p>
        <form method="post">
            <input type="hidden" name="post_token" value="<?= htmlentities($_SESSION['post_token'] ?? '') ?>" />
            <label for="title">Title:</label><br>
            <input type="text" name="title" id="title" value="<?= htmlentities($_POST['title'] ?? '')  ?>" required><br><br>
            <label for="description">Description:</label><br>
            <textarea name="description" id="description" cols="30" rows="10"><?= htmlentities($_POST['description'] ?? '')  ?></textarea>
            <input type="submit" value="Submit">
        </form>
    </main>
</body>

</html>