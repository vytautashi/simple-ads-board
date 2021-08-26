<?php
session_start();
session_destroy();
require_once('inc/helper.php');

Redirect::to_login();
