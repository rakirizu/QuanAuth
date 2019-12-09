<?php include 'function/function_core.php';?>
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
    <title>授权在线查询 - <?php echo $G['config']['sitename'] ?></title>
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
            <span class="mdl-layout-title">授权在线查询 - <?php echo $G['config']['sitename'] ?></span>
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
        <main class="mdl-layout__content">
            <div class="page-content">
                <div style="display: inline;">
                    <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 95%">
                        <label for="kt_robotqq" class="mdl-textfield__label">输入主人QQ或者机器人QQ或者代理QQ</label>
                        <input class="mdl-textfield__input" id="queryqq" type="text">
                    </div>
                    <div id="vaptchaContainer" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label"
                         style="width: 95%">
                    </div>
                    <button type="button"
                            class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent"
                            onclick="queryauth();" style="width: 95%">立即查询
                    </button>
                </div>
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


<script src="./mdl/material.min.js"></script>
<script src="./static/jquery-3.3.1.js"></script>
<script type="text/javascript" src="./static/frame/layui/layui.js"></script>
<script>
    layui.use(['layer'], function () {
        window.layer = layui.layer;
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


    function queryauth() {
        var qq = $('#queryqq').val();
        var token ='';
        if (typeof vaptchaObj != 'undefined'){
            token = vaptchaObj.getToken();
            vaptchaObj.reset();
        }
        var loading = layer.load();
        $.ajax({
            url: 'ajax.php?mod=queryauth',
            type: 'POST',
            dataType: 'html',
            data: 'qq=' + qq + '&token=' + token,
            success: function (data) {
                layer.close(loading);
                layer.alert(data);
            },
            error: function (data) {
                layer.close(loading);
                layer.msg('请求失败' + data);
            }
        })
    }
</script>

<!-- /.container -->


</body>

</html>
