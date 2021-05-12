<?php
/**
 * Created by PhpStorm.
 * User: 温泉
 * Date: 2017-10-19
 * Time: 22:12
 */
require '../function/function_core.php';


if ($_GET['mod'] === 'login'){
    if (!empty($_POST['accesstoken'])) {
        if (!$result = $db->select_first_row('sq_admin', '*', array('accesstoken' => $_POST['accesstoken']), 'AND')) {
            die(json_encode(array('code' => '-1', 'msg' => '秘钥错误')));
        } else {
            $_SESSION['admin_username'] = $result['username'];
            $_SESSION['admin_id'] = $result['ID'];
            $_SESSION['admin_qq'] = $result['qq'];
            $_SESSION['admin_password'] = $result['password'];
            $_SESSION['admin_HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
            //$db->insert_back_id('sq_log_system',array('time'=>time(),'ip'=>get_real_ip(),'msg'=>'管理员 '.$_POST['username'].'使用API秘钥登陆成功','type'=>'success'));
            $db->posterror('管理员 '.$result['username'].'使用API秘钥登陆成功','success');
            die(json_encode(array('code' => '1', 'msg' => '登陆成功')));
        }
    }
    if (empty($_POST['username'])){
        die(json_encode(array('code'=>-1,'msg'=>'请输入您的用户名')));
    }
    if (empty($_POST['password'])) {
        die(json_encode(array('code' => -2, 'msg' => '请输入您的密码')));
    }
    include '../function/VerificationCode.class.php';
    $verification = Verification::check($_POST['token']);
    if ($verification !== true) {
        die(json_encode(array('code' => '-88', 'msg' => '请先进行人机验证')));
    }
    if (!$result = $db->select_first_row('sq_admin','*',array('username'=>$_POST['username'],'password'=>md5($_POST['password'])),'AND')){
        //$db->insert_back_id('sq_log_system',array('time'=>time(),'ip'=>get_real_ip(),'msg'=>'管理员 '.$_POST['username'].'使用密码'.$_POST['password'].'登陆失败：密码错误或账号不存在','type'=>'danger'));
        $db->posterror('管理员登录尝试失败，账号['.$_POST['username'].']密码['.$_POST['password'].']','success');
        die(json_encode(array('code' => -3, 'msg' => '输入的账号密码错误！'.$db->geterror())));
    }else{
        $_SESSION['admin_username'] = $_POST['username'];
        $_SESSION['admin_id'] = $result['ID'];
        $_SESSION['admin_qq'] = $result['qq'];
        $_SESSION['admin_password'] = md5($_POST['password']);
        $_SESSION['admin_HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
        $db->update('sq_admin',array('username'=>$_SESSION['admin_username']),'AND',array('loginip'=>get_real_ip(),'logintime'=>time()));
       // $db->insert_back_id('sq_log_system',array('time'=>time(),'ip'=>get_real_ip(),'msg'=>'['.get_real_ip().']管理员 '.$_POST['username'].'使用密码登陆成功！','type'=>'success'));
        $db->posterror('管理员 '.$_POST['username'].'使用密码登录后台成功','success');
        die(json_encode(array('code' => '1', 'msg' => '登陆成功，正在跳转！')));
    }
} else if($_GET['mod'] === 'checklogin'){
    if (empty($_SESSION['admin_username']) || empty($_SESSION['admin_password'])){
        die(json_encode(array('code'=>-1)));
    }
    if (!$result = $db->select_first_row('sq_admin','*',array('username'=>$_SESSION['admin_username'],'password'=>$_SESSION['admin_password']),'AND')){
        die(json_encode(array('code'=>-2)));
    }
    if ($_SERVER['HTTP_USER_AGENT'] !== $_SESSION['admin_HTTP_USER_AGENT']){
        die(json_encode(array('code'=>-3)));
    }else{
        die(json_encode(array('code'=>1,'username'=>$_SESSION['admin_username'],'adminqq'=>$_SESSION['admin_qq'])));
    }

} else{
    if (empty($_SESSION['admin_username']) || empty($_SESSION['admin_password'])){
        echo '没有登录:code:-1';
        header('Location: login.html');
        die();
    }
    if (!$result = $db->select_first_row('sq_admin','*',array('username'=>$_SESSION['admin_username'],'password'=>$_SESSION['admin_password']),'AND')){
        echo '没有登录:code:-2';
        header('Location: login.html');
        die();
    }
    if ($_SERVER['HTTP_USER_AGENT'] !== $_SESSION['admin_HTTP_USER_AGENT']){
        echo '没有登录:code:-3';
        header('Location: login.html');
        die();
    }
}
switch ($_GET['mod']){
    case 'addapp':
        $txms = rand_str(64);
        $jmms = rand_str(64);
        if ($_POST['sqfs'] == 'jcbd' && $_POST['bdip'] == 'false' && $_POST['bdjq'] == 'false' && $_POST['bdqq'] == 'false'){
            die(makejson(-1,'当授权方式为检查绑定的时候，绑定IP/MAC/QQ中至少需要选择一个！'));
        }
        if(!$id = $db->insert_back_id('sq_apps',array('appname'=>$_POST['app_name'],'bindip'=>textbooltonum($_POST['bdip']),'bindmac'=>textbooltonum($_POST['bdjq']),'bindqq'=>textbooltonum($_POST['bdqq']),'onlinecheck'=>textbooltonum($_POST['zxjc']),'allowchange'=>textbooltonum($_POST['yxhb']),'allowunbind'=>textbooltonum($_POST['yxjb']),'logintype'=>$_POST['sqfs'],'usetype'=>$_POST['syfs'],'cycreduce'=>$_POST['zqkc'],'unbindreduce'=>$_POST['jbkc'],'rechargeface'=>$_POST['czmz'],'onlinesecond'=>$_POST['zxms'],'reggive'=>$_POST['zczs'],'notice'=>$_POST['yygg'],'ver'=>$_POST['yybb'],'uplog'=>$_POST['gxrz'],'upurl'=>$_POST['gxdz'],'forceup'=>textbooltonum($_POST['qzgx']),'free'=>textbooltonum($_POST['mfms']),'connectkey'=>$txms,'decryptkey'=>$jmms,'data'=>$_POST['zdysj']))){
            die(makejson(-1,'插入数据发生错误：'.$db->geterror()));
        }else{
            die(makejson(1,'success',array('appid'=>$id,'Decryptkey'=>$jmms,'Connectkey'=>$txms)));
        }
        break;
    case 'getsysinfo':

        $fanhuishuju['appnum'] = $db->select_count_row('sq_apps');
        $fanhuishuju['sysver'] = $G['siteinfo']['ver'];
        $fanhuishuju['czkeynum'] = $db->select_count_row('sq_key');
        $fanhuishuju['tckeynum'] = $db->select_count_row('sq_fidkey');
        $fanhuishuju['agentnum'] = $db->select_count_row('sq_agent');
        $fanhuishuju['tradenum'] = $db->select_count_row('sq_trade');

        die(json_encode($fanhuishuju));
        break;
    case 'getapplist':
        if(!$arr = $db->select_limit_row('sq_apps','*',($_GET['page'] - 1) * $_GET['limit'] , $_GET['limit'], array(), 'AND')){
            $backinfo['code'] = -1;
            $backinfo['msg'] = $db->geterror();
            die();
        }
        $backinfo['code'] = 0;
        $backinfo['msg'] = '';
        $backinfo['count'] = $db->select_count_row('sq_apps');
        $i='';
        foreach ($arr as $value){
            $newinfo = array();
            $newinfo['id'] = $value['ID'];
            $newinfo['app_name'] = $value['appname'];
            $newinfo['num_user'] = $db->select_count_row('sq_user',array('appid'=>$value['ID']),'AND');
            $newinfo['num_online'] = $db->select_count_row('sq_user','appid='.$value['ID'].' AND htime>'.(time()-$value['onlinesecond']),'AND');
            $newinfo['bdqq'] = '<input type="checkbox" value="'.$value['ID'].'" name="bdqq" title="绑定QQ"'.($value['bindqq'] == 1? ' checked' : '').'>';
            $newinfo['bdip'] = '<input type="checkbox" value="'.$value['ID'].'" name="bdip" title="绑定IP"'.($value['bindip'] == 1? ' checked' : '').'>';
            $newinfo['bdjq'] = '<input type="checkbox" value="'.$value['ID'].'" name="bdjq" title="绑定设备"'.($value['bindmac'] == 1? ' checked' : '').'>';
            $newinfo['zxjc'] = '<input type="checkbox" value="'.$value['ID'].'" name="zxjc" title="在线检测"'.($value['onlinecheck'] == 1? ' checked' : '').'>';
            $newinfo['yxhb'] = '<input type="checkbox" value="'.$value['ID'].'" name="yxhb" title="允许换绑"'.($value['allowchange'] == 1? ' checked' : '').'>';
            $newinfo['yxjb'] = '<input type="checkbox" value="'.$value['ID'].'" name="yxjb" title="允许解绑"'.($value['allowunbind'] == 1? ' checked' : '').'>';
            $newinfo['zqkc'] = $value['cycreduce'];
            $newinfo['sqfs'] = $value['logintype'];
            $newinfo['syfs'] = $value['usetype'];
            $newinfo['jbkc'] = $value['unbindreduce'];
            $newinfo['czmz'] = $value['rechargeface'];
            $newinfo['zxms'] = $value['onlinesecond'];
            $newinfo['zczs'] = $value['reggive'];
            $newinfo['yygg'] = htmlspecialchars($value['notice']);
            $newinfo['yybb'] = $value['ver'];
            $newinfo['gxrz'] = htmlspecialchars($value['uplog']);
            $newinfo['gxdz'] = htmlspecialchars($value['upurl']);
            $newinfo['zdysj'] = $value['data'];
            $newinfo['connectkey'] = $value['connectkey'];
            $newinfo['decryptkey'] = $value['decryptkey'];
            $newinfo['qzgx'] =  '<input type="checkbox" value='.$value['ID'].' name="qzgx" lay-skin="switch" lay-text="开启|关闭" id="qzgx"'.($value['forceup'] == 1? ' checked' : '').'>';
            $newinfo['mfms'] =  '<input type="checkbox" value='.$value['ID'].' name="mfms" lay-skin="switch" lay-text="开启|关闭" id="mfms"'.($value['free'] == 1? ' checked' : '').'>';
            $newinfo['gbyy'] =  '<input type="checkbox" value='.$value['ID'].' name="gbyy" lay-skin="switch" id="gbyy"'.($value['close'] == 1? ' checked' : '').'>';
            $backinfo['data'][] = $newinfo;
        }
        die(json_encode($backinfo));
        break;
    case 'savechange':
        switch ($_POST['mod']){
            case 'bdip':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('bindip'=>textbooltonum($_POST['status'])))){
                    die($db->geterror());
                }else{
                    die('绑定IP设置保存成功');
                }
                break;
            case 'bdjq':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('bindmac'=>textbooltonum($_POST['status'])))){
                    die($db->geterror());
                }else{
                    die('绑定机器设置保存成功');
                }
                break;
            case 'bdqq':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('bindqq'=>textbooltonum($_POST['status'])))){
                    die($db->geterror());
                }else{
                    die('绑定QQ设置保存成功');
                }
                break;
            case 'zxjc':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('onlinecheck'=>textbooltonum($_POST['status'])))){
                    die($db->geterror());
                }else{
                    die('在线检测设置保存成功');
                }
                break;
            case 'yxhb':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('allowchange'=>textbooltonum($_POST['status'])))){
                    die($db->geterror());
                }else{
                    die('允许换绑设置保存成功');
                }
                break;
            case 'yxjb':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('allowunbind'=>textbooltonum($_POST['status'])))){
                    die($db->geterror());
                }else{
                    die('允许解绑设置保存成功');
                }
                break;
            case 'qzgx':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('forceup'=>textbooltonum($_POST['status'])))){
                    die($db->geterror());
                }else{
                    die('强制更新设置保存成功');
                }
                break;
            case 'gbyy':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('close'=>textbooltonum($_POST['status'])))){
                    die($db->geterror());
                }else{
                    die('应用状态设置保存成功');
                }
                break;
            case 'mfms':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('free'=>textbooltonum($_POST['status'])))){
                    die($db->geterror());
                }else{
                    if (textbooltonum($_POST['status'])){
                        die('免费模式设置保存成功<br>免费模式开启后可以通过任何数据(包括空)登录授权');
                    }
                    die('免费模式设置保存成功');
                }
                break;
            case 'app_name':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('appname'=>$_POST['status']))){
                    die($db->geterror());
                }else{
                    die('应用名称设置保存成功');
                }
                break;
            case 'zqkc':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('cycreduce'=>$_POST['status']))){
                    die($db->geterror());
                }else{
                    die('周期扣除设置保存成功');
                }
                break;
            case 'jbkc':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('unbindreduce'=>$_POST['status']))){
                    die($db->geterror());
                }else{
                    die('解绑扣除设置保存成功');
                }
                break;
            case 'czmz':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('rechargeface'=>$_POST['status']))){
                    die($db->geterror());
                }else{
                    die('充值面值设置保存成功');
                }
                break;
            case 'zxms':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('onlinesecond'=>$_POST['status']))){
                    die($db->geterror());
                }else{
                    die('在线秒数设置保存成功');
                }
                break;
            case 'yygg':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('notice'=>$_POST['status']))){
                    die($db->geterror());
                }else{
                    die('应用公告设置保存成功');
                }
                break;
            case 'yybb':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('ver'=>$_POST['status']))){
                    die($db->geterror());
                }else{
                    die('应用版本设置保存成功');
                }
                break;
            case 'gxrz':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('uplog'=>$_POST['status']))){
                    die($db->geterror());
                }else{
                    die('更新日志设置保存成功');
                }
                break;
            case 'gxdz':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('upurl'=>$_POST['status']))){
                    die($db->geterror());
                }else{
                    die('更新地址设置保存成功');
                }
                break;
            case 'zdysj':
                if(!$db->update('sq_apps',array('ID'=>$_POST['appid']),'',array('data'=>$_POST['status']))){
                    die($db->geterror());
                }else{
                    die('自定义数据设置保存成功');
                }
                break;
            default:
                die('未知请求模块');
        }
    case 'appkey':
        $info  = $db->select_first_row('sq_apps','connectkey,decryptkey',array('ID'=>$_POST['appid']),'');
        die('Connectkey：'.$info['connectkey'].'<br>Decryptkey：'.$info['decryptkey']);
        break;
    case 'resetkey':
        $txms = rand_str(64);
        $jmms = rand_str(64);
        $db->update('sq_apps',array('ID'=>$_POST['appid']),'AND',array('decryptkey'=>$jmms,'connectkey'=>$txms));
        die('秘钥重置成功，以下是新秘钥：<br>Connectkey：'.$txms.'<br>Decryptkey：'.$jmms);
        break;
    case 'delapp':
        if (empty($_POST['appid'])){
            die('非法提交');
        }
        if (!($db->delete('sq_user',array('appid'=>$_POST['appid']),'AND'))){
            echo('用户列表删除失败！<br>');
        }else{
            echo '应用的用户列表已清空<br>';
        }
        if (!($db->delete('sq_fidlist',array('appid'=>$_POST['appid']),'AND'))){
            echo('商品列表删除失败！<br>');
        }else{
            echo '应用的商品列表已清空<br>';
        }
        if (!($db->delete('sq_level',array('appid'=>$_POST['appid']),'AND'))){
            echo('代理级别列表删除失败！<br>');
        }else{
            echo '代理级别列表已清空<br>';
        }
        if (!($db->delete('sq_agent',array('appid'=>$_POST['appid']),'AND'))){
            echo('代理列表删除失败！<br>');
        }else{
            echo '应用的代理列表已清空<br>';
        }
        if (!($db->delete('sq_token',array('appid'=>$_POST['appid']),'AND'))){
            echo('Token数据清空失败！<br>');
        }else{
            echo 'Token数据已清空<br>';
        }

        if (!($db->delete('sq_token',array('appid'=>$_POST['appid']),'AND'))){
            echo('云黑列表清空失败！<br>');
        }else{
            echo '该应用云黑列表已清空<br>';
        }

        if (!$db->delete('sq_apps',array('ID'=>$_POST['appid']),'AND')){
            echo('删除失败 '.$db->geterror());
        }else{
            die('应用已成功删除');
        }

        break;
    case 'user_applist':
        $back = $db->select_all_row('sq_apps','ID,appname',array(),'AND');
        if (count($back) === 0){
            die();
        }
        $i = '<option></option>';
        foreach ($back as $value){
            $i .= '<option value="'.$value['ID'].'">'.$value['appname'].'</option>';
        }
        die($i);
        break;
    case 'getgradelist':
        $where = array();

        $backinfo['code'] = 0;
        $backinfo['count'] = $db->select_count_row('sq_level',array(),'AND');
        include "../function/function_app.php";

        if(!$arr = $db->select_limit_row('sq_level','*',($_GET['page'] - 1) * $_GET['limit'] , $_GET['limit'], array(), 'AND')){
            //$backinfo['code'] = -1;
            $backinfo['msg'] = $db->geterror();
            die(json_encode($backinfo));
        }
        foreach ($arr as $key => $value){
            //$appinfo = app_idgetinfo($value['appid']);
            $array = explode(',',$value['appid']);
            $appname = '';
            foreach ($array as $appid){
                if ($appname != ''){
                    $appname .= '，'.app_idgetname($appid);
                }else{
                    $appname = app_idgetname($appid);
                }
            }
            $arr[$key]['appname'] = $appname;
        }
        $backinfo['data'] = $arr;
        die(json_encode($backinfo));
        break;
    case 'getuserlist':
        if (empty($_GET['appid'])){
            die(json_encode(array('code'=>'-2','msg'=>'请先选择一个应用(如果应用过多，可点击上方下拉选择框后，输入关键词可快速查找应用)')));
        }
        //$where['appid'] = $_GET['appid'];
        if (!empty($_GET['search'])){
            $where['username'] = $where['password'] = $where['mac'] = $where['rip'] = $where['lip'] = $where['uqq'] = $where['mail'] = $where['balance'] = $where['rqq'] = $_GET['search'];
            //$where['uqq'] = intval($where['uqq'])
            if (!is_numeric($where['uqq'])){
                unset($where['uqq']);
            }
            if (!is_numeric($where['rqq'])){
                unset($where['rqq']);
            }
            if (!is_numeric($where['balance'])){
                unset($where['balance']);
            }
            $whereinfo = 'appid='.intval($_GET['appid']).' AND ('. $db->wheretosql($where,'OR').')';
        }else{
            $whereinfo = 'appid='.intval($_GET['appid']);
        }
        //echo $whereinfo;
        $backinfo['code'] = 0;
        $backinfo['count'] = $db->select_count_row('sq_user',$whereinfo,'AND');
        include "../function/function_app.php";
        $appinfo = app_idgetinfo($_GET['appid']);
        if(!$arr = $db->select_limit_row('sq_user','*',($_GET['page'] - 1) * $_GET['limit'] , $_GET['limit'], $whereinfo, 'AND','ORDER BY ID DESC')){
            //$backinfo['code'] = -1;
            $backinfo['msg'] = $db->geterror();
            die(json_encode($backinfo));
        }

        $backinfo['msg'] = '';

        foreach ($arr as $value){
            $newinfo = array();
            $newinfo['id'] = $value['ID'];
            $newinfo['username'] = $value['username'];
            $newinfo['password'] = $value['password'];
            $newinfo['mac'] = $value['mac'];
            $newinfo['lip'] = $value['lip'];
            $newinfo['rip'] = $value['rip'];
            $newinfo['uqq'] = $value['uqq'];
            $newinfo['mail'] = $value['mail'];
            $newinfo['rtime'] = Get_Date($value['rtime']);
            $newinfo['ltime'] = Get_Date($value['ltime']);
            if ($appinfo['usetype'] === 'dqsj'){
                if (!empty($value['balance'])){
                    if ($value['balance'] == '-1'){
                        $value['balance'] = '永久使用';
                    }
                    $value['balance'] = Get_Date($value['balance']);
                }else{
                    $value['balance']='-';
                }
            }
            $newinfo['balance'] = $value['balance'];
            $newinfo['rqq'] = htmlspecialchars($value['rqq']);
            if ((time()-$value['htime']) < $appinfo['onlinesecond']){
                $online = '在线';
            }else{
                $online = '离线';
            }
            $newinfo['login'] = $online;
            $newinfo['status'] = '<input type="checkbox" value='.$value['ID'].' name="status" lay-skin="switch" lay-text="正常|冻结" id="qzgx"'.($value['status'] == 1? ' checked' : '').'>';

            $newinfo['origin'] = GetOriginText($value['origin']);

            $newinfo['oid'] = $value['oid'];
            $backinfo['data'][] = $newinfo;
        }

        //print_r($backinfo);
        die(json_encode($backinfo));

        break;
    case 'changegrade':
        if (empty($_POST['mod'])){
            die('模块标识不能为空');
        }
        if (empty($_POST['gid'])){
            die('等级ID不能为空');
        }
        if (!$db->update('sq_level',array('ID'=>$_POST['gid']),'AND',array($_POST['mod']=>$_POST['value']))){
            die('修改失败'.$db->geterror());
        }else{
            die('修改成功！');
        }

        break;
    case 'changeuser':
        if (empty($_POST['mod'])){
            die('模块标识不能为空');
        }
        if (empty($_POST['userid'])){
            die('用户ID不能为空');
        }
        if ($_POST['mod'] == 'status'){
            $_POST['value'] = textbooltonum($_POST['value']);
        }
        if ($_POST['mod'] == 'balance'){
            if ($_POST['value'] == '永久使用'){
                $_POST['value'] = -1;
            }else{
                $value = strtotime($_POST['value']);
                if (!empty($value)){
                    $_POST['value'] = $value;
                }
            }

        }

        if (!$db->update('sq_user',array('ID'=>$_POST['userid']),'AND',array($_POST['mod']=>$_POST['value']))){
            die('修改失败'.$db->geterror());
        }else{
            die('修改成功！');
        }
        break;
    case 'deluser':
        if (empty($_POST['userid'])){
            die('用户ID不能为空');
        }
        $db->delete('sq_token',array('uid'=>$_POST['userid']),'AND');
        if (!$db->delete('sq_user',array('ID'=>$_POST['userid']),'AND')){
            die('删除失败'.$db->geterror());
        }else{
            die('删除成功！');
        }
        
    case 'dl_applist':
        $back = $db->select_all_row('sq_apps','ID,appname',array(),'AND');
        if (count($back) === 0){
            die();
        }
        $i = '<option value="0">代理全部应用</option>';
        foreach ($back as $value){
            $i .= '<option value="'.$value['ID'].'">'.$value['appname'].'</option>';
        }
        die($i);
        break;
    case 'addfid':
        $_POST = json_decode($_POST['content'],true);
        if(!$db->insert_back_id('sq_fidlist',array('fidname'=>$_POST['fid_name'],'introduce'=>$_POST['fid_introduce'],'appid'=>$_POST['applist'],'buyprice'=>$_POST['fid_price'],'agentprice'=>$_POST['fid_agentprice'],'num'=>$_POST['fid_count'],'openbuy'=>textbooltonum($_POST['openbuy']),'agentbuy'=>textbooltonum($_POST['agentbuy']),'allrechargecard'=>textbooltonum($_POST['keybuy'])))){
            die('添加失败：'.$db->geterror());
        }else{
            die('套餐添加成功！');
        }
        break;
    case 'getfidlist':
        include '../function/function_app.php';
        if(!$result = $db->select_limit_row('sq_fidlist','*',($_GET['page'] - 1) * $_GET['limit'] , $_GET['limit'], array(), 'AND')){

            $backinfo['msg'] = $db->geterror();
            $backinfo['code'] = $backinfo['msg'] == ''? 0 : -1;
            die(json_encode($backinfo));
        }else{
            $info = '';
            $backinfo['code'] = 0;
            $backinfo['msg'] = '';
            $backinfo['count'] = $db->select_count_row('sq_fidlist');
            foreach ($result as $value){
                $value['appname'] = app_idgetname($value['appid']);
                $value['openbuy'] = '<input type="checkbox" value="'.$value['ID'].'" name="openbuy" title="前台购买"'.($value['openbuy'] == 1? ' checked' : '').'>';
                $value['agentbuy'] = '<input type="checkbox" value="'.$value['ID'].'" name="agentbuy" title="代理开通"'.($value['agentbuy'] == 1? ' checked' : '').'>';
                $value['allrechargecard'] = '<input type="checkbox" value="'.$value['ID'].'" name="allrechargecard" title="可用充值卡"'.($value['allrechargecard'] == 1? ' checked' : '').'>';
                $backinfo['data'][] = $value;
            }
            die(json_encode($backinfo));
        }
        break;
    case 'changefid':
        if (empty($_POST['mod'])){
            die('模块标识不能为空');
        }
        if (empty($_POST['fid'])){
            die('分类ID不能为空');
        }
        if ($_POST['mod'] == 'openbuy'){
            $_POST['value'] = textbooltonum($_POST['value']);
        }
        if ($_POST['mod'] == 'agentbuy'){
            $_POST['value'] = textbooltonum($_POST['value']);
        }
        if ($_POST['mod'] == 'allrechargecard'){
            $_POST['value'] = textbooltonum($_POST['value']);
        }
        if ($_POST['mod'] == 'balance'){
            $value = strtotime($_POST['value']);
            if (!empty($value)){
                $_POST['value'] = $value;
            }
        }
        if (!$db->update('sq_fidlist',array('ID'=>$_POST['fid']),'AND',array($_POST['mod']=>$_POST['value']))){
            die('修改失败'.$db->geterror());
        }else{
            die('修改成功！');
        }
        break;
    case 'delfid':
        $db->delete('sq_fidkey',array('fid'=>$_POST['fid']),'AND');
        if(!$db->delete('sq_fidlist',array('ID'=>$_POST['fid']),'AND')){
            die('删除失败：'.$db->geterror());
        }else{
            die('分类删除成功!');
        }
        break;
    case 'addgrade':
        if(!$db->insert_back_id('sq_level',array('lname'=>$_POST['grade_name'],'appid'=>$_POST['app'],'fracture'=>$_POST['grade_present'],'price'=>$_POST['grade_price'],'discount'=>'1'))){
            die('添加失败：'.$db->geterror());
        }else{
            die('代理级别添加成功！');
        }
        break;
    case 'getgrideinfo':
        if (empty($_POST['id'])){
            die('级别为空，无法加载');
        }
        if (!$result = $db->select_first_row('sq_level','*',array('ID'=>$_POST['id']),'AND')){
            die('数据库错误：'.$db->geterror());
        }
        die(json_encode(array(
            'grade_name'=>$result['lname'],
            'grade_present'=>$result['fracture'],
            'grade_price'=>$result['price'],
        )));
        break;
    case 'getlevallist':
        include '../function/function_app.php';
        $list  = '<option></option>';
        $back = $db->select_all_row('sq_level','ID,lname',array(),'AND');
        if (!empty($_POST['aid'])){
            $info = $db->select_first_row('sq_agent','levelid',array('ID'=>$_POST['aid']),'AND');
            print_r($info);
            $lid = $info['levelid'];
        }
        //echo $lid;
        foreach ($back as $value){
            $list.='<option value="'.$value['ID'].'"'.(!empty($_POST['aid']) && $lid == $value['ID'] ? ' selected' : '').'>'.$value['lname'].'</option>';
        }
        die($list);
        break;
    case 'addagent':
        if ($db->select_first_row('sq_agent','*',array('username'=>$_POST['agent_name']),'AND') != false){
            die('该代理账号已经存在，请尝试其他用户名');
        }
        if(!$db->insert_back_id('sq_agent',array('username'=>$_POST['agent_name'],'password'=>$_POST['agent_pass'],'money'=>$_POST['agent_money'],'levelid'=>$_POST['agent_leval'],'begintime'=>time(),'status'=>'1','loginip'=>'-','qq'=>$_POST['agent_qq']))){
            die('添加失败：'.$db->geterror());
        }else{
            die('代理添加成功！');
        }
        break;
    case 'getagentlist':
        include '../function/function_app.php';
        if(!$result = $db->select_limit_row('sq_agent','*',($_GET['page'] - 1) * $_GET['limit'] , $_GET['limit'], array(), 'AND')){
			
            $backinfo['code'] = -1;
            $backinfo['msg'] = $db->geterror();
			if(empty($backinfo['msg'])){
				$backinfo['code'] = 0;
			}
            die(json_encode($backinfo));
        }else{
            $info = '';
            $backinfo['code'] = 0;
            $backinfo['msg'] = '';
            $backinfo['count'] = $db->select_count_row('sq_agent');
            foreach ($result as $value){
                if ($value['logintime'] == 0){
                    $value['logintime'] = '-';
                }
                $value['begintime'] = Get_Date($value['begintime']);
                $value['logintime'] = Get_Date($value['logintime']);
                $value['levelname'] = level_idgetname($value['levelid']);
                $value['status'] = '<input type="checkbox" value='.$value['ID'].' name="status" lay-skin="switch" lay-text="正常|冻结" id="qzgx"'.($value['status'] == 1? ' checked' : '').'>';
                $backinfo['data'][] = $value;
            }
            die(json_encode($backinfo));
        }
        break;
    case 'coldagent':
        if (!$db->update('sq_agent',array('ID'=>$_POST['agentid']),'AND',array('status'=>0))){
            die('冻结失败'.$db->geterror());
        }else{
            die('冻结成功！');
        }
        break;
    case 'uncoldagent':
        if (!$db->update('sq_agent',array('ID'=>$_POST['agentid']),'AND',array('status'=>1))){
            die('解冻失败'.$db->geterror());
        }else{
            die('解冻成功！');
        }
        break;
    case 'getagentinfo':
        $result = $db->select_first_row('sq_agent','*',array('ID'=>$_POST['id']),'AND');
        die(json_encode(array(
            'agent_leval'=>$result['levelid'],
            'agent_money'=>$result['money'],
            'agent_name'=>$result['username'],
            'agent_pass'=>$result['password']

        )));
        break;
    case 'changeagent':
//        if (!$db->update('sq_agent',array('ID'=>$_POST['id']),'AND',array('username'=>$_POST['agent_name'],'password'=>$_POST['agent_pass'],'money'=>$_POST['agent_money'],'levelid'=>$_POST['agent_leval'],'begintime'=>time(),'status'=>'1','loginip'=>'-'))){
//            die('修改失败'.$db->geterror());
//        }else{
//            die('修改成功！');
//        }
        if (empty($_POST['mod'])){
            die('模块标识不能为空');
        }
        if (empty($_POST['aid'])){
            die('代理ID不能为空');
        }
        if ($_POST['mod'] == 'status') $_POST['value'] = textbooltonum($_POST['value']);
        if (!$db->update('sq_agent',array('ID'=>$_POST['aid']),'AND',array($_POST['mod']=>$_POST['value']))){
            die('修改失败'.$db->geterror());
        }else{
            die('修改成功！');
        }
        break;
    case 'delgrade':
        if (empty($_POST['gid'])){
            die('非法提交');
        }
        $db->delete('sq_agent',array('levelid'=>$_POST['gid']),'AND');
        if (!$db->delete('sq_level',array('ID'=>$_POST['gid']),'AND')){
            die('删除失败 '.$db->geterror());
        }else{
            die('删除代理级别成功');
        }
        break;
    case 'delagent':
        if (empty($_POST['aid'])){
            die('非法提交');
        }
        if (!$db->delete('sq_agent',array('ID'=>$_POST['aid']),'AND')){
            die('删除失败 '.$db->geterror());
        }else{
            die('删除代理成功');
        }
        break;
    case 'saveepayset':
        updateset('pay_domain',$_POST['pay_domain']);
        updateset('epay_pid',$_POST['epay_pid']);
        updateset('epay_key',$_POST['epay_key']);
        updateset('epay_againcheck',textbooltonum($_POST['epay_againcheck']));
        die('修改成功！');
        break;
    case 'savesysset':
        updateset('alipaytype',$_POST['alipaytype']);
        updateset('qqpaytype',$_POST['qqpaytype']);
        updateset('wxpaytype',$_POST['wxpaytype']);
        updateset('kqxt',textbooltonum($_POST['kqxt']));
        updateset('yjtx',textbooltonum($_POST['yjtx']));
        updateset('xtsy',textbooltonum($_POST['xtsy']));
        die('修改成功！');
        break;
    case 'getsysset':

        die(json_encode($G['config']));
        break;
    case 'savesignset':
        updateset('alipay_id',$_POST['alipay_id']);
        updateset('alipay_rsa',$_POST['alipay_rsa']);
        updateset('alipay_pkey',$_POST['alipay_pkey']);
        updateset('tenpay_id',$_POST['tenpay_id']);
        updateset('qqpay_key',$_POST['qqpay_key']);
        die('修改成功！');
        break;
    case 'savesiteinfo':
        updateset('sitename',$_POST['sitename']);
        updateset('adminqq',$_POST['adminqq']);
        updateset('adminmail',$_POST['adminmail']);
        updateset('beian',$_POST['beian']);
        updateset('weburl',$_POST['weburl']);
        die('保存成功');
        break;
    case 'addadmin':
        if (empty($_POST['addusername']) || empty($_POST['adduserpass']) || empty($_POST['adduserqq'])){
            die('数据不能为空，请检查');
        }
        $db->insert_back_id('sq_admin',array('username'=>$_POST['addusername'],'password'=>md5($_POST['adduserpass']),'qq'=>$_POST['adduserqq']));
        die('管理员新增成功，如果需要删除请进数据库中的表sq_admin进行删除');
        break;
    case 'sendtestmail':
		$T['title'] = '测试邮件';
        $T['content'][] = '收到此邮件表示您的配置正常可用！';
        $MailTips[] = $T;
        
        if (!SendTipsMail($G['config']['adminmail'],$MailTips,$back)){
            die('请求超时或返回的数据解析失败：'.$back);
        }else{
			die('邮件已发送至您开通授权时候设置的站长邮箱，请检查收件箱和垃圾箱是否已收到文件');
        }
        break;
    case 'savemailset':
        updateset('smtp_address',$_POST['smtp_address']);
        updateset('smtp_port',$_POST['smtp_port']);
        updateset('smtp_user',$_POST['smtp_user']);
        updateset('smtp_pass',$_POST['smtp_pass']);
        die('保存成功');
        break;
    case 'savenotice':
        updateset('agentnotice',$_POST['agentnotice']);
        updateset('mainnav',$_POST['mainnav']);
        updateset('maindrawer',$_POST['maindrawer']);
        die('保存成功');
        break;
    case 'creatkey':
        $count = $_POST['sc_count'];
        if ($count <= 0){
            die(json_encode(array('code'=>'-1','msg'=>'生成的数量不能为空或者0或者负数')));
        }
        $time = time();
        $money = $_POST['sc_money'];
        if ($money <= 0){
            die(json_encode(array('code'=>'-1','msg'=>'卡密金额不能为空或者0或者负数')));
        }
        $keylist='';
        for ($x=1; $x<=$_POST['sc_count']; $x++) {
            $key = substr($time,0,6).'-'.rand_str(6).'-'.rand_str(6).'-'.rand_str(6).'-'.rand_str(6).'-'.$money;
            $insarray[] = "'{$key}','{$time}','0','{$money}','{$money}','0','1'";
            $keylist .= '<br>'.$key;
        }
        $sign = rand_str(32);
        $_SESSION['cards'][$sign] = str_replace('<br>',"\r\n",$keylist);
        if(!$num = $db->insert_back_row('sq_key',array('kami','creattime','firstusetime','allmoney','lastmoney','lastusetime','status'),$insarray)){
            die(json_encode(array('code'=>'-1','msg'=>'数据库错误：'.$db->geterror())));
        }else{
            die(json_encode(array('code'=>'1','sign'=>$sign,'keys'=>'您的卡密如下：'.$keylist)));
        }
        break;
    case 'download':
        if (empty($_SESSION['cards'][$_GET['sign']])){
            die('该Sign不存在或者已过期，无法下载卡密');
        }else{
            $filename = $_GET['sign'].".txt";
            header('Content-Type:text/plain'); //指定下载文件类型
            header('Content-Disposition: attachment; filename="'.$filename.'"'); //指定下载文件的描述
            header('Content-Length:'.strlen($_SESSION['cards'][$_GET['sign']])); //指定下载文件的大小
            die($_SESSION['cards'][$_GET['sign']]);
        }
        break;
    case 'getkeylist':
        $where = array();
        if (!empty($_GET['search'])){
            $where['kami'] = $_GET['search'];
        }
        $backinfo['code'] = 0;
        $backinfo['count'] = $db->select_count_row('sq_key',$where,'AND');
        if(!$arr = $db->select_limit_row('sq_key','*',($_GET['page'] - 1) * $_GET['limit'] , $_GET['limit'], $where, 'AND','ORDER BY ID DESC')){
            $backinfo['msg'] = $db->geterror();
            die(json_encode($backinfo));
        }
        foreach ($arr as $key => $value){
            if ($value['creattime'] == 0){
                $arr[$key]['creattime'] = '-';
            }else{
                $arr[$key]['creattime'] = Get_Date($value['creattime']);
            }

            if ($value['firstusetime'] == 0){
                $arr[$key]['firstusetime'] = '-';
            }else{
                $arr[$key]['firstusetime'] = Get_Date($value['firstusetime']);
            }
            if ($value['lastusetime'] == 0){
                $arr[$key]['lastusetime'] = '-';
            }else{
                $arr[$key]['lastusetime'] = Get_Date($value['lastusetime']);
            }
            $arr[$key]['status'] = '<input type="checkbox" value='.$value['ID'].' name="status" lay-skin="switch" lay-text="正常|冻结" id="qzgx"'.($value['status'] == 1? ' checked' : '').'>';
        }
        $backinfo['data'] = $arr;
        die(json_encode($backinfo));
        break;
    case 'keystatus':
        if (empty($_POST['keyid'])){
            die(makejson(-1,'keyid不能为空'));
        }
        if(!$db->update('sq_key',array('ID'=>$_POST['keyid']),'AND',array('status'=>textbooltonum($_POST['status'])))){
            die(makejson(-2,'修改失败或者没有任何更改'.$db->geterror()));
        }else{
            die(makejson(1,'状态保存成功'));
        }
    case 'sitelog':
        $backinfo['pages'] = ceil($db->select_count_row('sq_log_system')/50);
        $backinfo['data'] = array();
        if (!$result = $db->select_limit_row('sq_log_system','*',($_GET['page']-1)*50,50,'',''," ORDER BY time DESC")){
            $backinfo['data'][] = $db->geterror();
            die(json_encode($backinfo));
        }
        foreach ($result as $value){
            $new['content'] = $value['msg'];
            $new['time'] = Get_Date($value['time']);
            switch ($value['type']){
                case 'success':
                    $new['icon'] = '&#x1005;';
                    $new['color'] = 'green';
                    break;
                case 'info':
                    $new['icon'] = '&#xe617;';
                    $new['color'] = 'blue';
                    break;
                case 'warning':
                    $new['icon'] = '&#xe702;';
                    $new['color'] = 'yellow';
                    break;
                case 'danger':
                    $new['icon'] = '&#x1007;';
                    $new['color'] = 'red';
                    break;
                default:
                    $new['icon'] = '&#xe617;';
                    $new['color'] = 'blue';
                    break;
            }
            $backinfo['data'][] = $new;
        }
        die(json_encode($backinfo));
        break;
    case 'coldkey':
        if (!$db->update('sq_key',array('ID'=>$_POST['keyid']),'AND',array('status'=>0))){
            die('冻结失败'.$db->geterror());
        }else{
            die('冻结成功！');
        }
        break;
    case 'uncoldkey':
        if (!$db->update('sq_key',array('ID'=>$_POST['keyid']),'AND',array('status'=>1))){
            die('解冻失败'.$db->geterror());
        }else{
            die('解冻成功！');
        }
        break;
    case 'delkey':
        if (empty($_POST['keyid'])){
            die(makejson(-1,'卡密ID不能为空'));
        }
        if (!$db->delete('sq_key',array('ID'=>$_POST['keyid']),'AND')){
            die(makejson(-2,'删除失败 '.$db->geterror()));
        }else{
            die(makejson(1,'删除成功！'));
        }
        break;
    case 'gettradelist':
        $where = array();
        if (!empty($_GET['search'])){
            $where['tradeno'] = $where['name'] = $where['user'] = $where['mail'] = $where['kami'] = $_GET['search'];
        }
        $backinfo['code'] = 0;
        $backinfo['count'] = $db->select_count_row('sq_trade',$where,'OR');
        if(!$arr = $db->select_limit_row('sq_trade','*',($_GET['page'] - 1) * $_GET['limit'] , $_GET['limit'], $where, 'OR','ORDER BY ID DESC')){
            $backinfo['msg'] = $db->geterror();
            die(json_encode($backinfo));
        }
        foreach ($arr as $key => $value){
            if ($value['begintime'] == 0){
                $arr[$key]['begintime'] = '-';
            }else{
                $arr[$key]['begintime'] = Get_Date($value['begintime']);
            }
            if ($value['overtime'] == 0){
                $arr[$key]['overtime'] = '-';
            }else{
                $arr[$key]['overtime'] = Get_Date($value['overtime']);
            }
            if ($value['paytype'] == 'zxzf'){
                switch ($value['onlinepaytype']){
                    case 'zfb':
                        $arr[$key]['paytype'] = '支付宝';
                        break;
                    case 'wx':
                        $arr[$key]['paytype'] = '微信';
                        break;
                    case 'qq':
                        $arr[$key]['paytype'] = 'QQ钱包';
                        break;
                    default:
                        $arr[$key]['paytype'] = '异常?';
                        break;
                }
            }else if ($value['paytype'] == 'czkm'){
                $arr[$key]['paytype'] = '余额卡密';
            }else if ($value['paytype'] == 'yezf'){
                $arr[$key]['paytype'] = '代理余额';
            }else{
                $arr[$key]['paytype'] = '异常?';
            }
            if ($value['status'] == 1){
                $arr[$key]['status'] = '等待付款';
                $button = '<button type="button" class="layui-btn layui-btn-xs layui-btn-normal" onclick="suppletrade(\''.$value['ID'].'\');">补单</button>';
            }else if ($value['status'] == 2){
                $arr[$key]['status'] = '等待验证';
                $button = '<button type="button" class="layui-btn layui-btn-xs layui-btn-normal" onclick="suppletrade(\''.$value['ID'].'\');">补单</button>';
            }elseif ($value['status'] == 3){
                $button = '';
                $arr[$key]['status'] = '完成';
            }else{
                $arr[$key]['status'] = '异常';
            }
            $arr[$key]['tools'] = $button.'<button type="button" class="layui-btn layui-btn-xs layui-btn-danger" onclick="deltrade(\''.$value['ID'].'\');">删除</button>';
        }
        $backinfo['data'] = $arr;
        die(json_encode($backinfo));
        break;

    case 'suppletrade':
        if (!$tradeinfo = $db->select_first_row('sq_trade','*',array('ID'=>$_POST['id']),'AND')){
            die('无法找到订单信息！');
        }
        $tradeno = $tradeinfo['tradeno'];
        if ($tradeinfo['status'] == '3'){
            die('该订单已完成，无需补单！');
        }
        $db->update('sq_trade',array('ID'=>$_POST['id']),'AND',array('status'=>3));
        $tradeinfo['status'] = 2;
        include '../function/trade.inc.php';
        $back = trade_do($tradeinfo);
        //print_r($back);
        $back = json_decode($back,true);
        if ($back['code'] == 2){
            die('成功生成卡密：'.$back['kami']);
        }

        die($back['msg']);
        break;
    case 'deltrade':
        if (!$db->delete('sq_trade',array('ID'=>$_POST['id']),'AND')){
            die('订单删除失败'.$db->geterror());
        }else{
            die('订单删除成功！');
        }
        break;
    case 'clearalltradeid':
        $limit = time() - 3600;
        if (!$db->delete('sq_trade','begintime<'.$limit,'AND')){
            die('订单删除失败'.$db->geterror());
        }else{
            die('订单删除成功！');
        }
        break;
    case 'clearnopay':
        $limit = time() - 3600;
        if (!$db->delete('sq_trade','begintime<'.$limit.' AND status <> 2 AND status <> 3','AND')){
            die('订单删除失败'.$db->geterror());
        }else{
            die('订单删除成功！');
        }
        break;
    case 'clearbeenpay':
        $limit = time() - 3600;
        if (!$db->delete('sq_trade',array('status'=>3),'AND')){
            die('订单删除失败'.$db->geterror());
        }else{
            die('订单删除成功！');
        }
        break;
    case 'changepassword':
        //die('本站点禁止修改密码');
        if (empty($_POST['oldpass']) || empty($_POST['newpass']) || empty($_POST['renewpass'])){
            die('请填写完所有数据！');
        }
        if ($_POST['newpass'] != $_POST['renewpass']){
            die('两次输入的密码一样，无法修改！');
        }
        if (!$agentinfo = $db->select_first_row('sq_admin','*',array('username'=>$_SESSION['admin_username']),'AND')){
            die('管理员信息获取失败');
        }
        if (md5($_POST['oldpass']) !== $agentinfo['password']){
            die('输入的原密码错误，无法修改！');
        }

        if(!$db->update('sq_admin',array('username'=>$_SESSION['admin_username']),'AND',array('password'=>md5($_POST['newpass'])))){
            die('密码修改失败，服务器内部发生错误');
        }else{
            die('管理密码修改成功，请使用新密码进行登录！');
        }

        break;
    case 'checkupdate':
		die(-1);
        $newver = curl_request('http://download.bwenquan.com/update/shouquanver.txt');

        if ($newver > $G['siteinfo']['ver'] ){
            die('1');
        }else{
            die('-1');
        }

        break;
    case 'downloadupdatepacks':
        set_time_limit(0);
        if(!getFile('http://download.wenquan6.cn/upload/shouquan/'.$G['siteinfo']['ver'].'.zip','../update/',$G['siteinfo']['ver'].'.zip')){
            die(json_encode(array('code'=>'-1','msg'=>'下载文件失败！')));
        }else{
            die(json_encode(array('code'=>'1')));
        }
        break;
    case 'unzippacks':
        set_time_limit(0);
        if (!file_exists('../update/'.$G['siteinfo']['ver'].'.zip')){
            die(json_encode(array('code'=>'-1','msg'=>'更新包文件不存在，无法解压')));
        }
        get_zip_originalsize('../update/'.$G['siteinfo']['ver'].'.zip','../update/');
        die(json_encode(array('code'=>'1','updateurl'=>'../../update/'.$G['siteinfo']['ver'].'/install.php')));
        break;
    case 'keylog':
        if (!$result = $db->select_limit_row('sq_log_kami','*','',0,array('keyid'=>$_POST['keyid']),'AND',"ORDER BY time DESC")){
            die(makejson(-1,'数据库中没有记录'));
        }

        die(makejson(1,'success',array('items'=>$result)));
        break;
    case 'loadbuybody':
        include "../function/function_app.php";
        if(!$appinfo = app_idgetinfo($_POST['appid'])){
            die();
        }

        $output = '';
        //$output = '<br><form class="form-horizontal" style="width: 90%">';
        if ($appinfo['logintype'] == 'zhmm'){
            $output .= '
    <div class="layui-form-item">
        <label class="layui-form-label">用户名</label>
        <div class="layui-input-block">
            <input type="text" name="kt_user" required  lay-verify="required" placeholder="开通/续费的用户名" autocomplete="off" class="layui-input">
        </div>
    </div>
    
    <div class="layui-form-item">
        <label class="layui-form-label">密码</label>
        <div class="layui-input-block">
            <input type="text" name="kt_pass" placeholder="开通的密码，如果是续费这里可留空" autocomplete="off" class="layui-input">
        </div>
    </div>
    ';
            $type = 1;
        }else if ($appinfo['logintype'] == 'kmsq'){
            $output .= '
    <div class="layui-form-item">
        <label class="layui-form-label">授权卡密</label>
        <div class="layui-input-block">
             <input type="text" name="kt_user" placeholder="输入为续费，留空为新开" autocomplete="off" class="layui-input">
        </div>
    </div>';
            $type = 2;
        }else if ($appinfo['logintype'] == 'jcbd'){
            if ($appinfo['bindqq'] == '1'){
                $output .= '
    <div class="layui-form-item">
        <label class="layui-form-label">机器人QQ</label>
        <div class="layui-input-block">
            <input type="text" name="kt_robotqq" placeholder="" required  lay-verify="required" autocomplete="off" class="layui-input">
        </div>
    </div>
    ';
            }
            if ($appinfo['bindmac'] == '1'){
                $output .= '<div class="layui-form-item">
        <label class="layui-form-label">设备码</label>
        <div class="layui-input-block">
            <input type="text" name="kt_mac" placeholder="" required  lay-verify="required" autocomplete="off" class="layui-input">
        </div>
    </div>
    ';
            }
            if ($appinfo['bindip'] == '1'){
                $output .= '<div class="layui-form-item">
        <label class="layui-form-label">IP地址</label>
        <div class="layui-input-block">
            <input type="text" name="kt_ip" placeholder="" required  lay-verify="required" autocomplete="off" class="layui-input">
        </div>
    </div>
    ';
            }
            $type = 3;
        }


        $output .= '<div class="layui-form-item">
        <label class="layui-form-label">开通数量</label>
        <div class="layui-input-block">
            <input type="text" name="kt_num" placeholder="" required  lay-verify="required|number" autocomplete="off" class="layui-input">
            <div class="layui-form-mid layui-word-aux">如果是余额方式则为余额点数，如果是到期时间方式则为分钟数量(1天=1440，30天=43200，180天=259200，365天=525600)，不限授权请填-1</div>
        </div>
         
    </div>
    
    <div class="layui-form-item">
        <label class="layui-form-label">用户邮箱</label>
        <div class="layui-input-block">
            <input type="text" name="kt_mail" placeholder="" required  lay-verify="required" autocomplete="off" class="layui-input">
        </div>
    </div>
     <div class="layui-form-item">
        <label class="layui-form-label">主人QQ</label>
        <div class="layui-input-block">
            <input type="text" name="kt_adminqq" placeholder="" required  lay-verify="required|number" autocomplete="off" class="layui-input">
        </div>
    </div>
';

        die($output);
        break;
    case 'buy_submit':
        include "../function/function_app.php";
        $_POST = json_decode($_POST['content'],true);
        if (!$appinfo = app_idgetinfo($_POST['id'])){
            die('应用信息获取失败');
        }
        if (empty($_POST['kt_adminqq'])){
            die('用户QQ不能为空');
        }
        if (empty($_POST['kt_mail'])){
            die('用户邮箱不能为空');
        }
        if (empty($_POST['kt_num'])){
            die('开通的数量不能为空');
        }
        $balance = $_POST['kt_num'];
        include '../function/function_auth.php';
        if(!isset($_POST['kt_ip'])){
            $_POST['kt_ip'] = '';
        }
        if(!isset($_POST['kt_robotqq'])){
            $_POST['kt_robotqq'] = '';
        }
        if(!isset($_POST['kt_mac'])){
            $_POST['kt_mac'] = '';
        } 
        if ($appinfo['logintype'] == 'jcbd'){
            $_POST['kt_user'] = json_encode(array(
                'lip'=>$_POST['kt_ip'],
                'rqq'=>$_POST['kt_robotqq'],
                'mac'=>$_POST['kt_mac']
            ));
        }
        if (empty($_POST['kt_pass'])){
            $_POST['kt_pass'] = '';
        }
        $back = auth_add($_POST['kt_user'],$_POST['kt_pass'],'',$_POST['kt_num'],$_POST['kt_adminqq'],$_POST['kt_mail'],$_POST['id'],1,$_SESSION['admin_id'],$newkey,$tips,array());
        if ($back == 3){
            die('成功生成卡密：'.$newkey);
        }else if ($back == 1){
            die('授权开通成功');
        }else if ($back == 2){
            die('授权续费成功');
        }else{
            die($tips);
        }
        break;
    case 'agentlog':
        if (!$result = $db->select_limit_row('sq_log_agent','*','',0,array('aid'=>$_POST['aid']),'AND',"ORDER BY time DESC")){
            die('数据库中没有记录！');
        }
        foreach ($result as $value){
            echo '['.Get_Date($value['time']).'] '.$value['msg'].'<br>';
        }
        echo '=======================<br>';
        die('只列出最近五十条记录，若想查看更多，请前往数据库执行这行语句查询：[SELECT * FROM sq_log_agent WHERE aid='.$_POST['agentid'].']');
        break;
    case 'saveintroduce':
        if (!$db->update('sq_apps',array('ID'=>$_POST['appid']),'AND',array('introduce'=>$_POST['content']))){
            die("保存失败".$db->geterror());
        }else{
            die('保存成功');
        }
    case 'introduce':
        if(!$appinfo = $db->select_first_row('sq_apps','introduce',array('ID'=>$_POST['appid']),'AND')){
            die('服务器内部错误');
        }else{
            die($appinfo['introduce']);
        }
    case 'upload':
        $upload_dir = './img/apps/';

        if(strtolower($_SERVER['REQUEST_METHOD']) != 'post'){
            die('错误的请求');
        }

        if(array_key_exists('file',$_FILES) && $_FILES['file']['error'] == 0 ){
            $pic = $_FILES['file'];
            $pic['name'] = time().'.png';
            if(move_uploaded_file($pic['tmp_name'], '.'.$upload_dir.$pic['name'])){
                if(!$db->update('sq_apps',array('ID'=>$_GET['appid']),'AND',array('imgsrc'=>$upload_dir.$pic['name']))){
                    die($db->geterror());
                }
                die(json_encode(array('code'=>0,'msg'=>'更换成功')));
            }
        }

        die(json_encode(array('code'=>0,'msg'=>$_FILES['file']['error'])));;
    case 'imgsrc':
        if(!$info = $db->select_first_row('sq_apps','imgsrc',array('ID'=>$_POST['appid']),'AND')){
            die($db->geterror());
        }
        if (empty($info['imgsrc'])){
            $info['imgsrc'] = './mdl/dog.png';
        }
        die('../.'.$info['imgsrc']);
    case 'gradelist':

        include '../function/function_app.php';
        $result = $db->select_all_row('sq_level','*','','');
        $data['gradelist_op'] = '<option value="0">请选择一个代理级别进行设置</option>';
        $data['gradelist_ad'] = '';
        //print_r($result);
        foreach ($result as $info){
            $appname = $info['appid'] == 0 ? '代理所有应用' : app_idgetname($info['appid']);

            $data['gradelist_op'] .= '<option value="'.$info['ID'].'">'.$info['lname'].'('.$appname.')'.'</option>';

            $data['gradelist_ad'] .= '<input type="checkbox" id="p'.$info['ID'].'" name="info['.$info['ID'].']" title="'.$info['lname'].'" value="'.$info['ID'].'">';
        }
        die(json_encode($data));
        break;

    case 'setlevel':
        if ($_POST['lid'] == 0){
            die('请先选择需要设置的级别');
        }
        if ($_POST['present'] == 0){
            echo '请注意：您设置了折率为0意味着该等级的代理可以任意免费开通选中等级的代理！<br>';
        }
        if(!$db->update('sq_level',array('ID'=>$_POST['lid']),'AND',array('subordinate'=>$_POST['info'],'discount'=>$_POST['present']))){
            die('数据更新失败：'.$db->geterror());
        }else{
            die('数据保存成功！');
        }
       break;
    case 'getsetlevel':
        if ($_POST['lid'] == 0){
            die(json_encode(array('present'=>'','info'=>'')));
        }
        $info = $db->select_first_row('sq_level','discount,subordinate',array('ID'=>$_POST['lid']),'AND');
        die(json_encode(array('present'=>$info['discount'],'info'=>$info['subordinate'])));
        break;
    case 'getnotice':
        die('GitHub地址：https://github.com/bwenquan/QuanAuth');
        break;
    case 'getuplog':
        die('请访问GitHub本人项目查看');
        break;
    case 'gettoken':
        $result = $db->select_first_row('sq_admin','accesstoken',array('username'=>$_SESSION['admin_username'],'password'=>$_SESSION['admin_password']),'AND');
        die($result['accesstoken']);
        break;
    case 'resettoken':
        $token = rand_str(64);
        $result = $db->update('sq_admin',array('username'=>$_SESSION['admin_username'],'password'=>$_SESSION['admin_password']),'AND',array('accesstoken'=>$token));
        die($token);
        break;
    case 'getbclist':

        if(!$result = $db->select_limit_row('sq_bclist','*',($_GET['page'] - 1) * $_GET['limit'] , $_GET['limit'], array(), 'AND')){

            $backinfo['msg'] = $db->geterror();
            if ($backinfo['msg'] == ''){
                $backinfo['code'] = 0;
            }
            die(json_encode($backinfo));
        }else{
            include_once '../function/function_app.php';
            $info = '';
            $backinfo['code'] = 0;
            $backinfo['msg'] = '';
            $backinfo['count'] = $db->select_count_row('sq_bclist');
            foreach ($result as $value){
                if ($value['appid'] == 0){
                    $value['app'] = '所有应用';
                }else{
                    $value['app'] = app_idgetname($value['appid']);
                }
                $value['time'] = Get_Date($value['time']);
                $backinfo['data'][] = $value;
            }


            die(json_encode($backinfo));
        }
        break;
    case 'addbc':

        if (empty($_POST['appid'])){
            $_POST['appid'] = '0';
        }
        if (empty($_POST['obj'])){
            die(json_encode(array('code'=>-1,'msg'=>'拉黑对象不能为空')));
        }
        if ($db->select_first_row('sq_bclist','ID',array('obj'=>$_POST['obj'],'appid'=>$_POST['appid']),'AND') != false){
            die(json_encode(array('code'=>-2,'msg'=>'拉黑对象已存在于黑名单中')));
        }
        $_POST['time'] = time();
        $_POST['uid'] = $_SESSION['admin_username'];
        if(!$db->insert_back_id('sq_bclist',$_POST)){
            die(json_encode(array('code'=>-3,'msg'=>$db->geterror())));
        }else{
            die(json_encode(array('code'=>1,'msg'=>'添加成功')));
        }

        break;
    case 'vaptcha':

        updateset('opencode',$_POST['vaptcha_open']);
        updateset('codevid',$_POST['vaptcha_id']);
        updateset('codekey',$_POST['vaptcha_key']);
        die('修改成功');
        break;
    case 'delbc':
        if (!$db->delete('sq_bclist',array('ID'=>$_POST['id']),'AND')){
            die(json_encode(array('code'=>-1,'msg'=>'删除失败'.$db->geterror())));
        }else{
            die(json_encode(array('code'=>1,'msg'=>'删除成功')));
        }
        break;
    case 'KeyOperation':
        switch ($_POST['operation']){
            case 'ExportNoUse':
                $result = $db->select_all_row('sq_key','kami',array('firstusetime'=>0,'status'=>1));
                $keylist = '';
                foreach ($result as $value){
                    $keylist .= $value['kami']."\r\n";
                }
                if (empty($keylist)){
                    die(makejson(-4,'导出失败，没有符合的卡密'));
                }
                $sign = rand_str(32);
                $_SESSION['cards'][$sign] = $keylist;
                die(makejson(1,'success',array('sign'=>$sign)));
                break;
            case 'ExportNoMoney':
                $result = $db->select_all_row('sq_key','kami',array('lastmoney'=>0,'status'=>1));
                $keylist = '';
                foreach ($result as $value){
                    $keylist .= $value['kami']."\r\n";
                }
                if (empty($keylist)){
                    die(makejson(-4,'导出失败，没有符合的卡密'));
                }
                $sign = rand_str(32);
                $_SESSION['cards'][$sign] = $keylist;
                die(makejson(1,'success',array('sign'=>$sign)));
                break;
            case 'ExportAll':
                $result = $db->select_all_row('sq_key','kami');
                $keylist = '';
                foreach ($result as $value){
                    $keylist .= $value['kami']."\r\n";
                }
                if (empty($keylist)){
                    die(makejson(-4,'导出失败，没有符合的卡密'));
                }
                $sign = rand_str(32);
                $_SESSION['cards'][$sign] = $keylist;
                die(makejson(1,'success',array('sign'=>$sign)));
                break;
            case 'DelNoMoney':
                $db->delete('sq_key',array('lastmoney'=>0),'AND');

                die(makejson(2,'success',array('nums'=>$db->affected_num())));
                break;
            case 'DelNoUse':
                $db->delete('sq_key',array('firstusetime'=>0),'AND');
                die(makejson(2,'success',array('nums'=>$db->affected_num())));
                break;
            case 'DelAll':
                $db->delete('sq_key',array('1'=>'1'),'AND');
                die(makejson(2,'success',array('nums'=>$db->affected_num())));
                break;
            default:
                die(makejson(-1,'未知操作：'.$_POST['operation']));
        }
        break;
    case 'fidlist_show':
        include_once '../function/function_app.php';
        $res = $db->select_all_row('sq_fidlist');
        $str = '<option></option>';
        foreach ($res as $info){
            $str .='<option value="'.$info['ID'].'">'.$info['fidname'].'('.app_idgetname($info['appid']).')</option>';
        }
        die($str);
        break;
    case 'creatfidkey':
        $count = $_POST['sc_count'];
        if ($count <= 0){
            die(json_encode(array('code'=>'-1','msg'=>'生成的数量不能为空或者0或者负数')));
        }
        $time = time();
        if ($count <= 0){
            die(json_encode(array('code'=>'-1','msg'=>'卡密金额不能为空或者0或者负数')));
        }
        $keylist='';
        for ($x=1; $x<=$_POST['sc_count']; $x++) {
            $key = substr($time,0,6).'-'.rand_str(6).'-'.rand_str(6).'-'.rand_str(6).'-'.rand_str(6).rand_str(6);
            $insarray[] = "'{$key}','{$time}','0','{$_POST['sc_fid']}','1'";
            $keylist .= '<br>'.$key;
        }
        $sign = rand_str(32);
        $_SESSION['cards'][$sign] = str_replace('<br>',"\r\n",$keylist);
        if(!$num = $db->insert_back_row('sq_fidkey',array('kami','creattime','usetime','fid','status'),$insarray)){
            die(json_encode(array('code'=>'-1','msg'=>'数据库错误：'.$db->geterror())));
        }else{
            die(json_encode(array('code'=>'1','sign'=>$sign,'keys'=>'您的卡密如下：'.$keylist)));
        }
        break;
    case 'gettckeylist':
        $where = array();
        if (!empty($_GET['search'])){
            $where['kami'] = $_GET['search'];
        }
        $where['fid'] = $_GET['fid'];
        $backinfo['code'] = 0;
        $backinfo['count'] = $db->select_count_row('sq_fidkey',$where,'AND');
        if(!$arr = $db->select_limit_row('sq_fidkey','*',($_GET['page'] - 1) * $_GET['limit'] , $_GET['limit'], $where, 'AND','ORDER BY ID DESC')){
            $backinfo['msg'] = $db->geterror();
            die(json_encode($backinfo));
        }
        foreach ($arr as $key => $value){
            if ($value['creattime'] == 0){
                $arr[$key]['creattime'] = '-';
            }else{
                $arr[$key]['creattime'] = Get_Date($value['creattime']);
            }

            if ($value['usetime'] == 0){
                $arr[$key]['usetime'] = '-';
            }else{
                $arr[$key]['usetime'] = Get_Date($value['usetime']);
            }

            $arr[$key]['status'] = '<input type="checkbox" value='.$value['ID'].' name="status" lay-skin="switch" lay-text="正常|冻结" id="qzgx"'.($value['status'] == 1? ' checked' : '').'>';
        }
        $backinfo['data'] = $arr;
        die(json_encode($backinfo));

        break;
    case 'FidKeyOperation':
        switch ($_POST['operation']){
            case 'ExportNoUse':
                $result = $db->select_all_row('sq_fidkey','kami',array('usetime'=>0,'status'=>1,'fid'=>$_POST['fid']),'AND');
                $keylist = '';
                foreach ($result as $value){
                    $keylist .= $value['kami']."\r\n";
                }
                if (empty($keylist)){
                    die('没有符合要求的卡密');
                }
                $sign = rand_str(32);
                $_SESSION['cards'][$sign] = $keylist;
                die('您的卡密已就绪，<a href="../ajax.php?mod=download&sign='.$sign.'">点击这里下载卡密</a>');
                break;
            case 'ExportAll':
                $result = $db->select_all_row('sq_fidkey','kami',array('fid'=>$_POST['fid'],'status'=>1));
                $keylist = '';
                foreach ($result as $value){
                    $keylist .= $value['kami']."\r\n";
                }
                if (empty($keylist)){
                    die('没有符合要求的卡密');
                }
                $sign = rand_str(32);
                $_SESSION['cards'][$sign] = $keylist;
                die('您的卡密已就绪，<a href="../ajax.php?mod=download&sign='.$sign.'">点击这里下载卡密</a>');
                break;
            case 'DelNoUse':
                $db->delete('sq_fidkey',array('usetime'=>0,'fid'=>$_POST['fid'],'status'=>1),'AND');
                die('成功删除'.(int)$db->affected_num().'行');
                break;
            case 'DelAll':
                $db->delete('sq_fidkey',array('fid'=>$_POST['fid']),'AND');
                die('成功删除'.(int)$db->affected_num().'行');
                break;
            case 'DelUse':
                $db->delete('sq_fidkey', '`usetime` > 0 AND `fid` = '.$_POST['fid'].' AND `aid` = '.$_SESSION['agent_id'].' AND `status` = 1','AND');
                die('成功删除'.(int)$db->affected_num().'行');
                break;
        }
        break;
    case 'deltckey':
        if (empty($_POST['keyid'])){
            die('非法提交');
        }
        if (!$db->delete('sq_fidkey',array('ID'=>$_POST['keyid']),'AND')){
            die('删除失败 '.$db->geterror());
        }else{
            die('删除成功！');
        }
        break;
    case 'tckeystatus':
        if(!$db->update('sq_fidkey',array('ID'=>$_POST['keyid']),'AND',array('status'=>textbooltonum($_POST['status'])))){
            die('更新失败'.$db->geterror());
        }else{
            die('成功');
        }
        break;
    case 'retoken':
        $new = rand_str(128);
        updateset('token',$new);
        die($new);
        break;
    default:
        die('What the fuck');
}
function get_zip_originalsize($filename, $path) {//解压zip文件
    $resource = zip_open($filename);
    while ($dir_resource = zip_read($resource)) {
        if (zip_entry_open($resource,$dir_resource)) {
            $file_name = $path.zip_entry_name($dir_resource);
            $file_path = substr($file_name,0,strrpos($file_name, "/"));
            if(!is_dir($file_path)){
                mkdir($file_path,0777,true);
            }
            if(!is_dir($file_name)){
                $file_size = zip_entry_filesize($dir_resource);
                $file_content = zip_entry_read($dir_resource,$file_size);
                file_put_contents($file_name,$file_content);
            }
            zip_entry_close($dir_resource);
        }
    }
    zip_close($resource);
}

function getFile($url, $save_dir = '', $filename = '', $type = 0) {
    if (trim($url) == '') {
        return false;
    }
    if (trim($save_dir) == '') {
        $save_dir = './';
    }
    if (0 !== strrpos($save_dir, '/')) {
        $save_dir.= '/';
    }
    //创建保存目录
    if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
        return false;
    }
    //获取远程文件所采用的方法
    if ($type) {
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $content = curl_exec($ch);
        curl_close($ch);
    } else {
        ob_start();
        readfile($url);
        $content = ob_get_contents();
        ob_end_clean();
    }
    //echo $content;
    $size = strlen($content);
    //文件大小
    $fp2 = @fopen($save_dir . $filename, 'a');
    fwrite($fp2, $content);
    fclose($fp2);
    unset($content, $url);
    return array(
        'file_name' => $filename,
        'save_path' => $save_dir . $filename,
        'file_size' => $size
    );
}
