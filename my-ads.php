<?php
session_start();
require_once('inc/mysql_conn.php');
require_once('inc/helper.php');

if (!Account::auth()) {
    Redirect::to_login();
}

$conn  = mysql_conn();
$email = $_SESSION['email'] ?? '';

$account = Account::get_account($email, $conn);
if ($account === null) {
    Redirect::to_logout();
}

$_SESSION['delete_token']             = Common::generate_token();
$_SESSION['delete_token_expire_time'] = time() + (60 * 10);

$advertisements = Advertisement::get_multiple_advertisements_admin($conn, $account['id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My ads</title>
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
        <h1>My ads</h1>
        <?php
        foreach ($advertisements as $row) {
            echo Advertisement::generate_ad_summary_html($row["id"], $row["title"], $row["description"], $row["create_date"], true, $_SESSION['delete_token']);
        }
        ?>
    </main>
</body>

</html>