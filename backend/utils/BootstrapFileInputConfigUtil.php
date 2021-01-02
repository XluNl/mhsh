<?php


namespace backend\utils;


use common\models\Common;
use common\utils\StringUtils;
use yii\helpers\Url;

class BootstrapFileInputConfigUtil
{

    private static $mimeMagicFile = '@yii/helpers/mimeTypes.php';

    private static $_mimeTypes;


    public static function createFailedResultConfig($errorMsg){
        $plugOptions['error'] = $errorMsg;
        return $plugOptions;
    }

    public static function createResultConfig($successFiles,$failedFiles,$errorMsg,$deleteUrl=""){
        $plugOptions = [];
        $initialPreview = [];
        $initialPreviewConfig = [];
        foreach ($successFiles as $fileName) {
            $wholeUrl = Common::generateAbsoluteUrl($fileName);
            $initialPreview[] =
                $wholeUrl;
            $initialPreviewConfig[] = [
                'caption' => $fileName,
                'width' => '120px',
                'url' => $deleteUrl,
                'key' => $fileName,
                'type'=> self::getFileType($fileName),
                'filetype'=>self::getMIMEByExtension($fileName),
              //  'type'=> 'image',
                // 'extra' => ['id' => 100],
            ];
        }
        $plugOptions['error'] = $errorMsg;
        $plugOptions['errorkeys'] = $failedFiles;
        $plugOptions['initialPreview'] = $initialPreview;
        $plugOptions['initialPreviewConfig'] = $initialPreviewConfig;
        $plugOptions['initialPreviewAsData'] = true;
        //$plugOptions['initialCaption'] = "xxxx";
        $plugOptions['overwriteInitial'] = false;
        $plugOptions['append'] = true;
        return $plugOptions;
    }




    public static function createInitConfigString($fileNames,$extraData=[],$fileInputAttr=[],$deleteUrl=""){
        $fileNamesArray = [];
        if (StringUtils::isNotBlank($fileNames)){
            $fileNamesArray =  explode(",", $fileNames);
        }
        return self::createInitConfigArray($fileNamesArray,$extraData,$fileInputAttr,$deleteUrl);
    }

    public static function createInitConfigArray($fileNames,$extraData=[],$fileInputAttr=[],$deleteUrl=""){
        $plugOptions = [];
        $initialPreview = [];
        $initialPreviewConfig = [];
        foreach ($fileNames as $fileName) {
            $initialPreview[] = Common::generateAbsoluteUrl($fileName);
            $initialPreviewConfig[] = [
                'caption' => $fileName,
                'width' => '120px',
                'url' => $deleteUrl,
                'key' => $fileName,
                'type'=> self::getFileType($fileName),
                'filetype'=>self::getMIMEByExtension($fileName),
                'extra' => $extraData,
            ];
        }
        $plugOptions['overwriteInitial'] = false;       // 覆盖初始预览内容和标题设置
        //$plugOptions['uploadUrl'] = Url::to(['/upload/file-upload']);  // 上传地址

        $plugOptions['uploadAsync']= true;
        $plugOptions['language'] = 'zh'; // 多语言设置，需要引入local中相应的js，例如locales/zh.js
        //$plugOptions['theme'] = 'explorer-fa';      // 主题
        $plugOptions['minFileCount'] = 0;     // 最小上传数量
        $plugOptions['maxFileCount'] = 5;     // 最大上传数量
        $plugOptions['showPreview'] = true;     //是否显示预览
        $plugOptions['showCancel'] = false;     // 显示取消按钮
        $plugOptions['showZoom'] = true;     // 显示预览按钮
        $plugOptions['showCaption'] = false;     // 显示文件文本框
        $plugOptions['dropZoneEnabled'] = true;     // 是否可拖拽
        $plugOptions['uploadLabel'] = '上传附件';     // 上传按钮内容
        $plugOptions['browseLabel'] = '选择附件';     // 浏览按钮内容
        $plugOptions['showRemove'] = false;     // 显示移除按钮
        $plugOptions['showUpload'] = false;     // 显示上传按钮
       // $plugOptions['browseClass'] = 'layui-btn';      // 浏览按钮样式
       // $plugOptions['uploadClass'] = 'layui-btn';    // 上传按钮样式

        $plugOptions['hideThumbnailContent'] = false;     // 是否隐藏文件内容
        $plugOptions['fileActionSettings'] = [       // 在预览窗口中为新选择的文件缩略图设置文件操作的对象配置
            'showRemove'=>true,  // 显示删除按钮
            'showUpload'=>true,   // 显示上传按钮
            'showDownload'=>true,   // 显示下载按钮
            'showZoom'=>false,   // 显示预览按钮
            'showDrag'=>false,   // 显示拖拽
            //'removeIcon'=>'<i class="fa fa-trash"></i>', // 删除图标
            //'uploadIcon'=>'<i class="fa upload"></i>', // 上传图标
            //'uploadRetryIcon'=>'<i class="fa repeat"></i>', // 重试图标
        ];     // 是否隐藏文件内容
        $plugOptions['msgFilesTooMany'] = "选择上传的文件数量({n}) 超过允许的最大文件数{m}！";
        $plugOptions['msgSizeTooLarge'] = '文件 "{name}" (<b>{size} KB</b>) 超过了允许大小 <b>{maxSize} KB</b>。';
        $plugOptions['initialPreview'] = $initialPreview;  //初始预览内容
        $plugOptions['initialPreviewConfig'] = $initialPreviewConfig; // 初始预览配置 caption 标题，size文件大小 ，url 删除地址，key删除时会传这个
        $plugOptions['initialPreviewAsData'] = true;
        //$plugOptions['initialCaption'] = "xxxx";
        $plugOptions['maxFileSize'] = 50 * 1024;
        $plugOptions['maxFilePreviewSize'] = 50 * 1024;
        //$plugOptions['initialPreviewFileType'] = ['image','video'];
        $plugOptions['allowedPreviewTypes'] = ['image','video'];
        $plugOptions['previewFileType'] = ['image','video'];
        $plugOptions['uploadExtraData'] = $extraData;     // 上传数据
        if (!empty($fileInputAttr)){
            foreach ($fileInputAttr as $k=>$v){
                $plugOptions[$k] = $v;
            }
        }
        return $plugOptions;
    }


    private static function getExtension($fileName){
        if (StringUtils::isBlank($fileName)){
            return "";
        }
        $arr=explode(".", $fileName);
        return  $arr[count($arr)-1];
    }

    private static function getMIMEByExtension($fileName){
        $extension = self::getExtension($fileName);
        if (StringUtils::isBlank($extension)){
            return "";
        }
        $extension = strtolower($extension);
        $magicFile = \Yii::getAlias(self::$mimeMagicFile);
        if (!isset(self::$_mimeTypes[$magicFile])) {
            self::$_mimeTypes[$magicFile] = require($magicFile) ;
        }
        $mimeType =  self::$_mimeTypes[$magicFile];
        if (key_exists($extension,$mimeType)){
            return $mimeType[$extension];
        }
        return "";
    }


    private static function getFileType($fileName){
        $mime =  self::getMIMEByExtension($fileName);
        if (StringUtils::isBlank($mime)){
            return "";
        }
        $arr=explode("/", $mime);
        return  $arr[0];
    }
}