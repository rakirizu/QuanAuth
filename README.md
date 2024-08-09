## 温泉PHP网络授权系统Beta

> 警告：原作者已不再更新程序，可能会存在安全漏洞、未知BUG，请谨慎使用；本项目由开源社区中的诸位自行维护。

### 许可协议
本程序仅供软件授权、学习研究、技术交流使用，严禁用于包括但不限于色情、赌博、诈骗等违反中华人民共和国相关法律法规或违反使用者所在地法律法规；本作者不再提供任何技术支持、出售、贩卖。
严禁将本程序二次开发后进行出售，请共同维护一个良好的开源环境。
您可以任意修改本程序，但您所做的修改必须通过 Pull Requests 提交到本仓库。


### 安装说明：

1.导入文件 “wenquan_auth.sql” 到您的数据库中 *** 导入完成后请务必删除数据库文件 ***

2.修改配置文件 “function/config.inc.php” 文件中的信息为您的数据库信息

3.通过执行以下SQL语句新增管理员（请自行将各项参数替换）： 
```sql
INSERT INTO sq_admin (ID, username, password, loginip, logintime, qq, lastaccesstime, accesstoken) VALUES (NULL, '您的用户名', MD5('您的密码'), '', unix_timestamp(now()) , '您的QQ', unix_timestamp(now()), '这里写64位随机字符');
```


上方执行示例：
```sql
INSERT INTO sq_admin (ID, username, password, loginip, logintime, qq, lastaccesstime, accesstoken) VALUES (NULL, '温泉', MD5('123456789'), '', unix_timestamp(now()) , '80071319', unix_timestamp(now()), 'JjYBHCBSNeuoNnkTVRCf5EENhZWDcolJOh7MQlmV9pn4S4p4SZFHJaSH75MBK3OZ');
```

以上语句表示新增一个管理员，用户名为【温泉】，密码为【123456789】，管理员QQ为【80071319】。

> 关于随机字符生成可使用：https://suijimimashengcheng.51240.com/

