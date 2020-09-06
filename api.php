<?php
/**
 * Created by PhpStorm.
 * User: 80071
 * Date: 2018/2/8
 * Time: 15:21
 */

/*
 * 初始化函数库和系统
 */
include 'function/function_core.php';
include 'function/function_app.php';
include 'function/communication.php';
$communication = new communication();
if (!$appinfo = $db->select_first_row('sq_apps','*',array('ID'=>$_GET['appid']),'AND')){
    die('?');
}
$yymy = $appinfo['connectkey'];
$txmy = $appinfo['decryptkey'];
unset($appinfo['connectkey']);
unset($appinfo['decryptkey']);
$userip=get_real_ip();
/*
 * 判断请求mod来区分各个功能
 */
if (empty($_GET['mod'])){
    die();
}

if ($_GET['mod'] == 'GetTempKey'){
    $tempkey = $communication->GET_Temp_Key();
    $_SESSION['tempkey'] = $tempkey;
    die($communication->str_encode($tempkey,$yymy,'qianming'));
}

if (empty($_SESSION['tempkey'])){
    die('-1');
}
$sign = $communication->str_sign($_POST['DATA'],$txmy,$_SESSION['tempkey']);
if ($_GET['sign'] !== $sign){
    echo ($communication->str_encode('签名校验失败！'.$sign,$yymy,$_SESSION['tempkey']));
    unset($_SESSION['tempkey']);
    die();
}


$decode = $communication->str_decode($_POST['DATA'],$yymy,$_SESSION['tempkey']);
if(!$postdata = json_decode($decode,true)){
    echo ($communication->str_encode('Json格式貌似有错误？'.$decode,$yymy,$_SESSION['tempkey']));
    unset($_SESSION['tempkey']);
    die();
}

switch ($_GET['mod']){
    case 'Initialization':
        backmsg('1');
        break;
    case 'GetAppInfo':
        $bool = true;
        $i = 0 ;
        while ($bool){
            if (isset($appinfo[$i])){
                unset($appinfo[$i]);
            }else{
                $bool = false;
            }
            $i ++ ;
        }
        backmsg('1','',$appinfo);
        break;
    case 'GetCustomData':
        $userinfo = getuserinfo($appinfo,$postdata);

        if ($appinfo['usetype'] == 'dqsj'){
            if ($userinfo['balance'] < time() && $userinfo['balance'] != '-1'){
                backmsg('-4','您的授权已到期，请先续费授权！');
            }
        }else if($appinfo['usetype'] == 'kcye'){
            if ($userinfo['balance'] <= 0 && $userinfo['balance'] != '-1'){
                backmsg('-5','授权余额不足，请先进行充值');
            }
        }else{
            backmsg('-6','应用信息配置错误，请联系管理员进行处理');
        }
        if ($appinfo['bindip'] == '1' && !empty($userinfo['lip']) &&  $userinfo['lip'] !== $userip){
            backmsg('-7','请在绑定的IP['.$userinfo['lip'].']上面登陆，当前IP['.$userip.']');
        }
        if ($appinfo['bindmac'] == '1' && !empty($userinfo['mac']) && $userinfo['mac'] !== $postdata['mac']){
            backmsg('-8','请在绑定的设备['.$userinfo['mac'].']上面登陆');
        }
        if ($appinfo['bindqq'] == '1' && !empty($userinfo['rqq']) && $userinfo['rqq'] !== $postdata['robot']){
            backmsg('-8','绑定的QQ['.$userinfo['rqq'].']与当前QQ['.$postdata['robot'].']不符');
        }


        backmsg('1','',array('data'=>$appinfo['data']));
        break;
    case 'CheckAuth':
        $userinfo = getuserinfo($appinfo,$postdata);

        if ($appinfo['usetype'] == 'dqsj'){
            if ($userinfo['balance'] < time() && $userinfo['balance'] != '-1'){
                backmsg('-4','您的授权已到期，请先续费授权！');
            }
        }else if($appinfo['usetype'] == 'kcye'){
            if ($userinfo['balance'] <= 0){
                backmsg('-5','授权余额不足，请先进行充值');
            }
        }else{
            backmsg('-6','应用信息配置错误，请联系管理员进行处理');
        }
        $userip = get_real_ip();
        if ($appinfo['bindip'] == '1' && !empty($userinfo['lip']) &&  $userinfo['lip'] !== $userip){
            backmsg('-7','请在绑定的IP['.$userinfo['lip'].']上面登陆，当前IP['.$userip.']');
        }
        if ($appinfo['bindmac'] == '1' && !empty($userinfo['mac']) && $userinfo['mac'] !== $postdata['mac']){
            backmsg('-8','请在绑定的设备['.$userinfo['mac'].']上面登陆');
        }
        if ($appinfo['bindqq'] == '1' && !empty($userinfo['rqq']) && $userinfo['rqq'] !== $postdata['robot']){
            backmsg('-8','绑定的QQ['.$userinfo['rqq'].']与当前QQ['.$postdata['robot'].']不符');
        }
        if ($appinfo['onlinecheck'] == '1' && ((time() - $userinfo['htime']) < $appinfo['onlinesecond'])){
            backmsg('-8','用户尚未下线！');
        }
        $db->update('sq_user',array('ID'=>$userinfo['ID'],'appid'=>$_GET['appid']),'AND',array('rqq'=>$postdata['robot'],'mac'=>$postdata['mac'],'lip'=>$userip,'ltime'=>time()));
        backmsg('1','登陆成功！');
        break;
    case 'GetUserInfo':
        $userip = get_real_ip();
        if ($appinfo['logintype'] == 'zhmm'){
            if(!$userinfo = $db->select_first_row('sq_user','*',array('appid'=>$_GET['appid'],'username'=>$postdata['user']),'AND')){
                backmsg('-1','账号不存在');
            }
            if ($postdata['pass'] !== $userinfo['password']){
                backmsg('-2','您输入的密码错误');
            }
        }else if ($appinfo['logintype'] == 'kmsq'){
            if(!$userinfo = $db->select_first_row('sq_user','*',array('appid'=>$_GET['appid'],'username'=>$postdata['user']),'AND')){
                backmsg('-1','授权卡密不存在');
            }
        }else if ($appinfo['logintype'] == 'jcbd'){
            $where['appid'] = $_GET['appid'];
            if ($appinfo['bindip'] == '1'){
                $where['lip'] = $userip;
            }
            if ($appinfo['bindmac'] == '1'){
                $where['mac'] = $postdata['mac'];
            }
            if ($appinfo['bindqq'] == '1'){
                $where['rqq'] = $postdata['robot'];
            }
            if(!$userinfo = $db->select_first_row('sq_user','username,mac,rip,uqq,mail,ltime,balance,rqq,lip,ltime',$where,'AND')){
                backmsg('-1','未查询到您的授权');
            }
        }
        backmsg('1','',$userinfo);
        break;
    case 'OnlineHeart':
        $userinfo = getuserinfo($appinfo,$postdata);
        if ($appinfo['usetype'] == 'dqsj'){
            if ($userinfo['balance'] < time() && $userinfo['balance'] != '-1'){
                backmsg('-4','您的授权已到期，请先续费授权！');
            }
        }else if($appinfo['usetype'] == 'kcye'){
            if ($userinfo['balance'] <= 0 && $userinfo['balance'] != '-1'){
                backmsg('-5','授权余额不足，请先进行充值');
            }
        }else{
            backmsg('-6','应用信息配置错误，请联系管理员进行处理');
        }
        if ($appinfo['bindip'] == '1' && !empty($userinfo['lip']) &&  $userinfo['lip'] !== $userip){
            backmsg('-7','请在绑定的IP['.$userinfo['lip'].']上面登陆，当前IP['.$userip.']');
        }
        if ($appinfo['bindmac'] == '1' && !empty($userinfo['mac']) && $userinfo['mac'] !== $postdata['mac']){
            backmsg('-8','请在绑定的设备['.$userinfo['mac'].']上面登陆');
        }
        if ($appinfo['bindqq'] == '1' && !empty($userinfo['rqq']) && $userinfo['rqq'] !== $postdata['robot']){
            backmsg('-8','绑定的QQ['.$userinfo['rqq'].']与当前QQ['.$postdata['robot'].']不符');
        }
        if ($appinfo['usetype'] == 'kcye'){
            $time = time();
            $chazhi = time()-$userinfo['lastreducetime'];
            if ($chazhi > $appinfo['onlinesecond']+120){
                //新登录
                $lastbalance = $userinfo['balance'] - $appinfo['cycreduce'];
                $lastreducetime = $time;
            }else{
                //在线减
                $oninemin = (int)($chazhi/60);
                $lastbalance = $userinfo['balance'] - $appinfo['cycreduce']*$oninemin;
                $lastreducetime = $userinfo['lastreducetime'] - $oninemin*60;
            }
            $db->update('sq_user',array('ID'=>$userinfo['ID'],'appid'=>$_GET['appid']),'AND',array('htime'=>time(),'balance'=>$lastbalance,'lastreducetime'=>$lastreducetime));
        }else{
            $db->update('sq_user',array('ID'=>$userinfo['ID'],'appid'=>$_GET['appid']),'AND',array('htime'=>time()));
        }
        backmsg('1',time());
        break;
    case 'Unbundling':
        if ($appinfo['logintype'] == 'jcbd'){
            backmsg('-12','检查绑定方式登陆的授权不允许解除绑定');
        }
        $userinfo = getuserinfo($appinfo,$postdata);
        if ($userinfo['mailcode']!== $postdata['vcode']){
            backmsg('-13','验证码输入错误，请重新输入');
        }
        if ($userinfo['sendtime'] < time()-600){
            backmsg('-13','验证码已过期，请尝试重新获取');
        }
        if ($appinfo['usetype'] == 'dqsj'){
            if ($userinfo['balance']-$appinfo['unbindreduce'] < time() && $userinfo['balance'] != '-1'){
                backmsg('-4','授权时长不足，无法解绑');
            }
        }else if($appinfo['usetype'] == 'kcye'){
            if ($userinfo['balance']-$appinfo['unbindreduce'] <= 0 && $userinfo['balance'] != '-1'){
                backmsg('-5','授权余额不足，无法解绑');
            }
        }else{
            backmsg('-6','应用信息配置错误，请联系管理员进行处理');
        }
        if ($userinfo['balance'] != '-1'){
            $lastbalance = $userinfo['balance'] - $appinfo['unbindreduce'];
        }else{
            $lastbalance = '-1';
        }

        $db->update('sq_user',array('ID'=>$userinfo['ID']),'AND',array('mailcode'=>'','sendtime'=>0,'mac'=>'','lip'=>'','rqq'=>'','balance'=>$lastbalance));
        if ($appinfo['usetype'] == 'dqsj'){
            backmsg('1',"解除绑定成功，扣除{$appinfo['unbindreduce']}分钟授权");
        }else{
            backmsg('1',"解除绑定成功，扣除{$appinfo['unbindreduce']}点余额");
        }
        break;
    case 'ReplacBinding':
        $userinfo = getuserinfo($appinfo,$postdata);
        if ($userinfo['mailcode']!== $postdata['vcode']){
            backmsg('-13','验证码输入错误，请重新输入');
        }
        if ($userinfo['sendtime'] < time()-600){
            backmsg('-13','验证码已过期，请尝试重新获取');
        }
        if ($appinfo['usetype'] == 'dqsj'){
            if ($userinfo['balance']-$appinfo['unbindreduce'] < time() && $userinfo['balance'] != '-1'){
                backmsg('-4','授权时长不足，无法换绑');
            }
        }else if($appinfo['usetype'] == 'kcye'){
            if ($userinfo['balance']-$appinfo['unbindreduce'] <= 0 && $userinfo['balance'] != '-1'){
                backmsg('-5','授权余额不足，无法换绑');
            }
        }else{
            backmsg('-6','应用信息配置错误，请联系管理员进行处理');
        }

        if ($userinfo['balance'] != '-1'){
            $lastbalance = $userinfo['balance'] - $appinfo['unbindreduce'];
        }else{
            $lastbalance = '-1';
        }
        $db->update('sq_user',array('ID'=>$userinfo['ID']),'AND',array('mailcode'=>'','sendtime'=>0,'mac'=>$postdata['newmac'],'lip'=>$postdata['newip'],'rqq'=>$postdata['newrobot'],'balance'=>$lastbalance));
        if ($appinfo['usetype'] == 'dqsj'){
            backmsg('1',"更换绑定成功，扣除{$appinfo['unbindreduce']}分钟授权");
        }else{
            backmsg('1',"更换绑定成功，扣除{$appinfo['unbindreduce']}点余额");
        }
        break;
    case 'ChangePassword':
        if ($appinfo['logintype'] !== 'zhmm'){
            backmsg('-13','非账号密码方式授权无法修改密码');
        }
        $userinfo = getuserinfo($appinfo,$postdata);
        if ($userinfo['mailcode']!== $postdata['vcode']){
            backmsg('-13','验证码输入错误，请重新输入');
        }
        if ($userinfo['sendtime'] < time()-600){
            backmsg('-13','验证码已过期，请尝试重新获取');
        }

        $db->update('sq_user',array('ID'=>$userinfo['ID']),'AND',array('password'=>$postdata['newpass']));
        backmsg('1');
        break;
    case 'SubscriberDownline':
        $userinfo = getuserinfo($appinfo,$postdata);
        $db->update('sq_user',array('ID'=>$userinfo['ID']),'AND',array('htime'=>0));
        backmsg('1');
        break;
    case 'BalanceRecharge':
        $userinfo = getuserinfo($appinfo,$postdata);
        if ($userinfo['balance'] == '-1'){
            backmsg('-15','您的授权已是永久授权，无需续费！');
        }
        if (!$keyinfo = $db->select_first_row('sq_key','*',array('kami'=>$postdata['key']),'AND')){
            backmsg('-13','您输入的卡密不存在');
        }
        if ($keyinfo['lastmoney'] < $postdata['money']){
            backmsg('-13','卡密余额不足');
        }
        if ($appinfo['usetype'] == 'dqsj'){  //当授权方式为到期时间
            if ($userinfo['balance'] < time()){ //判断是否已经过期，如果 用户的到期时间小于当前时间
                $newbalance = time() + $appinfo['rechargeface'] * $postdata['money'] *60;
                //新到期时间 = 当前时间 + 充值面值 * 充值数量 * 60
            }else{
                $newbalance = $userinfo['balance'] + $appinfo['rechargeface'] * $postdata['money']*60;
                //否则新到期时间 = 原有时间 + 充值面值 * 充值数量 *60
            }
        }else if ($appinfo['usetype'] == 'kcye'){
            $newbalance = $userinfo['balance'] + $appinfo['rechargeface'] * $postdata['money'];
        }
        $newmoney = $keyinfo['lastmoney'] - $postdata['money'];
        if ($keyinfo['firstusetime'] == 0){
            $newinfo['firstusetime'] = time();
        }
        $newinfo['lastmoney'] = $newmoney;
        $newinfo['lastusetime'] = time();
        $db->update('sq_key',array('ID'=>$keyinfo['ID']),'AND',$newinfo);
        $db->update('sq_user',array('ID'=>$userinfo['ID']),'AND',array('balance'=>$newbalance));
        backmsg('1');
        break;
    case 'RegisteredAccount':
        $accessurl = 'http://'.$G['config']['pay_domain'].'/ajax.php?mod=register&username='.urlencode($postdata['user']).'&pass='.urlencode($postdata['pass']).'&mail='.urlencode($postdata['mail']);
        $tittle = '';
        break;
    case 'GetVerifyingCode':
        $userinfo = getuserinfo($appinfo,$postdata);
        if (time()-$userinfo['sendtime'] < 60){
            backmsg('-1','邮件发送过于频繁，请耐心等待'.(time()-$userinfo['sendtime']).'秒后发送');
        }
        $code = rand_str(6);
        $db->update('sq_user',array('ID'=>$userinfo['ID']),'AND',array('mailcode'=>$code,'sendtime'=>time()));
        if(!$back = sendemail('验证码 - '.$appinfo['appname'],'['.$userinfo['username'].']您正在进行敏感操作，您本次操作的验证码为：'.$code,$userinfo['mail'])){
            backmsg('-1',$back);
        }
        backmsg('1');
        break;
    case 'RetrievePassword':
        $userinfo = getuserinfo($appinfo,$postdata);
        if (time()-$userinfo['sendtime'] < 60){
            backmsg('-1','邮件发送过于频繁，请耐心等待'.(time()-$userinfo['sendtime']).'秒后发送');
        }
        $db->update('sq_user',array('ID'=>$userinfo['ID']),'AND',array('sendtime'=>time()));
        sendemail('找回密码 - '.$appinfo['appname'],'['.$userinfo['username'].']您的密码为：'.$userinfo['password'],$userinfo['mail']);
        backmsg('1');
        break;

}
function backmsg($code,$message='',$array = array()){
    global $communication;
    global $yymy;
    $back = $array;
    $back['code'] = $code;
    $back['msg'] = $message;

    echo ($communication->str_encode(json_encode($back),$yymy,$_SESSION['tempkey']));
    unset($_SESSION['tempkey']);
    die();
}
function getuserinfo($appinfo,$postdata){
    $userip = get_real_ip();
    global $db;
    if ($appinfo['logintype'] == 'zhmm'){
        if(!$userinfo = $db->select_first_row('sq_user','*',array('appid'=>$_GET['appid'],'username'=>$postdata['user']),'AND')){
            backmsg('-1','账号不存在');
        }
        if ($postdata['pass'] !== $userinfo['password']){
            backmsg('-2','您输入的密码错误');
        }
    }else if ($appinfo['logintype'] == 'kmsq'){
        if(!$userinfo = $db->select_first_row('sq_user','*',array('appid'=>$_GET['appid'],'username'=>$postdata['user']),'AND')){
            backmsg('-1','授权卡密不存在');
        }
    }else if ($appinfo['logintype'] == 'jcbd'){
        $where['appid'] = $_GET['appid'];
        if ($appinfo['bindip'] == '1'){
            $where['lip'] = $userip;
        }
        if ($appinfo['bindmac'] == '1'){
            $where['mac'] = $postdata['mac'];
        }
        if ($appinfo['bindqq'] == '1'){
            $where['rqq'] = $postdata['robot'];
        }
        if(!$userinfo = $db->select_first_row('sq_user','*',$where,'AND')){
            backmsg('-1','未查询到您的授权');
        }
    }
    if ($userinfo['status'] != '1'){
        backmsg('-3','用户状态不正常，请联系客服处理');
    }

    return $userinfo;
}