<?php

namespace business\models; 
use common\models\GoodsSort;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/** 
 * This is the model class for table "{{%sg_goods}}". 
 * 
 * @property string $id
 * @property string $goods_name
 * @property string $sort_parent
 * @property string $sort_parent_name
 * @property string $sort_child
 * @property string $sort_child_name
 * @property string $goods_description
 * @property string $goods_addtime
 * @property integer $user_id
 */ 
class SggoodsForm extends  Model
{
    public $sort_parent;
    public $sort_child;
    public $goods_name;
    public $goods_description;
    /** 
     */ 
    public function getSortList($pid)
    {
        $company_id = BusinessCommon::getFCompanyId();
        $model = GoodsSort::findAll(array('parent_id'=>$pid,'sort_show'=>1,'sort_status'=>1,'company_id'=>$company_id));
        return ArrayHelper::map($model, 'id', 'sort_name');
    }
    
    public function getSortName($id)
    {
        $company_id = BusinessCommon::getFCompanyId();
        $model = GoodsSort::find()->where(array('id'=>$id,'company_id'=>$company_id))->all();
        if (!empty($model)){
            return $model[0]->sort_name;
        }
        else return "";
    }
    public function rules()
    {
        return [
            array('sort_parent','required','message'=>'请选择大类！'),
            array('sort_child','required','message'=>'请选择小类！'),
            array('sort_parent','match','not'=>true, 'pattern'=>'/empty/','message'=>'请选择大类！'),
            array('sort_child','match', 'not'=>true, 'pattern'=>'/empty/','message'=>'请选择小类！'),
			array(array('goods_name'), 'required',"message"=>"菜品名称不能为空"),
            array(array('goods_description'), 'required',"message"=>"菜品描述不能为空"),
            array('goods_name', 'string', 'max'=>20,"tooLong"=>"菜品名称最多20个字符"),
            array('goods_description', 'string', 'max'=>255,"tooLong"=>"菜品描述最多255个字符"),
		];
    }
} 