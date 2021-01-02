<?php


namespace backend\models\forms;


use yii\base\Model;

/**
 *  GoodsImportForm
 *
 * @property string $file
 */
class GoodsImportForm extends Model
{
    public $file;

    public function rules()
    {
        return [
            [['file'], 'file','extensions' => ['xls','xlsx'], 'checkExtensionByMimeType' => false,'maxSize'=>4096000,'wrongExtension'=>'只支持xls,xlsx两种格式','tooBig'=>'文件大小限制为4M','skipOnEmpty'=>false,'uploadRequired'=>'请上传文件！'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'file' => '文件',
        ];
    }
}