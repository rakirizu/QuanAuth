<?php
/**
 * 本页面为未执行数据监控程序，通过监控本脚本实现自动补单、清理数据库中的沉余数据，使程序运行效率更高
 * 注：本脚本会由温泉PHP网络授权系统云端监控程序自动监控访问，您无需自己监控，请将此文件所在目录添加到防攻击白名单中，否则无法监控
 */

//设置链接断开仍然处理
set_time_limit(0);
ignore_user_abort(1);

//调用核心文件
include '../function/function_core.php';
//获取已支付但未同步的订单信息
$error = array();
$log = array();
if(!!$result = $db->select_limit_row('sq_trade','*','',5,array('status'=>'2'),'AND') && count($result) > 0){
    include '../function/trade.inc.php';
    foreach ($result as $tradeinfo){
        if (!$db->update('sq_trade', array('tradeno' => $tradeno,'status'=>'2'), 'AND', array('status' => 3)))
            $error[] = '[监控信息]订单状态更新失败：'.$tradeinfo['tradeno'].'[Error:'.$db->geterror().']';
        else
            $log[] = '[监控信息] 订单号处理成功';
            trade_do($tradeinfo);
    }
}

//清理初始化后60分钟未操作的Token数据
if($db->delete('sq_token','`lastest` = 0 AND `addtime` < '.(time()-3600).' AND `start` = 0','')){
    $num = $db->affected_num();
    if ($num > 0){
        $log[] = '[监控信息] 清理初始化后60分钟未操作的Token数据'.$num.'条';
    }
}else{
    $log[] = '[监控信息] 清理过程中发生错误：'.$db->geterror();
}

//清理心跳过期数据
if(!$result = $db->select_all_row('sq_apps','ID,appname,onlinesecond')){
    $log[] = '[监控信息] 获取应用信息发生错误：'.$db->geterror();
}
foreach ($result as $appinfo){
    if ($appinfo['onlinesecond'] >0 ){
        $OutTime = $appinfo['onlinesecond'] * 3;
        if($db->delete('sq_token','`appid` = '.$appinfo['ID'].' AND `lastest` < '.$OutTime,'')){
            $num = $db->affected_num();
            if ($num > 0) {
                $log[] = '[监控信息] 清理 '.$appinfo['appname'].' 过期Token数据' . $num . '条';
            }
        }else{
            $log[] = '[监控信息] 清理 '.$appinfo['appname'].' 过期数据发生错误' . $db->geterror();
        }
    }
}

