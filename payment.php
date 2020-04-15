<?php
/**
 * Created by PhpStorm.
 * User: 80071
 * Date: 2018/2/5
 * Time: 20:17
 */ 
include 'function/function_core.php';
error_reporting(E_ALL);

if (empty($_GET['tradeno'])) {
    tips('订单号为空！');
}
if (!$tradinfo = $db->select_first_row('sq_trade', '*', array('tradeno' => $_GET['tradeno']), 'AND')) {
    tips('订单信息拉取失败，无法继续支付');
}
if ($tradinfo['status'] != '1') {
    tips('订单状态不正常，也许是已经支付成功了');
}

if ($tradinfo['paymoney'] == 0) {
    $time = time();
    $db->update('sq_trade', array('tradeno' => $_GET['tradeno']), 'AND', array('overtime' => $time, 'status' => '2'));
    tips('该订单为免费订单，点击确定跳转到结果页...', "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/payresult.php?tradeno=" . $_GET['tradeno']);

}

if ($tradinfo['paytype'] == 'zxzf') {

    if ($tradinfo['onlinepaytype'] === 'zfb') {
        if ($G['config']['alipaytype'] == '2') {
            tips('支付宝在线支付渠道目前已关闭，请选择其他支付方式！');
        }

        if ($G['config']['alipaytype'] == '0') {
            //免签约支付echo '111';
            mqyturn('alipay');
        }
        if ($G['config']['alipaytype'] == '1') {
            //支付宝签约支付
        }
    }
    if ($tradinfo['onlinepaytype'] === 'wx') {
        if ($G['config']['wxpaytype'] == '2') {
            tips('微信在线支付渠道目前已关闭，请选择其他支付方式！');
        }
        if ($G['config']['wxpaytype'] == '0') {
            //免签约支付
            mqyturn('wxpay');
        }
        if ($G['config']['wxpaytype'] == '1') {
            //微信签约支付
        }
    }
    if ($tradinfo['onlinepaytype'] === 'qq') {
        if ($G['config']['qqpaytype'] == '2') {
            tips('QQ钱包在线支付渠道目前已关闭，请选择其他支付方式！');
        }
        if ($G['config']['qqpaytype'] == '0') {
            //免签约支付
            mqyturn('qqpay');
        }
        if ($G['config']['qqpaytype'] == '1') {
            //QQ签约支付
        }
    }
    tips('What the fuck?');
}
if ($tradinfo['paytype'] == 'czkm') {
    $usekey = $tradinfo['kami'];
    if (!$keyinfo = $db->select_first_row('sq_key', '*', array('kami' => $usekey), 'AND')) {
        tips('卡密不存在！');
    }
    if ($keyinfo['lastmoney'] < $tradinfo['paymoney']) {
        tips('卡密余额不足！');
    }
    if ($keyinfo['status'] != '1') {
        tips('卡密已被封禁，无法使用！');
    }
    $lastmoney = $keyinfo['lastmoney'] - $tradinfo['paymoney'];
    $time = time();
    $keynewinfo['lastmoney'] = $lastmoney;
    if ($keyinfo['firstusetime'] == 0) {
        $keynewinfo['firstusetime'] = $time;
    }
    $keynewinfo['lastusetime'] = $time;
    $db->update('sq_key', array('kami' => $usekey), 'AND', $keynewinfo);
    $db->insert_back_id('sq_log_kami', array('keyid' => $keyinfo['ID'], 'type' => 'info', 'spendmoney' => $tradinfo['paymoney'], 'lastmoney' => $keynewinfo['lastmoney'], 'time' => $time, 'object' => $tradinfo['user'], 'IP' => get_real_ip(), 'msg' => $tradinfo['name']));
    $db->update('sq_trade', array('tradeno' => $_GET['tradeno']), 'AND', array('overtime' => $time, 'status' => '2'));
    tips('已成功使用卡密进行支付，点击确认跳转至结果页面...', "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/payresult.php?tradeno=" . $_GET['tradeno']);
}
if ($tradinfo['paytype'] == 'yezf') {
    if (empty($tradinfo['agentid']) || $tradinfo['agentid'] == 0) {
        tips('错误的付款方式，请重新选择！');
    }
    if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('ID' => $tradinfo['agentid']), 'AND')) {
        tips('代理信息获取失败，无法继续支付！');
    }
    if (($_SESSION['agent_username'] != $agentinfo['username']) || ($_SESSION['agent_password'] != $agentinfo['password'])) {
        tips('当前登录的代理账号或密码与订单代理或密码不匹配！');
    }
    if ($agentinfo['status'] != '1') {
        tips('代理状态不正常，无法继续支付！');
    }
    if ($agentinfo['money'] < $tradinfo['paymoney']) {
        tips('很抱歉，您的余额不足，无法支付！');
    }
    $lastmoney = $agentinfo['money'] - $tradinfo['paymoney'];
    $allspend = $agentinfo['allspend'] + $tradinfo['paymoney'];
    $db->update('sq_agent', array('ID' => $tradinfo['agentid']), 'AND', array('money' => $lastmoney));
    $db->update('sq_trade', array('tradeno' => $_GET['tradeno']), 'AND', array('overtime' => time(), 'status' => '2'));
    $db->insert_back_id('sq_log_agent', array('time' => time(), 'aid' => $agentinfo['ID'], 'ip' => get_real_ip(), 'msg' => '使用余额支付' . $tradinfo['paymoney'] . '元购买' . $tradinfo['name'] . '，余额：' . $lastmoney));
    tips('您已成功使用余额付款，点击确认跳转至结果页...', "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/payresult.php?tradeno=" . $_GET['tradeno']);
}
tips('What the fuck?');
function mqyturn($paytype)
{

    global $G;
    global $tradinfo;

    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>正在跳转到支付页面，请稍后</title>
</head>
';
    require_once("./pay/epay/lib/epay_submit.class.php");
    $alipay_config['partner'] = $G['config']['epay_pid'];
    $alipay_config['key'] = $G['config']['epay_key'];
    $alipay_config['sign_type'] = strtoupper('MD5');
    $alipay_config['input_charset'] = strtolower('utf-8');
    $alipay_config['transport'] = 'http';
    $alipay_config['apiurl'] = 'http://' . $G['config']['pay_domain'] . '/';

    $notify_url = str_replace("\\", '/', str_replace("\/", '/', "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/pay/epay/notify_url.php"));
    $return_url = str_replace("\\", '/', str_replace("\/", '/', "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/pay/epay/return_url.php"));
    $out_trade_no = $_GET['tradeno'];
    $type = $paytype;

    $name = $tradinfo['name'];
    $money = $tradinfo['paymoney'];
    $sitename = $G['config']['sitename'];
    $parameter = array(
        "pid" => trim($alipay_config['partner']),
        "type" => $type,
        "notify_url" => $notify_url,
        "return_url" => $return_url,
        "out_trade_no" => $out_trade_no,
        "name" => $name,
        "money" => $money,
        "sitename" => $sitename
    );
    $alipaySubmit = new AlipaySubmit($alipay_config);

    $html_text = $alipaySubmit->buildRequestForm($parameter);

    echo $html_text;
    echo '
</body>
</html>';
    die();
}
