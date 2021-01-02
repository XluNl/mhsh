<?php
namespace business\models;
use yii\base\Model;

/**
 * This is the model class for table "{{%evaluate}}".
 * @property string $id
 * @property integer $user_id
 * @property integer $order_id
 * @property integer $v1
 * @property integer $v2
 * @property string $statement
 * @property string $create_time
 */
class EvaluateForm extends Model
{
    public $statement;
    public $v1;
    public $v2;
    public $order_no;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%evaluate}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['v1', 'v2'], 'integer'],
//             [['order_id'], 'required','message'=>'非法操作，请返回'],
            [['statement'], 'string', 'max' => 255]
        ];
    }
    
    
}
