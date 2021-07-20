<?php include 'function/function_core.php'; ?>
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
    <title>授权绑定在线更换 - <?php echo $G['config']['sitename'] ?></title>
    <link rel="stylesheet" href="./mdl/material.min.css">
    <script src="https://cdn.vaptcha.com/v2.js"></script>
    <?php include './function/color.inc.html'?>
</head>

<body class="flat-blue landing-page">
<!-- Always shows a header, even in smaller screens. -->
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header" id="content">

    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <!-- Title -->
            <span class="mdl-layout-title">授权绑定在线更换 - <?php echo $G['config']['sitename'] ?></span>
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

    <main class="mdl-layout__content">
        <div class="page-content">
            <div style="display: inline;">
                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
                    <select class="mdl-textfield__input" style="width: 100%" id="applist" onchange="changegetform()">
                        <option value="0">选择应用</option>
                    </select>
                    <label class="mdl-textfield__label" for="applist">请选择一个应用</label>
                </div>

                <div id="changecontent"></div>

                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
                    <label for="cg_mail" class="mdl-textfield__label">请输入您授权时候的邮箱</label>
                    <input class="mdl-textfield__input" id="cg_mail" type="text">
                </div>
                <div id="vaptchaContainer" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label"
                     style="width: 100%">
                </div>
                <button type="button"
                        class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect"
                        onclick="getvercode();" style="width: 100%" id="sendcode">获取验证码
                </button>
                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
                    <label for="cg_vercode" class="mdl-textfield__label">邮箱验证码</label>
                    <input class="mdl-textfield__input" id="cg_vercode" type="text">
                </div>

                <button type="button"
                        class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent"
                        onclick="postchange();" style="width: 100%">确认换绑
                </button>
            </div>
        </div>
    </main>

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


<script src="./mdl/material.min.js"></script>
<script src="./static/jquery-3.3.1.js"></script>
<script type="text/javascript" src="./static/frame/layui/layui.js"></script>
<script>
    layui.use(['layer'], function () {
        $('#show_kmcz').hide();
        window.layer = layui.layer;
        var loading = layer.load();
        $.ajax({
            url: 'ajax.php?mod=buy_applist',
            type: 'POST',
            dataType: 'html',
            data: '',
            success: function (data) {
                layer.close(loading);
                if (data === '' || data === null) {
                    layer.open({
                        type: 1,title: false,closeBtn: false,area: '300px;',shade: 0.8,id: 'sitenotice',resize: false,btn: ['确定'],btnAlign: 'c',moveType: 1
                        ,content: '<div style="padding: 20px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">站长没有添加任何应用</div>'
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

        $.ajax({
            url: './function/GetVerification.php',
            type: 'GET',
            dataType: 'html',
            success: function (data) {
                $('#vaptchaContainer').html(data);
            },
            error: function (data) {
                layer.msg('[' + data.status + ']' + data.statusText);
            }
        });

    });

    function changegetform() {
        var loading = layer.load();
        var appid = $("#applist").val();
        $.ajax({
            url: 'ajax.php?mod=changegetform',
            type: 'POST',
            dataType: 'html',
            data: 'appid='+appid,
            success: function (data) {

                $('#changecontent').html(data);
                componentHandler.upgradeElements(document.getElementById('changecontent'));
                layer.close(loading);
            },
            error: function (data) {
                layer.close(loading);
                layer.msg('[' + data.status + ']' + data.statusText);
            }
        });
    }
    function getvercode() {
        var loading = layer.load();
        var putArr = document.getElementsByClassName("mdl-textfield__input");
        var postvalue = '';
        for (var i=0; i< putArr.length; i++){
            postvalue = postvalue + putArr[i].id+'='+ encodeURIComponent(putArr[i].value)+'&';
        }
        var token='';
        if (typeof vaptchaObj != 'undefined'){
            token = vaptchaObj.getToken();
            vaptchaObj.reset();
        }
        $.ajax({
            url: 'ajax.php?mod=ChangeGetVerCode',
            type: 'POST',
            dataType: 'json',
            data: postvalue+'token='+token,
            success: function (data) {
                if (data.code === 1){
                    $('#sendcode').html('60');
                    $('#sendcode').attr("disabled",true);
                    layer.msg('邮件发送成功，请注意检查垃圾箱和收件箱');
                    window.interval = setInterval(function () {
                        var i =parseInt($('#sendcode').html()) -1;
                        $('#sendcode').html(i.toString());
                        if (i === -1){
                            clearInterval(interval);
                            $('#sendcode').html('获取验证码');
                            $('#sendcode').removeAttr("disabled");
                        }

                    }, 1000);
                }else{
                    layer.alert(data.msg);
                }
                layer.close(loading);

            },
            error: function (data) {
                layer.close(loading);
                layer.msg('[' + data.status + ']' + data.statusText);
            }
        });
    }

    function postchange() {
        var loading = layer.load();
        var putArr = document.getElementsByClassName("mdl-textfield__input");
        var postvalue = '';
        for (var i=0; i< putArr.length; i++){
            postvalue = postvalue + putArr[i].id+'='+ encodeURIComponent(putArr[i].value)+'&';
        }

        $.ajax({
            url: 'ajax.php?mod=postchange',
            type: 'POST',
            dataType: 'html',
            data: postvalue+'token=',
            success: function (data) {
                layer.alert(data);
                layer.close(loading);
            },
            error: function (data) {
                layer.close(loading);
                layer.msg('[' + data.status + ']' + data.statusText);
            }
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

<!-- /.container -->


</body>

</html>
