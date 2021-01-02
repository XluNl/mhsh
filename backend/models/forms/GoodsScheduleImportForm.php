<?php


namespace backend\models\forms;


use yii\base\Model;

/**
 *  GoodsScheduleImportForm
 *
 * @property integer $collection_id
 * @property string $file
 */
class GoodsScheduleImportForm extends Model
{
    public $collection_id;
    public $file;

    public function rules()
    {
        return [
            [['collection_id'], 'required'],
            [['file'], 'file','extensions' => ['xls','xlsx'], 'checkExtensionByMimeType' => false,'maxSize'=>4096000,'wrongExtension'=>'只支持xls,xlsx两种格式','tooBig'=>'文件大小限制为4M','skipOnEmpty'=>false,'uploadRequired'=>'请上传文件！'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'collection_id' => '房间名称',
            'file' => '文件',
        ];
    }
}