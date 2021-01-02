<?php


namespace business\services;


use business\utils\ExceptionAssert;
use business\utils\StatusCode;
use common\models\PopularizerSelect;
use yii\db\Query;

class PopularizerService extends \common\services\PopularizerService
{

    public static function requiredModel($id, $model = false){
        $deliveryModel = parent::getActiveModel($id,null,$model);
        ExceptionAssert::assertNotNull($deliveryModel,StatusCode::createExpWithParams(StatusCode::POPULARIZER_NOT_EXIST,'分享团长不存在'));
        return $deliveryModel;
    }

    /**
     * 获取当前设定的配送点id
     * @param $userId
     * @return |null
     */
    public static function getSelectedPopularizerId($userId){
        $popularizerSelect = (new Query())->from(PopularizerSelect::tableName())->where(['user_id'=>$userId])->one();
        if (empty($popularizerSelect)){
            $popularizerModels = parent::getActiveModelByUserId($userId);
            if (empty($popularizerModels)){
                return null;
            }
            return $popularizerModels[0]['id'];
        }
        return $popularizerSelect['popularizer_id'];
    }

    /**
     * 修改默认发货团长
     * @param $userId
     * @param $popularizerId
     */
    public static function changeSelectedPopularizerId($userId, $popularizerId){
        $popularizerModel = self::requiredModel($popularizerId);
        ExceptionAssert::assertTrue($popularizerModel['user_id']==$userId,StatusCode::createExpWithParams(StatusCode::POPULARIZER_CHANGE_ERROR,'分享团长不属于你'));
        $popularizerSelect = PopularizerSelect::find()->where(['user_id'=>$userId])->one();
        if (empty($popularizerSelect)){
            $popularizerSelect = new PopularizerSelect();
            $popularizerSelect->user_id = $userId;
        }
        $popularizerSelect->popularizer_id = $popularizerId;
        ExceptionAssert::assertTrue($popularizerSelect->save(),StatusCode::createExpWithParams(StatusCode::POPULARIZER_CHANGE_ERROR,'保存记录失败'));
    }

    /**
     * 获取列表（带当前选中的团长）
     * @param $userId
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getListWithDefault($userId)
    {
        $list = parent::getActiveModelByUserId($userId);
        if (!empty($list)){
            $selectId = self::getSelectedPopularizerId($userId);
            foreach ($list as $k=>$v){
                if ($v['id']==$selectId){
                    $v['selected'] = true;
                }
                else{
                    $v['selected'] = false;
                }
                $list[$k] = $v;
            }
        }
        return $list;
    }

}