<?php
/**
 * Created by PhpStorm.
 * User: 80071
 * Date: 2018/2/6
 * Time: 18:12
 */


include "function/function_core.php";
error_reporting(E_ALL);
if (empty($_GET['tradeno'])) {
    tips('订单号为空！', 'index.php');
}

$tradeno = $_GET['tradeno'];
if (!$tradeinfo = $db->select_first_row('sq_trade', '*', array('tradeno' => $tradeno), 'AND')) {
    tips('无法拉取订单信息！', 'index.php');
}
if ($tradeinfo['status'] == '3') {
    tips('该订单已处理成功，无需再次处理！', 'index.php');
}
if ($tradeinfo['status'] != '2') {
    tips('订单状态不正常，无法继续操作！', 'index.php');
}

if (!$db->update('sq_trade', array('tradeno' => $tradeno,'status'=>'2'), 'AND', array('status' => 3))) {
    tips('订单更新失败', 'index.php');
}
include 'function/trade.inc.php';
$back = trade_do($tradeinfo);
$back = json_decode($back,true);
if ($back['code'] == 2){
    die('已成功生成卡密：'.$back['kami']);
}else{
    tips($back['msg'],'index.php');
}