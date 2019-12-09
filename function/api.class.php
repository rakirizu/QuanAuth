<?php


class ApiCommunication
{
    private $connectkey;
    private $decryptKey;
    public function __construct($connectKey,$decryptKey)
    {
        $this->connectkey = $connectKey;
        $this->decryptKey = $decryptKey;
    }

    public function getsign($data)
    {
        return md5($this->connectkey.md5(md5($data.$this->connectkey).$this->decryptKey).$this->decryptKey);
    }

    public function back($code,$type='',$options = array()){
        include 'Tips.php';
        global $Tips;
        $Tips['ok'] = 'ok';
        $type = empty($type) ? 'ok' : $type;
        $data  = makejson($code,$Tips[$type],$options);//一次封装取签名
        $sign = $this->getsign($data);

        die(makejson(1,'ok',array('sign'=>$sign,'data'=>$data)));//二次封装
    }
}