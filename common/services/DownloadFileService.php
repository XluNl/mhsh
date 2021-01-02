<?php


namespace common\services;


use common\utils\PathUtils;
use common\utils\UUIDUtils;
use Yii;
use yii\helpers\FileHelper;

class DownloadFileService
{
    /**
     * 下载文件并返回url
     * @param $url
     * @param $path
     * @param $extension
     * @return array
     * @throws \yii\base\Exception
     */
    public static function downloadFile($url,$path,$extension){
        $rootPath = Yii::$app->params['imageUploadRootPath'];
        $folder = PathUtils::join($path,date('Ymd'));
        if (!is_dir(PathUtils::join($rootPath ,$folder))) {
            FileHelper::createDirectory(PathUtils::join($rootPath ,$folder),0777, true);
        }
        $fileName = self::downloadImageFromUrl($url,PathUtils::join($rootPath ,$folder),$extension);
        $fileSize = filesize(PathUtils::joins($rootPath ,$folder,$fileName));
        if ($fileSize==0){
            $fileName = self::downloadImageFromUrl($url,PathUtils::join($rootPath ,$folder),$extension);
            $fileSize = filesize(PathUtils::joins($rootPath ,$folder,$fileName));
            if ($fileSize==0){
                Yii::error("url:{$url},fileName:".PathUtils::joins($rootPath ,$folder,$fileName).",size:{$fileSize}","zeroDownloadFile");
            }
            else{
                Yii::error("url:{$url},fileName:".PathUtils::joins($rootPath ,$folder,$fileName).",size:{$fileSize}","downloadFile");
            }
        }
        else{
            Yii::error("url:{$url},fileName:".PathUtils::joins($rootPath ,$folder,$fileName).",size:{$fileSize}","downloadFile");
        }
        return [
            'url' =>  PathUtils::joins(Yii::getAlias("@publicImageUrl"),$folder,$fileName),
            'attachment' =>PathUtils::joins( $folder , $fileName)
        ];
    }

    private static function downloadImageFromUrl($url, $path='images/',$extension)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file = curl_exec($ch);
        curl_close($ch);
        return self::saveAsImage( $file, $path,$extension);
    }

    private static function saveAsImage( $file, $path,$extension)
    {
        $filename = UUIDUtils::uuid() . '.' .$extension ;
        $resource = fopen(PathUtils::joins($path , $filename), 'a');
        fwrite($resource, $file);
        fclose($resource);
        return $filename;
    }
}