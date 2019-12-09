<?php
include $_SERVER['DOCUMENT_ROOT'] . '/function/function_core.php';
$alipay_config['partner'] = $G['config']['epay_pid'];
$alipay_config['key'] = $G['config']['epay_key'];
$alipay_config['sign_type'] = strtoupper('MD5');
$alipay_config['input_charset'] = strtolower('utf-8');
$alipay_config['transport'] = 'http';
$alipay_config['apiurl'] = 'http://' . $G['config']['pay_domain'] . '/';
require_once("lib/epay_notify.class.php");
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();
if ($verify_result) {
    $out_trade_no = $_GET['out_trade_no'];
    $trade_no = $_GET['trade_no'];
    $trade_status = $_GET['trade_status'];
    $type = $_GET['type'];
    if ($_GET['trade_status'] == 'TRADE_SUCCESS') {

        if ($G['config']['epay_againcheck'] == 'true') {
            $info = json_decode(file_get_contents('http://' . $G['config']['pay_domain'] . '/api.php?act=order&pid=' . $G['config']['epay_pid'] . '&key=' . $G['config']['epay_key'] . '&out_trade_no=' . $out_trade_no), true);
            if ($info['status'] != '1') {
                die('fail');
            }
        }
        if (!$tradeinfo = $db->select_first_row('sq_trade', '*', array('tradeno' => $out_trade_no), 'AND')) {
            die('fail');
        }
        if ($tradeinfo['paymoney'] != $_GET['money']) {
            die('fail');
        }
        if ($tradeinfo['status'] == '1') {
            if (!empty($tradeinfo['agentid']) && $tradeinfo['agentid'] != '0') {
                /*
                  $agentinfo = $db->select_first_row('sq_agent','*',array('ID'=>$tradeinfo['agentid']),'AND');
                  $lastmoney = $agentinfo['money'] - $tradeinfo['paymoney'];
                  $allspend = $agentinfo['allspend'] + $tradeinfo['paymoney'];
                  */
                $db->update('sq_agent', array('ID' => $tradeinfo['agentid']), 'AND', array('money' => $lastmoney, 'allspend' => $allspend));
            }
            $db->update('sq_trade', array('tradeno' => $out_trade_no), 'AND', array('overtime' => time(), 'status' => '2'));
            die('success');
        } else if ($tradeinfo['status'] == '2') {
            die('success');
        } else {
            die('fail');
        }
    }
    echo "success";
} else {
    echo "fail";
}
?>