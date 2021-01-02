<?php


namespace common\utils;


class EncryptUtils
{

    /**
     * 简单对称加密算法之加密
     * @param String $string 需要加密的字串
     * @param String $secretKey 加密EKY
     * @return String
     */
    public static function encode($string = '', $secretKey = 'mhsh') {
        $strArr = str_split(base64_encode($string));
        $strCount = count($strArr);
        foreach (str_split($secretKey) as $key => $value)
            $key < $strCount && $strArr[$key].=$value;
        return str_replace(array('=', '+', '/'), array('O0O0O', 'o000o', 'oo00o'), join('', $strArr));
    }

    /**
     * 简单对称加密算法之解密
     * @param String $string 需要解密的字串
     * @param String $secretKey 解密KEY
     * @return String
     */
    public static function decode($string = '', $secretKey = 'mhsh') {
        $strArr = str_split(str_replace(array('O0O0O', 'o000o', 'oo00o'), array('=', '+', '/'), $string), 2);
        $strCount = count($strArr);
        foreach (str_split($secretKey) as $key => $value)
            $key <= $strCount && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
        return base64_decode(join('', $strArr));
    }
}