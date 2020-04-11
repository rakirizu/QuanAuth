-- phpMyAdmin SQL Dump
-- version 5.0.1
-- https://www.phpmyadmin.net/
--
-- 主机： 127.0.0.1
-- 生成日期： 2020-04-11 12:35:58
-- 服务器版本： 10.4.11-MariaDB
-- PHP 版本： 7.4.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- 数据库： `wenquan_auth`
--

-- --------------------------------------------------------

--
-- 表的结构 `sq_admin`
--

CREATE TABLE `sq_admin` (
  `ID` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(33) NOT NULL,
  `loginip` varchar(20) NOT NULL,
  `logintime` int(11) NOT NULL,
  `qq` varchar(12) NOT NULL,
  `lastaccesstime` int(11) NOT NULL,
  `accesstoken` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `sq_agent`
--

CREATE TABLE `sq_agent` (
  `ID` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(64) NOT NULL,
  `begintime` int(11) NOT NULL,
  `logintime` int(11) NOT NULL,
  `loginip` varchar(20) NOT NULL,
  `levelid` int(11) NOT NULL,
  `money` double NOT NULL,
  `status` int(11) NOT NULL,
  `allrecharge` double NOT NULL,
  `allspend` double NOT NULL,
  `qq` bigint(11) NOT NULL,
  `superior` int(11) NOT NULL,
  `accesstoken` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `sq_apps`
--

CREATE TABLE `sq_apps` (
  `ID` int(11) NOT NULL,
  `appname` varchar(100) NOT NULL,
  `bindip` int(1) NOT NULL COMMENT '绑定IP',
  `bindmac` int(1) NOT NULL,
  `bindqq` int(1) NOT NULL,
  `onlinecheck` int(1) NOT NULL,
  `allowchange` int(1) NOT NULL,
  `allowunbind` int(1) NOT NULL,
  `logintype` varchar(4) NOT NULL,
  `usetype` varchar(4) NOT NULL,
  `cycreduce` bigint(20) NOT NULL COMMENT '周期扣除',
  `unbindreduce` bigint(20) NOT NULL COMMENT '解绑扣除',
  `rechargeface` bigint(20) NOT NULL COMMENT '充值面值',
  `onlinesecond` bigint(20) NOT NULL COMMENT '在线秒数',
  `reggive` bigint(20) NOT NULL COMMENT '注册赠送',
  `notice` text NOT NULL,
  `ver` varchar(50) NOT NULL,
  `uplog` text NOT NULL,
  `forceup` int(1) NOT NULL COMMENT '强制更新',
  `upurl` text NOT NULL COMMENT '更新地址',
  `connectkey` varchar(65) NOT NULL COMMENT '应用秘钥',
  `decryptkey` varchar(65) NOT NULL COMMENT '通信秘钥',
  `data` text NOT NULL,
  `introduce` text DEFAULT NULL,
  `imgsrc` text DEFAULT NULL,
  `free` int(1) NOT NULL,
  `close` int(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='所有字段1为开启，除1外均为关闭' ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `sq_bclist`
--

CREATE TABLE `sq_bclist` (
  `ID` int(11) NOT NULL,
  `appid` int(11) NOT NULL,
  `obj` varchar(255) NOT NULL,
  `time` int(11) NOT NULL,
  `reason` text NOT NULL,
  `uid` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `sq_config`
--

CREATE TABLE `sq_config` (
  `ID` int(11) NOT NULL,
  `setname` text NOT NULL,
  `setvalue` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `sq_fidkey`
--

CREATE TABLE `sq_fidkey` (
  `ID` int(11) NOT NULL,
  `kami` varchar(255) NOT NULL,
  `user` varchar(255) NOT NULL,
  `creattime` int(11) NOT NULL,
  `usetime` int(11) NOT NULL,
  `fid` int(11) NOT NULL,
  `status` int(1) NOT NULL,
  `aid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `sq_fidlist`
--

CREATE TABLE `sq_fidlist` (
  `ID` int(11) NOT NULL,
  `fidname` varchar(64) NOT NULL,
  `introduce` text NOT NULL,
  `appid` int(11) NOT NULL,
  `openbuy` int(1) NOT NULL,
  `agentbuy` int(1) NOT NULL,
  `allrechargecard` int(1) NOT NULL,
  `buyprice` double NOT NULL,
  `agentprice` double NOT NULL,
  `num` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `sq_key`
--

CREATE TABLE `sq_key` (
  `ID` bigint(20) NOT NULL,
  `kami` varchar(60) NOT NULL,
  `creattime` int(11) NOT NULL,
  `firstusetime` int(11) NOT NULL,
  `allmoney` double NOT NULL,
  `lastmoney` double NOT NULL,
  `lastusetime` int(11) NOT NULL,
  `status` int(1) NOT NULL,
  `aid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `sq_level`
--

CREATE TABLE `sq_level` (
  `ID` int(11) NOT NULL,
  `appid` varchar(100) NOT NULL,
  `lname` varchar(100) NOT NULL,
  `fracture` float NOT NULL COMMENT '折率',
  `price` int(11) NOT NULL,
  `discount` float NOT NULL COMMENT '等级开通折率',
  `subordinate` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `sq_log_agent`
--

CREATE TABLE `sq_log_agent` (
  `ID` int(11) NOT NULL,
  `aid` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `msg` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `sq_log_kami`
--

CREATE TABLE `sq_log_kami` (
  `ID` int(11) NOT NULL,
  `keyid` int(11) NOT NULL,
  `type` varchar(10) NOT NULL,
  `spendmoney` double NOT NULL,
  `lastmoney` double NOT NULL,
  `time` int(11) NOT NULL,
  `object` varchar(100) NOT NULL,
  `IP` varchar(20) NOT NULL,
  `msg` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `sq_log_system`
--

CREATE TABLE `sq_log_system` (
  `ID` int(11) NOT NULL,
  `time` int(11) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `msg` text NOT NULL,
  `type` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `sq_token`
--

CREATE TABLE `sq_token` (
  `token` varchar(64) NOT NULL,
  `uid` bigint(20) NOT NULL,
  `lastest` int(11) NOT NULL,
  `start` int(11) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `addtime` int(11) NOT NULL,
  `appid` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `sq_trade`
--

CREATE TABLE `sq_trade` (
  `ID` int(11) NOT NULL,
  `tradeno` varchar(32) NOT NULL,
  `name` varchar(255) NOT NULL,
  `begintime` int(11) NOT NULL,
  `user` varchar(128) NOT NULL,
  `pass` varchar(128) NOT NULL,
  `fid` int(11) NOT NULL,
  `num` bigint(20) NOT NULL,
  `paymoney` double NOT NULL,
  `mail` varchar(64) NOT NULL,
  `paytype` varchar(8) NOT NULL,
  `onlinepaytype` varchar(8) NOT NULL,
  `kami` varchar(64) NOT NULL,
  `overtime` int(11) NOT NULL,
  `ip` varchar(20) NOT NULL,
  `type` int(11) NOT NULL,
  `status` int(1) NOT NULL,
  `agentid` int(11) NOT NULL,
  `uqq` varchar(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

-- --------------------------------------------------------

--
-- 表的结构 `sq_user`
--

CREATE TABLE `sq_user` (
  `ID` int(11) NOT NULL,
  `username` varchar(128) NOT NULL,
  `password` varchar(128) NOT NULL,
  `mac` varchar(64) NOT NULL,
  `rip` varchar(20) NOT NULL,
  `lip` varchar(20) NOT NULL,
  `uqq` varchar(11) NOT NULL,
  `mail` varchar(64) NOT NULL,
  `rtime` int(11) NOT NULL,
  `ltime` int(11) NOT NULL,
  `balance` bigint(20) NOT NULL,
  `htime` int(11) NOT NULL,
  `status` int(1) NOT NULL,
  `mailcode` varchar(6) NOT NULL,
  `sendtime` int(11) NOT NULL,
  `rqq` varchar(11) NOT NULL,
  `appid` int(11) NOT NULL,
  `lastreducetime` int(11) NOT NULL,
  `aid` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `origin` int(1) NOT NULL,
  `oid` bigint(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- 转储表的索引
--

--
-- 表的索引 `sq_admin`
--
ALTER TABLE `sq_admin`
  ADD PRIMARY KEY (`ID`) USING BTREE,
  ADD KEY `username` (`username`) USING BTREE,
  ADD KEY `accesstoken` (`accesstoken`) USING BTREE;

--
-- 表的索引 `sq_agent`
--
ALTER TABLE `sq_agent`
  ADD PRIMARY KEY (`ID`) USING BTREE,
  ADD KEY `username` (`username`) USING BTREE,
  ADD KEY `accesstoken` (`accesstoken`) USING BTREE,
  ADD KEY `superior` (`superior`) USING BTREE;

--
-- 表的索引 `sq_apps`
--
ALTER TABLE `sq_apps`
  ADD PRIMARY KEY (`ID`) USING BTREE;

--
-- 表的索引 `sq_bclist`
--
ALTER TABLE `sq_bclist`
  ADD PRIMARY KEY (`ID`) USING BTREE,
  ADD KEY `qq` (`obj`) USING BTREE,
  ADD KEY `appid` (`appid`) USING BTREE;

--
-- 表的索引 `sq_config`
--
ALTER TABLE `sq_config`
  ADD PRIMARY KEY (`ID`) USING BTREE;

--
-- 表的索引 `sq_fidkey`
--
ALTER TABLE `sq_fidkey`
  ADD PRIMARY KEY (`ID`) USING BTREE,
  ADD KEY `fid` (`fid`) USING BTREE,
  ADD KEY `kami` (`kami`) USING BTREE,
  ADD KEY `aid` (`aid`) USING BTREE;

--
-- 表的索引 `sq_fidlist`
--
ALTER TABLE `sq_fidlist`
  ADD PRIMARY KEY (`ID`) USING BTREE,
  ADD KEY `appid` (`appid`) USING BTREE;

--
-- 表的索引 `sq_key`
--
ALTER TABLE `sq_key`
  ADD PRIMARY KEY (`ID`) USING BTREE,
  ADD KEY `kami` (`kami`) USING BTREE,
  ADD KEY `aid` (`aid`) USING BTREE;

--
-- 表的索引 `sq_level`
--
ALTER TABLE `sq_level`
  ADD PRIMARY KEY (`ID`) USING BTREE,
  ADD KEY `appid` (`appid`) USING BTREE,
  ADD KEY `appid_2` (`appid`) USING BTREE;

--
-- 表的索引 `sq_log_agent`
--
ALTER TABLE `sq_log_agent`
  ADD PRIMARY KEY (`ID`) USING BTREE,
  ADD KEY `username` (`aid`) USING BTREE;

--
-- 表的索引 `sq_log_kami`
--
ALTER TABLE `sq_log_kami`
  ADD PRIMARY KEY (`ID`) USING BTREE,
  ADD KEY `kami` (`keyid`) USING BTREE,
  ADD KEY `keyid` (`keyid`) USING BTREE;

--
-- 表的索引 `sq_log_system`
--
ALTER TABLE `sq_log_system`
  ADD PRIMARY KEY (`ID`) USING BTREE;

--
-- 表的索引 `sq_token`
--
ALTER TABLE `sq_token`
  ADD PRIMARY KEY (`token`) USING BTREE,
  ADD KEY `uid` (`uid`) USING BTREE,
  ADD KEY `lastest` (`lastest`) USING BTREE,
  ADD KEY `addtime` (`addtime`) USING BTREE,
  ADD KEY `appid` (`appid`) USING BTREE;

--
-- 表的索引 `sq_trade`
--
ALTER TABLE `sq_trade`
  ADD PRIMARY KEY (`ID`) USING BTREE,
  ADD KEY `tradeno` (`tradeno`) USING BTREE,
  ADD KEY `begintime` (`begintime`) USING BTREE;

--
-- 表的索引 `sq_user`
--
ALTER TABLE `sq_user`
  ADD PRIMARY KEY (`ID`) USING BTREE,
  ADD KEY `username` (`username`) USING BTREE,
  ADD KEY `mac` (`mac`,`lip`,`rqq`) USING BTREE,
  ADD KEY `appid` (`appid`) USING BTREE,
  ADD KEY `aid` (`aid`) USING BTREE,
  ADD KEY `token` (`token`) USING BTREE;

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `sq_admin`
--
ALTER TABLE `sq_admin`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `sq_agent`
--
ALTER TABLE `sq_agent`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `sq_apps`
--
ALTER TABLE `sq_apps`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `sq_bclist`
--
ALTER TABLE `sq_bclist`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `sq_config`
--
ALTER TABLE `sq_config`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `sq_fidkey`
--
ALTER TABLE `sq_fidkey`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `sq_fidlist`
--
ALTER TABLE `sq_fidlist`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `sq_key`
--
ALTER TABLE `sq_key`
  MODIFY `ID` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `sq_level`
--
ALTER TABLE `sq_level`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `sq_log_agent`
--
ALTER TABLE `sq_log_agent`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `sq_log_kami`
--
ALTER TABLE `sq_log_kami`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `sq_log_system`
--
ALTER TABLE `sq_log_system`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `sq_trade`
--
ALTER TABLE `sq_trade`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- 使用表AUTO_INCREMENT `sq_user`
--
ALTER TABLE `sq_user`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
