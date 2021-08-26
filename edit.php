<?php
session_start();
require_once('inc/mysql_conn.php');
require_once('inc/helper.php');

if (!Account::auth()) {
    Redirect::to_login();
}


$edit = edit();
$error_msg = $edit['error'];
$advertisement = $edit['ad'];

function edit()
{
    $conn             = mysql_conn();
    $email            = $_SESSION['email'] ?? '';
    $advertisement_id = (int)$_GET['id'] ?? '';
    $title            = $_POST['title'] ?? '';
    $description      = $_POST['description'] ?? '';

    $edit_token_session = $_SESSION['edit_token'] ?? null;
    $edit_token         = $_POST['edit_token'] ?? '';

    $_SESSION['edit_token'] = Common::generate_token();

    $account = Account::get_account($email, $conn);
    if ($account === null) {
        Redirect::to_logout();
    }

    $advertisement = Advertisement::get_advertisement_admin($conn, $advertisement_id, $account['id']);
    if ($advertisement === null) {
        Redirect::to_admin_panel();
    }

    if (empty($_POST)) {
        return ['ad' => $advertisement, 'error' => null];
    }

    if ($edit_token !== $edit_token_session) {
        return ['ad' => $advertisement, 'error' => 'Token has been expired, try submit form again'];
    }

    if (strlen($title) < 1 || strlen($description) < 1) {
        return ['ad' => $advertisement, 'error' => 'Advertisement title or/and description is too short'];
    }

    if (!Advertisement::update($conn, $title, $description, $advertisement_id, $account['id'])) {
        return ['ad' => $advertisement, 'error' => 'Failed to update advertisement'];
    }
    Redirect::to_admin_panel();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Edit ad</title>
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
            <li><a href="/post-ad">Post ad</a></li>
            <li><a href="/logout">(<?= htmlentities($_SESSION['email'] ?? '')  ?>)logout</a></li>
        </ul>
    </div>
    <main>
        <h1>Edit ad (id: <?= htmlentities($advertisement['id'] ?? '') ?>)</h1>
        <p class="err"><?= htmlentities($error_msg ?? '') ?></p>
        <form method="post">
            <input type="hidden" name="edit_token" value="<?= htmlentities($_SESSION['edit_token'] ?? '') ?>" />
            <label for="title">Title:</label><br>
            <input type="text" name="title" id="title" value="<?= htmlentities($_POST['title'] ?? $advertisement['title'] ?? '')  ?>" required><br><br>
            <label for="description">Description:</label><br>
            <textarea name="description" id="description" cols="30" rows="10"><?= htmlentities($_POST['description'] ?? $advertisement['description'] ?? '')  ?></textarea>
            <input type="submit" value="Submit">
        </form>
    </main>
</body>

</html>