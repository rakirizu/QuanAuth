## 温泉PHP网络授权系统Beta
### 数据库更新时间：2019-7-29 10:35

### 安装说明：

1.导入文件 “wenquan_auth.sql” 到您的数据库中 *** 导入完成后请务必删除数据库文件 ***

2.修改配置文件 “function/config.inc.php” 文件中的信息为您的数据库信息

3.通过执行以下代码新增管理员（请自行将各项参数替换）： `INSERT INTO sq_admin (ID, username, password, loginip, logintime, qq, lastaccesstime, accesstoken) VALUES (NULL, '您的用户名', MD5('您的密码'), '', unix_timestamp(now()) , '您的QQ', unix_timestamp(now()), '这里写64位随机字符');`

上方执行示例：

INSERT INTO sq_admin (ID, username, password, loginip, logintime, qq, lastaccesstime, accesstoken) VALUES (NULL, '温泉', MD5('123456789'), '', unix_timestamp(now()) , '80071319', unix_timestamp(now()), 'JjYBHCBSNeuoNnkTVRCf5EENhZWDcolJOh7MQlmV9pn4S4p4SZFHJaSH75MBK3OZ');

以上语句表示新增一个管理员，用户名为【温泉】，密码为【123456789】，管理员QQ为【80071319】。

> 关于随机字符生成可使用：https://suijimimashengcheng.51240.com/



### 注意事项：

1.此仓库中的文件为温泉PHP授权系统最新版文件<br>
2.在您每次升级更新之前，请备份好您的数据以便回滚<br>
3.此页面地址属于内部地址，请勿公开分享、发表<br>
4.此授权程序除核心文件外均为源码，未经许可谢绝转载二开<br>
5.若您有闲心和能力，欢迎注册账号加入此仓库的维护<br>
6.文件更新后请注意数据库同步更新，否则会出错！<br>
7.最终解释权归温泉所有，测试过程中遇到问题请及时提交issue，感谢您的支持！
<br><br><br>

<h1>数据库同步说明</h1><br>
<h3>数据库信息：</h3>
服务器：gitlab.acequan.com<br>
端口号：3306<br>
数据库：QuanAuth<br>
用户名：QuanAuth  (注意大小写)<br>
密码：shouquan.acequan.com<br>
<small>*该用户为只读权限，请不要尝试修改数据库！</small>
<h3>同步方式：</h3>

采用Navicat软件进行同步，请参考看云文档：<span>http://doc.shouquan.wenquan6.cn/740819</span>，教程中使用到的链接信息请使用上方的数据库信息.
注意！！！！后面的语句无需执行！后面的语句无需执行！后面的语句无需执行！无需执行SQL语句！！！