<?php


namespace common\components;

use common\models\Common;
use common\utils\PathUtils;
use common\utils\UUIDUtils;
use \Yii;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class BootstrapFileUpload extends Model
{
    public $file;
    public $fileAttrName;
    private $_appendRules;
    public function init ()
    {
        parent::init();
        $this->_appendRules = [
            [['file'], 'file', 'mimeTypes' => 'video/*,image/*'],
        ];
    }

    public function rules()
    {
        $baseRules = [
            [[ 'fileAttrName'], 'required'],
        ];
        return array_merge($baseRules, $this->_appendRules);
    }

    /**
     * @param $path
     * @return array
     * @throws \yii\base\Exception
     */
    public function uploadFile ($path)
    {
        $successResult =[];
        $failedResult =[];
        $success = true;
        $errorMsg = "";
        $this->file = UploadedFile::getInstanceByName($this->fileAttrName);
        if (!$this->file) {
            return [$success,$errorMsg,$successResult,$failedResult];
        }
        if ($this->validate()) {
            $rootPath = Yii::$app->params['imageUploadRootPath'];
            $folder = PathUtils::join($path,date('Ymd'));
            $fileName = UUIDUtils::uuid() . '.' . $this->file->extension;
            $fileRelativeUrl = PathUtils::joins($folder ,$fileName);
            if (!is_dir(PathUtils::join($rootPath ,$folder))) {
                FileHelper::createDirectory(PathUtils::join($rootPath ,$folder),0777, true);
            }
            if ($this->file->saveAs(PathUtils::joins($rootPath,$fileRelativeUrl))){
                $successResult[] = $fileRelativeUrl;
            }
            else{
                $errorMsg .= "文件({$fileRelativeUrl})失败;";
                $success = false;
                $failedResult[] = $fileRelativeUrl;
            }
        } else {
            $success = false;
            $errorMsg = Common::getExistModelErrors($this);
        }
        return [$success,$errorMsg,$successResult,$failedResult];
    }


    public function removeFile($fileName){
        $rootPath = Yii::$app->params['imageUploadRootPath'];
        $wholePath = PathUtils::join($rootPath,$fileName);
        if (file_exists($wholePath)) {
            unlink($wholePath);
        }
        return true;
    }
}