<?php


namespace backend\models\forms;


use common\models\Common;
use yii\base\Model;

/**
 *  DrawCouponForm
 *
 * @property integer $biz_type
 * @property integer $biz_id
 * @property string $batch_no
 * @property integer $num
 * @property string $remark
 */
class DrawBonusForm extends Model
{
    public $biz_type;
    public $biz_id;
    public $batch_no;
    public $num;
    public $remark;

    public function rules()
    {
        return [
            [['batch_no','remark','biz_type','biz_id','num'],'required'],
            [['batch_no','remark'],'string','max' => 255],
            [['biz_type','biz_id'],'integer'],
            [['num'],'number'],
            ['num','compare','compareValue' => 0,'operator' =>'>',"message" => '数量最少为0.01元'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'batch_no' => '批次号',
            'biz_type'=>'账户类型',
            'biz_id'=>'账户名称',
            'num' => '数量',
            'remark'=>'备注',
        ];
    }

    public function restoreForm(){
        $this->num = Common::showAmount($this->num);
        return $this;
    }
}