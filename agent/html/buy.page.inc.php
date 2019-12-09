<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>代理开通授权</title>
    <link rel="stylesheet" href="../static/frame/layui/css/layui.css">
    <link rel="stylesheet" href="../static/frame/static/css/style.css">
    <link rel="icon" href="../static/frame/static/image/code.png">
</head>
<body class="body">

<div class="layui-row layui-col-space10 my-index-main">
    <form class="layui-form" action="">
        <?php echo $show_form; ?>
        <div class="layui-form-item">
            <label for="kt_num" class="layui-form-label">开通数量</label>
            <div class="layui-input-block">
                <input class="layui-input" name="kt_num" placeholder="" value="1" type="text">
            </div>
        </div>

        <div class="layui-form-item">
            <label for="kt_mail" class="layui-form-label"><B><font color="red">用户</font></B>邮箱</label>
            <div class="layui-input-block">
                <input class="layui-input" name="kt_mail" placeholder="用于接收开通结果" type="text">
            </div>
        </div>
        <div class="layui-form-item">
            <label for="kt_mail" class="layui-form-label">主人QQ</label>
            <div class="layui-input-block">
                <input class="layui-input" name="kt_adminqq" placeholder="用户的主人QQ" type="text">
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">支付方式</label>
            <div class="layui-input-block">
                <input type="radio" name="zffs" value="zxzf" lay-filter="olinepay" title="在线支付" checked>
                <input type="radio" name="zffs" value="czkm" lay-filter="cardpay" title="充值卡密">
                <input type="radio" name="zffs" value="yezf" lay-filter="moneypay" title="余额支付">
            </div>
        </div>
        <div class="layui-form-item" id="show_kmcz" hidden="">
            <label for="kami" class="layui-form-label">充值卡密</label>
            <div class="layui-input-block">
                <input class="layui-input" id="kt_key" name="kt_key" placeholder="" type="text">
            </div>
        </div>

        <div class="layui-form-item" id="show_zxzf">
            <label class="layui-form-label">支付方式</label>
            <div class="layui-input-block">
                <input type="radio" name="zxzffs" value="zfb" title="支付宝" checked>
                <input type="radio" name="zxzffs" value="wx" title="微信支付">
                <input type="radio" name="zxzffs" value="qq" title="QQ钱包">
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-input-block">
                <button class="layui-btn" lay-submit lay-filter="formDemo">立即开通</button>
                <button type="reset" class="layui-btn layui-btn-primary">重置</button>
            </div>
        </div>
    </form>
</div>
<script src="../static/jquery-3.3.1.js"></script>
<script type="text/javascript" src="../static/frame/layui/layui.js"></script>
<script type="text/javascript" src="../static/js/index.js"></script>
<script type="text/javascript" src="../static/frame/echarts/echarts.min.js"></script>
<script type="text/javascript">
    layui.use(['form', 'layer'], function () {


        var form = layui.form;

        form.on('submit(formDemo)', function (data) {
            var loading = layer.load();
            var kt_varinfo = '';
            for (var postkey in data.field) {
                kt_varinfo += '&' + postkey + '=' + encodeURI(data.field[postkey]);
            }
            //alert(kt_varinfo);
            $.ajax({
                url: 'ajax.php?mod=buy_submit',
                type: 'POST',
                dataType: 'json',
                data: 'id=' + getQueryVariable('fid') + kt_varinfo,
                success: function (data) {
                    layer.close(loading);
                    if (data.code === '1') {
                        layer.confirm(data.info, {
                            btn: ['立即付款', '关闭'] //按钮
                        }, function () {
                            window.open('../payment.php?tradeno=' + data.tradeno);
                            layer.closeAll();
                            layer.confirm('请在新打开的窗口中进行付款！', {
                                btn: ['已付款', '关闭'] //按钮
                            }, function () {
                                window.open('../payresult.php?tradeno=' + data.tradeno);
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
            return false;
        });


        form.on('radio(cardpay)', function (data) {
            $('#show_kmcz').show();
            $('#show_zxzf').hide();
        });
        form.on('radio(olinepay)', function (data) {
            $('#show_kmcz').hide();
            $('#show_zxzf').show();
        });
        form.on('radio(moneypay)', function (data) {
            $('#show_kmcz').hide();
            $('#show_zxzf').hide();
        });

    });

    function getQueryVariable(variable) {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i = 0; i < vars.length; i++) {
            var pair = vars[i].split("=");
            if (pair[0] === variable) {
                return pair[1];
            }
        }
        return (false);
    }
</script>
</body>
</html>