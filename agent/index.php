<?php
/**
 * Created by PhpStorm.
 * User: 温泉
 * Date: 2017-10-15
 * Time: 12:08
 */
require '../function/function_core.php';
if (empty($_SESSION['agent_username']) || empty($_SESSION['agent_password'])) {
    echo '没有登录:code:-1';
    header('Location: login.html');
    die();
}
if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username'], 'password' => $_SESSION['agent_password']), 'AND')) {
    echo '没有登录:code:-2';
    header('Location: login.html');
    die();
}
if ($_SERVER['HTTP_USER_AGENT'] !== $_SESSION['agent_HTTP_USER_AGENT']) {
    echo '没有登录:code:-3';
    header('Location: login.html');
    die();
}
if (!empty($_GET['mod']) && $_GET['mod'] == 'loginout') {
    unset($_SESSION['agent_username']);
    unset($_SESSION['agent_password']);
    unset($_SESSION['agent_HTTP_USER_AGENT']);
    unset($_SESSION['agent_id']);

    header('Location: login.html');
    die();
}
header('Location: agent.html');