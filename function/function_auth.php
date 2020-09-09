<?php
/**
 * Created by PhpStorm.
 * User: 温泉
 * Date: 2019/2/9
 * Time: 15:50
 */


/**
 * 授权操作
 * @access public
 * @param string $username 用户名、卡密
 * @param string $password 密码
 * @param string $ip 用户IP、绑定的IP
 * @param int $balance 新增授权点数或秒数，-1为永久
 * @param string $uqq 用户QQ
 * @param string $mail 用户邮箱
 * @param int $appid 应用ID
 * @param int $agentid 代理ID
 * @param string $newkey 返回的卡密
 * @param string $tips 提示
 * @return int 1为新开成功，2为续费成功，3为卡密新开成功
 */
function auth_add($username,$password,$ip,$balance,$uqq,$mail,$appid,$origin,$oid,&$newkey,&$tips,$tradeinfo = array()){
    include_once 'function_app.php';
    global $db,$G,$MailTips;


    $appinfo = app_idgetinfo($appid);
    if ($appinfo['usetype']=='dqsj'){
        if ($balance!='-1'){
            $balance=$balance*60;
        }
    }
    if ($appinfo['logintype'] == 'zhmm') {
        //账号密码授权方式
        if (!$userinfo = $db->select_first_row('sq_user', '*', array('username' => $username, 'appid' => $appid), 'AND')) {
            $T['title'] = '您的授权账号已新开成功！';
            $T['content'][] = '应用名称：'.$appinfo['appname'];
            $T['content'][] = '注册时间：'.Get_Date();
            if ($appinfo['usetype']=='dqsj'){
                if ($balance!='-1'){
                    $balance=time()+$balance;
                    $T['content'][] = '到期时间：'.Get_Date($balance);
                }else{
                    $T['content'][] = '到期时间：永久授权';
                }
            }else{
                if ($balance == '-1'){
                    $T['content'][] = '开通余额：无限使用';
                }else{
                    $T['content'][] = '开通余额：'.$balance;
                }

            }

            $db->insert_back_id('sq_user', array(
                    'username' => $username,
                    'password' =>$password,
                    'mail' => $mail,
                    'uqq' => $uqq,
                    'rtime' => time(),
                    'status' => '1',
                    'appid' => $appid,
                    'aid' => $oid,
                    'balance'=>$balance,
                    'origin' => $origin,
                    'oid'=>$oid
                )
            );
            $T['content'][] = '授权来源：'.GetOriginText($origin);
            $T['content'][] = '联系方式：QQ '.$uqq;
            $T['content'][] = '授权账号：'.$username;
            $T['content'][] = '用户邮箱：'.$mail;
            $MailTips[] = $T;
            SendTipsMail($mail,$MailTips,$backinfo);
            return 1;
        }
        $T['title'] = '您的授权账号已续费成功！';
        $T['content'][] = '应用名称：'.$appinfo['appname'];
        $T['content'][] = '注册时间：'.Get_Date($userinfo['rtime']);
        if ($balance != '-1') {
            if ($appinfo['usetype'] == 'dqsj') {
                if ($userinfo['balance'] < time()) {
                    $usernewinfo['balance'] = time() + $balance;
                } else {
                    $usernewinfo['balance'] = $userinfo['balance'] + $balance;
                }
                $T['content'][] = '到期时间：'.Get_Date($usernewinfo['balance']);
            } else if ($appinfo['usetype'] == 'kcye') {
                $usernewinfo['balance'] = $userinfo['balance'] +$balance;
                $T['content'][] = '授权余额：'.Get_Date($usernewinfo['balance']);
            } else {
                $tips='应用使用方式设置异常';
                return -3;
            }

        } else {
            $usernewinfo['balance'] = '-1';
            $T['content'][] = $appinfo['usetype']=='dqsj' ? '到期时间：永久授权' : '开通余额：无限使用';
        }
        $T['content'][] = '授权来源：'.GetOriginText($origin);
        $T['content'][] = '联系方式：QQ '.$uqq;
        $T['content'][] = '授权账号：'.$userinfo['username'];
        $T['content'][] = '用户邮箱：'.$userinfo['mail'];
        $MailTips[]= $T;
        $db->update('sq_user', array('ID'=>$userinfo['ID']), 'AND', $usernewinfo);
        SendTipsMail($userinfo['mail'],$MailTips,$backinfo);
        return 2;
    } else if ($appinfo['logintype'] == 'kmsq') {
        //卡密登录方式
        if (empty($username)) {
            //没有卡密新开授权
            $T['title'] = '您的授权卡密已新开成功！';
            $T['content'][] = '应用名称：'.$appinfo['appname'];
            $T['content'][] = '注册时间：'.Get_Date();
            if ($appinfo['usetype']=='dqsj'){
                if ($balance!='-1'){
                    $balance=time()+$balance;
                    $T['content'][] = '到期时间：'.Get_Date($balance);
                }else{
                    $T['content'][] = '到期时间：永久使用';
                }
            }else{
                if ($balance == '-1'){
                    $T['content'][] = '开通余额：无限使用';
                }else{
                    $T['content'][] = '开通余额：'.$balance;
                }
            }
            $newkey = rand_str(32);
            while ($info = $db->select_first_row('sq_user', 'ID', array('username' => $newkey, 'appid' => $appinfo['ID']), 'AND')) {
                $newkey = rand_str(32);
            }
            //echo $newkey;
            $db->insert_back_id('sq_user', array(
                'username' => $newkey,
                'mail' => $mail,
                'rtime' => time(),
                'balance' => $balance,
                'status' => 1,
                'origin' => $origin,
                'oid'=>$oid,
                'appid' => $appid,
                'aid' => $oid,
            ));
            $T['content'][] = '授权来源：'.GetOriginText($origin);
            $T['content'][] = '联系方式：QQ '.$uqq;
            $T['content'][] = '授权卡密：'.$newkey;
            $T['content'][] = '用户邮箱：'.$mail;
            $MailTips[] = $T;
            SendTipsMail($mail,$MailTips,$backinfo);
            return 3;
        }
        //续费授权
        if (!$userinfo = $db->select_first_row('sq_user', '*', array('username' => $username, 'appid' => $appid), 'AND')) {
            $tips = '授权卡密不存在';
            return -2;
        }
        $T['title'] = '您的授权卡密已续费成功！';
        $T['content'][] = '应用名称：'.$appinfo['appname'];
        $T['content'][] = '注册时间：'.Get_Date($userinfo['rtime']);
        if ($balance != '-1') {
            if ($appinfo['usetype'] == 'dqsj') {
                if ($userinfo['balance'] < time()) {
                    $usernewinfo['balance'] = time() + $balance;
                } else {
                    $usernewinfo['balance'] = $userinfo['balance'] + $balance;
                }
                $T['content'][] = '到期时间：'.Get_Date($usernewinfo['balance']);
            } else if ($appinfo['usetype'] == 'kcye') {
                $usernewinfo['balance'] = $userinfo['balance'] +$balance;
                $T['content'][] = '授权余额：'.Get_Date($usernewinfo['balance']);
            } else {
                $tips='应用使用方式设置异常';
                return -3;
            }
        } else {
            $usernewinfo['balance'] = '-1';
            $T['content'][] = $appinfo['usetype']=='dqsj' ? '到期时间：永久授权' : '开通余额：无限使用';
        }
        $T['content'][] = '授权来源：'.GetOriginText($origin);
        $T['content'][] = '联系方式：QQ '.$uqq;
        $T['content'][] = '授权卡密：'.$userinfo['username'];
        $T['content'][] = '用户邮箱：'.$userinfo['mail'];
        $MailTips[]= $T;
        $db->update('sq_user', array('username' => $username, 'appid' => $appinfo['ID']), 'AND', $usernewinfo);
        SendTipsMail($userinfo['mail'],$MailTips,$backinfo);
        return 2;
    } else if ($appinfo['logintype'] == 'jcbd') {
        $chect = json_decode($username,true);
        if ($appinfo['bindip'] == '1'){
            $search['lip'] = $chect['lip'];
            $T['content'][] = '绑定IP：'.$chect['lip'];
        }
        if ($appinfo['bindqq'] == '1'){
            $search['rqq'] = $chect['rqq'];
            $T['content'][] = '绑定QQ：'.$chect['rqq'];
        }
        if ($appinfo['bindmac'] == '1'){
            $search['mac'] = $chect['mac'];
            $T['content'][] = '绑定设备：'.$chect['mac'];
        }
        $search['appid'] = $appid;
        if (!isset($chect['lip'])){
            $chect['lip'] = '';
        }
        if (!isset($chect['rqq'])){
            $chect['rqq'] = '';
        }
        if (!isset($chect['mac'])){
            $chect['mac'] = '';
        }
        if (!$userinfo = $db->select_first_row('sq_user', '*', $search, 'AND')) {
            $T['title'] = '您的授权绑定已新开成功！';
            $T['content'][] = '应用名称：'.$appinfo['appname'];
            $T['content'][] = '注册时间：'.Get_Date();
            if ($appinfo['usetype']=='dqsj'){
                if ($balance!='-1'){
                    $balance=time()+$balance;
                    $T['content'][] = '到期时间：'.Get_Date($balance);
                }else{
                    $T['content'][] = '到期时间：永久使用';
                }
            }else{
                if ($balance == '-1'){
                    $T['content'][] = '开通余额：无限使用';
                }else{
                    $T['content'][] = '开通余额：'.$balance;
                }
            }

            $db->insert_back_id('sq_user', array(
                'mail' => $mail,
                'rtime' => time(),
                'balance' => $balance,
                'status' => 1,
                'appid' => $appid,
                'uqq' => $uqq,
                'mac' => $chect['mac'],
                'rqq' => $chect['rqq'],
                'rip' => $ip,
                'lip' => $chect['lip'],
                'aid' => $oid,
                'origin' => $origin,
                'oid'=>$oid,
            ));

            $T['content'][] = '授权来源：'.GetOriginText($origin);
            $T['content'][] = '联系方式：QQ '.$uqq;
            $T['content'][] = '用户邮箱：'.$mail;
            $MailTips[] = $T;
            SendTipsMail($mail,$MailTips,$backinfo);
            return 1;
        }

        $T['title'] = '您的授权卡密已续费成功！';
        $T['content'][] = '应用名称：'.$appinfo['appname'];
        $T['content'][] = '注册时间：'.Get_Date($userinfo['rtime']);
        if ($balance != '-1') {
            if ($appinfo['usetype'] == 'dqsj') {
                if ($userinfo['balance'] < time()) {
                    $usernewinfo['balance'] = time() + $balance;
                } else {
                    $usernewinfo['balance'] = $userinfo['balance'] + $balance;
                }
                $T['content'][] = '到期时间：'.Get_Date($usernewinfo['balance']);
            } else if ($appinfo['usetype'] == 'kcye') {
                $usernewinfo['balance'] = $userinfo['balance'] +$balance;
                $T['content'][] = '授权余额：'.Get_Date($usernewinfo['balance']);
            } else {
                $tips='应用使用方式设置异常';
                return -3;
            }
        } else {
            $usernewinfo['balance'] = '-1';
            $T['content'][] = $appinfo['usetype']=='dqsj' ? '到期时间：永久授权' : '开通余额：无限使用';
        }

        $T['content'][] = '授权来源：'.GetOriginText($origin);
        $T['content'][] = '联系方式：QQ '.$uqq;
        $T['content'][] = '用户邮箱：'.$userinfo['mail'];
        $MailTips[]= $T;
        $db->update('sq_user', $search, 'AND', $usernewinfo);
        SendTipsMail($userinfo['mail'],$MailTips,$backinfo);
        return 2;
    }
    $tips = '未知授权请求？';
    return -2;
}

