<?php
/**
 * Created by PhpStorm.
 * User: 温泉
 * Date: 2018/2/3
 * Time: 13:24
 */

include "function/function_core.php";
error_reporting(E_ALL);
switch ($_GET['mod']) {
    case 'buy_applist':
        $back = $db->select_all_row('sq_apps', 'ID,appname', array(), 'AND');
        if (count($back) === 0) {
            die();
        }
        $i = '<option value="0">显示所有应用</option>';
        foreach ($back as $value) {
            $i .= '<option value="' . $value['ID'] . '">' . $value['appname'] . '</option>';
        }
        die($i);
        break;
    case 'loadbuylist':
        include 'function/function_app.php';
        $where = array();
        $where['openbuy'] = '1';

        if (!empty($_POST['loadbuylist']) && $_POST['loadbuylist'] != 0) {
            $where['appid'] = intval($_POST['loadbuylist']);
        }
        //print_r($where);

        if (!$result = $db->select_all_row('sq_fidlist', '*', $where, 'AND')) {
            die('站长没有添加任何商品' . $db->geterror());
        }
        //print_r($result);
        $info = '';
        foreach ($result as $item) {
            //echo '1';
            if (!$appinfo = $db->select_first_row('sq_apps', 'appname,usetype,imgsrc,upurl', array('ID' => $item['appid']), 'AND')) {
                echo $db->geterror();
                continue;
            }
            //echo '2';
            if ($item['num'] != '-1') {
                if ($appinfo['usetype'] === 'dqsj') {
                    $numtip = '使用时长：' . time_last($item['num'] * 60);
                } else {
                    $numtip = '充值点数：' . $item['num'] . '点';
                }
            } else {
                if ($appinfo['usetype'] === 'dqsj') {
                    $numtip = '使用时长：无期授权';
                } else {
                    $numtip = '充值点数：无限使用';
                }
            }
            if (empty($appinfo['imgsrc'])) {
                $appinfo['imgsrc'] = './mdl/dog.png';
            }
            $info .= '<div style="display: inline-block;padding: 10px;"><div class="demo-card-square mdl-card mdl-shadow--2dp">
  <div class="mdl-card__title mdl-card--expand"  style="color: #fff;text-shadow: #000 1px 1px 1px;background: url(' . $appinfo['imgsrc'] . ') top left no-repeat #46B6AC;background-size: 100% 100%;">
    <h2 class="mdl-card__title-text">' . $item['fidname'] . '</h2>
  </div>
  
  <div class="mdl-card__supporting-text">
       
        购买价格：' . $item['buyprice'] . '元<br>' . $numtip . '<br>应用名称：' . $appinfo['appname'] . '
  </div>
  <div class="mdl-card__actions mdl-card--border">
    <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored mdl-js-ripple-effect" onclick="info(' . $item['appid'] . ')">
      查看详情
    </button>
    <button class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent" onclick="buy(' . $item['ID'] . ')">
      立即购买
    </button>
    <button class="mdl-button mdl-js-button mdl-button--raised mdl-button--colored mdl-js-ripple-effect" onclick="window.location.href=\'' . $appinfo['upurl'] . '\'">
      下载应用
    </button>
  </div>
</div></div>';

        }
        die($info);
        break;
    case 'buy_first':
        include "function/function_app.php";
        $fidinfo = $db->select_first_row('sq_fidlist', '*', array('ID' => $_GET['fid']), 'AND');
        $appinfo = app_idgetinfo($fidinfo['appid']);
        $output = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Material Design Lite">
    <title>授权在线开通 - '.$G['config']['sitename'].'</title>
    <link rel="stylesheet" href="./mdl/material.min.css">
  <script src="https://v.vaptcha.com/v3.js"></script>
</head>
<body class="flat-blue landing-page">';
        $output .= '<div id="submit_div" style="padding: 20px;">';
        $output .= GetBuyFormHtml($appinfo,$type);
        $output .= '<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_num" class="mdl-textfield__label">开通数量（如果为永久系统默认为1，修改无效）</label>
       
            <input class="mdl-textfield__input" id="kt_num" value="1" type="text">

    </div>

     <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_mail" class="mdl-textfield__label">请输入主人QQ，我们将通过此QQ联系您</label>
      
            <input class="mdl-textfield__input" id="kt_adminqq" onkeyup="dofill()" type="text">
   
    </div>
        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_mail" class="mdl-textfield__label">请输入您的邮箱</label>
  
            <input class="mdl-textfield__input" id="kt_mail" type="text">

    </div>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        请选择您的支付方式：
        <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="radio4">
            <input type="radio" id="radio4" class="mdl-radio__button" name="zffs" value="zxzf" onclick="$(\'#show_kmcz\').hide();$(\'#show_zxzf\').show();" checked>
            <span class="mdl-radio__label">在线支付</span>
        </label>
        <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="radio5" >
            <input type="radio" id="radio5" class="mdl-radio__button" name="zffs" value="czkm" onclick="$(\'#show_kmcz\').show();$(\'#show_zxzf\').hide();">
            <span class="mdl-radio__label">余额卡密</span>
        </label>
    </div>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" id="show_kmcz" style="width: 100%">
        <label for="kt_key" class="mdl-textfield__label">请输入卡密</label>
        <input class="mdl-textfield__input" id="kt_key" type="text">
    </div>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" id="show_zxzf" style="width: 100%">
        请选择在线支付方式：
        <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="radio6" >
            <input type="radio" id="radio6" class="mdl-radio__button" name="zxzffs" value="zfb" checked>
            <span class="mdl-radio__label">支付宝</span>
        </label>
        <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="radio7" >
            <input type="radio" id="radio7" class="mdl-radio__button" name="zxzffs" value="wx">
            <span class="mdl-radio__label">微信支付</span>
        </label>
        <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="radio8" >
            <input type="radio" id="radio8" class="mdl-radio__button" name="zxzffs" value="qq">
            <span class="mdl-radio__label">QQ钱包</span>
        </label>
    </div>
             <div id="vaptchaContainer" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        </div><br><br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
            <button type="button" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent" onclick="buy_submit(' . $_GET['fid'] . ',' . $type . ');" style="width: 100%">立即开通</button>
    </div>

</div>
<script src="./static/jquery-3.3.1.js"></script>
<script type="text/javascript" src="./static/frame/layui/layui.js"></script>
<script>layui.use([\'layer\'],function () {var layer = layui.layer;        $.ajax({
            url: \'./function/GetVerification.php\',
            type: \'GET\',
            dataType: \'html\',
            success: function(data){
                $(\'#vaptchaContainer\').html(data);
            },
            error: function(data){
               
                layer.msg(\'[\'+data.status+\']\'+data.statusText);
            }
        });});
function buy_submit(id,type) {
    var loading = layer.load();
    if (type === 1){
        //账号密码登陆
        var kt_user= $("#kt_user").val();
        var kt_pass= $("#kt_pass").val();
        var kt_varinfo = \'&kt_user=\'+encodeURI(kt_user)+\'&kt_pass=\'+kt_pass;
    }else if (type === 2){
        //卡密授权
        var kt_kami= $("#kt_user").val();
        var kt_varinfo = \'&kt_user=\'+encodeURI(kt_kami);
        var kt_kaminum= $("#kt_keysnum").val();
    }else if (type === 3){
        //检查绑定
        var kt_robotqq= $("#kt_robotqq").val();
        var kt_mac= $("#kt_mac").val();
        var kt_ip= $("#kt_ip").val();
        var kt_varinfo = \'&kt_robotqq=\'+kt_robotqq+\'&kt_mac=\'+kt_mac+\'&kt_ip=\'+encodeURI(kt_ip);
    }
    var kt_num= $("#kt_num").val();
    var kt_adminqq= $("#kt_adminqq").val();
    var kt_mail= $("#kt_mail").val();
    var zffs = $(\'input:radio[name="zffs"]:checked\').val();
    var zxzffs = $(\'input:radio[name="zxzffs"]:checked\').val();
    var kt_key= $("#kt_key").val();
        var token =\'\';
        if (typeof vaptchaObj != \'undefined\'){
            token = vaptchaObj.getToken();
            vaptchaObj.reset();
        }

    $.ajax({
        url: \'ajax.php?mod=buy_submit\',
        type: \'POST\',
        dataType: \'json\',
        data: \'id=\'+id+kt_varinfo+\'&kt_num=\'+kt_num+\'&kt_mail=\'+kt_mail+\'&zffs=\'+zffs+\'&zxzffs=\'+zxzffs+\'&kt_key=\'+kt_key+\'&type=\'+type+\'&kt_adminqq=\'+kt_adminqq+\'&token=\'+token,
        success: function(data){
            layer.close(loading);
            if(data.code === \'1\'){
                layer.confirm(data.info, {
                    btn: [\'立即付款\',\'关闭\'] //按钮
                }, function(){
                    window.open(\'payment.php?tradeno=\'+data.tradeno);
                    layer.closeAll();
                    layer.confirm(\'请在新打开的窗口中进行付款！\', {
                        btn: [\'已付款\',\'关闭\'] //按钮
                    }, function(){
                        window.open(\'payresult.php?tradeno=\'+data.tradeno);
                    });
                });


            }else{
                layer.msg(data.msg);
            }
        },
        error: function(data){
            layer.close(loading);
            layer.msg(\'请求失败\'+data);
        }
    })
}
function dofill() {
        var value= $(\'#kt_adminqq\').val()+\'@qq.com\';
        //alert(value);
  $(\'#kt_mail\').val(value)
}
</script>
<script src="./mdl/material.min.js"></script>
<!-- /.container -->

<script>$(\'#show_kmcz\').hide();$(\'#show_zxzf\').show();</script>
</body>

</html>
';
        die($output);
        break;
    case 'buy_agent':
        include './function/VerificationCode.class.php';
        $verification = Verification::check($_POST['token']);
        if ($verification !== true) {
            die(json_encode(array('code' => '-88', 'msg' => '请先进行人机验证')));
        }
        if (empty($_POST['lid'])) {
            die(json_encode(array('code' => -2, 'msg' => '请选择一个级别')));
        }
        if (empty($_POST['username'])) {
            die(json_encode(array('code' => -3, 'msg' => '请输入用户名,用户名和密码是您登陆代理后台的唯一凭证！')));
        }
        if (empty($_POST['password'])) {
            die(json_encode(array('code' => -4, 'msg' => '请输入您的密码,用户名和密码是您登陆代理后台的唯一凭证！')));
        }
        if (empty($_POST['qq'])) {
            die(json_encode(array('code' => -5, 'msg' => '请输入您的QQ')));
        }
        if (!$levelinfo = $db->select_first_row('sq_level', 'lname,price', array('ID' => $_POST['lid']), 'AND')) {
            die(json_encode(array('code' => -5, 'msg' => '无法找到该代理等级')));
        }
        if ($levelinfo['price'] <= 0) {
            die(json_encode(array('code' => -6, 'msg' => '该代理等级不允许在线开通')));
        }
        if ($db->select_first_row('sq_agent', 'ID', array('username' => $_POST['username']), '') != false) {
            die(json_encode(array('code' => -6, 'msg' => '用户名重复，请尝试其他用户名')));
        }
        $time = time();
        $tradno = $time . rand_str(10, 'num');
        $money = $levelinfo['price'];
        $tips = '您正在开通新代理，请确认以下信息：<br>';
        $tips .= '交易订单号：' . $tradno . '<br>';
        $tips .= '正在开通等级：' . $levelinfo['lname'] . '<br>';
        $tips .= '代理用户名：' . $_POST['username'] . '<br>';
        $tips .= '代理代理密码：' . $_POST['password'] . '<br>';
        $tips .= '代理QQ：' . $_POST['qq'] . '<br>';
        $tips .= '应付金额：' . $levelinfo['price'] . '<br>';
        if ($_POST['zffs'] == 'zxzf') {
            $tips .= '付款方式：在线支付<br>';
            switch ($_POST['zxzffs']) {
                case 'zfb':
                    $tips .= '支付方式：支付宝<br>';
                    break;
                case 'wx':
                    $tips .= '支付方式：微信<br>';
                    break;
                case 'qq':
                    $tips .= '支付方式：QQ钱包<br>';
                    break;
                default:
                    die(json_encode(array('code' => -7, 'msg' => '错误的付款方式')));
            }
        } else if ($_POST['zffs'] == 'czkm') {
            $tips .= '付款方式：余额卡密<br>';
            if (empty($_POST['kt_key'])) {
                die(json_encode(array('code' => -8, 'msg' => '余额卡密不能为空')));
            }
            $tips .= '正在使用的卡密：' . $_POST['kt_key'] . '<br>';
        } else {
            die(json_encode(array('code' => -5, 'msg' => '错误的支付方式')));
        }
        $newtrade = array('name' => $levelinfo['lname'],
            'tradeno' => $tradno,
            'begintime' => $time,
            'user' => $_POST['username'],
            'pass' => $_POST['password'],
            'fid' => $_POST['lid'],
            'num' => 1,
            'paymoney' => $money,
            'mail' => $_POST['qq'] . '@qq.com',
            'paytype' => $_POST['zffs'],
            'onlinepaytype' => $_POST['zxzffs'],
            'kami' => $_POST['kt_key'],
            'uqq' => $_POST['qq'],
            'ip' => get_real_ip(),
            'status' => '1',
            'type' => 4
        );
        $db->insert_back_id('sq_trade', $newtrade);
        die(json_encode(array('code' => '1', 'tradeno' => $tradno, 'info' => $tips)));
        break;

    case 'buy_submit':
        include './function/VerificationCode.class.php';
        $verification = Verification::check($_POST['token']);
        if ($verification !== true) {
            die(json_encode(array('code' => '-88', 'msg' => '请先进行人机验证')));
        }
        include 'function/function_app.php';

        if (empty($_POST['kt_key'])) {
            $_POST['kt_key'] = '无';
        }
        if (!$fidinfo = $db->select_first_row('sq_fidlist', '*', array('ID' => $_POST['id']), 'AND')) {
            die(json_encode(array('code' => -2, 'msg' => '无法找到该商品，请刷新页面重试')));
        }
        if (!$appinfo = app_idgetinfo($fidinfo['appid'])) {
            die(json_encode(array('code' => -2, 'msg' => '无法找到应用，也许是该应用已下架')));
        }
        if ($fidinfo['num'] == -1 && $_POST['kt_num'] != 1) {
            die(json_encode(array('code' => '-3', 'msg' => '此套餐包含的授权时长为永久，请固定开通数量为1')));
        }
        if ($_POST['kt_num'] <= 0 || $_POST['kt_num'] >= 10) {
            die(json_encode(array('code' => '-4', 'msg' => '开通数量填写错误，开通数量必须为1-10之间')));
        }
        $time = time();
        $tradno = $time . rand_str(10, 'num');
        $tips = '交易订单号：' . $tradno . '<br>';
        $spendmoney = $fidinfo['buyprice'] * $_POST['kt_num'];
        $tips .= '应付金额：' . $spendmoney . '<br>';
        if ($spendmoney <= 0) {
            die(json_encode(array('code' => -7, 'msg' => '错误的支付金额：' . $spendmoney)));
        }

        if ($_POST['type'] == '1') {
            if (empty($_POST['kt_user'])) {
                die(json_encode(array('code' => -13, 'msg' => '用户名不能为空！')));
            }
            if (empty($_POST['kt_pass'])) {
                die(json_encode(array('code' => -14, 'msg' => '密码不能为空！')));
            }
            if (!$userinfo = $db->select_first_row('sq_user', '*', array('username' => $_POST['kt_user'], 'appid' => $fidinfo['appid']), 'AND')) {

            } else {
                if ($_POST['kt_pass'] !== $userinfo['password']) {
                    die(json_encode(array('code' => -2, 'msg' => '您的用户名已开通，您需要输入的正确密码才能进行续费，当前输入的用户密码错误！')));
                }
            }
            $tips .= '开通用户名：' . $_POST['kt_user'] . '<br>';
            $newtrade = array('name' => $fidinfo['fidname'],
                'tradeno' => $tradno,
                'begintime' => $time,
                'user' => $_POST['kt_user'],
                'pass' => $_POST['kt_pass'],
                'fid' => $_POST['id'],
                'num' => $_POST['kt_num'],
                'paymoney' => $spendmoney,
                'mail' => $_POST['kt_mail'],
                'paytype' => $_POST['zffs'],
                'onlinepaytype' => $_POST['zxzffs'],
                'kami' => $_POST['kt_key'],
                'uqq' => $_POST['kt_adminqq'],
                'ip' => get_real_ip(),
                'status' => '1',
                'type' => 3
            );
        } else if ($_POST['type'] == '2') {
            //卡密方式
            if (!$userinfo = $db->select_first_row('sq_user', '*', array('username' => $_POST['kt_user'], 'appid' => $fidinfo['appid']), 'AND')) {
                $userkey = $userinfo['username'];
                $tips .= '开通方式：新开卡密<br>';
            } else {
                $userkey = '';
                $tips .= '开通方式：续费卡密(' . $_POST['kt_user'] . ')<br>';
            }
            $newtrade = array(
                'user'=>$_POST['kt_user'],
                'name' => $fidinfo['fidname'],
                'tradeno' => $tradno,
                'begintime' => $time,
                'fid' => $_POST['id'],
                'num' => $_POST['kt_num'],
                'paymoney' => $spendmoney,
                'mail' => $_POST['kt_mail'],
                'paytype' => $_POST['zffs'],
                'onlinepaytype' => $_POST['zxzffs'],
                'uqq' => $_POST['kt_adminqq'],
                'kami' => $_POST['kt_key'],
                'ip' => get_real_ip(),
                'status' => '1',
                'type' => 3
            );

        } else {
            //检查绑定

            $tips_ = '';
            if ($appinfo['bindip'] == '1') {
                $check['lip'] = $_POST['kt_ip'];
                if (empty($_POST['kt_ip']) || $_POST['kt_ip'] == 'undefined') {
                    die(json_encode(array('code' => -10, 'msg' => '绑定的IP不能为空')));
                }
                $tips_ .= '绑定IP：' . $check['lip'] . '<br>';
            }
            if ($appinfo['bindmac'] == '1') {
                $check['mac'] = $_POST['kt_mac'];
                if (empty($_POST['kt_mac']) || $_POST['kt_mac'] == 'undefined') {
                    die(json_encode(array('code' => -10, 'msg' => '绑定的机器不能为空')));
                }
                $tips_ .= '绑定机器：' . $check['mac'] . '<br>';

            }
            if ($appinfo['bindqq'] == '1') {
                $check['rqq'] = $_POST['kt_robotqq'];
                if (empty($_POST['kt_robotqq']) || $_POST['kt_robotqq'] == 'undefined') {
                    die(json_encode(array('code' => -10, 'msg' => '绑定的机器人QQ不能为空')));
                }
                $tips_ .= '绑定QQ：' . $check['rqq'] . '<br>';
            }
            //$check['appid'] = $appinfo['ID'];
            if (empty($check)) {
                die(json_encode(array('code' => -10, 'msg' => '系统内部错误(应用绑定信息配置异常)，请联系管理员解决')));
            }
            $newtrade = array('name' => $fidinfo['fidname'],
                'tradeno' => $tradno,
                'begintime' => $time,
                'user' => json_encode($check),
                'fid' => $_POST['id'],
                'num' => $_POST['kt_num'],
                'paymoney' => $spendmoney,
                'mail' => $_POST['kt_mail'],
                'paytype' => $_POST['zffs'],
                'onlinepaytype' => $_POST['zxzffs'],
                'kami' => $_POST['kt_key'],
                'ip' => get_real_ip(),
                'status' => '1',
                'uqq' => $_POST['kt_adminqq'],
                'type' => 3
            );

            if (!$userinfo = $db->select_first_row('sq_user', '*', $check, 'AND')) {
                $tips .= '开通方式：新开授权<br>绑定信息如下：<br>' . $tips_ . '<br><br>';
            } else {
                $tips .= '开通方式：续费授权<br>绑定信息如下：<br>' . $tips_ . '<br><br>';
            }
        }
        if (empty($userinfo['balance'])) {
            $userinfo['balance'] = 0;
        }
        $tips .= '正在开通套餐：' . $fidinfo['fidname'] . '<br>';
        if ($appinfo['usetype'] === 'dqsj') {
            if (empty($userinfo['balance']) || $userinfo['balance'] == 0) {
                $tips .= '您目前到期时间：无<br>';
                $userinfo['balance'] = time();
            } else {
                $tips .= '您目前到期时间：' . Get_Date($userinfo['balance']) . '<br>';
            }
            $tips .= '新增时长：' . time_last($fidinfo['num'] * $_POST['kt_num'] * 60) . '<br>';
        } else {
            $tips .= '用户剩余点数：' . $userinfo['balance'] . '点<br>';
            $tips .= '购买后点数：' . ($userinfo['balance'] + $fidinfo['num'] * $_POST['kt_num']) . '点<br>';
        }

        if ($_POST['zffs'] == 'zxzf') {
            $tips .= '付款方式：在线支付<br>';
            switch ($_POST['zxzffs']) {
                case 'zfb':
                    $tips .= '支付方式：支付宝<br>';
                    break;
                case 'wx':
                    $tips .= '支付方式：微信<br>';
                    break;
                case 'qq':
                    $tips .= '支付方式：QQ钱包<br>';
                    break;
                default:
                    die(json_encode(array('code' => -5, 'msg' => '错误的付款方式')));
            }
        } else if ($_POST['zffs'] == 'czkm') {
            if ($fidinfo['allrechargecard'] != 1) {
                die(json_encode(array('code' => -5, 'msg' => '本套餐不支持使用卡密支付')));
            }
            $tips .= '付款方式：余额卡密<br>';
            $tips .= '正在使用的卡密：' . $_POST['kt_key'] . '<br>';
        } else {
            die(json_encode(array('code' => -5, 'msg' => '错误的支付方式')));
        }
        $tips .= '注意：请认真核对信息，付款后一律不支持退款！<br>';

        if (!$db->insert_back_id('sq_trade', $newtrade)) {
            die(json_encode(array('code' => -3, 'msg' => '系统错误：' . $db->geterror())));
        } else {
            die(json_encode(array('code' => '1', 'tradeno' => $tradno, 'info' => $tips)));
        }
        break;
    case 'introduce':
        if (!$appinfo = $db->select_first_row('sq_apps', 'introduce', array('ID' => $_GET['appid']), 'AND')) {
            die('服务器内部错误');
        } else {
            if (empty($appinfo['introduce'])){
                die('管理员没有写任何详情 :(');
            }
            die($appinfo['introduce']);
        }
        break;
    case 'queryauth':
        include './function/VerificationCode.class.php';
        $verification = Verification::check($_POST['token']);
        if ($verification !== true) {
            die(json_encode(array('code' => '-88', 'msg' => '请先进行人机验证')));
        }
        if($result = $db->select_first_row('sq_bclist','*',array('obj'=>$_POST['qq']),'AND')){
            if ($result['appid'] != 0){
                include_once 'function/function_app.php';
                $app = app_idgetname($result['appid']);
            }else{
                $app = '所有应用';
            }
            die('查询对象属于云黑名单中！<br>原因：'.$result['reason'].'<br>拉黑应用：'.$app);
        }

        $_POST['qq'] = intval($_POST['qq']);
        if (empty($_POST['qq'])) {
            die('查询的QQ号码不能为空');
        }
        $info = $db->select_all_row('sq_agent', 'begintime,levelid,qq', array('qq' => $_POST['qq'], 'status' => 1), 'AND');
        include 'function/function_app.php';
        if (!is_array($info) || count($info) == 0) {
            echo('QQ' . $_POST['qq'] . '没有查询到任何代理信息' . '<br>');
        } else {
            foreach ($info as $qqinfo) {
                echo '代理QQ：' . $qqinfo['qq'] . '<br>';
                echo '开通时间：' . Get_Date($qqinfo['begintime']) . '<br>';

                $levelinfo = $db->select_first_row('sq_level', '*', array('ID' => $qqinfo['levelid']), 'AND');
                echo '代理级别：' . $levelinfo['lname'] . '<br>';

                $array = explode(',', $levelinfo['appid']);
                $appname = '';
                foreach ($array as $appid) {
                    if (!empty($appname)) {
                        $appname .= '，' . app_idgetname($appid);
                    } else {
                        $appname = app_idgetname($appid);
                    }
                }
                echo '所属应用：' . $appname;
                echo '<hr>';
            }
        }

        $info = $db->select_all_row('sq_user', 'uqq,rqq,rtime,balance,appid,aid', array('uqq' => $_POST['qq'], 'rqq' => $_POST['qq']), 'OR');
        if (!is_array($info) || count($info) == 0) {
            die('QQ' . $_POST['qq'] . '没有查询到任何授权信息');
        }

        foreach ($info as $qqinfo) {
            echo '用户QQ：' . $qqinfo['uqq'] . '<br>';
            echo '机器人QQ：' . $qqinfo['rqq'] . '<br>';
            echo '开通时间：' . Get_Date($qqinfo['rtime']) . '<br>';
            $appinfo = app_idgetinfo($qqinfo['appid']);
            echo '所属应用：' . $appinfo['appname'] . '<br>';
            if ($appinfo['usetype'] == 'dqsj') {
                if ($qqinfo['balance'] == '-1') {
                    echo '到期时间：永久授权<br>';
                } else {
                    echo '到期时间：' . Get_Date($qqinfo['balance']) . '<br>';
                }
            } else if ($appinfo['usetype'] == 'kcye') {
                echo '剩余余额：' . $qqinfo['balance'] . '<br>';
            }
            if ($qqinfo['aid'] != 0) {
                $agentinfo = $db->select_first_row('sq_agent', 'username', array('ID' => $qqinfo['aid']), 'AND');
                echo '开通代理：' . $agentinfo['username'] . '<br>';
            }
            echo '<hr>';
        }
        die();
    case 'levellist':
        $info = $db->select_all_row('sq_level', 'ID,lname', 'price<>0', '', '');
        $i = '';
        foreach ($info as $value) {
            $i .= '<option value="' . $value['ID'] . '">' . $value['lname'] . '</option>';
        }
        die($i);
        break;
    case 'FieldKamiOpen_First':
        include './function/VerificationCode.class.php';
        $verification = Verification::check($_GET['token']);
        if ($verification !== true) {
            die(json_encode(array('code' => '-88', 'msg' => '请先进行人机验证')));
        }
        if (!$info = $db->select_first_row('sq_fidkey','*',array('kami'=>$_GET['kami']),'AND')) die('卡密不存在');
        if ($info['status'] != 1) die('卡密已被冻结');
        if ( !empty($info['user']) || !empty($info['usetime'])) die('卡密已被使用');
        if (!$fidinfo = $db->select_first_row('sq_fidlist','fidname,appid',array('ID'=>$info['fid']),'AND'))die('无法找到对应套餐，请联系管理员处理');
        include "function/function_app.php";
        $appinfo = app_idgetinfo($fidinfo['appid']);
        $output = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Material Design Lite">
    <title>使用卡密'.$_GET['kami'].' - '.$G['config']['sitename'].'</title>
    <link rel="stylesheet" href="./mdl/material.min.css">
    <script src="https://v.vaptcha.com/v3.js"></script>
</head>
<body class="flat-blue landing-page">';
        $output .= '<div id="submit_div" style="padding: 20px;">';
        $output .= GetBuyFormHtml($appinfo,$type);
        $output .= '
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_mail" class="mdl-textfield__label">请输入您的邮箱</label>
  
            <input class="mdl-textfield__input" id="kt_mail" type="text">
    </div>
     <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_mail" class="mdl-textfield__label">请输入主人QQ，我们将通过此QQ联系您</label>
            <input class="mdl-textfield__input" id="kt_adminqq" type="text">
    </div>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" id="show_kmcz" style="width: 100%" hidden>
        <label for="kt_key" class="mdl-textfield__label">请输入卡密</label>
        <input class="mdl-textfield__input" id="kt_key" type="text" value="'.$_GET['kami'].'">
    </div>
    <div id="vaptchaContainer" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label"
         style="width: 95%">
    </div>
    <br><br>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
            <button type="button" class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent" onclick="buy_submit(' . $info['fid'] . ',' . $type . ');" style="width: 100%">立即开通</button>
    </div>

</div>
<script src="./static/jquery-3.3.1.js"></script>
<script type="text/javascript" src="./static/frame/layui/layui.js"></script>
<script>
layui.use([\'layer\'],function () {
var layer = layui.layer;
        $.ajax({
            url: \'./function/GetVerification.php\',
            type: \'GET\',
            dataType: \'html\',
            success: function(data){
                $(\'#vaptchaContainer\').html(data);
            },
            error: function(data){

                layer.msg(\'[\'+data.status+\']\'+data.statusText);
            }
        });
});
function buy_submit(id,type) {
    var loading = layer.load();
    if (type === 1){
        //账号密码登陆
        var kt_user= $("#kt_user").val();
        var kt_pass= $("#kt_pass").val();
        var kt_varinfo = \'&kt_user=\'+encodeURI(kt_user)+\'&kt_pass=\'+kt_pass;
    }else if (type === 2){
        //卡密授权
        var kt_kami= $("#kt_user").val();
        var kt_varinfo = \'&kt_user=\'+encodeURI(kt_kami);
        var kt_kaminum= $("#kt_keysnum").val();
    }else if (type === 3){
        //检查绑定
        var kt_robotqq= $("#kt_robotqq").val();
        var kt_mac= $("#kt_mac").val();
        var kt_ip= $("#kt_ip").val();
        var kt_varinfo = \'&kt_robotqq=\'+kt_robotqq+\'&kt_mac=\'+kt_mac+\'&kt_ip=\'+encodeURI(kt_ip);
    }

    var kt_adminqq= $("#kt_adminqq").val();
    var kt_mail= $("#kt_mail").val();
    var kt_key= $("#kt_key").val();
            var token = \'\';
            if (typeof vaptchaObj != \'undefined\'){
                token = vaptchaObj.getToken();
                vaptchaObj.reset();
            }
    $.ajax({
        url: \'ajax.php?mod=FieldKeyBuy_submit\',
        type: \'POST\',
        dataType: \'json\',
        data: \'id=\'+id+kt_varinfo+\'&kt_mail=\'+kt_mail+\'&kt_key=\'+kt_key+\'&type=\'+type+\'&kt_adminqq=\'+kt_adminqq+\'&token=\'+token,
        success: function(data){
            layer.close(loading);
            layer.alert(data.msg);
        },
        error: function(data){
            layer.close(loading);
            layer.msg(\'请求失败\'+data);
        }
    })
}
</script>
<script src="./mdl/material.min.js"></script>
<!-- /.container -->

<script>$(\'#show_kmcz\').hide();$(\'#show_zxzf\').show();</script>
</body>

</html>
';
        die($output);
        break;
    case 'FieldKeyBuy_submit':
		error_reporting(E_ALL);
        include './function/VerificationCode.class.php';
        $verification = Verification::check($_POST['token']);
        if ($verification !== true) {
            die(json_encode(array('code' => '-88', 'msg' => '请先进行人机验证')));
        }
        if (!$info = $db->select_first_row('sq_fidkey','*',array('kami'=>$_POST['kt_key']),'AND')) die(json_encode(array('code' => '-1', 'msg' => '卡密不存在')));;
        if ($info['status'] != 1) die(json_encode(array('code' => '-101', 'msg' => '卡密已被冻结')));
        if ( !empty($info['user']) || !empty($info['usetime'])) die(json_encode(array('code' => '-102', 'msg' => '卡密已被使用')));
        if (!$fidinfo = $db->select_first_row('sq_fidlist','*',array('ID'=>$info['fid']),'AND'))die(json_encode(array('code' => '-201', 'msg' => '套餐无法找到，请联系管理员')));;
        $tips='';
		include 'function/function_app.php';
        if (!$appinfo = app_idgetinfo($fidinfo['appid'])) {
            die(json_encode(array('code' => -2, 'msg' => '无法找到应用，也许是该应用已下架')));
        }
		$spendmoney = 0;
        $time = time();
        if ($_POST['type'] == '1') {
            if (empty($_POST['kt_user'])) {
                die(json_encode(array('code' => -13, 'msg' => '用户名不能为空！')));
            }
            if (empty($_POST['kt_pass'])) {
                die(json_encode(array('code' => -14, 'msg' => '密码不能为空！')));
            }
            if (!$userinfo = $db->select_first_row('sq_user', '*', array('username' => $_POST['kt_user'], 'appid' => $fidinfo['appid']), 'AND')) {

            } else {
                if ($_POST['kt_pass'] !== $userinfo['password']) {
                    die(json_encode(array('code' => -2, 'msg' => '用户密码错误！')));
                }
            }
            $tips .= '开通用户名：' . $_POST['kt_user'] . '<br>';
            $newtrade = array('name' => $fidinfo['fidname'],
                'begintime' => $time,
                'user' => $_POST['kt_user'],
                'pass' => $_POST['kt_pass'],
                'fid' => $_POST['id'],
                'mail' => $_POST['kt_mail'],
                'kami' => $_POST['kt_key'],
                'uqq' => $_POST['kt_adminqq'],
                'ip' => get_real_ip(),
                'status' => '1',
                'type' => 3,

            );
        } else if ($_POST['type'] == '2') {
            //卡密方式
            if (!$userinfo = $db->select_first_row('sq_user', '*', array('username' => $_POST['kt_user'], 'appid' => $fidinfo['appid']), 'AND')) {
                $userkey = $userinfo['username'];
                $tips .= '开通方式：新开卡密<br>';
            } else {
                $userkey = '';
                $tips .= '开通方式：续费卡密(' . $_POST['kt_user'] . ')<br>';
            }
            $newtrade = array('name' => $fidinfo['fidname'],
                'tradeno' => $tradno,
                'begintime' => $time,
                'user' => $_POST['kt_user'],
                'fid' => $_POST['id'],
                'paymoney' => $spendmoney,
                'mail' => $_POST['kt_mail'],
                'paytype' => 'tck',
                'onlinepaytype' => '',
                'uqq' => $_POST['kt_adminqq'],
                'kami' => $_POST['kt_key'],
                'ip' => get_real_ip(),
                'status' => '1',
                'type' => 3,
				'pass'=>''
            );

        } else {
            //检查绑定

            $tips_ = '';
            if ($appinfo['bindip'] == '1') {
                $check['lip'] = $_POST['kt_ip'];
                if (empty($_POST['kt_ip']) || $_POST['kt_ip'] == 'undefined') {
                    die(json_encode(array('code' => -10, 'msg' => '绑定的IP不能为空')));
                }
                $tips_ .= '绑定IP：' . $check['lip'] . '<br>';
            }
            if ($appinfo['bindmac'] == '1') {
                $check['mac'] = $_POST['kt_mac'];
                if (empty($_POST['kt_mac']) || $_POST['kt_mac'] == 'undefined') {
                    die(json_encode(array('code' => -10, 'msg' => '绑定的机器不能为空')));
                }
                $tips_ .= '绑定机器：' . $check['mac'] . '<br>';

            }
            if ($appinfo['bindqq'] == '1') {
                $check['rqq'] = $_POST['kt_robotqq'];
                if (empty($_POST['kt_robotqq']) || $_POST['kt_robotqq'] == 'undefined') {
                    die(json_encode(array('code' => -10, 'msg' => '绑定的机器人QQ不能为空')));
                }
                $tips_ .= '绑定QQ：' . $check['rqq'] . '<br>';
            }
            //$check['appid'] = $appinfo['ID'];
            if (empty($check)) {
                die(json_encode(array('code' => -10, 'msg' => '系统内部错误(应用绑定信息配置异常)，请联系管理员解决')));
            }
			$tradno = $time . rand_str(10, 'num');
            $newtrade = array('name' => $fidinfo['fidname'],
                'tradeno' => $tradno,
                'begintime' => $time,
                'user' => json_encode($check),
                'fid' => $_POST['id'],
                'paymoney' => $spendmoney,
                'mail' => $_POST['kt_mail'],
                'paytype' => 'tck',
                'onlinepaytype' => '',
                'kami' => $_POST['kt_key'],
                'ip' => get_real_ip(),
                'status' => '1',
                'uqq' => $_POST['kt_adminqq'],
                'type' => 3,
				'pass'=>''
            );

            if (!$userinfo = $db->select_first_row('sq_user', '*', $check, 'AND')) {
                $tips .= '开通方式：新开授权<br>绑定信息如下：<br>' . $tips_ . '<br><br>';
            } else {
                $tips .= '开通方式：续费授权<br>绑定信息如下：<br>' . $tips_ . '<br><br>';
            }
        }
		//$newtrade['ID'] = $db->insert_back_id('sq_trade', $newtrade);
		
        include_once './function/function_auth.php';
        $back = auth_add($newtrade['user'],$newtrade['pass'],$newtrade['ip'], $fidinfo['num'],$newtrade['uqq'],$newtrade['mail'],$fidinfo['appid'],3,$info['ID'],$info['aid'],$newkey,$tips,$newtrade);
        $db->update('sq_fidkey',array('ID'=>$info['ID']),'AND',array('usetime'=>time(),'user'=>$newtrade['user']));
        if ($back == 1){
            die(makejson(1,$tips.'授权新开成功！'));
        }else if ($back==2){
            die(makejson(1,$tips.'授权续费成功！'));
        }else if ($back==3){
            die(makejson(2,'授权登录卡密生成成功，请使用此卡密登录授权：'.$newkey));
        }else{
            die(makejson($back,$tips));
        }

        break;
    case 'changegetform':
        include './function/function_app.php';
        if(!$appinfo = app_idgetinfo(intval($_POST['appid']))){
            die('');
        }
        $output = '';
        if ($appinfo['logintype'] == 'zhmm') {
            $output .= '

    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_user" class="mdl-textfield__label">请输入您的账号</label>
            <input class="mdl-textfield__input" id="kt_user" type="text">
       
    </div>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_pass" class="mdl-textfield__label">请输入您的密码</label>
        
            <input class="mdl-textfield__input" id="kt_pass" type="password">
       
    </div>
    ';
            $type = 1;
        } else if ($appinfo['logintype'] == 'kmsq') {
            $output .= '
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_user" class="mdl-textfield__label">请输入授权登录卡密</label>
            <input class="mdl-textfield__input" id="kt_user" type="text">
    </div>';
            $type = 2;
        }else if ($appinfo['logintype'] == 'jcbd') {
            if ($appinfo['bindqq'] == '1') {
                $output .= '
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_robotqq" class="mdl-textfield__label">原机器人QQ</label>
       
            <input class="mdl-textfield__input" id="kt_robotqq" type="text">
 
    </div>
    ';
            }
            if ($appinfo['bindmac'] == '1') {
                $output .= '<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_mac" class="mdl-textfield__label">原设备码</label>
        
            <input class="mdl-textfield__input" id="kt_mac" type="text">
       
    </div>
    ';
            }
            if ($appinfo['bindip'] == '1') {
                $output .= '<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_ip" class="mdl-textfield__label">原IP地址</label>
        
            <input class="mdl-textfield__input" id="kt_ip" type="text">

    </div>
    ';
            }
            $type = 3;
        } else {
            die('未知授权方式：' . $appinfo['logintype']);
        }

        $check = false;
        if ($appinfo['bindqq'] == '1') {
            $check = true;
            $output .= '
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="cg_robotqq" class="mdl-textfield__label">新机器人QQ</label>
       
            <input class="mdl-textfield__input" id="cg_robotqq" type="text">
 
    </div>
    ';
        }
        if ($appinfo['bindmac'] == '1') {
            $check = true;
            $output .= '<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="cg_mac" class="mdl-textfield__label">新设备码</label>
        
            <input class="mdl-textfield__input" id="cg_mac" type="text">
       
    </div>
    ';
        }
        if ($appinfo['bindip'] == '1') {
            $check = true;
            $output .= '<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="cg_ip" class="mdl-textfield__label">新IP地址</label>
        
            <input class="mdl-textfield__input" id="cg_ip" type="text">

    </div>
    ';
        }
        if(!$check){
            die('<script>layer.alert("当前应用未绑定任何数据")</script>');
        }
        die($output);
        break;
    case 'ChangeGetVerCode':
        include './function/VerificationCode.class.php';
        $verification = Verification::check($_POST['token']);
        if ($verification !== true) {
            die(json_encode(array('code' => '-88', 'msg' => '请先进行人机验证')));
        }
        include './function/function_app.php';
        if(!$appinfo = app_idgetinfo(intval($_POST['applist']))){
            die(makejson(-1,'请先选择一个应用'));
        }
        $appid = $_POST['applist'];
        if ($appinfo['logintype'] === 'zhmm'){
            if (empty($_POST['kt_user']) || empty($_POST['kt_pass'])){
                die(makejson(-2,'请输入您的登录信息'));
            }
            if (!$userinfo = $db->select_first_row('sq_user','ID,sendtime,mail',array('username'=>$_POST['kt_user'],'password'=>$_POST['kt_pass'],'appid'=>$appid),'AND')){
                die(makejson(-3,'未查询到您的授权信息！<br>请检查您输入的账号密码是否正确！'));
            }
        }else if ($appinfo['logintype'] === 'kmsq'){
            if (empty($_POST['kt_user'])){
                die(makejson(-2,'请输入您登录使用的卡密'));
            }
            if (!$userinfo = $db->select_first_row('sq_user','ID,sendtime,mail',array('username'=>$_POST['kt_user'],'appid'=>$appid),'AND')){
                die(makejson(-3,'未查询到您的授权信息！<br>请检查您输入的卡密是否正确！'));
            }
        }else if ($appinfo['logintype'] === 'jcbd'){
            $where['appid'] = $appid;
            $check = false;
            if ($appinfo['bindip'] == '1') {
                if (empty($_POST['kt_ip'])){
                    die(makejson(-21,'请输入您当前已授权的IP'));
                }
                $where['lip'] = $_POST['kt_ip'];
                $check = true;
            }
            if ($appinfo['bindmac'] == '1') {
                if (empty($_POST['kt_mac'])){
                    die(makejson(-22,'请输入您当前已授权的设备'));
                }
                $where['mac'] = $_POST['kt_mac'];
                $check = true;
            }
            if ($appinfo['bindqq'] == '1') {
                if (empty($_POST['kt_robotqq'])){
                    die(makejson(-23,'请输入您当前已授权的机器人QQ'));
                }
                $where['rqq'] = $_POST['kt_robotqq'];
                $check = true;
            }
            if (!$userinfo = $db->select_first_row('sq_user','ID,sendtime,mail',$where,'AND')){
                die(makejson(-7,'未查询到您的授权信息'));
            }
        }
        $_SESSION['change_uid'] = $userinfo['ID'];
        if ($_POST['cg_mail'] !== $userinfo['mail']){
            die(makejson(-5,'输入的邮箱与授权开通时候的邮箱不符，请重新输入'));
        }
        if (time() - (int)$userinfo['sendtime'] < 120){
            die(makejson(-4,'邮件发送频率过快，请等待 ' .( 120 - (time() - (int)$userinfo['sendtime'])).'秒后再发送'));
        }

        $newvercode = rand_str(6);

        $db->update('sq_user',array('ID'=>$userinfo['ID'],'appid'=>$_POST['applist']),'AND',array('mailcode'=>$newvercode,'sendtime'=>time()));
        $T['title'] = '换绑操作验证码';
        $T['content'][] = '验证码：'.$newvercode;
        $T['content'][] = '发送时间：'.Get_Date(time());
        $T['content'][] = '到期时间：'.Get_Date(time()+300);
        $T['content'][] = '注意：若您没有执行换绑操作请忽略此邮件并严格保密邮件内容！';
        $MailTips[] = $T;

        if(SendTipsMail($userinfo['mail'],$MailTips,$backinfo)){
            die(makejson(1,'邮件发送成功'));
        }else{
            $db->update('sq_user',array('ID'=>$userinfo['ID']),'AND',array('mailcode'=>'','sendtime'=>0));
            die(makejson('-4',$backinfo));
        }
        break;
    case 'postchange':
        if (empty($_SESSION['change_uid'])){
            die('缓存中未找到验证码请求，请先获取验证码');
        }
        if (!$userinfo = $db->select_first_row('sq_user','sendtime,mailcode',array('ID'=>$_SESSION['change_uid'],'appid'=>$_POST['applist']),'AND')){
            die('缓存中的用户不存在，请尝试重新请求验证码');
        }
        if ($userinfo['sendtime'] + 300 < time()){
            die('验证码已过期，请尝试重新获取');
        }
        if ($userinfo['mailcode'] !== $_POST['cg_vercode']){
            die('验证码错误，请重新输入');
        }

        include_once './function/function_app.php';
        if(!$appinfo = app_idgetinfo($_POST['applist'])){
            die('服务器错误：应用无法找到');
        }
        $updata['sendtime'] = 0;
        $updata['mailcode'] = '';

        if ($appinfo['bindqq'] == '1') {
            $updata['rqq']=$_POST['cg_robotqq'];
        }
        if ($appinfo['bindmac'] == '1') {
            $updata['mac']=$_POST['cg_mac'];
        }
        if ($appinfo['bindip'] == '1') {
            $updata['lip']=$_POST['cg_ip'];
        }
        $db->update('sq_user',array('ID'=>$_SESSION['change_uid'],'appid'=>$_POST['applist']),'AND',$updata);
        die('用户绑定信息修改成功');
        break;
    default:
        die('未知请求');
}
function GetBuyFormHtml($appinfo,&$type){
    $output = '';
    if ($appinfo['logintype'] == 'zhmm') {
        $output .= '

    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_user" class="mdl-textfield__label">请输入需要开通的账号(不存在即注册)</label>
            <input class="mdl-textfield__input" id="kt_user" type="text">
       
    </div>
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_pass" class="mdl-textfield__label">请输入您账号的密码</label>
        
            <input class="mdl-textfield__input" id="kt_pass" type="text">
       
    </div>
    ';
        $type = 1;
    } else if ($appinfo['logintype'] == 'kmsq') {
        $output .= '
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_user" class="mdl-textfield__label">请输入卡密(输入续费，留空新开)</label>
        
            <input class="mdl-textfield__input" id="kt_user" type="text">
      
    </div>';
        $type = 2;
    } else if ($appinfo['logintype'] == 'jcbd') {
        if ($appinfo['bindqq'] == '1') {
            $output .= '
    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_robotqq" class="mdl-textfield__label">请输入机器人QQ</label>
       
            <input class="mdl-textfield__input" id="kt_robotqq" type="text">
 
    </div>
    ';
        }
        if ($appinfo['bindmac'] == '1') {
            $output .= '<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_mac" class="mdl-textfield__label">请输入设备码</label>
        
            <input class="mdl-textfield__input" id="kt_mac" type="text">
       
    </div>
    ';
        }
        if ($appinfo['bindip'] == '1') {
            $output .= '<div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
        <label for="kt_ip" class="mdl-textfield__label">请输入IP地址</label>
        
            <input class="mdl-textfield__input" id="kt_ip" type="text">

    </div>
    ';
        }
        $type = 3;
    } else {
        die('未知授权方式：' . $appinfo['logintype']);
    }
    return $output;
}