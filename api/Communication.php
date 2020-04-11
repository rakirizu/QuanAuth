<?php
/**
 * Created by PhpStorm.
 * User: 温泉
 * Date: 2018/7/9
 * Time: 17:31
 * Notice: 强加密版Api，与易语言通信
 */

error_reporting(1); //只输出致命错误

include '../function/function_core.php';
include '../function/function_app.php';
include '../function/Tips.php';
if (!empty($_GET['c']) && $_GET['c'] == 'api') include '../function/api.class.php';else include '../function/Encryption.class.php';

if (empty($_GET['appid'])) die(makejson(-1,$Tips['NoAppID']));
if (strlen($_GET['sign']) !== 32) die(makejson(-2,$Tips['NoSign']));
$data = $_POST['data'];
if (!$appinfo = app_idgetinfo($_GET['appid'])) die(makejson(-4,$Tips['GetAppInfoErr']));
$communication = !empty($_GET['c']) &&  $_GET['c'] == 'api'? new ApiCommunication($appinfo['connectkey'],$appinfo['decryptkey']) : new communication($appinfo['connectkey'],$appinfo['decryptkey']);
if ($communication->getsign($data) !== $_GET['sign']) die(makejson(-3,$Tips['SignError']));
$time = time();
$d = !empty($_GET['c']) && $_GET['c'] == 'api' ? $data : $communication->data_decode($data);
unset($data);
$data = json_decode($d,true);
$sign = '';
if ($data['mod'] === 'Initialization'){
    if ($data['ddition'] < $appinfo['ver'] && numtobool($appinfo['forceup'])) $communication->back(-101,'AppVerOld',array('updateurl'=>$appinfo['upurl'],'updatelog'=>$appinfo['uplog'],'notice'=>$appinfo['notice']));
    if (numtobool($appinfo['close'])) $communication->back(-102,'AppClosed',array('updateurl'=>$appinfo['upurl'],'updatelog'=>$appinfo['uplog'],'notice'=>$appinfo['notice']));
    $token = rand_str(64);
    $db->insert_back_id('sq_token',array('token'=>$token,'ip'=>get_real_ip(),'addtime'=>$time,'appid'=>$_GET['appid']));
    $communication->back(1,'',array('updateurl'=>$appinfo['upurl'],'updatelog'=>$appinfo['uplog'],'notice'=>$appinfo['notice'],'token'=>$token));
}

if ($data['mod'] === 'Login'){
    //echo 'login';
    if (empty($data['token']) || !$tokeninfo = $db->select_first_row('sq_token','uid,ip',array('token'=>$data['token']),'AND')) $communication->back(-201,'InitTimeOut');

    $userip = empty($data['ip']) ? get_real_ip() : $data['ip'];
    if ($appinfo['free']) {
        $db->update('sq_token',array('token'=>$data['token']),'AND',array('uid'=>0,'start'=>time()));
        $communication->back(1);
    }

    if ($appinfo['logintype'] == 'zhmm') {
        if (empty($data['user'])) $communication->back(-202,'NoUsername');

        if (empty($data['pass'])) $communication->back(-203,'NoPassword');

        if (!$userinfo = $db->select_first_row('sq_user', '*', array('appid' => $_GET['appid'], 'username' => $data['user']), 'AND')) $communication->back(-204,'ErrorUsername');

        if ($data['pass'] !== $userinfo['password']) $communication->back(-205,'ErrorPassword');

    } else if ($appinfo['logintype'] == 'kmsq') {
        if (empty($data['user'])) $communication->back(-211,'NoKami');
        if (!$userinfo = $db->select_first_row('sq_user', '*', array('appid' => $_GET['appid'], 'username' => $data['user']), 'AND')) $communication->back(-212,'ErrorKami');

    } else if ($appinfo['logintype'] == 'jcbd') {
        $where['appid'] = $_GET['appid'];
        if ($appinfo['bindip'] == 1) $where['lip'] = $userip;
        if ($appinfo['bindmac'] == 1) $where['mac'] = $data['mac'];
        if ($appinfo['bindqq'] == 1) $where['rqq'] = $data['robot'];

        if (!$userinfo = $db->select_first_row('sq_user', '*', $where, 'AND')) $communication->back(-221,'ErrorBind');
    }
    if ($userinfo['status'] != 1)  $communication->back(-231,'UserCold');
    if ($appinfo['usetype'] == 'dqsj') {
        if ($userinfo['balance'] < time() && $userinfo['balance'] != '-1') $communication->back(-232,'AuthTimeOut',array('balance'=>$userinfo['balance']));

    } else if ($appinfo['usetype'] == 'kcye') {
        if ($userinfo['balance'] <= 0) $communication->back(-233,'BalanceOut');
    } else {
        $communication->back(-4,'GetAppInfoErr');
    }
    $bindip = $userinfo['lip'];
    $bindmac = $userinfo['mac'];
    $usermac  = $data['mac'];
    $bindrqq = $userinfo['rqq'];
    $userqq =  $data['robot'];

    if ($appinfo['bindip'] == 1 && !empty($userinfo['lip']) && $userinfo['lip'] !== $userip) {
        $communication->back(-234,'IpBindNeed',array('bindip'=>$userinfo['lip']));

    }
    if ($appinfo['bindmac'] == 1 && !empty($userinfo['mac']) && $bindmac !== $usermac) {
        $communication->back(-235,'MacBindNeed',array('bindmac'=>$userinfo['mac']));
    }
    if ($appinfo['bindqq'] == 1 && !empty($userinfo['rqq']) && $bindrqq !== $userqq) {
        $communication->back(-236,'QQBindNeed',array('bindqq'=>$userinfo['rqq']));
    }

    $db->update('sq_user', array('ID' => $userinfo['ID']), 'AND', array('rqq' =>$userqq, 'mac' =>$usermac, 'lip' => $userip, 'ltime' => time()));
    //$token  = rand_str(64);
    $db->update('sq_token',array('token'=>$data['token']),'AND',array('uid'=>$userinfo['ID'],'start'=>time()));
    //$db->insert_back_id('sq_token',array('token'=>$token,'uid'=>$userinfo['ID'],'lastest'=>$time,'start'=>$time));
    if ($appinfo['onlinecheck'] == 1) {
        $onlineuser = $db->select_all_row('sq_token','lastest',array('uid'=>$userinfo['ID']),'AND','ORDER BY lastest DESC');
        foreach ($onlineuser as $on){
            if ((time() - $on['lastest']) < $appinfo['onlinesecond']){
                $communication->back(-301,'LoggedInTips',array('ip'=>$tokeninfo['ip'],''));
            }else{
                break;
            }
        }
    }
    $communication->back(1);

}

if ($data['mod'] === 'GoOnline'){
    if (empty($data['token']) || !$tokeninfo = $db->select_first_row('sq_token','*',array('token'=>$data['token']),'AND')) $communication->back(-201,'InitTimeOut');
    if ($tokeninfo['start'] == 0 || empty($tokeninfo['start'])){
        $communication->back(-201,'InitTimeOut');
    }
    if ($appinfo['free']) {
        $db->update('sq_token',array('token'=>$data['token']),'AND',array('lastest'=>time()));
        $communication->back(1);
    }
    if ($appinfo['onlinecheck'] == 1){
        $db->delete('sq_token',array('uid'=>$tokeninfo['uid']),'AND');
    }
    //$db->insert_back_id('sq_token',array('token'=>$tokeninfo['token'],'uid'=>$tokeninfo['uid'],'start'=>$tokeninfo['start'],'lastest'=>time()));
    $communication->back(1);

}

if ($data['mod'] === 'OnlineHeart'){
    if (empty($data['token'])) $communication->back(-401,'HeartNoToken');
    if (!$tokeninfo = $db->select_first_row('sq_token','*',array('token'=>$data['token']),'AND')) $communication->back(-402,'HeartTokenError');
    if ($tokeninfo['start'] == 0 || empty($tokeninfo['start'])){
        $communication->back(-201,'InitTimeOut');
    }
    $db->update('sq_token',array('token'=>$data['token']),'AND',array('lastest'=>time()));
    if ($appinfo['free']) {
        $communication->back(1,'',array('time'=>$time));
    }
    if (!$userinfo = $db->select_first_row('sq_user','*',array('ID'=>$tokeninfo['uid']),'AND')) $communication->back(-234,'HeartNoFoundUser');
    if ($userinfo['status'] != 1)  $communication->back(-231,'UserCold');
    if ($appinfo['usetype'] == 'dqsj') {
        if ($userinfo['balance'] < time() && $userinfo['balance'] != '-1') $communication->back(-232,'AuthTimeOut');
    } else if ($appinfo['usetype'] == 'kcye') {
        if ($userinfo['balance'] <= 0) $communication->back(-233,'BalanceOut');
    } else {
        $communication->back(-4,'GetAppInfoErr');
    }

    $msg = '';
    if ($appinfo['usetype'] == 'kcye') {
        $time = time();
        $chazhi = time() - $userinfo['lastreducetime'];
        if ($chazhi > $appinfo['onlinesecond'] + 120) {
            //新登录
            $lastbalance = (int)$userinfo['balance'] - (int)$appinfo['cycreduce'];
            $lastreducetime = $time;
            $msg = '余额扣除成功';
        } else {
            //在线减
            $oninemin = (int)($chazhi / 60);
            $lastbalance = $userinfo['balance'] - $appinfo['cycreduce'] * $oninemin;
            $lastreducetime = $userinfo['lastreducetime'] - $oninemin * 60;
            $msg = '余额扣除成功';
        }
        $db->update('sq_user', array('ID' => $userinfo['ID'], 'appid' => $_GET['appid']), 'AND', array('htime' => time(), 'balance' => $lastbalance, 'lastreducetime' => $lastreducetime));
    } else {
        $db->update('sq_user', array('ID' => $userinfo['ID'], 'appid' => $_GET['appid']), 'AND', array('htime' => time()));
    }
    $communication->back(1,'',array('time'=>$time,'o'=>$msg));
}

if ($data['mod'] == 'GetCustumData'){
    if (empty($data['token'])) $communication->back(-401,'HeartNoToken');
    if (!$tokeninfo = $db->select_first_row('sq_token','*',array('token'=>$data['token']),'AND')) $communication->back(-402,'HeartTokenError');
    if ($tokeninfo['start'] == 0 || empty($tokeninfo['start'])){
        $communication->back(-201,'InitTimeOut');
    }

    if ($appinfo['free']) {
        $communication->back(1,'',array('data'=>$appinfo['data']));
    }
    if (!$userinfo = $db->select_first_row('sq_user','*',array('ID'=>$tokeninfo['uid']),'AND')) $communication->back(-234,'HeartNoFoundUser');
    if ($userinfo['status'] != 1)  $communication->back(-231,'UserCold');
    if ($appinfo['usetype'] == 'dqsj') {
        if ($userinfo['balance'] < time() && $userinfo['balance'] != '-1') $communication->back(-232,'AuthTimeOut');
    } else if ($appinfo['usetype'] == 'kcye') {
        if ($userinfo['balance'] <= 0) $communication->back(-233,'BalanceOut');
    } else {
        $communication->back(-4,'GetAppInfoErr');
    }
    $communication->back(1,'',array('data'=>$appinfo['data']));
}

if ($data['mod'] == 'GetAppInfo'){
    if (empty($data['token']) || !$tokeninfo = $db->select_first_row('sq_token','uid',array('token'=>$data['token']),'AND')) $communication->back(-201,'InitTimeOut');

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
    $communication->back(1,'',$appinfo);
}

if ($data['mod'] == 'GetUserInfo'){
    if (empty($data['token'])) $communication->back(-401,'HeartNoToken');
    if (!$tokeninfo = $db->select_first_row('sq_token','*',array('token'=>$data['token']),'AND')) $communication->back(-402,'HeartTokenError');
    if ($tokeninfo['start'] == 0 || empty($tokeninfo['start'])){
        $communication->back(-201,'InitTimeOut');
    }

    if ($appinfo['free']) {
        $communication->back(2,'');
    }

    if (!$userinfo = $db->select_first_row('sq_user','*',array('ID'=>$tokeninfo['uid']),'AND')) $communication->back(-234,'HeartNoFoundUser');
    if ($userinfo['status'] != 1)  $communication->back(-231,'UserCold');
    if ($appinfo['usetype'] == 'dqsj') {
        if ($userinfo['balance'] < time() && $userinfo['balance'] != '-1') $communication->back(-232,'AuthTimeOut');
    } else if ($appinfo['usetype'] == 'kcye') {
        if ($userinfo['balance'] <= 0) $communication->back(-233,'BalanceOut');
    } else {
        $communication->back(-4,'GetAppInfoErr');
    }
    $communication->back(1,'',$userinfo);
}

if($data['mod'] == 'OffLine'){
    if (empty($data['token'])) $communication->back(-401,'HeartNoToken');
    if (!$tokeninfo = $db->select_first_row('sq_token','*',array('token'=>$data['token']),'AND')) $communication->back(-402,'HeartTokenError');
    $db->delete('sq_token',array('token'=>$data['token']),'AND');
    $communication->back(1);
}
