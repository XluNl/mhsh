<?php

namespace frontend\models;
use yii\base\Model;

/**
 * This is the model class for table "{{%cpandsg}}".
 *
 * @property string $id
 * @property integer $user_id
 * @property string $suggestion
 */
class CpandsgForm extends Model
{
    public $suggestion;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
			array(array('suggestion'), 'required',"message"=>"投诉建议不能为空"),
            array('suggestion', 'string', 'max'=>255,"tooLong"=>"投诉建议最多255个字符"),
		];
    }
}
