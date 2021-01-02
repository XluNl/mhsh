<?php


namespace common\utils;


use yii\httpclient\Client;

class HttpClientUtils
{
    /**
     * POST JSON
     * @param  $url
     * @param  $data
     * @return mixed
     * @throws \Exception
     */
    public static function post($url,$data){
        try {
            $client = new Client();
            $response = $client->createRequest()
                ->setMethod('POST')
                ->setUrl($url)
                ->setData($data)
                ->send();
            if ($response->isOk) {
                return $response->data;
            }
            else{
                throw new \Exception("错误码".$response->getStatusCode());
            }
        }
        catch (\Exception $e){
            throw new \Exception("请求失败:".$e->getMessage());
        }
    }


    /**
     * @param $url
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public static function postJson($url,$data){
        try {
            $client = new Client();
            $response = $client->createRequest()
                ->setFormat(Client::FORMAT_JSON)
                ->setMethod('POST')
                ->setUrl($url)
                ->setData($data)
                ->send();
            if ($response->isOk) {
                return $response->data;
            }
            else{
                throw new \Exception("错误码".$response->getStatusCode());
            }
        }
        catch (\Exception $e){
            throw new \Exception("请求失败:".$e->getMessage());
        }
    }

    /**
     * GET JSON
     * @param $url
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public static function get($url,$data){
        try {
            $client = new Client();
            $response = $client->createRequest()
                ->setFormat(Client::FORMAT_JSON)
                ->setMethod('GET')
                ->setUrl($url)
                ->setData($data)
                ->send();
            if ($response->getIsOk()) {
                return $response->getData();
            }
            else{
                throw new \Exception("错误码".$response->getStatusCode());
            }
        }
        catch (\Exception $e){
            throw new \Exception("请求失败:".$e->getMessage());
        }
    }


    /**
     * postXml
     * @param $url
     * @param $data
     * @return mixed
     * @throws \Exception
     */
    public static function postXml($url,$data){
        try {
            $client = new Client();
            $response = $client->createRequest()
                ->setFormat(Client::FORMAT_XML)
                ->setMethod('POST')
                ->setUrl($url)
                ->setData($data)
                ->send();
            if ($response->getIsOk()) {
                try {
                    return $response->getData();
                }
                catch (\Exception $e){
                    return $response->getContent();
                }
            }
            else{
                throw new \Exception("错误码".$response->getStatusCode());
            }
        }
        catch (\Exception $e){
            throw new \Exception("请求失败:".$e->getMessage());
        }
    }
}