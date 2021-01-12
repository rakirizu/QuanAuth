<?php
$path = dirname(dirname(dirname(__FILE__)));//当前目录
include $path . '/function/function_core.php';
require_once("lib/epay_notify.class.php");
$alipay_config['partner'] = $G['config']['epay_pid'];
$alipay_config['key'] = $G['config']['epay_key'];
$alipay_config['sign_type'] = strtoupper('MD5');
$alipay_config['input_charset'] = strtolower('utf-8');
$alipay_config['transport'] = 'http';
$alipay_config['apiurl'] = 'http://' . $G['config']['pay_domain'] . '/';
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyReturn();
if ($verify_result) {
    $out_trade_no = $_GET['out_trade_no'];
    $trade_no = $_GET['trade_no'];
    $trade_status = $_GET['trade_status'];
    $type = $_GET['type'];
    if ($_GET['trade_status'] == 'TRADE_SUCCESS' || $_GET['trade_status'] == 'TRADE_FINISHED') {
        if ($G['config']['epay_againcheck'] == 'true') {
            $info = json_decode(file_get_contents('http://' . $G['config']['pay_domain'] . '/api.php?act=order&pid=' . $G['config']['epay_pid'] . '&key=' . $G['config']['epay_key'] . '&out_trade_no=' . $out_trade_no), true);
            if ($info['status'] != '1') {
                tips('订单回调验证失败，可能是您尚未支付！');
            }
        }
        if (!$tradeinfo = $db->select_first_row('sq_trade', '*', array('tradeno' => $out_trade_no), 'AND')) {
            tips('订单拉取失败，无法继续处理！');
        }
        if ($tradeinfo['paymoney'] != $_GET['money']) {
            tips('商品金额校验失败，应付：' . $tradeinfo['paymoney'] . '，实付：' . $_GET['money']);
        }
        if ($tradeinfo['status'] == '1') {
            /*
              if (!empty($tradeinfo['agentid']) && $tradeinfo['agentid'] != '0'){
                  $agentinfo = $db->select_first_row('sq_agent','*',array('ID'=>$tradeinfo['agentid']),'AND');
                  $lastmoney = $agentinfo['money'] - $tradeinfo['paymoney'];
                  $allspend = $agentinfo['allspend'] + $tradeinfo['paymoney'];
                  $db->update('sq_agent',array('ID'=>$tradeinfo['agentid']),'AND',array('money'=>$lastmoney,'allspend'=>$allspend));
              }*/
            $db->update('sq_trade', array('tradeno' => $out_trade_no), 'AND', array('overtime' => time(), 'status' => '2'));
            tips('订单校验成功，点击确认跳转至结果页...', "../../payresult.php?tradeno=" . $out_trade_no);
        } else if ($tradeinfo['status'] == '2') {
            tips('订单校验成功，点击确认跳转至结果页...', "../../payresult.php?tradeno=" . $out_trade_no);
        } else {
            tips('订单状态不正常，无法继续处理！');
        }
    } else {
        tips('订单状态异常：' . $_GET['trade_status']);
    }
} else {
    tips('订单校验失败，无法继续处理！');
}
?>