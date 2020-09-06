<?php
/**
 * Created by PhpStorm.
 * User: 温泉
 * Date: 2017-10-15
 * Time: 12:07
 */



define('IN_SYS', true);//初始化系统



error_reporting(E_ALL);

/*
 * 初始化脚本
 */
session_set_cookie_params(time() + 2592000);//保持登陆30天
session_start();//开启SESSION功能
date_default_timezone_set('PRC');//设置当前时区为中国
header("Content-type: text/html; charset=utf-8");
//过滤xss代码
foreach ($_POST as $key => $value) {
    $_POST[$key] = xss_clean($value);
}
foreach ($_GET as $key => $value) {
    $_GET[$key] = xss_clean($value);
}
foreach ($_COOKIE as $key => $value) {
    $_COOKIE[$key] = xss_clean($value);
}
foreach ($_REQUEST as $key => $value) {
    $_REQUEST[$key] = xss_clean($value);
}

function makejson($code = 1,$msg = 'ok',$data = array()){
    return json_encode(array_merge(array('code'=>intval($code),'msg'=>$msg),$data));
}

/*
 * 初始化数据库链接
 */

$G = array();
include 'db.class.php';
include 'config.inc.php';
$return = $db = new db($G['db']['server'], $G['db']['user'], $G['db']['pass'], $G['db']['dbname']);
if (!$return) {
    die('数据库链接失败，请检查设置是否正确:' . $db->geterror());
}


/*
 * 读取系统相关设置
 */
$back = $db->select_all_row('sq_config', array('*'), '', '');

foreach ($back as $value) {
    $G['config'][$value['setname']] = $value['setvalue'];
}
include  'setname.inc.php';
foreach ($SetName as $key=>$value){
    if (!isset($G['config'][$key])){
        $db->insert_back_id('sq_config',array('setname'=>$key,'setvalue'=>$value));
    }
}
$back = $db->select_all_row('sq_config', array('*'), '', '');
foreach ($back as $value) {
    $G['config'][$value['setname']] = $value['setvalue'];
}
$G['config']['kqxt'] = numtobool($G['config']['kqxt']);
$G['config']['yjtx'] = numtobool($G['config']['yjtx']);
$G['config']['xtsy'] = numtobool($G['config']['xtsy']);
$G['config']['epay_againcheck'] = numtobool($G['config']['epay_againcheck']);
$G['sys']['rootdir'] = dirname(__DIR__);
/*
$_SESSION['auth']['checktime'] = 0;
if (empty($_SESSION['auth']['checktime']) || time() - $_SESSION['auth']['checktime'] > 3600) {
    if (isset($ServerDomain)) unset($ServerDomain);
    $ServerDomain['1'] = 'https://new.api.shouquan.wenquan6.cn:99/api.php';
    $ServerDomain['2'] = 'https://new.api.shouquan.wenquan.cleverqq.cn:99/api.php';
    $ServerDomain['3'] = 'http://new.api.shouquan.wenquan6.cn:98/api.php';
    $ServerDomain['4'] = 'http://new.api.shouquan.wenquan.cleverqq.cn:98/api.php';
    $ServerDomain['5'] = 'http://api.shouquan.wenquan6.cn/api.php';
    $ServerDomain['6'] = 'http://api.shouquan.wenquan.cleverqq.cn/api.php';

    if (isset($ServerDomain[(string)$G['config']['sid']]) && (time() - $G['config']['stime']) < 86400){
        $auth = json_decode(curl_request($ServerDomain[$G['config']['sid']].'?mod=checkauth&domain=' . $_SERVER['HTTP_HOST'].'&sitekey='.$G['config']['sitekey']), true);
    }

    if (empty($auth['code'])) {
        foreach ($ServerDomain as $key=>$value){
            $auth = json_decode(curl_request($value.'?mod=checkauth&domain=' . $_SERVER['HTTP_HOST'].'&sitekey='.$G['config']['sitekey']), true);
            if (!empty($auth['code'])){
                updateset('sid',$key);
                updateset('stime',time());
                break;
            }
        }
    }

    if ($auth['code'] == '-3') {
        die($auth['tips']);
    }

}

if ($auth['code'] == '1'){
    $_SESSION['auth']['checktime'] = time();
    $ServerURL = $ServerDomain[$G['config']['sid']];
}
*/

$ServerURL = 'https://new.api.shouquan.wenquan6.cn:99/api.php';
include 'ver.inc.php';
include_once 'MailTipsTemplate.php';
/*
 *  注册申明常用函数
 */


$http_type = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? 'https://' : 'http://';

/*
 * 一些系统提示，用alert并且跳转页面
 */
function tips($message, $url = 'javascript:history.go(-1);')
{
    die('<html lang="zh">
<head>
    <title>系统提示</title>
    <script type="text/javascript">
        alert("' . $message . '");
        window.location.href=\'' . $url . '\';
    </script>
</head>
<body>
</body>
</html>');
}

/*
 * 获取用户的真实IP
 */
function get_real_ip()
{
    $ip=FALSE;
    //客户端IP 或 NONE
    if(!empty($_SERVER["HTTP_CLIENT_IP"])){
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    }
    //多重代理服务器下的客户端真实IP地址（可能伪造）,如果没有使用代理，此字段为空
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
        if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
        for ($i = 0; $i < count($ips); $i++) {
            if (!preg_match ("/^(10│172.16│192.168)./", $ips[$i])) {
                $ip = $ips[$i];
                break;
            }
        }
    }
    return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
}

/*
 * 将时间戳转换为北京标准时间，如果时间戳为空则是取当前时间
 */
function Get_Date($time = '')
{
    if (empty($time)) {
        $time = time();
    }
    if (!is_numeric($time)) {
        return $time;
    }
    return date('Y-m-d H:i:s', $time);
}

/*
 * 随机字符生成
 */
function rand_str($length, $p = 'all')
{
    $yznums = '0123456789';
    $lowers = 'abcdefghijklmnopqrstuvwxyz';
    $uppers = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    if ($p == 'all') {
        $src = $yznums . $lowers . $uppers;
    } else {
        $src = '';
        if (strpos($p, 'num') !== false)
            $src .= $yznums;
        if (strpos($p, 'lower') !== false)
            $src .= $lowers;
        if (strpos($p, 'upper') !== false)
            $src .= $uppers;
    }
    return $src ? substr(str_shuffle($src), 0, $length) : $src;
}

/**
 * 更新系统设置
 */
function updateset($name, $value)
{
    global $G,$db;
    $db->update('sq_config', array('setname' => $name), 'AND', array('setvalue' => $value));
    $G['config'][$name] = $value;
}


/*
 * 布尔逻辑型转换为数字0或1
 */
function booltonum($bool)
{
    if ($bool) {
        return 1;
    } else {
        return 0;
    }
}

/*
 * 布尔文本型转换为数字0或1
 */
function textbooltonum($text)
{
    if ($text == 'on' || $text == 'true') {
        return '1';
    } else {
        return '0';
    }
}

/*
 * 数字还原为普通的布尔逻辑值
 */
function numtobool($num)
{
    if ($num == 1) {
        return true;
    } else {
        return false;
    }
}

/**
 * XSS暴力过滤神器，防止xss攻击
 */
function xss_clean($data)
{
    // Fix &entity\n;
    $data = str_replace(array('&amp;', '&lt;', '&gt;'), array('&amp;amp;', '&amp;lt;', '&amp;gt;'), $data);
    $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
    $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
    $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');
    // Remove any attribute starting with "on" or xmlns
    $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);
    // Remove javascript: and vbscript: protocols
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
    $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);
    // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
    $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);
    // Remove namespaced elements (we do not need them)
    $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);
    // http://www.111cn.net/
    do {// Remove really unwanted tags
        $old_data = $data;
        $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
    } while ($old_data !== $data);
    // we are done...
    return $data;
}

/**
 * @param $second
 * @return string
 */
function time_last($second)
{
    if ($second < 0) {
        return '已结束';
    }
    $day = floor($second / 86400);
    $second = $second - $day * 86400;

    $hour = floor($second / 3600);
    $second = $second - $hour * 3600;


    $minute = floor($second / 60);
    $second = $second - $minute * 60;

    return $day . '天' . $hour . '时' . $minute . '分' . $second . '秒';
}

/**
 * 使用CURL进行HTTP请求
 * 参数1：访问的URL，
 * 参数2：post数据(不填则为GET)，
 * 参数3：提交的$cookies,
 * 参数4：是否返回$cookies
 */
function curl_request($url, $post = '', $cookie = '', $returnCookie = 0)
{
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/6.0)');
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1);
    curl_setopt($curl, CURLOPT_REFERER, "http://XXX");
    if ($post) {
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
    }
    if ($cookie) {
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
    }
    curl_setopt($curl, CURLOPT_HEADER, $returnCookie);
    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    $data = curl_exec($curl);
    if (curl_errno($curl)) {
        return '';
    }
    curl_close($curl);
    if ($returnCookie) {
        list($header, $body) = explode("\r\n\r\n", $data, 2);
        preg_match_all("/Set\-Cookie:([^;]*);/", $header, $matches);
        $info['cookie'] = substr($matches[1][0], 1);
        $info['content'] = $body;
        return $info;
    } else {
        return $data;
    }
}


/**
 * 调用温泉云平台进行邮件发送
 */
function sendemail($title, $contant, $tomail, &$msg = '', $debug = false)
{
    global $G,$ServerURL;
	require_once ('PHPMailer.php');
    require_once ('SMTP.php');
    require_once ('Exception.php');
    $mail = new \PHPMailer\PHPMailer\PHPMailer();
    $mail->SMTPDebug = $debug;
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Host = $G['config']['smtp_address'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = $G['config']['smtp_port'];
    $mail->CharSet = 'UTF-8';
    $mail->FromName = $G['config']['sitename'];
    $mail->Username = $G['config']['smtp_user'];
    $mail->Password = $G['config']['smtp_pass'];
    $mail->From = $G['config']['smtp_user'];
    $mail->isHTML(true);
    $mail->addAddress($tomail);
    $mail->Subject = $title;
    $mail->Body = $contant;
    $mail->SMTPOptions = array(
       'ssl' => array(
           'verify_peer' => false,
           'verify_peer_name' => false,
           'allow_self_signed' => true
        )
    );
	echo $mail->SMTPDebug;
    $re = $mail->send();
    if($re){
        return true;
    }else{
        $msg = '邮件发送失败返回错误：'.$mail->ErrorInfo;
        return false;
    }
	
	
	/*
    foreach ($_POST as $v=>$value){
        $_POST[$v] = urlencode($value);
    }
    $ev['server'] = urlencode($G['config']['smtp_address']);
    $ev['port'] = urlencode($G['config']['smtp_port']);
    $ev['username'] = urlencode($G['config']['smtp_user']);
    $ev['password'] = urlencode($G['config']['smtp_pass']);
    $ev['formname'] = urlencode($G['config']['sitename']);
    $ev['tomail'] = urlencode($tomail);
    $ev['subject'] = urlencode($title);
    $ev['content'] = urlencode($contant);
    //$post = "server={$ev['server']}&port={$ev['port']}&username={$ev['username']}&password={$ev['password']}&formname={$ev['formname']}&tomail={$ev['tomail']}&subject={$ev['subject']}&content={$ev['content']}";
    $back = curl_request($ServerURL.'?mod=sendmail&domain=' . urlencode($_SERVER['HTTP_HOST']) . '&sitekey=' . $G['config']['sitekey'], $post);
    if (!$json = json_decode($back, true)) {
        $msg = '返回数据解析失败：'.$back;
        return false;
    } else {
        if ($json['code'] != 1) {
            $msg = $json['msg'];
            return false;
        }
        $msg = 'success';
        return true;
    }
	*/
}

function show_page_404()
{
    die('404 您访问的页面不存在！');
}


function SendTipsMail($tomail,$content,&$BackMsg,$debug = false){

    global $G,$MailTipsCon,$MailTipsFram,$MailTipsLine;
    $c = '';
    foreach ($content as $array){
        $con = '';
        foreach ($array['content'] as $line){
            $con .= str_replace('[content]',$line,$MailTipsLine);
        }
        $c .= str_replace('[content]',$con,str_replace('[title]',$array['title'],$MailTipsCon));
    }
    $html = str_replace('[content]',$c,$MailTipsFram);
    return sendemail('系统提醒 - '.$G['config']['sitename'],$html,$tomail,$BackMsg,$debug);
}

function GetOriginText($origin){
    if ($origin == 1){
        return '管理后台';
    }else if ($origin == 2){
        return '前台购买';
    }else if ($origin == 3){
        return '套餐卡密';
    }else if ($origin == 4){
        return '代理中心';
    }else{
        return '未知来源';
    }
}
