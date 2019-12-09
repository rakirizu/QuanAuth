<?php
/**
 * 验证通信相关操作类模块
 * Created by PhpStorm.
 * User: 80071
 * Date: 2018/2/8
 * Time: 15:33
 */

class communication
{
    private $connectkey = '';
    private $decryptKey = '';

    public function __construct($connectkey,$decryptKey)
    {
        $this->connectkey = $connectkey;
        $this->decryptKey = $decryptKey;
    }

    private function str_decode($str, $key, $key_rand)
    {//解密函数
        return (string)$this->ARSC($this->BytesHex((string)$this->ARSC($this->BytesHex($str), (string)$key_rand)), $key);
    }

    private function ARSC($Data, $Key)
    {
        $key[] = "";
        $box[] = "";
        $pwd_length = strlen($Key);
        $data_length = strlen($Data);
        for ($i = 0; $i < 256; $i++) {
            $key[$i] = ord($Key[$i % $pwd_length]);
            $box[$i] = $i;
        }
        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $key[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        $cipher = '';
        for ($a = $j = $i = 0; $i < $data_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;

            $k = $box[(($box[$a] + $box[$j]) % 256)];
            $cipher .= chr(ord($Data[$i]) ^ $k);
        }
        return $cipher;
    }

    private function BytesHex($s)
    {
        $r = "";
        for ($i = 0; $i < strlen($s); $i += 2) {
            $x1 = ord($s{$i});
            $x1 = ($x1 >= 48 && $x1 < 58) ? $x1 - 48 : $x1 - 97 + 10;
            $x2 = ord($s{$i + 1});
            $x2 = ($x2 >= 48 && $x2 < 58) ? $x2 - 48 : $x2 - 97 + 10;
            $r .= chr((($x1 << 4) & 0xf0) | ($x2 & 0x0f));
        }
        return $r;
    }

    private function str_encode($str, $key, $key_rand)
    {//加密函数
        $key_temp = $this->HexBytes($this->ARSC($str, (string)$key_rand));
        $key_temp = strtoupper($this->HexBytes($this->ARSC($key_temp, $key)));
        return $key_temp;
    }

    private function HexBytes($s)
    {
        $r = "";
        $hexes = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9", "a", "b", "c", "d", "e", "f");
        for ($i = 0; $i < strlen($s); $i++) {
            $r .= ($hexes [(ord($s{$i}) >> 4)] . $hexes [(ord($s{$i}) & 0xf)]);
        }
        return $r;
    }


    /**
     * @access DynamicEncryption
     * @explain 该函数用于文本数据动态加密，每次加密结果不一致，但解密数据相同
     * @param string $date 将要加密的字符串
     * @param string $pas 加密密码
     * @return string 返回加密后的结果字符串
     */
    private  function DynamicEncryption($date,$pas){
        $date = $this->_tobit($date);
        if(count($date)%2 == 1){
            $date[] = '0';
        }
        $date = $this->_DataDisruption($date);
        $dateleng = count($date);
        $Code = $this->_DataDisruption($this->_tobit ('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890~!@#$%^&*()_+|'));
        $Codeleng = count($Code);
        $pasbit = $this->_DataDisruption($this->_tobit($pas));
        $pasleng = count($pasbit);
        $int2=0;
        $int3=0;
        $ret=Array();
        for ($x=1; $x<=$dateleng; $x++) {
            $int2=$int2+1;
            if($int2>$Codeleng){
                $int2=1;
            }
            $int3=$int3+1;
            if($int3>$pasleng){
                $int3=1;
            }
            $randompas =mt_rand(1,5000);
            $Bus = floor($randompas / $Codeleng + 1);
            $Yu = $randompas % $Codeleng + 1;
            $ret = array_merge($ret,array_slice($Code,floor($Bus)-1,1));
            $ret = array_merge($ret,array_slice($Code,floor($Yu)-1,1));

            $tembit= $date[$x-1]^$Code[$int2-1]^$pasbit[$int3-1]^$randompas;
            $Bus = $tembit / $Codeleng + 1;
            $Yu = $tembit % $Codeleng + 1;
            $ret = array_merge($ret,array_slice($Code,floor($Bus)-1,1));
            $ret = array_merge($ret,array_slice($Code,floor($Yu)-1,1));
        }
        return $this->_bintotxt($ret);
    }

    /**
     * @access DynamicDecryption
     * @explain 该函数用于文本数据动态解密
     * @param string $date 将要解密的字符串
     * @param string $pas 解密密码
     * @return string 返回解密后的结果字符串
     */
    private function DynamicDecryption($date,$pas){

        $date = $this->_tobit($date);
        $dateleng = count ($date);
        $Code = $this->_DataDisruption ($this->_tobit ("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890~!@#$%^&*()_+|"));
        $Codeleng = count ($Code);
        $pasbin = $this->_DataDisruption ($this->_tobit ($pas));
        $pasleng = count ($pasbin);
        $ret = Array();
        $lnt3=0;
        $lnt2=0;
        for ($lnt1=1; $lnt1<=($dateleng/4); $lnt1++) {
            $lnt2 = $lnt2 + 1 ;
            if($lnt2 > $Codeleng){
                $lnt2 = 1;
            }
            $lnt3 = $lnt3 + 1 ;
            if($lnt3 > $pasleng){
                $lnt3 = 1;
            }
            $Bus = array_slice($date, floor(($lnt1-1)*4+1)-1, 1);
            $Bus = array_search($Bus[0],$Code);
            $yu = array_slice($date, floor(($lnt1-1)*4+2)-1, 1);
            $yu = array_search($yu[0],$Code);
            $randompas = $Bus * $Codeleng + $yu;
            $Bus = array_slice($date, floor(($lnt1-1)*4+3)-1, 1);
            $Bus = array_search($Bus[0],$Code);
            $yu = array_slice($date, floor(($lnt1-1)*4+4)-1, 1);
            $yu = array_search($yu[0],$Code);
            $tembit = $Bus * $Codeleng + $yu;
            $tembit = $tembit^$Code[$lnt2-1]^$pasbin[$lnt3-1]^$randompas;
            $ret[] = $tembit;
        }
        return $this->_bintotxt($this->_DataDisruption($ret));
        //return @iconv('gbk','utf-8',$this->_bintotxt($this->_DataDisruption($ret)));
    }

    private function _bintotxt($bytes) {
        $str='';
        foreach($bytes as $ch){
            $str.=chr($ch);
        }
        return $str;
    }
    private function _tobit($string){
        $bytes = array();
        for($i = 0; $i < strlen($string); $i++){
            $bytes[] = ord($string[$i]);
        }
        return $bytes;
    }
    private function _DataDisruption($date){
        $date1=array_slice($date,0,floor(count($date)/2));
        $date2=array_slice($date,ceil(count($date)-(count($date)/2)));
        return @array_merge($this->_DataReverse($date1),$this->_DataReverse($date2));
    }
    private function _DataReverse($date){
        $sj = array();
        for ($x=1; $x<=count($date); $x++) {
            $sj[$x-1]=$date[count($date)-$x];
        }
        return $sj;
    }

    public function data_encode($data,&$sign){
        $data = $this->DynamicEncryption($data,$this->connectkey);
        $sign =md5(md5($this->connectkey.$this->decryptKey).$data);
        return $this->str_encode($data,$this->connectkey,$this->decryptKey);
    }

    public function data_decode($data){
        $post_data = $this->str_decode($data,$this->connectkey,$this->connectkey);
        return $this->rc4_decode($this->DynamicDecryption($post_data,$this->decryptKey));
    }

    public function getsign($data){
        $post_data = $this->str_decode($data,$this->connectkey,$this->connectkey);
        return md5(md5($this->connectkey.$this->decryptKey).$post_data);
    }

    public function back($code,$type='',$options = array()){

        global $Tips;
        //include 'Tips.php';
        $Tips['ok'] = 'ok';
        $type = empty($type) ? 'ok' : $type;
        $data  = $this->data_encode(makejson($code,$Tips[$type],$options),$sign);//一次封装取签名
        //echo $data;
        //echo '222';
        die(makejson(1,'ok',array('sign'=>$sign,'data'=>$data)));//二次封装
    }

    function rc4 ($pwd, $data) {

        $key[] ="";

        $box[] ="";

        $pwd_length = strlen($pwd);

        $data_length = strlen($data);

        for ($i = 0; $i < 256; $i++) {

            $key[$i] = ord($pwd[$i % $pwd_length]);

            $box[$i] = $i;

        }

        for ($j = $i = 0; $i < 256; $i++) {

            $j = ($j + $box[$i] + $key[$i]) % 256;

            $tmp = $box[$i];

            $box[$i] = $box[$j];

            $box[$j] = $tmp;

        }
        $cipher = '';
        for ($a = $j = $i = 0; $i < $data_length; $i++) {

            $a = ($a + 1) % 256;

            $j = ($j + $box[$a]) % 256;

            $tmp = $box[$a];

            $box[$a] = $box[$j];

            $box[$j] = $tmp;

            $k = $box[(($box[$a] + $box[$j]) %256)];

            $cipher .= chr(ord($data[$i]) ^ $k);

        }

        return $cipher;

    }

    function   hexToStr($hex)
    {
        $string="";
        for   ($i=0;$i<strlen($hex)-1;$i+=2)
            $string.=chr(hexdec($hex[$i].$hex[$i+1]));
        return   $string;
    }
    function strToHex($string)
    {
        return substr(chunk_split(bin2hex($string)),0,-2);

    }
    private function rc4_encode($string)
    {

        return $this->strToHex($this->rc4($this->connectkey,$string));


    }
    private function rc4_decode($string)
    {

        return  $this->rc4($this->connectkey,pack('H*',$string));


    }
//rc4b解密
//rc4a加密
}