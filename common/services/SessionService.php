<?php


namespace common\services;


use yii\base\Exception;

class SessionService
{

    /**
     * @param $sessionData
     * @return array
     * @throws Exception
     */
    public static function unSerializeSessionData($sessionData) {
        $method = ini_get("session.serialize_handler");
        switch ($method) {
            case "php":
                return self::unSerializePhp($sessionData);
                break;
            case "php_binary":
                return self::unSerializePhpBinary($sessionData);
                break;
            default:
                throw new Exception("Unsupported session.serialize_handler: " . $method . ". Supported: php, php_binary");
        }
    }

    private static function unSerializePhp($sessionData) {
        $return_data = [];
        $offset = 0;
        while ($offset < strlen($sessionData)) {
            if (!strstr(substr($sessionData, $offset), "|")) {
                throw new Exception("invalid data, remaining: " . substr($sessionData, $offset));
            }
            $pos = strpos($sessionData, "|", $offset);
            $num = $pos - $offset;
            $varname = substr($sessionData, $offset, $num);
            $offset += $num + 1;
            $data = unserialize(substr($sessionData, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }

    private static function unSerializePhpBinary($sessionData) {
        $return_data = [];
        $offset = 0;
        while ($offset < strlen($sessionData)) {
            $num = ord($sessionData[$offset]);
            $offset += 1;
            $varname = substr($sessionData, $offset, $num);
            $offset += $num;
            $data = unserialize(substr($sessionData, $offset));
            $return_data[$varname] = $data;
            $offset += strlen(serialize($data));
        }
        return $return_data;
    }
}