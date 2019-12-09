<?php
/**
 * Created by PhpStorm.
 * User: 温泉
 * Date: 2019/2/17
 * Time: 11:47
 *
 */


/**
 * 订单处理
 * @param $tradeinfo
 * @return false|string
 */
function trade_do($tradeinfo){
    global $db;
    $MailTips[] = MakeTradeTips($tradeinfo);
    ignore_user_abort(true);//关闭浏览器继续处理，保证订单不会处理到一半就凉了
    if ($tradeinfo['type'] == '1') {
        //代理充值
        if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('username' => $tradeinfo['user']), 'AND')) {
            return makejson(-101,'代理信息获取失败');
        }
        $newinfo['money'] = $agentinfo['money'] + $tradeinfo['paymoney'];
        $newinfo['allrecharge'] = $agentinfo['allrecharge'] + $tradeinfo['paymoney'];
        if (!$db->update('sq_agent', array('username' => $tradeinfo['user']), 'AND', $newinfo)) {
            return makejson(-102,'代理信息更新失败');
        }
        $db->insert_back_id('sq_log_agent', array('time' => time(), 'aid' => $agentinfo['ID'], 'ip' => get_real_ip(), 'msg' => '成功充值' . $tradeinfo['paymoney'] . '元，余额' . $newinfo['allrecharge']));
        return makejson(1,'充值余额成功！');
    }
    if ($tradeinfo['type'] == '2') {
        //代理开通商品
        if (!$fidinfo = $db->select_first_row('sq_fidlist', '*', array('ID' => $tradeinfo['fid']), 'AND')) {
            return makejson(-201,'商品信息拉取失败');
        }
        if (!$agentinfo = $db->select_first_row('sq_agent', '*', array('ID' => $tradeinfo['agentid']), 'AND')) {
            return makejson(-202,'代理信息拉取失败');
        }
        $allspend = $agentinfo['allspend'] + $tradeinfo['paymoney'];
        $db->update('sq_agent', array('ID' => $tradeinfo['agentid']), 'AND', array('allspend' => $allspend));
        include_once 'function_auth.php';
        $back = auth_add($tradeinfo['user'],$tradeinfo['pass'],$tradeinfo['ip'],$tradeinfo['num'] * $fidinfo['num'],$tradeinfo['uqq'],$tradeinfo['mail'],$fidinfo['appid'],4,$agentinfo['ID'],$newkey,$tips);
        if ($back == 1){
            return makejson(1,'授权新增成功');
        }else if ($back==2){
            return makejson(1,'授权续费成功');
        }else if ($back==3){
            return makejson(2,'成功生成卡密',array('kami'=>$newkey));
        }else{
            return makejson($back,$tips);
        }
    }
    if ($tradeinfo['type'] == '3') {
        //在线购买
        if (!$fidinfo = $db->select_first_row('sq_fidlist', '*', array('ID' => $tradeinfo['fid']), 'AND')) {
            return makejson(-301,'商品信息拉取失败');
        }
        include_once 'function_auth.php';
        $back = auth_add($tradeinfo['user'],$tradeinfo['pass'],$tradeinfo['ip'],$tradeinfo['num'] * $fidinfo['num'],$tradeinfo['uqq'],$tradeinfo['mail'],$fidinfo['appid'],2,$tradeinfo['ID'],$newkey,$tips);
        if ($back == 1){
            return makejson(1,'授权新增成功');
        }else if ($back==2){
            return makejson(1,'授权续费成功');
        }else if ($back==3){
            return makejson(2,'成功生成卡密',array('kami'=>$newkey));
        }else{
            return makejson($back,$tips);
        }
    }
    if ($tradeinfo['type'] == '4') {
        $nowtime = Get_Date();
        $db->insert_back_id('sq_agent', array('username' => $tradeinfo['user'], 'password' => $tradeinfo['pass'], 'begintime' => time(), 'levelid' => $tradeinfo['fid'], 'status' => 1, 'qq' => $tradeinfo['uqq']));
        $T['title'] = '您的代理已成功开通！';
        $T['content'][] = 'QQ：'.$tradeinfo['uqq'];
        $T['content'][] = '账号：'.$tradeinfo['user'];
        $T['content'][] = '时间：'.Get_Date();
        $T['content'][] = '等级：'.level_idgetname($tradeinfo['fid']);
        $MailTips[] = $T;
        SendTipsMail($tradeinfo['mail'],$MailTips,$back);
        return makejson(1,'代理开通成功');
    }
    return makejson(-404,'订单类型异常');
}

function MakeTradeTips($tradeinfo){
    $T['title'] = '您的订单已成功处理！';
    $T['content'][] = '订单金额：'.$tradeinfo['paymoney'].' 元';
    switch ($tradeinfo['paytype']){
        case 'czkm':
            $T['content'][] = '支付方式：充值卡密';
            break;
        case 'yezf':
            $T['content'][] = '支付方式：代理余额';
            break;
        case 'zxzf':
            switch ($tradeinfo['onlinepaytype']){
                case 'zfb':
                    $T['content'][] = '支付方式：在线支付-支付宝';
                    break;
                case 'wx':
                    $T['content'][] = '支付方式：在线支付-微信支付';
                    break;
                case 'qq':
                    $T['content'][] = '支付方式：在线支付-QQ钱包';
                    break;
                default:
                    $T['content'][] = '支付方式：在线支付-未知';
            }
            break;
        default:
            $T['content'][] = '支付方式：未知方式';
    }
    $T['content'][] = '订单名称：'.$tradeinfo['name'];
    $T['content'][] = '下单时间：'.Get_Date($tradeinfo['begintime']);
    $T['content'][] = '完成时间：'.Get_Date();
    $T['content'][] = '订单编号：'.$tradeinfo['tradeno'];
    return $T;
}

