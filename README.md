## 温泉PHP网络授权系统Beta

警告：源码原作者已放弃更新，可能会存在安全漏洞、未知BUG，请谨慎使用；同时也欢迎各位大佬帮忙维护此项目。

### 安装说明：

1.导入文件 “wenquan_auth.sql” 到您的数据库中 *** 导入完成后请务必删除数据库文件 ***

2.修改配置文件 “function/config.inc.php” 文件中的信息为您的数据库信息

3.通过执行以下代码新增管理员（请自行将各项参数替换）： `INSERT INTO sq_admin (ID, username, password, loginip, logintime, qq, lastaccesstime, accesstoken) VALUES (NULL, '您的用户名', MD5('您的密码'), '', unix_timestamp(now()) , '您的QQ', unix_timestamp(now()), '这里写64位随机字符');`

上方执行示例：

INSERT INTO sq_admin (ID, username, password, loginip, logintime, qq, lastaccesstime, accesstoken) VALUES (NULL, '温泉', MD5('123456789'), '', unix_timestamp(now()) , '80071319', unix_timestamp(now()), 'JjYBHCBSNeuoNnkTVRCf5EENhZWDcolJOh7MQlmV9pn4S4p4SZFHJaSH75MBK3OZ');

以上语句表示新增一个管理员，用户名为【温泉】，密码为【123456789】，管理员QQ为【80071319】。

> 关于随机字符生成可使用：https://suijimimashengcheng.51240.com/

