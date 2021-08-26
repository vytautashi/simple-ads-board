<?php
require_once('inc/mysql_conn.php');
require_once('inc/helper.php');

$conn = mysql_conn();
$page = (int)($_GET['p'] ?? 1);
$advertisements = Advertisement::get_multiple_advertisements($conn, $page, 5);


?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Advertisements board</title>
    <link type="text/css" rel="stylesheet" href="/styles.css">
</head>

<body>
    <nav>
        <ul>
            <li><a href="/" class="active">Advertisements</a></li>
            <li><a href="/my-ads">My ads</a></li>
            <li><a href="/about">About</a></li>
        </ul>
    </nav>
    <main>
        <h1>Advertisements</h1>
        <?php
        foreach ($advertisements as $row) {
            echo Advertisement::generate_ad_summary_html($row["id"], $row["title"], $row["description"], $row["create_date"], false);
        }
        ?>
        <?= Common::pagination_html($page) ?>
    </main>
</body>

</html>