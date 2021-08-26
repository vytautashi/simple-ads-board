<?php
session_start();
require_once('inc/mysql_conn.php');
require_once('inc/helper.php');

if (!Account::auth()) {
    Redirect::to_login();
}


$conn                 = mysql_conn();
$email                = $_SESSION['email'] ?? '';
$delete_token         = $_GET['delete_token'] ?? '';
$delete_token_session = $_SESSION['delete_token'] ?? null;
$token_expire_time    = (int)$_SESSION['delete_token_expire_time'] ?? 0;
$advertisement_id     = (int)$_GET['id'] ?? 0;

if ($delete_token !== $delete_token_session || time() > $token_expire_time) {
    Redirect::to_admin_panel();
}

$account = Account::get_account($email, $conn);
if ($account === null) {
    Redirect::to_logout();
}

if (Advertisement::delete($advertisement_id, $account['id'], $conn)) {
    unset($_SESSION['delete_token']);
    unset($_SESSION['delete_token_expire_time']);
}

Redirect::to_admin_panel();
