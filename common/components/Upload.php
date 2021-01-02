<?php


namespace common\components;

use common\utils\PathUtils;
use common\utils\UUIDUtils;
use \Yii;
use yii\base\Model;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;

class Upload extends Model
{
    public $file;
    private $_appendRules;
    public function init ()
    {
        parent::init();
        $extensions = Yii::$app->params['webuploader']['baseConfig']['accept']['extensions'];
        $this->_appendRules = [
            [['file'], 'file', 'extensions' => $extensions],
        ];
    }

    public function rules()
    {
        $baseRules = [];
        return array_merge($baseRules, $this->_appendRules);
    }

    /**
     *
     * @param $path
     * @return array|bool
     * @throws \yii\base\Exception
     */
    public function upImage ($path)
    {
        $model = new static;
        $model->file = UploadedFile::getInstanceByName('file');
        if (!$model->file) {
            return false;
        }
        if ($model->validate()) {
            $rootPath = Yii::$app->params['imageUploadRootPath'];
            $folder = PathUtils::join($path,date('Ymd'));
            $fileName = UUIDUtils::uuid() . '.' . $model->file->extension;
            if (!is_dir(PathUtils::join($rootPath ,$folder))) {
                FileHelper::createDirectory(PathUtils::join($rootPath ,$folder),0777, true);
            }
            $model->file->saveAs(PathUtils::joins($rootPath,$folder ,$fileName));
            return [
                'code' => 0,
                'url' =>  PathUtils::joins(Yii::getAlias("@publicImageUrl"),$folder,$fileName),
                'attachment' =>PathUtils::joins( $folder , $fileName)
            ];
        } else {
            $errors = $model->errors;
            return [
                'code' => 1,
                'msg' => current($errors)[0]
            ];
        }
    }
}