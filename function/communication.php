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

    private function ARSC($Data,$Key)
    {
        $key[] ="";
        $box[] ="";
        $pwd_length = strlen($Key);
        $data_length = strlen($Data);
        for ($i = 0; $i < 256; $i++)
        {
            $key[$i] = ord($Key[$i % $pwd_length]);
            $box[$i] = $i;
        }
        for ($j = $i = 0; $i < 256; $i++)
        {
            $j = ($j + $box[$i] + $key[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }
        $cipher = '';
        for ($a = $j = $i = 0; $i < $data_length; $i++)
        {
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
    private function BytesHex($s){
        $r = "";
        for ( $i = 0; $i<strlen($s); $i += 2)
        {
            $x1 = ord($s{$i});
            $x1 = ($x1>=48 && $x1<58) ? $x1-48 : $x1-97+10;
            $x2 = ord($s{$i+1});
            $x2 = ($x2>=48 && $x2<58) ? $x2-48 : $x2-97+10;
            $r .= chr((($x1 << 4) & 0xf0) | ($x2 & 0x0f));
        }
        return $r;
    }
    private function HexBytes($s) {
        $r = "";
        $hexes = array ("0","1","2","3","4","5","6","7","8","9","a","b","c","d","e","f");
        for ($i=0; $i<strlen($s); $i++) {$r .= ($hexes [(ord($s{$i}) >> 4)] . $hexes [(ord($s{$i}) & 0xf)]);}
        return $r;
    }
    public function str_decode($str,$key,$key_rand){//解密函数
        return (string)$this->ARSC($this->BytesHex((string)$this->ARSC($this->BytesHex($str),(string)$key_rand)),$key);
    }
    public function str_encode($str,$key,$key_rand){//加密函数
        $key_temp = $this->HexBytes($this->ARSC($str,(string)$key_rand));
        $key_temp = strtoupper($this->HexBytes($this->ARSC($key_temp,$key)));
        return $key_temp;
    }

    public function GET_Temp_Key(){
        mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
        $charid = strtoupper(md5(uniqid(rand(), true)));
        $hyphen = chr(45);// "-"
        $uuid = chr(123)// "{"
            .substr($charid, 0, 8).$hyphen
            .substr($charid, 8, 4).$hyphen
            .substr($charid,12, 4).$hyphen
            .substr($charid,16, 4).$hyphen
            .substr($charid,20,12)
            .chr(125);// "}"
        return $uuid;
    }
    public function str_sign($str,$txmy,$tempkey){

        return md5($str.$txmy.$tempkey);
    }




}