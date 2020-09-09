<?php

class Verification
{
    static function loadcode()
    {
        global $G;

        if ($G['config']['opencode'] == 'true') {

            die('            <div class="vaptcha-init-main">
                <div class="vaptcha-init-loading">
                    <a href="/" target="_blank">
                        <img src="https://cdn.vaptcha.com/vaptcha-loading.gif" />
                    </a>
                    <span class="vaptcha-text">Vaptcha启动中...</span>
                </div>
            </div>

            <script>
                vaptcha({
                    //配置参数
                    vid: \''.$G['config']['codevid'].'\', // 验证单元id
                    type: \'click\', // 展现类型 点击式
                    container: \'#vaptchaContainer\' // 按钮容器，可为Element 或者 selector
                }).then(function (vaptchaObj) {
                    vaptchaObj.render()// 调用验证实例 vaptchaObj 的 render 方法加载验证按钮
                    window.vaptchaObj=vaptchaObj;
                })
            </script>');
        }
    }

    static function check($token)
    {
        global $G;

        if ($G['config']['opencode'] == 'true' || $G['config']['opencode'] == '1') {
            if (strlen($token) == 32) {
                unset($_POST['token']);
                //对待签名参数数组排序
                $para_filter = paraFilter($_POST);
                $para_sort = argSort($para_filter);
                //生成签名结果
                $prestr = createLinkstring($para_sort);
                $mysign = md5Sign($prestr, $G['config']['token']);
                //签名结果与签名方式加入请求提交参数组中
                if ($mysign == $token) {
                    return true;
                }else{
                    return false;
                }
            }
            $info = json_decode(curl_request('http://api.vaptcha.com/v2/validate', 'id=' . $G['config']['codevid'] . '&secretkey=' . $G['config']['codekey'] . '&token=' . $token . '&ip=' . get_real_ip()), true);
            if ($info['success'] == '1') {
                return true;
            } else {
                return $info;
            }
        } else {
            return true;
        }


    }
}