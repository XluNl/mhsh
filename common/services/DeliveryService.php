<?php


namespace common\services;


use common\models\CommonStatus;
use common\models\Delivery;
use common\models\Goods;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use yii\db\Expression;
use yii\db\Query;

class DeliveryService
{
    /**
     * 获取所有的
     * @param null $ids
     * @param null $company_id
     * @return array
     */
    public static function getAllActiveModel($ids=null,$company_id=null){
        $conditions = ['status'=>CommonStatus::STATUS_ACTIVE];
        if (!empty($ids)){
            $conditions['id']=$ids;
        }
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        $result = (new Query())->from(Delivery::tableName())->where($conditions)->all();
        return $result;
    }

    /**
     * @param null $ids
     * @param null $company_id
     * @return array
     */
    public static function getAllModel($ids=null,$company_id=null){
        $conditions = [];
        if (!empty($ids)){
            $conditions['id']=$ids;
        }
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        $result = (new Query())->from(Delivery::tableName())->where($conditions)->all();
        return $result;
    }

    /**
     * 获取model
     * @param $id
     * @param null $company_id
     * @param bool $model
     * @return array|bool|Delivery|\yii\db\ActiveRecord|null
     */
    public static function getActiveModel($id, $company_id=null, $model = false){
        $conditions = ['id' => $id,'status'=>CommonStatus::STATUS_ACTIVE];
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return Delivery::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Delivery::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * @param $id
     * @param null $company_id
     * @param bool $model
     * @return array|bool|\yii\db\ActiveRecord|null
     */
    public static function getModel($id, $company_id=null, $model = false){
        $conditions = ['id' => $id];
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return Delivery::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Delivery::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * @param $userId
     * @param bool $model
     * @return array|\yii\db\ActiveRecord[]
     */
    public static function getModelByUserId($userId, $model = false){
        $conditions = ['user_id' => $userId];
        if ($model){
            return Delivery::find()->where($conditions)->all();
        }
        else{
            return (new Query())->from(Delivery::tableName())->where($conditions)->all();
        }
    }

    /**
     * 获取model
     * @param $userId
     * @param bool $model
     * @return array|bool|Delivery|\yii\db\ActiveRecord|null
     */
    public static function getActiveModelByUserId($userId, $model = false){
        $conditions = ['user_id' => $userId,'status'=>CommonStatus::STATUS_ACTIVE];
        if ($model){
            return Delivery::find()->where($conditions)->all();
        }
        else{
            return (new Query())->from(Delivery::tableName())->where($conditions)->all();
        }
    }

    public static function getActiveModelByUserIdAndCompanyId($userId,$companyId, $model = false){
        $conditions = ['user_id' => $userId,'company_id'=>$companyId,'status'=>CommonStatus::STATUS_ACTIVE];
        if ($model){
            return Delivery::find()->where($conditions)->all();
        }
        else{
            return (new Query())->from(Delivery::tableName())->where($conditions)->all();
        }
    }

    /**
     *
     * @param $ids
     * @param null $company_id
     * @param bool $model
     * @return array|bool|Delivery|\yii\db\ActiveRecord|null
     */
    public static function getActiveModels($ids, $company_id=null, $model = false){
        $conditions = ['id' => $ids,'status'=>CommonStatus::STATUS_ACTIVE];
        if (!StringUtils::isEmpty($company_id)){
            $conditions['company_id'] = $company_id;
        }
        if ($model){
            return Delivery::find()->where($conditions)->all();
        }
        else{
            $result = (new Query())->from(Delivery::tableName())->where($conditions)->all();
            return $result;
        }
    }

    /**
     * 根据用户id和配送点id
     * @param $id
     * @param $userId
     * @param $model boolean
     * @return array|bool|Delivery|\yii\db\ActiveRecord|null
     */
    public static function getActiveModelByIdAndUserId($id,$userId,$model=false){
        $conditions = ['id' => $id,'user_id'=>$userId,'status'=>CommonStatus::STATUS_ACTIVE];
        if ($model){
            return Delivery::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Delivery::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 搜寻附近的提货点
     * @param $lat
     * @param $lng
     * @param $type
     * @param $companyId
     * @param string $keyword
     * @return array
     */
    public static function getNearBy($lat,$lng,$type,$companyId=null,$keyword=''){
        $query = (new Query())->from(Delivery::tableName())->limit(20);
        $conditions = [
            'and',
            [
                'status'=>CommonStatus::STATUS_ACTIVE,
                'allow_order'=>Delivery::ALLOW_ORDER_TRUE,
            ],
            ['not',['lat'=>null]],
            ['not',['lng'=>null]]
        ];
        if (StringUtils::isNotBlank($type)){
            $conditions[] = ['type'=>$type];
        }
        if (StringUtils::isNotBlank($companyId)){
            $conditions[] = ['company_id'=>$companyId];
        }
        if (StringUtils::isNotBlank($keyword)){
            $conditions[] = ['or',
                ['like', 'nickname', $keyword],
                ['like', 'realname', $keyword],
                ['like', 'community', $keyword],
                ['phone'=>$keyword]
            ];
            $query->limit(50);
        }
        $deliveryModels = $query->where($conditions)->select(new Expression("*,POW(`lat`-{$lat},2) + POW(`lng`-{$lng},2) as dist"))->orderBy("dist")->all();
        if (StringUtils::isBlank($keyword)){
            if (!empty($deliveryModels)){
                foreach ($deliveryModels as $k=>$v){
                    $deliveryModels[$k]['distance'] = LocationService::getDistance($v['lng'],$v['lat'],$lng,$lat);
                    $deliveryModels[$k]['distance_text'] = LocationService::resolveDistance($deliveryModels[$k]['distance']);
                    if ($deliveryModels[$k]['distance']>Delivery::$typeLenArr[$type]){
                        unset($deliveryModels[$k]);
                    }
                }
            }
        }
        return $deliveryModels;
    }


    /**
     * 团长商品投放新增
     * @param $companyId
     * @param $deliveryId
     * @param $goodsIds
     * @return array
     * @throws \yii\db\Exception
     */
    public static function goodsDeliveryChannelAdd($companyId,$deliveryId,$goodsIds){
        $goodsModels = GoodsService::getActiveOwnerGoodsArray($goodsIds,$companyId);
        $goodsModels = ArrayUtils::index($goodsModels,'id');
        foreach ($goodsIds as $goodsId){
            if (key_exists($goodsId,$goodsModels)){
                $goodsModel = $goodsModels[$goodsId];
                if ($goodsModel['goods_sold_channel_type']==Goods::GOODS_SOLD_CHANNEL_TYPE_DELIVERY){
                    list($result,$error) = GoodsSoldChannelService::simpleAddGoodsSoldChannel($deliveryId,$goodsId,$companyId);
                    if (!$result){
                        return [false,"商品{$goodsId}新增失败({$error})"];
                    }
                }
            }
            else{
                return [false,"商品{$goodsId}不存在"];
            }
        }
        return [true,''];
    }


    public static function batchSetRenamePicture($models){
        if (empty($models)){
            return [];
        }
        foreach ($models as $k=>$v){
            $v = self::setRenamePicture($v);
            $models[$k] = $v;
        }
        return $models;
    }

    public static function setRenamePicture($model){
        if (empty($model)){
            return [];
        }
        $model = GoodsDisplayDomainService::renameImageUrl($model,'contract_images');
        $model = GoodsDisplayDomainService::renameImageUrl($model,'head_img_url');
        return $model;
    }

    public static function getDeliveryById($deliveryId){
        return Delivery::find()->where(['id'=>$deliveryId])->one();
    }
}