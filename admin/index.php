<?php
/**
 * Created by PhpStorm.
 * User: 温泉
 * Date: 2017-10-15
 * Time: 12:08
 */
require '../function/function_core.php';


if (empty($_SESSION['admin_username']) || empty($_SESSION['admin_password'])) {
    echo '没有登录:code:-1';
    header('Location: login.html');
    die();
}
if (!$admininfo = $db->select_first_row('sq_admin', '*', array('username' => $_SESSION['admin_username'], 'password' => $_SESSION['admin_password']), 'AND')) {
    echo '没有登录:code:-2';
    header('Location: login.html');

    die();
}
if ($_SERVER['HTTP_USER_AGENT'] !== $_SESSION['admin_HTTP_USER_AGENT']) {
    echo '没有登录:code:-3';
    header('Location: login.html');
    die();
}
if (!empty($_GET['mod']) && $_GET['mod'] == 'loginout') {
    unset($_SESSION['admin_username']);
    unset($_SESSION['admin_password']);
    unset($_SESSION['admin_HTTP_USER_AGENT']);
    header('Location: login.html');
}
header('Location: admin.html');