<?php include 'function/function_core.php';
if (!$G['config']['xtsy']) die(); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="Material Design Lite">
    <title>授权在线开通 - <?php echo $G['config']['sitename'] ?></title>
    <link rel="stylesheet" href="./mdl/material.min.css">
    <?php include './function/color.inc.html' ?>
</head>
<style>
    .demo-card-square.mdl-card {
       -webkit-transition: all .4s;
    width: 350px;
    height: 350px;
    margin-left: auto;
    margin-right: auto;
    }
</style>
<body class="flat-blue landing-page">
<!-- Always shows a header, even in smaller screens. -->
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header" id="content">

    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <!-- Title -->
            <span class="mdl-layout-title">授权在线开通 - <?php echo $G['config']['sitename'] ?></span>
            <!-- Add spacer, to align navigation to the right -->
            <div class="mdl-layout-spacer"></div>
            <!-- Navigation. We hide it in small screens. -->
            <nav class="mdl-navigation mdl-layout--large-screen-only">
                <?php echo $G['config']['mainnav'] ?>
            </nav>
        </div>
    </header>
    <div class="mdl-layout__drawer">
        <span class="mdl-layout-title"><?php echo $G['config']['sitename'] ?></span>
        <nav class="mdl-navigation">
            <?php echo $G['config']['mainnav'] ?>
        </nav>
    </div>

        <center>
        <main class="mdl-layout__content" style="width: 100%;">
                <div style="display: inline;">
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 95%">
                        <select class="mdl-textfield__input" style="width: 100%" id="applist" onchange="loadbuylist();">
                            <option value="0">请选择一个应用</option>
                        </select>
                        <label class="mdl-textfield__label" for="sample3">选择您需要购买的应用</label>
                    </div>
                </div>

                <div style="width:100%; " id="buylist">
                    数据正在等待中
                </div>

        </main>
        </center>

    <!--    <footer class="mdl-mini-footer" id="footer" >
        <div class="mdl-mini-footer__left-section">
            <ul class="mdl-mini-footer__link-list">
                <li>©<?php /*echo $G['config']['sitename']*/ ?> </li>
                <?php /*if (!empty($G['config']['beian'])) echo '<li><a href="http://www.miitbeian.gov.cn" target="_blank">'.$G['config']['beian'].'</a></li>'*/ ?>
            </ul>


        </div>
        <div class="mdl-mini-footer__right-section">
            <ul class="mdl-mini-footer__link-list">
                <li>客服QQ：<a href="http://wpa.qq.com/msgrd?v=3&uin=<?php /*echo $G['config']['adminqq']*/ ?>&site=qq&menu=yes" target="_blank"><?php /*echo $G['config']['adminqq']*/ ?></a></li>
                <li>客服邮箱：<a href="http://wpa.qq.com/msgrd?v=3&uin=<?php /*echo $G['config']['adminmail']*/ ?>&site=qq&menu=yes" target="_blank"><?php /*echo $G['config']['adminmail']*/ ?></a></li>
            </ul>
        </div>
    </footer>-->
</div>


<script src="./static/jquery-3.3.1.js"></script>
<script type="text/javascript" src="./static/frame/layui/layui.js"></script>
<script src="./mdl/material.min.js"></script>
<script>
    layui.use(['layer'], function () {

        window.layer = layui.layer;
        var loading = layer.load();
        var loading2 = layer.load();
        $.ajax({
            url: 'ajax.php?mod=buy_applist',
            type: 'POST',
            dataType: 'html',
            data: '',
            success: function (data) {
                layer.close(loading);
                if (data === '' || data === null) {
                    layer.open({
                        type: 1
                        ,
                        title: false
                        ,
                        closeBtn: false
                        ,
                        area: '300px;'
                        ,
                        shade: 0.8
                        ,
                        id: 'sitenotice'
                        ,
                        resize: false
                        ,
                        btn: ['确定']
                        ,
                        btnAlign: 'c'
                        ,
                        moveType: 1
                        ,
                        content: '<div style="padding: 20px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">站长没有添加任何应用</div>'
                        ,
                        success: function (layero) {
                        }
                    });
                } else {
                    $('#applist').html(data);
                    if (getQueryVariable('appid') !== false) {
                        $("#applist").val(getQueryVariable('appid'));
                    }
                }
            },
            error: function (data) {
                layer.close(loading);
                layer.msg('请求失败' + data);
            }
        });

        if (getQueryVariable('appid') !== false) {
            var loadbuylist = 'loadbuylist=' + getQueryVariable('appid');
        } else {
            var loadbuylist = '';
        }
        $.ajax({
            url: 'ajax.php?mod=loadbuylist',
            type: 'POST',
            dataType: 'html',
            data: loadbuylist,
            success: function (data) {
                layer.close(loading2);
                if (data === '' || data === null) {
                    layer.open({
                        type: 1
                        ,
                        title: false
                        ,
                        closeBtn: false
                        ,
                        area: '300px;'
                        ,
                        shade: 0.8
                        ,
                        id: 'sitenotice'
                        ,
                        resize: false
                        ,
                        btn: ['确定']
                        ,
                        btnAlign: 'c'
                        ,
                        moveType: 1
                        ,
                        content: '<div style="padding: 20px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">站长没有添加任何应用</div>'
                        ,
                        success: function (layero) {
                        }
                    });
                } else {
                    $('#buylist').html(data);
                    //footerchange();
                }
            },
            error: function (data) {
                layer.close(loading2);
                layer.msg('请求失败' + data);
            }
        })
    });


    function loadbuylist() {
        var loadbuylist = $("#applist").val();
        var loading = layer.load();
        $.ajax({
            url: 'ajax.php?mod=loadbuylist',
            type: 'POST',
            dataType: 'html',
            data: 'loadbuylist=' + loadbuylist,
            success: function (data) {

                layer.close(loading);
                if (data === '' || data === null) {
                    layer.msg('没有数据');
                } else {
                    $('#buylist').html(data);
                    //footerchange();
                }

            },
            error: function (data) {
                layer.close(loading);
                layer.msg('请求失败' + data);
            }
        })
    }



    /*function footerchange() {
        var _ch =document.getElementById("footer").scrollHeight;//这个就是你中间内容div的高度
        var _wh = $(window).height();//整个窗口的高度
        var _bd = $("#content").height();

        if (_ch > _wh - _bd){
            $("#footer").css("margin-top","1px");
        }else{
            $("#footer").css("margin-top",(_wh - _bd -_ch)+"px");
        }
    }*/
    function buy(id) {
        layer.open({
            type: 2,
            title: '开通授权(商品ID:' + id + ')',
            shadeClose: true,
            shade: 0.8,
            area: ['90%', '90%'],
            content: 'ajax.php?mod=buy_first&fid=' + id //iframe的url
        });
    }

    function buy_submit(id, type) {
        var loading = layer.load();
        if (type === 1) {
            //账号密码登陆
            var kt_user = $("#kt_user").val();
            var kt_pass = $("#kt_pass").val();
            var kt_varinfo = '&kt_user=' + encodeURI(kt_user) + '&kt_pass=' + kt_pass;
        } else if (type === 2) {
            //卡密授权
            var kt_kami = $("#kt_user").val();
            var kt_varinfo = '&kt_user=' + encodeURI(kt_kami);
            var kt_kaminum = $("#kt_keysnum").val();
        } else if (type === 3) {
            //检查绑定
            var kt_robotqq = $("#kt_robotqq").val();
            var kt_mac = $("#kt_mac").val();
            var kt_ip = $("#kt_ip").val();
            var kt_varinfo = '&kt_robotqq=' + kt_robotqq + '&kt_mac=' + kt_mac + '&kt_ip=' + encodeURI(kt_ip);
        }
        var kt_num = $("#kt_num").val();
        var kt_adminqq = $("#kt_adminqq").val();
        var kt_mail = $("#kt_mail").val();
        var zffs = $('input:radio[name="zffs"]:checked').val();
        var zxzffs = $('input:radio[name="zxzffs"]:checked').val();
        var kt_key = $("#kt_key").val();

        $.ajax({
            url: 'ajax.php?mod=buy_submit',
            type: 'POST',
            dataType: 'json',
            data: 'id=' + id + kt_varinfo + '&kt_num=' + kt_num + '&kt_mail=' + kt_mail + '&zffs=' + zffs + '&zxzffs=' + zxzffs + '&kt_key=' + kt_key + '&type=' + type + '&kt_adminqq=' + kt_adminqq,
            success: function (data) {
                layer.close(loading);
                if (data.code === '1') {
                    layer.confirm(data.info, {
                        btn: ['立即付款', '关闭'] //按钮
                    }, function () {
                        window.open('payment.php?tradeno=' + data.tradeno);
                        layer.closeAll();
                        layer.confirm('请在新打开的窗口中进行付款！', {
                            btn: ['已付款', '关闭'] //按钮
                        }, function () {
                            window.open('payresult.php?tradeno=' + data.tradeno);
                        });
                    });
                } else {
                    layer.msg(data.msg);
                }
            },
            error: function (data) {
                layer.close(loading);
                layer.msg('请求失败' + data);
            }
        })
    }
    function info(id) {
        layer.open({
            type: 2,
            title: '应用详情(应用ID:' + id + ')',
            shadeClose: true,
            shade: 0.8,
            area: ['90%', '90%'],
            content: 'ajax.php?mod=introduce&appid=' + id //iframe的url
        });
    }

    function getQueryVariable(variable) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split("=");
            if (pair[0] == variable) {
                return pair[1];
            }
        }
        return (false);
    }
</script>
</body>
</html>
