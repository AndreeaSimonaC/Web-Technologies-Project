<?php
session_start();
require_once '../config/db.php';
require_once '../models/LogModel.php';

setcookie("remember_user", "", time() - 3600, "/");

if (isset($_SESSION['user_id'])) {
    writeLog('User logged out', $_SESSION['user_id']);
}

session_destroy();
header("Location: index.php");
exit();
?>