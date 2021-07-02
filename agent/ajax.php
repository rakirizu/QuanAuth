<?php
/**
 * Created by PhpStorm.
 * User: 温泉
 * Date: 2017-10-19
 * Time: 22:12
 */
include '../function/function_core.php';
if ($_GET['mod'] === 'login') {
    if (!empty($_POST['accesstoken'])) {
        if (!$result = $db->select_first_row('sq_agent', '*', array('accesstoken' => $_POST['accesstoken']), 'AND')) {
            die(json_encode(array('code' => '-1', 'msg' => '秘钥错误')));
        } else {
            $_SESSION['agent_username'] = $_POST['username'];
            $_SESSION['agent_password'] = $_POST['password'];
            $_SESSION['agent_id'] = $result['ID'];
            $_SESSION['agent_qq'] = $result['qq'];
            $_SESSION['agent_HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
            $db->update('sq_agent', array('username' => $_SESSION['agent_username']), 'AND', array('loginip' => get_real_ip(), 'logintime' => time()));
            die(json_encode(array('code' => '1', 'msg' => '登陆成功')));
        }
    }


    include '../function/VerificationCode.class.php';
    $verification = Verification::check($_POST['token']);
    if ($verification !== true) {
        die(json_encode(array('code' => '-88', 'msg' => '请先进行人机验证')));
    }
    if (empty($_POST['username'])) {
        die(json_encode(array('code' => -1, 'msg' => '请输入您的用户名')));
    }
    if (empty($_POST['password'])) {
        die(json_encode(array('code' => -2, 'msg' => '请输入您的密码')));
    }

    if (!$result = $db->select_first_row('sq_agent', '*', array('username' => $_POST['username'], 'password' => $_POST['password']), 'AND')) {
        die(json_encode(array('code' => -3, 'msg' => '输入的账号密码错误！' . $db->geterror())));
    } else {
        if ($result['status'] != '1') {
            die(json_encode(array('code' => -4, 'msg' => '代理账号已被冻结，请联系管理员或者上级处理')));
        }
        $_SESSION['agent_username'] = $_POST['username'];
        $_SESSION['agent_password'] = $_POST['password'];
        $_SESSION['agent_id'] = $result['ID'];
        $_SESSION['agent_qq'] = $result['qq'];
        $_SESSION['agent_HTTP_USER_AGENT'] = $_SERVER['HTTP_USER_AGENT'];
        $db->update('sq_agent', array('username' => $_SESSION['agent_username']), 'AND', array('loginip' => get_real_ip(), 'logintime' => time()));
        die(json_encode(array('code' => '1', 'msg' => '登陆成功，正在跳转！')));
    }


} else if ($_GET['mod'] === 'checklogin') {

    if (empty($_SESSION['agent_username']) || empty($_SESSION['agent_password'])) {
        die(json_encode(array('code' => -1)));
    }
    if (!$result = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username'], 'password' => $_SESSION['agent_password']), 'AND')) {
        die(json_encode(array('code' => -2)));
    }
    if ($_SERVER['HTTP_USER_AGENT'] !== $_SESSION['agent_HTTP_USER_AGENT']) {
        die(json_encode(array('code' => -3)));
    }
    die(json_encode(array('code' => 1, 'username' => $_SESSION['agent_username'], 'qq' => $_SESSION['agent_qq'])));
} else {
    if (empty($_SESSION['agent_username']) || empty($_SESSION['agent_password']) || empty($_SESSION['agent_id'])) {
        echo '没有登录:code:-1';
        header('Location: login.html');
        die();
    }
    if (!$result = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username'], 'password' => $_SESSION['agent_password']), 'AND')) {
        echo '没有登录:code:-2';
        header('Location: login.html');

        die();
    }
    if ($_SERVER['HTTP_USER_AGENT'] !== $_SESSION['agent_HTTP_USER_AGENT']) {
        echo '没有登录:code:-3';
        header('Location: login.html');
        die();
    }
}
switch ($_GET['mod']) {
    case 'getnotice':
        die($G['config']['agentnotice']);
        break;
    case 'getsysinfo':
        if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username']), 'AND')) {
            die(json_encode(array('code' => -2, 'msg' => '代理信息获取失败')));
        }
        if (!$levelinfo = $db->select_first_row('sq_level', '*', array('ID' => $agentinfo['levelid']), 'AND')) {
            die(json_encode(array('code' => -2, 'msg' => '代理等级信息获取失败！')));
        }
        $backinfo = array();
        $backinfo['level'] = $levelinfo['lname'];
        $backinfo['allspend'] = $agentinfo['allspend'];
        $backinfo['allrecharge'] = $agentinfo['allrecharge'];
        $backinfo['usernum'] = $db->select_count_row('sq_user', array('aid' => $_SESSION['agent_id']));
        $backinfo['money'] = $agentinfo['money'];
        $backinfo['subordinate'] = $db->select_count_row('sq_agent', array('superior' => $_SESSION['agent_id']));
        die(json_encode($backinfo));
        break;
    case 'buy_applist':
        if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username']), 'AND')) {
            die(json_encode(array('code' => -2, 'msg' => '代理信息获取失败')));
        }
        if (!$levelinfo = $db->select_first_row('sq_level', '*', array('ID' => $agentinfo['levelid']), 'AND')) {
            die(json_encode(array('code' => -2, 'msg' => '代理等级信息获取失败！')));
        }
        include '../function/function_app.php';
        $where = array();
        $i = '<option value="0">显示所有商品</option>';
        $info = explode(',', $levelinfo['appid']);
        foreach ($info as $aid) {
            $i .= '<option value="' . $aid . '">' . app_idgetname($aid) . '</option>';
        }
        /*        if ($levelinfo['appid']!=0){
                    $where['ID'] = $levelinfo['appid'];
                }else{
                    $i .= '<option value="0">显示所有应用</option>';
                }
                $back = $db->select_all_row('sq_apps','ID,appname',$where,'AND');
                if (count($back) === 0){
                    die();
                }
                foreach ($back as $value){
                    $i .= '<option value="'.$value['ID'].'">'.$value['appname'].'</option>';
                }*/
        die($i);
        break;
    case 'buy_fidlist':
        include '../function/function_app.php';

        //$where['agentbuy'] = '1';
        if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username']), 'AND')) {
            die(json_encode(array('code' => -2, 'msg' => '代理信息获取失败')));
        }
        if (!$levelinfo = $db->select_first_row('sq_level', '*', array('ID' => $agentinfo['levelid']), 'AND')) {
            die(json_encode(array('code' => -3, 'msg' => '代理等级信息获取失败！')));
        }

        $applist = explode(',', $levelinfo['appid']);


        $backinfo = array();
        $backinfo['code'] = 0;
        if (!empty($_GET['appid']) && $_GET['appid'] != '0') {
            if (!in_array($_GET['appid'], $applist)) {
                die(json_encode(array('code' => -4, 'msg' => '您没有权限查看此应用的商品列表！')));
            }

            $where = array();
            $where['appid'] = $_GET['appid'];
            $where['agentbuy'] = '1';
            $backinfo['count'] = $db->select_count_row('sq_fidlist', $where, 'AND');
            if (!$result = $db->select_limit_row('sq_fidlist', '*', ($_GET['page'] - 1) * $_GET['limit'], $_GET['limit'], $where, 'AND')) {
                $erro = $db->geterror();
                if (!empty($erro)) {
                    die(json_encode(array('code' => -1, 'msg' => $erro)));
                }

                die(json_encode($backinfo));
            }
        } else {
            $sqlwhere = '';
            foreach ($applist as $aid) {
                if (empty($sqlwhere)) {
                    $sqlwhere = 'appid=' . $aid;
                } else {
                    $sqlwhere .= ' OR appid=' . $aid;
                }
            }
            $where = 'agentbuy=1 AND (' . $sqlwhere . ')';
            $backinfo['count'] = $db->select_count_row('sq_fidlist', $where, 'AND');
            if (!$result = $db->select_limit_row('sq_fidlist', '*', ($_GET['page'] - 1) * $_GET['limit'], $_GET['limit'], $where, 'AND')) {
                $erro = $db->geterror();
                if (!empty($erro)) {
                    die(json_encode(array('code' => -1, 'msg' => $erro)));
                }
                die(json_encode($backinfo));
            }
        }


        $backinfo['data'] = array();
        foreach ($result as $item) {
            $linshi = array();
            $linshi['id'] = $item['ID'];
            $appinfo = app_idgetinfo($item['appid']);
            $linshi['fidname'] = $item['fidname'];
            $linshi['appname'] = app_idgetname($item['appid']);
            if ($item['num'] != '-1') {
                if ($appinfo['usetype'] === 'dqsj') {
                    $numtip = time_last($item['num'] * 60);
                } else {
                    $numtip = $item['num'] . '点';
                }
            } else {
                if ($appinfo['usetype'] === 'dqsj') {
                    $numtip = '无期授权';
                } else {
                    $numtip = '无限使用';
                }
            }
            $linshi['sqnum'] = $numtip;
            $linshi['agentprice'] = $item['agentprice'];
            $backinfo['data'][] = $linshi;
        }
        die(json_encode($backinfo));
        break;
    case 'getuserlist':
        if (empty($_GET['appid'])) {
            die(json_encode(array('code' => '-2', 'msg' => '请先选择一个应用(如果应用过多，可点击上方下拉选择框后，输入关键词可快速查找应用)')));
        }
        //$where['appid'] = $_GET['appid'];
        if (!empty($_GET['search'])) {
            $where['username'] = $where['password'] = $where['mac'] = $where['rip'] = $where['lip'] = $where['uqq'] = $where['mail'] = $where['balance'] = $where['rqq'] = $_GET['search'];
            //$where['uqq'] = intval($where['uqq'])
            if (!is_numeric($where['uqq'])) {
                unset($where['uqq']);
            }
            if (!is_numeric($where['rqq'])) {
                unset($where['rqq']);
            }
            if (!is_numeric($where['balance'])) {
                unset($where['balance']);
            }
            //echo '11111';
            $whereinfo = 'appid=' . intval($_GET['appid']) . ' AND aid=' . $_SESSION['agent_id'] . ' AND (' . $db->wheretosql($where, 'OR') . ')';
        } else {
            //echo '2222';
            $whereinfo = 'appid=' . intval($_GET['appid']) . ' AND aid=' . $_SESSION['agent_id'];
        }
        //echo $whereinfo;
        $backinfo['code'] = 0;
        $backinfo['count'] = $db->select_count_row('sq_user', $whereinfo, 'AND');
        include "../function/function_app.php";
        $appinfo = app_idgetinfo($_GET['appid']);
        if (!$arr = $db->select_limit_row('sq_user', '*', ($_GET['page'] - 1) * $_GET['limit'], $_GET['limit'], $whereinfo, 'AND')) {
            //$backinfo['code'] = -1;
            $backinfo['msg'] = $db->geterror();
            die(json_encode($backinfo));
        }

        $backinfo['msg'] = '';

        foreach ($arr as $value) {
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
            if ($appinfo['usetype'] === 'dqsj') {
                if (!empty($value['balance'])) {
                    if ($value['balance'] == '-1') {
                        $value['balance'] = '永久使用';
                    }
                    $value['balance'] = Get_Date($value['balance']);
                } else {
                    $value['balance'] = '-';
                }
            }
            $newinfo['balance'] = $value['balance'];
            $newinfo['rqq'] = htmlspecialchars($value['rqq']);
            if ((time() - $value['htime']) < $appinfo['onlinesecond']) {
                $online = '在线';
            } else {
                $online = '离线';
            }
            $newinfo['login'] = $online;
            $newinfo['status'] = '<input type="checkbox" value=' . $value['ID'] . ' name="status" lay-skin="switch" lay-text="正常|冻结" id="qzgx"' . ($value['status'] == 1 ? ' checked' : '') . '>';

            $backinfo['data'][] = $newinfo;
        }

        //print_r($backinfo);
        die(json_encode($backinfo));

        break;
    case 'changeuser':
        if (empty($_POST['mod'])) {
            die('模块标识不能为空');
        }
        if (empty($_POST['userid'])) {
            die('用户ID不能为空');
        }

        if (!in_array($_POST['mod'], array('username', 'password', 'mac', 'rip', 'lip', 'uqq', 'mail', 'rqq', 'status'))) die('不支持修改的字段');
        if ($_POST['mod'] == 'status') {
            $_POST['value'] = textbooltonum($_POST['value']);
        }
        if ($_POST['mod'] == 'balance') {
            if ($_POST['value'] == '永久使用') {
                $_POST['value'] = -1;
            } else {
                $value = strtotime($_POST['value']);
                if (!empty($value)) {
                    $_POST['value'] = $value;
                }
            }

        }


        if (!$db->update('sq_user', array('ID' => $_POST['userid'], 'aid' => $_SESSION['agent_id']), 'AND', array($_POST['mod'] => $_POST['value']))) {
            die('修改失败' . $db->geterror());
        } else {
            $db->insert_back_id('sq_log_agent', array('time' => time(), 'aid' => $_SESSION['agent_id'], 'ip' => get_real_ip(), 'msg' => '更改用户ID ' . $_POST['userid'] . ' 的 ' . $_POST['mod'] . ' 值为 ' . $_POST['value']));

            die('修改成功！');
        }
        break;
    case 'deluser':
        if (empty($_POST['userid'])) {
            die('用户ID不能为空');
        }
        $userinfo = $db->select_first_row('sq_user', '*', array('ID' => $_POST['userid']), 'AND');
        if (!$db->delete('sq_user', array('ID' => $_POST['userid'], 'aid' => $_SESSION['agent_id']), 'AND')) {
            die('删除失败' . $db->geterror());
        } else {
            $db->insert_back_id('sq_log_agent', array('time' => time(), 'aid' => $_SESSION['agent_id'], 'ip' => get_real_ip(), 'msg' => '删除用户 ' . json_encode($userinfo)));

            die('删除成功！');
        }

    case 'buy_first':

        include "../function/function_app.php";
        $fidinfo = $db->select_first_row('sq_fidlist', '*', array('ID' => $_GET['fid']), 'AND');
        $appinfo = app_idgetinfo($fidinfo['appid']);
        $show_form = '';

        if ($appinfo['logintype'] == 'zhmm') {
            $show_form .= '
    <div class="layui-form-item">
        <label for="kt_user" class="layui-form-label">用户名</label>
        <div class="layui-input-block">
            <input class="layui-input" name="kt_user" placeholder="" type="text">
        </div>
    </div>
    <div class="layui-form-item">
        <label for="kt_user" class="layui-form-label">密码</label>
        <div class="layui-input-block">
            <input class="layui-input" name="kt_pass" placeholder="" type="text">
        </div>
    </div>
    ';
            $type = 1;
        } else if ($appinfo['logintype'] == 'kmsq') {
            $show_form .= '
    <div class="layui-form-item">
        <label for="kt_user" class="layui-form-label">登陆卡密</label>
        <div class="layui-input-block">
            <input class="layui-input" name="kt_user" placeholder="输入为续费，留空则新开" type="text">
        </div>
    </div>';
            $type = 2;
        } else if ($appinfo['logintype'] == 'jcbd') {
            if ($appinfo['bindqq'] == '1') {
                $show_form .= '
    <div class="layui-form-item">
        <label for="kt_user" class="layui-form-label">机器人QQ</label>
        <div class="layui-input-block">
            <input class="layui-input" name="kt_robotqq" placeholder="" type="text">
        </div>
    </div>
    ';
            }
            if ($appinfo['bindmac'] == '1') {
                $show_form .= '<div class="layui-form-item">
        <label for="kt_user" class="layui-form-label">设备码</label>
        <div class="layui-input-block">
            <input class="layui-input" name="kt_mac" placeholder="" type="text">
        </div>
    </div>
    ';
            }
            if ($appinfo['bindip'] == '1') {
                $show_form .= '<div class="layui-form-item">
        <label for="kt_ip" class="layui-form-label">用户IP</label>
        <div class="layui-input-block">
            <input class="layui-input" name="kt_ip" placeholder="" type="text">
        </div>
    </div>
    ';
            }
            $type = 3;
        }
        include './html/buy.page.inc.php';
        die();
        break;
    case 'buy_submit':
        include '../function/function_app.php';

        if (!$fidinfo = $db->select_first_row('sq_fidlist', '*', array('ID' => $_POST['id']), 'AND')) {
            die(json_encode(array('code' => -2, 'msg' => '无法找到该商品，请刷新页面重试')));
        }
        if (!$appinfo = app_idgetinfo($fidinfo['appid'])) {
            die(json_encode(array('code' => -2, 'msg' => '无法找到应用，也许是该应用已下架')));
        }
        if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username']), 'AND')) {
            die(json_encode(array('code' => -2, 'msg' => '代理信息获取失败')));
        }
        $time = time();
        $tradno = $time . rand_str(10, 'num');
        $tips = '交易订单号：' . $tradno . '<br>';
        if (!$levelinfo = $db->select_first_row('sq_level', '*', array('ID' => $agentinfo['levelid']), 'AND')) {
            die(json_encode(array('code' => -2, 'msg' => '代理对应的等级信息获取失败！')));
        }
        $spendmoney = $fidinfo['agentprice'] * $_POST['kt_num'] * $levelinfo['fracture'];
        $tips .= '应付金额：' . $spendmoney . '<br>';
        if ($spendmoney < 0) {
            die(json_encode(array('code' => -7, 'msg' => '错误的支付金额：' . $spendmoney)));
        }

        if ($appinfo['logintype'] == 'zhmm') {
            if (!$userinfo = $db->select_first_row('sq_user', '*', array('username' => $_POST['kt_user'], 'appid' => $fidinfo['appid']), 'AND')) {
                $tips .= '<font color="green">该用户名尚未注册！</font><br>';
            } else {
                $tips .= '<font color="red">当前用户名已注册！</font><br>';
            }
            $tips .= '用户名：' . $_POST['kt_user'] . '<br>';
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
                'type' => 2,
                'agentid' => $agentinfo['ID']
            );
        } else if ($appinfo['logintype'] == 'kmsq') {
            //卡密方式
            if (empty($_POST['kt_user']) || !$userinfo = $db->select_first_row('sq_user', '*', array('username' => $_POST['kt_user'], 'appid' => $fidinfo['appid']), 'AND')) {
                $_POST['kt_user'] = '';
                $tips .= '开通方式：新开卡密<br>';
            } else {
                $tips .= '开通方式：续费卡密(' . $_POST['kt_user'] . ')<br>';
            }
            $newtrade = array('name' => $fidinfo['fidname'],
                'tradeno' => $tradno,
                'begintime' => $time,
                'user' => $_POST['kt_user'],
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
                'type' => 2,
                'agentid' => $agentinfo['ID']
            );

        } else if ($appinfo['logintype'] == 'jcbd') {
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
            if (empty($check)) {
                die(json_encode(array('code' => -10, 'msg' => '系统内部错误(应用绑定信息配置异常)，请联系管理员解决')));
            }
            $check['appid'] = $appinfo['ID'];
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
                'uqq' => $_POST['kt_adminqq'],
                'ip' => get_real_ip(),
                'status' => '1',
                'type' => 2,
                'agentid' => $agentinfo['ID']
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
        if (empty($userinfo['balance']) && $userinfo['balance'] == '-1') {
            die(json_encode(array('code' => 3, 'msg' => '已是永久授权，无法再次开通和续费！')));
        }
        if ($appinfo['usetype'] === 'dqsj') {
            if (empty($userinfo['balance']) || $userinfo['balance'] == 0) {
                $tips .= '当前到期时间：没有授权<br>';
                $userinfo['balance'] = time();
            } else {
                $tips .= '当前到期时间：' . Get_Date($userinfo['balance']) . '<br>';
            }
            if ($fidinfo['num'] != '-1') {
                $tips .= '新增时长：' . time_last($fidinfo['num'] * $_POST['kt_num'] * 60) . '<br>';
            } else {
                $tips .= '购买后您可以无限使用<br>';
            }

        } else {
            $tips .= '用户剩余点数：' . $userinfo['balance'] . '点<br>';
            if ($fidinfo['num'] != '-1') {
                $tips .= '购买后点数：' . ($userinfo['balance'] + $fidinfo['num'] * $_POST['kt_num']) . '点<br>';
            } else {
                $tips .= '购买后点数：无限制<br>';
            }

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
        } else if ($_POST['zffs'] == 'yezf') {
            $tips .= '付款方式：余额支付<br>';
            if ($agentinfo['money'] < $spendmoney) {
                die(json_encode(array('code' => -5, 'msg' => '您的代理余额不足，请先充值或者选择其他付款方式！')));
            }
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
    case 'recharge':
        if ($_POST['money'] <= 0) {
            die(json_encode(array('code' => -1, 'msg' => '充值的金额必须为正整数')));
        }
        if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username']), 'AND')) {
            die(json_encode(array('code' => -2, 'msg' => '代理信息获取失败')));
        }
        $time = time();
        $tradno = $time . rand_str(10, 'num');
        $tips = '您的订单已经生成完毕:<br>订单号：' . $tradno . '<br>充值金额：' . $_POST['money'] . '<br>';
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
            $tips .= '付款方式：余额卡密<br>';
            $tips .= '正在使用的卡密：' . $_POST['kt_key'] . '<br>';
        } else {
            die(json_encode(array('code' => -5, 'msg' => '错误的支付方式')));
        }

        if (!$db->insert_back_id('sq_trade', array('name' => '代理' . $_SESSION['agent_username'] . '充值余额' . $_POST['money'] . '元', 'tradeno' => $tradno, 'begintime' => $time, 'user' => $_SESSION['agent_username'], 'status' => '1', 'type' => '1', 'paymoney' => $_POST['money'], 'agentid' => $agentinfo['ID'], 'paytype' => $_POST['zffs'], 'onlinepaytype' => $_POST['zxzffs'], 'kami' => $_POST['kt_key']))) {
            die(json_encode(array('code' => -5, '订单创建失败' . $db->geterror())));
        } else {
            die(json_encode(array('code' => '1', 'tradeno' => $tradno, 'info' => $tips)));
        }
        break;
    case 'getlevallist':
        $back = $db->select_all_row('sq_level', 'ID,lname', array(), 'AND');
        if (count($back) === 0) {
            die();
        }
        $i = '<option value="0">请选择一个级别</option>';
        foreach ($back as $value) {
            $i .= '<option value="' . $value['ID'] . '">' . $value['lname'] . '</option>';
        }
        die($i);
        break;

    case 'getbuylist':

        if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username']), 'AND')) {
            die('代理信息获取失败');
        }

        if (!$agentlevelinfo = $db->select_first_row('sq_level', '*', array('ID' => $agentinfo['levelid']), 'AND')) {
            die('等级信息获取失败');
        }

        $info = json_decode($agentlevelinfo['subordinate'], true);
        $i = '<option value="0">请选择一个级别</option>';
        foreach ($info as $lid => $could) {
            if ($could) {
                $value = $db->select_first_row('sq_level', 'ID,lname', array('ID' => $lid), 'AND');
                $i .= '<option value="' . $value['ID'] . '">' . $value['lname'] . '</option>';
            }

        }
        die($i);
        break;
    case 'gettradelist':
        $where = array();

        if (!empty($_GET['search'])) {
            $where['tradeno'] = $where['name'] = $where['user'] = $where['mail'] = $where['kami'] = $_GET['search'];
            $whereinfo = 'agentid=' . $_SESSION['agent_id'] . ' AND (' . $db->wheretosql($where, 'OR') . ')';
        } else {
            $whereinfo = 'agentid=' . $_SESSION['agent_id'];
        }
        $backinfo['code'] = 0;
        $backinfo['count'] = $db->select_count_row('sq_trade', $whereinfo, 'OR');
        if (!$arr = $db->select_limit_row('sq_trade', '*', ($_GET['page'] - 1) * $_GET['limit'], $_GET['limit'], $whereinfo, 'OR', 'ORDER BY ID DESC')) {
            $backinfo['msg'] = $db->geterror();
            die(json_encode($backinfo));
        }
        foreach ($arr as $key => $value) {
            if ($value['begintime'] == 0) {
                $arr[$key]['begintime'] = '-';
            } else {
                $arr[$key]['begintime'] = Get_Date($value['begintime']);
            }
            if ($value['overtime'] == 0) {
                $arr[$key]['overtime'] = '-';
            } else {
                $arr[$key]['overtime'] = Get_Date($value['overtime']);
            }
            if ($value['paytype'] == 'zxzf') {
                switch ($value['onlinepaytype']) {
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
            } else if ($value['paytype'] == 'czkm') {
                $arr[$key]['paytype'] = '余额卡密';
            } else if ($value['paytype'] == 'yezf') {
                $arr[$key]['paytype'] = '代理余额';
            } else {
                $arr[$key]['paytype'] = '异常?';
            }
            if ($value['status'] == 1) {
                $arr[$key]['status'] = '等待付款';
                $button = '<button type="button" class="layui-btn layui-btn-xs layui-btn-normal" onclick="pay(\'' . $value['tradeno'] . '\');">付款</button>';
            } else if ($value['status'] == 2) {
                $arr[$key]['status'] = '等待验证';
                $button = '<button type="button" class="layui-btn layui-btn-xs" onclick="check(\'' . $value['tradeno'] . '\');">验证</button>';
            } elseif ($value['status'] == 3) {
                $button = '';
                $arr[$key]['status'] = '完成';
            } else {
                $arr[$key]['status'] = '异常';
            }
            $arr[$key]['tools'] = $button . '<button type="button" class="layui-btn layui-btn-xs layui-btn-danger" onclick="deltrade(\'' . $value['ID'] . '\');">删除</button>';
        }
        $backinfo['data'] = $arr;
        die(json_encode($backinfo));
        break;
    case 'deltrade':
        if (!$db->delete('sq_trade', array('ID' => $_POST['id'], 'agentid' => $_SESSION['agent_id']), 'AND')) {
            die('订单删除失败或者删除的数量为0' . $db->geterror());
        } else {
            die('订单删除成功！');
        }
        break;
    case 'clearalltradeid':
        $limit = time() - 3600;
        if (!$db->delete('sq_trade', 'beigintime<' . $limit . ' AND agentid=' . $_SESSION['agent_id'], 'AND')) {
            die('订单删除失败' . $db->geterror());
        } else {
            die('订单删除成功！');
        }
        break;
    case 'clearnopay':
        $limit = time() - 3600;
        if (!$db->delete('sq_trade', 'begintime<' . $limit . ' AND status = 1 ' . ' AND agentid=' . $_SESSION['agent_id'], 'AND')) {
            die('订单删除失败' . $db->geterror());
        } else {
            die('订单删除成功！');
        }
        break;
    case 'clearbeenpay':
        $limit = time() - 3600;
        if (!$db->delete('sq_trade', array('status' => 3, 'agentid' => $_SESSION['agent_id']), 'AND')) {
            die('订单删除失败' . $db->geterror());
        } else {
            die('订单删除成功！');
        }
        break;
    case 'updatelevel':
        if ($_POST['leval'] == 0) {
            die('请先选择一个代理等级');
        }
        if (!$levelinfo = $db->select_first_row('sq_level', '*', array('ID' => $_POST['leval']), 'AND')) {
            die('等级信息获取失败，无法继续升级！');
        }
        if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username']), 'AND')) {
            die('代理信息获取失败');
        }
        if ($agentinfo['money'] < $levelinfo['price']) {
            die('您的余额不足，无法升级，请先进行充值！');
        }
        if ($agentinfo['levelid'] == $_POST['leval']) {
            die('您目前的等级已经是这个了，重复开通干嘛￣へ￣');
        }
        $newmoney = $agentinfo['money'] - $levelinfo['price'];
        if (!$db->update('sq_agent', array('username' => $_SESSION['agent_username']), 'AND', array('money' => $newmoney, 'levelid' => $_POST['leval']))) {
            die('系统内部错误，升级失败！');
        } else {
            $db->insert_back_id('sq_log_agent', array('time' => time(), 'aid' => $_SESSION['agent_id'], 'ip' => get_real_ip(), 'msg' => '自助花费余额提升等级为 ' . $levelinfo['lname']));
            die('代理等级提升成功！');
        }
        break;
    case 'buyagent':
        if ($_POST['leval'] == 0) {
            die('请先选择一个代理等级');
        }
        if (!$levelinfo = $db->select_first_row('sq_level', '*', array('ID' => $_POST['leval']), 'AND')) {
            die('等级信息获取失败，无法继续开通！');
        }
        if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username']), 'AND')) {
            die('代理信息获取失败');
        }
        if ($db->select_first_row('sq_agent', '*', array('username' => $_POST['username']), 'AND') != false) {
            die('该代理账号已经存在，请尝试其他用户名');
        }
        if (!$agentlevelinfo = $db->select_first_row('sq_level', '*', array('ID' => $agentinfo['levelid']), 'AND')) {
            die('等级信息获取失败');
        }
        $buymoney = $agentlevelinfo['discount'] * $levelinfo['price'];
        if ($agentinfo['money'] < $buymoney) {
            die('您的余额不足，无法开通，请先进行充值！');
        }
        $couldbuy = json_decode($agentlevelinfo['subordinate'], true);
        if (@$couldbuy[$_POST['leval']] !== true) {
            die('您无法开通此等级的代理！');
        }

        $newmoney = $agentinfo['money'] - $buymoney;
        if (!$db->update('sq_agent', array('username' => $_SESSION['agent_username']), 'AND', array('money' => $newmoney))) {
            die('系统内部错误，开通失败！');
        } else {
            if (!$db->insert_back_id('sq_agent', array('username' => $_POST['username'], 'password' => $_POST['pass'], 'begintime' => time(), 'levelid' => $_POST['leval'], 'status' => '1', 'superior' => $_SESSION['agent_id']))) {
                die('您的余额已扣除但是内部发生错误，下级开通失败，请联系管理员处理！' . $db->geterror());
            } else {
                $db->insert_back_id('sq_log_agent', array('time' => time(), 'aid' => $_SESSION['agent_id'], 'ip' => get_real_ip(), 'msg' => '成功开通下级代理 ' . $_POST['username']));
                die('成功开通下级代理！');
            }
        }
        break;
    case 'getagentlist':
        include '../function/function_app.php';
        if (!$result = $db->select_limit_row('sq_agent', '*', ($_GET['page'] - 1) * $_GET['limit'], $_GET['limit'], array('superior' => $_SESSION['agent_id']), 'AND')) {
            $error = $db->geterror();
            if (!empty($error)) {
                $backinfo['code'] = -1;
                $backinfo['msg'] = $db->geterror();
            } else {
                $backinfo['code'] = 0;
                $backinfo['msg'] = '';
                $backinfo['count'] = 0;
            }

            die(json_encode($backinfo));
        } else {
            $info = '';
            $backinfo['code'] = 0;
            $backinfo['msg'] = '';
            $backinfo['count'] = $db->select_count_row('sq_agent', array('superior' => $_SESSION['agent_id']));
            foreach ($result as $value) {
                if ($value['logintime'] == 0) {
                    $value['logintime'] = '-';
                }
                $value['begintime'] = Get_Date($value['begintime']);
                $value['logintime'] = Get_Date($value['logintime']);
                $value['levelname'] = level_idgetname($value['levelid']);
                $value['status'] = '<input type="checkbox" value=' . $value['ID'] . ' name="status" lay-skin="switch" lay-text="正常|冻结" id="qzgx"' . ($value['status'] == 1 ? ' checked' : '') . '>';
                $backinfo['data'][] = $value;
            }


            die(json_encode($backinfo));
        }
        break;
    case 'changeagent':
//        if (!$db->update('sq_agent',array('ID'=>$_POST['id']),'AND',array('username'=>$_POST['agent_name'],'password'=>$_POST['agent_pass'],'money'=>$_POST['agent_money'],'levelid'=>$_POST['agent_leval'],'begintime'=>time(),'status'=>'1','loginip'=>'-'))){
//            die('修改失败'.$db->geterror());
//        }else{
//            die('修改成功！');
//        }
        if (empty($_POST['mod'])) {
            die('模块标识不能为空');
        }
        if (empty($_POST['aid'])) {
            die('代理ID不能为空');
        }
        if ($_POST['mod'] != 'username' && $_POST['mod'] != 'password' && $_POST['mod'] != 'qq' && $_POST['mod'] != 'status') die('不支持修改的字段');

        if ($_POST['mod'] == 'status') $_POST['value'] = textbooltonum($_POST['value']);
        if (!$db->update('sq_agent', array('ID' => $_POST['aid'], 'superior' => $_SESSION['agent_id']), 'AND', array($_POST['mod'] => $_POST['value']))) {
            die('修改失败' . $db->geterror());
        } else {
            die('修改成功！');
        }
        break;
    case 'delagent':
        if (empty($_POST['aid'])) {
            die('非法提交');
        }
        if (!$db->delete('sq_agent', array('ID' => $_POST['aid'], 'superior' => $_SESSION['agent_id']), 'AND')) {
            die('删除失败 ' . $db->geterror());
        } else {
            die('删除代理成功');
        }
        break;
    case 'leveltips':
        include '../function/function_app.php';
        if (!$levelinfo = $db->select_first_row('sq_level', '*', array('ID' => $_POST['leval']), 'AND')) {
            die('等级信息获取失败');
        }
        if ($levelinfo['appid'] == '0') {
            $appinfo = '全局代理';
        } else {
            $appinfo = app_idgetname($levelinfo['appid']);
        }
        die('<center>代理应用：' . $appinfo . '，等级名称：' . $levelinfo['lname'] . '，升级金额：' . $levelinfo['price'] . '，拿货折率：' . $levelinfo['fracture'] . '<br>注意：升级后当前等级会被释放，而不是同时有两个等级，如果希望有多个等级请联系站长新开代理账号！</center>');
        break;
    case 'addagenttips':
        include '../function/function_app.php';
        if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username']), 'AND')) {
            die('代理信息获取失败');
        }
        if (!$agentlevelinfo = $db->select_first_row('sq_level', '*', array('ID' => $agentinfo['levelid']), 'AND')) {
            die('等级信息获取失败');
        }
        if (!$levelinfo = $db->select_first_row('sq_level', '*', array('ID' => $_POST['leval']), 'AND')) {
            die('选择等级信息获取失败');
        }

        if ($levelinfo['appid'] == '0') {
            $appinfo = '全局代理';
        } else {
            $appinfo = app_idgetname($levelinfo['appid']);
        }
        die('代理应用：' . $appinfo . '，等级名称：' . $levelinfo['lname'] . '，开通金额：' . $levelinfo['price'] . '，拿货折率：' . $levelinfo['fracture'] . '<br>您当前代理等级开通下级折率：' . $agentlevelinfo['discount'] . '，实际支付：<font color="red">' . ($levelinfo['price'] * $agentlevelinfo['discount']) . '元</font>');
        break;
    case 'changepassword':
        if (empty($_POST['oldpass']) || empty($_POST['newpass']) || empty($_POST['renewpass'])) {
            die('请填写完所有数据！');
        }
        if ($_POST['newpass'] != $_POST['renewpass']) {
            die('两次输入的密码一样，无法修改！');
        }
        if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username']), 'AND')) {
            die('代理信息获取失败');
        }
        if ($_POST['oldpass'] !== $agentinfo['password']) {
            die('输入的原密码错误，无法修改！');
        }

        if (!$db->update('sq_agent', array('username' => $_SESSION['agent_username']), 'AND', array('password' => $_POST['newpass']))) {
            die('密码修改失败，服务器内部发生错误');
        } else {
            die('代理密码修改成功，请使用新密码进行登录！');
        }

        break;
    case 'fidlist_show':
        include '../function/function_app.php';
        //$where['agentbuy'] = '1';
        if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username']), 'AND')) {
            die(json_encode(array('code' => -2, 'msg' => '代理信息获取失败')));
        }
        if (!$levelinfo = $db->select_first_row('sq_level', '*', array('ID' => $agentinfo['levelid']), 'AND')) {
            die(json_encode(array('code' => -3, 'msg' => '代理等级信息获取失败！')));
        }
        $applist = explode(',', $levelinfo['appid']);
        $backinfo = array();
        $backinfo['code'] = 0;
        $i = '<option value="0"></option>';
        foreach ($applist as $appid){
            $backinfo = $db->select_all_row('sq_fidlist','ID,fidname', array('appid'=>$appid,'agentbuy'=>'1'), 'AND');
            $appname = app_idgetname($appid);
            foreach ($backinfo as $fidinfo) {
                $i .= '<option value="' . $fidinfo['ID'] . '">' .$fidinfo['fidname'].'('.$appname.')' . '</option>';
            }
        }
        die($i);
        break;
    case 'gettckeylist':
        $where = array();
        if (!empty($_GET['search'])){
            $where['kami'] = $_GET['search'];
        }
        $where['fid'] = $_GET['fid'];
        $where['aid'] = $_SESSION['agent_id'];
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
    case 'creatfidkey':
        $count = $_POST['sc_count'];
        if ($count <= 0){
            die(json_encode(array('code'=>'-1','msg'=>'生成的数量不能为空或者0或者负数')));
        }
        $time = time();
        if ($count <= 0){
            die(json_encode(array('code'=>'-1','msg'=>'卡密金额不能为空或者0或者负数')));
        }
        if (!$fidinfo = $db->select_first_row('sq_fidlist', '*', array('ID' => $_POST['sc_fid']), 'AND')) {
            die(json_encode(array('code' => -2, 'msg' => '无法找到该商品，请刷新页面重试')));
        }
        if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username']), 'AND')) {
            die(json_encode(array('code' => -3, 'msg' => '代理信息获取失败')));
        }
        $spendmoney = $fidinfo['agentprice'] * $count;
        if ($spendmoney > $agentinfo['money']){
            die(makejson(-10,'代理余额不足'.$spendmoney.'，请先进行充值'));
        }
        $newmoney = $agentinfo['money'] - $spendmoney;
        if (!$db->update('sq_agent', array('ID' => $_SESSION['agent_id']), 'AND', array('money' => $newmoney))) {
            die('系统内部错误！');
        }

        $keylist='';
        for ($x=1; $x<=$_POST['sc_count']; $x++) {
            $key = substr($time,0,6).'-'.rand_str(6).'-'.rand_str(6).'-'.rand_str(6).'-'.rand_str(6).rand_str(6);
            $insarray[] = "'{$key}','{$time}','0','{$_POST['sc_fid']}','1','{$_SESSION['agent_id']}'";
            $keylist .= '<br>'.$key;
        }
        $sign = rand_str(32);
        $_SESSION['cards'][$sign] = str_replace('<br>',"\r\n",$keylist);
        if(!$num = $db->insert_back_row('sq_fidkey',array('kami','creattime','usetime','fid','status','aid'),$insarray)){
            die(json_encode(array('code'=>'-1','msg'=>'数据库错误：'.$db->geterror())));
        }else{
            die(json_encode(array('code'=>'1','sign'=>$sign,'keys'=>'您的卡密如下：'.$keylist)));
        }
        break;
    case 'tckeystatus':
        if(!$db->update('sq_fidkey',array('ID'=>$_POST['keyid'],'aid'=>$_SESSION['agent_id']),'AND',array('status'=>textbooltonum($_POST['status'])))){
            die('更新失败'.$db->geterror());
        }else{
            die('成功');
        }
        break;
    case 'deltckey':
        if (empty($_POST['keyid'])){
            die('非法提交');
        }
        if (!$db->delete('sq_fidkey',array('ID'=>$_POST['keyid'],'aid'=>$_SESSION['agent_id']),'AND')){
            die('删除失败 '.$db->geterror());
        }else{
            die('删除成功！');
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
        $where['aid'] = $_SESSION['agent_id'];
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
        $spendmoney = $money * $count;
        if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('username' => $_SESSION['agent_username']), 'AND')) {
            die(json_encode(array('code' => -2, 'msg' => '代理信息获取失败')));
        }
        if ($spendmoney > $agentinfo['money']){
            die(makejson(-10,'代理余额不足'.$spendmoney.'，请先进行充值'));
        }
        $newmoney = $agentinfo['money'] - $spendmoney;
        if (!$db->update('sq_agent', array('ID' => $_SESSION['agent_id']), 'AND', array('money' => $newmoney))) {
            die('系统内部错误！');
        }

        $keylist='';
        for ($x=1; $x<=$_POST['sc_count']; $x++) {
            $key = substr($time,0,6).'-'.rand_str(6).'-'.rand_str(6).'-'.rand_str(6).'-'.rand_str(6).'-'.$money;
            $insarray[] = "'{$key}','{$time}','0','{$money}','{$money}','0','1','{$_SESSION['agent_id']}'";
            $keylist .= '<br>'.$key;
        }
        $sign = rand_str(32);
        $_SESSION['cards'][$sign] = str_replace('<br>',"\r\n",$keylist);
        if(!$num = $db->insert_back_row('sq_key',array('kami','creattime','firstusetime','allmoney','lastmoney','lastusetime','status','aid'),$insarray)){
            die(json_encode(array('code'=>'-1','msg'=>'数据库错误：'.$db->geterror())));
        }else{
            die(json_encode(array('code'=>'1','sign'=>$sign,'keys'=>'您的卡密如下：'.$keylist)));
        }
        break;
    case 'FidKeyOperation':
        switch ($_POST['operation']){
            case 'ExportNoUse':
                $result = $db->select_all_row('sq_fidkey','kami',array('usetime'=>0,'status'=>1,'fid'=>$_POST['fid'],'aid'=>$_SESSION['agent_id']),'AND');
                $keylist = '';
                foreach ($result as $value){
                    $keylist .= $value['kami']."\r\n";
                }
                $sign = rand_str(32);
                $_SESSION['cards'][$sign] = $keylist;
                die('您的卡密已就绪，<a href="../ajax.php?mod=download&sign='.$sign.'">点击这里下载卡密</a>');
                break;
            case 'ExportAll':
                $result = $db->select_all_row('sq_fidkey','kami',array('fid'=>$_POST['fid'],'aid'=>$_SESSION['agent_id'],'status'=>1));
                $keylist = '';
                foreach ($result as $value){
                    $keylist .= $value['kami']."\r\n";
                }
                $sign = rand_str(32);
                $_SESSION['cards'][$sign] = $keylist;
                die('您的卡密已就绪，<a href="../ajax.php?mod=download&sign='.$sign.'">点击这里下载卡密</a>');
                break;
            case 'DelNoUse':
                $db->delete('sq_fidkey',array('usetime'=>0,'fid'=>$_POST['fid'],'status'=>1,'aid'=>$_SESSION['agent_id']),'AND');
                die('成功删除'.(int)$db->affected_num().'行');
                break;
            case 'DelUse':
                $db->delete('sq_fidkey', '`usetime` > 0 AND `fid` = '.$_POST['fid'].' AND `aid` = '.$_SESSION['agent_id'].' AND `status` = 1','AND');
                die('成功删除'.(int)$db->affected_num().'行');
                break;
            case 'DelAll':
                $db->delete('sq_fidkey',array('fid'=>$_POST['fid'],'aid'=>$_SESSION['agent_id']),'AND');
                die('成功删除'.(int)$db->affected_num().'行');
                break;
        }
        break;
    case 'KeyOperation':
        switch ($_POST['operation']){
            case 'ExportNoUse':
                $result = $db->select_all_row('sq_key','kami',array('firstusetime'=>0,'status'=>1,'aid'=>$_SESSION['agent_id']));
                $keylist = '';
                foreach ($result as $value){
                    $keylist .= $value['kami']."\r\n";
                }
                $sign = rand_str(32);
                $_SESSION['cards'][$sign] = $keylist;
                die(makejson(1,'success',array('sign'=>$sign)));
                break;
            case 'ExportNoMoney':
                $result = $db->select_all_row('sq_key','kami',array('lastmoney'=>0,'status'=>1,'aid'=>$_SESSION['agent_id']));
                $keylist = '';
                foreach ($result as $value){
                    $keylist .= $value['kami']."\r\n";
                }
                $sign = rand_str(32);
                $_SESSION['cards'][$sign] = $keylist;
                die(makejson(1,'success',array('sign'=>$sign)));
                break;
            case 'ExportAll':
                $result = $db->select_all_row('sq_key','kami',array('aid'=>$_SESSION['agent_id']));
                $keylist = '';
                foreach ($result as $value){
                    $keylist .= $value['kami']."\r\n";
                }
                $sign = rand_str(32);
                $_SESSION['cards'][$sign] = $keylist;
                die(makejson(1,'success',array('sign'=>$sign)));
                break;
            case 'DelNoMoney':
                $db->delete('sq_key',array('lastmoney'=>0,'aid'=>$_SESSION['agent_id']),'AND');

                die(makejson(2,'success',array('nums'=>$db->affected_num())));
                break;
            case 'DelNoUse':
                $db->delete('sq_key',array('firstusetime'=>0,'aid'=>$_SESSION['agent_id']),'AND');
                die(makejson(2,'success',array('nums'=>$db->affected_num())));
                break;
            case 'DelAll':
                $db->delete('sq_key',array('aid'=>$_SESSION['agent_id']),'AND');
                die(makejson(2,'success',array('nums'=>$db->affected_num())));
                break;
            default:
                die(makejson(-1,'未知操作：'.$_POST['operation']));
        }
        break;
    case 'keylog':
        if (!$result = $db->select_limit_row('sq_log_kami','*','',0,array('keyid'=>$_POST['keyid'],'aid'=>$_SESSION['agent_id']),'AND',"ORDER BY time DESC")){
            die(makejson(-1,'数据库中没有记录'));
        }
        die(makejson(1,'success',array('items'=>$result)));
        break;
    case 'delkey':
        if (empty($_POST['keyid'])){
            die(makejson(-1,'卡密ID不能为空'));
        }
        if (!$db->delete('sq_key',array('ID'=>$_POST['keyid'],'aid'=>$_SESSION['agent_id']),'AND')){
            die(makejson(-2,'删除失败 '.$db->geterror()));
        }else{
            die(makejson(1,'删除成功！'));
        }
        break;
}
die('啊嘞嘞？？？');
