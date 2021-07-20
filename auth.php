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
    <title>套餐卡密在线使用开通授权 - <?php echo $G['config']['sitename'] ?></title>
    <link rel="stylesheet" href="./mdl/material.min.css">
    <script src="https://cdn.vaptcha.com/v2.js"></script>
    <?php include './function/color.inc.html' ?>
</head>

<body class="flat-blue landing-page">
<!-- Always shows a header, even in smaller screens. -->
<div class="mdl-layout mdl-js-layout mdl-layout--fixed-header" id="content">

    <header class="mdl-layout__header">
        <div class="mdl-layout__header-row">
            <!-- Title -->
            <span class="mdl-layout-title">套餐卡密在线使用开通授权 - <?php echo $G['config']['sitename'] ?></span>
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
                    <label for="kami" class="mdl-textfield__label">请输入您的套餐卡密</label>
                    <input class="mdl-textfield__input" id="kami" type="text">
                </div>
                <div id="vaptchaContainer" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label"
                     style="width: 100%">
                </div>
                <button type="button"
                        class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent"
                        onclick="queryauth();" style="width: 100%">立即开通
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
        window.layer = layui.layer;
        $.ajax({
            url: './function/GetVerification.php',
            type: 'GET',
            dataType: 'html',
            success: function(data){
                $('#vaptchaContainer').html(data);
            },
            error: function(data){
                layer.msg('['+data.status+']'+data.statusText);
            }
        });
    });


    function queryauth() {
        var kami = $('#kami').val();
        var token ='';
        if (typeof vaptchaObj != 'undefined'){
            token = vaptchaObj.getToken();
            vaptchaObj.reset();
        }
        layer.open({
            type: 2,
            title: '使用卡密'+kami,
            shadeClose: true,
            shade: 0.8,
            area: ['90%', '90%'],
            content: 'ajax.php?mod=FieldKamiOpen_First&kami=' + kami + '&token='+token
        });
    }
</script>

<!-- /.container -->


</body>

</html>
