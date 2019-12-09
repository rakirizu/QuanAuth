<?php
//页面全局
$MailTipsFram = <<<Fram
<p>尊敬的用户，上午好</p>
<p>这封邮件是您在平台执行了某项操作发送的，若您并未进行任何操作请忽略此邮件并对邮件内容保密。</p>

[content]
<center>
<p><a href="{$G['config']['weburl']}query.php" style="display:inline-block;padding:4px 30px;border-radius:1px;color:#0083ff;border:2px solid #0083ff;text-decoration:none;margin-top:20px;box-shadow: 1px 1px 2px #888888;" target="_blank">查询中心</a>&nbsp;  <a href="tencent://message/?uin={$G['config']['adminqq']}" style="display:inline-block;padding:4px 25px;border-radius:1px;color:#0083ff;border:2px solid #0083ff;text-decoration:none;margin-top:20px;box-shadow: 1px 1px 2px #888888;" target="_blank">联系客服</a></p>

<small><p>Powered by {$_SERVER['HTTP_HOST']}</p></small></center>
Fram;

//内容+标题模板行
$MailTipsCon = <<<CON
<div style="box-shadow: 1px 1px 2px #888888;background:#fbfbfb;padding:10px 20px;font-size:18px;border-left:3px solid #0083ff;margin-top:20px;">
[title]<br>
<div style="margin-left:40px;margin-top:5px;font-size:16px;">
[content]
</div>
</div>
CON;

//内容行
$MailTipsLine = <<<Lin
[content]<br>
Lin;


