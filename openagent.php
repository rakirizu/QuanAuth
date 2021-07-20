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
    <title>代理在线开通 - <?php echo $G['config']['sitename'] ?></title>
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
            <span class="mdl-layout-title">代理在线开通 - <?php echo $G['config']['sitename'] ?></span>
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
                    <select class="mdl-textfield__input" style="width: 100%" id="levellist">
                        <option value="0">选择一个级别</option>
                    </select>
                    <label class="mdl-textfield__label" for="levellist">选择需要开通的代理级别</label>
                </div>


                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
                    <label for="kt_user" class="mdl-textfield__label">开通的代理账号</label>
                    <input class="mdl-textfield__input" id="kt_user" type="text">
                </div>
                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
                    <label for="kt_pass" class="mdl-textfield__label">开通的账号的密码</label>
                    <input class="mdl-textfield__input" id="kt_pass" type="text">
                </div>
                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
                    <label for="kt_qq" class="mdl-textfield__label">您的QQ账号</label>
                    <input class="mdl-textfield__input" id="kt_qq" type="text">
                </div>

                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width: 100%">
                    支付方式：
                    <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="radio4">
                        <input type="radio" id="radio4" class="mdl-radio__button" name="zffs" value="zxzf"
                               onclick="$('#show_kmcz').hide();$('#show_zxzf').show();" checked>
                        <span class="mdl-radio__label">在线支付</span>
                    </label>
                    <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="radio5">
                        <input type="radio" id="radio5" class="mdl-radio__button" name="zffs" value="czkm"
                               onclick="$('#show_kmcz').show();$('#show_zxzf').hide();">
                        <span class="mdl-radio__label">充值卡密</span>
                    </label>
                </div>

                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" id="show_kmcz"
                     style="width: 100%">
                    <label for="kt_key" class="mdl-textfield__label">请输入卡密</label>
                    <input class="mdl-textfield__input" id="kt_key" type="text">
                </div>


                <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" id="show_zxzf"
                     style="width: 100%">
                    请选择在线支付方式：
                    <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="radio6">
                        <input type="radio" id="radio6" class="mdl-radio__button" name="zxzffs" value="zfb" checked>
                        <span class="mdl-radio__label">支付宝</span>
                    </label>
                    <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="radio7">
                        <input type="radio" id="radio7" class="mdl-radio__button" name="zxzffs" value="wx">
                        <span class="mdl-radio__label">微信支付</span>
                    </label>
                    <label class="mdl-radio mdl-js-radio mdl-js-ripple-effect" for="radio8">
                        <input type="radio" id="radio8" class="mdl-radio__button" name="zxzffs" value="qq">
                        <span class="mdl-radio__label">QQ钱包</span>
                    </label>
                </div>
                <div id="vaptchaContainer" class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label"
                     style="width: 100%">
                </div>
                <button type="button"
                        class="mdl-button mdl-js-button mdl-button--raised mdl-js-ripple-effect mdl-button--accent"
                        onclick="buyagent();" style="width: 100%">立即开通
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
            url: 'ajax.php?mod=levellist',
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
                        content: '<div style="padding: 20px; line-height: 22px; background-color: #393D49; color: #fff; font-weight: 300;">站在没有添加任何可以购买的代理级别</div>'
                        ,
                        success: function (layero) {
                        }
                    });
                } else {
                    $('#levellist').html(data);
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

    function buyagent() {
        var lid = $('#levellist').val();
        var user = $('#kt_user').val();
        var pass = $('#kt_pass').val();
        var qq = $('#kt_qq').val();
        var zffs = $('input:radio[name="zffs"]:checked').val();
        var zxzffs = $('input:radio[name="zxzffs"]:checked').val();
        var kt_key = $("#kt_key").val();
        var loading = layer.load();
        var token ='';
        if (typeof vaptchaObj != 'undefined'){
            token = vaptchaObj.getToken();
            vaptchaObj.reset();
        }
        $.ajax({
            url: 'ajax.php?mod=buy_agent',
            type: 'POST',
            dataType: 'json',
            data: 'lid=' + lid + '&username=' + encodeURI(user) + '&password=' + encodeURI(pass) + '&qq=' + qq + '&zffs=' + zffs + '&zxzffs=' + zxzffs + '&kt_key=' + kt_key + '&token=' + token,
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
        });

    }
</script>

<!-- /.container -->


</body>

</html>
