<?php


namespace backend\models\forms;


use common\models\Common;
use yii\base\Model;

/**
 *  ClaimBalanceForm
 *
 * @property integer $biz_type
 * @property integer $biz_id
 * @property integer $num
 * @property integer $type
 * @property string $remark
 */
class ClaimBalanceForm extends Model
{
    public $biz_type;
    public $biz_id;
    public $type;
    public $num;
    public $remark;

    public function rules()
    {
        return [
            [['remark','biz_type','biz_id','num','type'],'required'],
            [['biz_type','biz_id','type'],'integer'],
            [['num'],'number'],
            ['num','compare','compareValue' => 0,'operator' =>'>',"message" => '数量最少为0.01元'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'biz_type'=>'账户类型',
            'biz_id'=>'账户名称',
            'type'=>'扣款类型',
            'num' => '金额',
            'remark'=>'备注',
        ];
    }

    public function restoreForm(){
        $this->num = Common::showAmount($this->num);
        return $this;
    }
}