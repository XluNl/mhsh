<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\BootstrapFileInputConfigUtil;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use common\components\BootstrapFileUpload;
use common\models\Goods;
use common\models\GoodsConstantEnum;
use common\utils\ArrayUtils;
use common\utils\StringUtils;
use yii\db\Query;

class GoodsService extends \common\services\GoodsService
{
    /**
     * 获取商品
     * @param $goodsId
     * @param $company_id
     * @param bool $model
     * @return array|bool|Goods|\yii\db\ActiveRecord|null
     */
    public static function getActiveGoods($goodsId,$company_id,$model = false){
        $conditions = ['id' => $goodsId, 'goods_status' => GoodsConstantEnum::$activeStatusArr,'company_id'=>$company_id];
        if ($model){
            return Goods::find()->where($conditions)->one();
        }
        else{
            $result = (new Query())->from(Goods::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }
    }

    /**
     * 获取商品，非空校验
     * @param $goodsId
     * @param $company_id
     * @param $validateException
     * @param bool $model
     * @return array|bool|Goods|\yii\db\ActiveRecord|null
     */
    public static function requireActiveGoods($goodsId,$company_id,$validateException,$model = false){
        $model = self::getActiveGoods($goodsId,$company_id,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }

    /**
     * 商品操作
     * @param $goodsId
     * @param $commander
     * @param $company_id
     * @param $validateException
     */
    public static function operate($goodsId,$commander,$company_id,$validateException){
        BExceptionAssert::assertTrue(in_array($commander,[GoodsConstantEnum::STATUS_UP,GoodsConstantEnum::STATUS_DOWN,GoodsConstantEnum::STATUS_DELETED]),$validateException);
        $count = Goods::updateAll(['goods_status'=>$commander],['id'=>$goodsId,'company_id'=>$company_id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }


    /**
     * 根据goodsOwner查询商品列表
     * @param $company_id
     * @param $goodsOwner
     * @param $validateException
     * @return array| null
     */
    public static function getListByGoodsOwner($company_id,$goodsOwner,$validateException){
        BExceptionAssert::assertTrue(key_exists($goodsOwner,GoodsConstantEnum::$ownerArr),$validateException);
        return self::getAllGoodsByOwner($goodsOwner,$company_id);
    }

    /**
     * 根据goodsOwner查询商品列表(列表专用)
     * @param $company_id
     * @param $goodsOwner
     * @param $validateException
     * @return array|null
     */
    public static function getListByGoodsOwnerOptions($company_id,$goodsOwner,$validateException){
        $goodsArr = self::getListByGoodsOwner($company_id,$goodsOwner,$validateException);
        $goodsArr = ArrayUtils::map($goodsArr,'id','goods_name');
        return $goodsArr;
    }

    /**
     * 根据bigsort查询商品
     * @param $company_id
     * @param $bigSort
     * @return array
     */
    public static function getListByBigSort($company_id,$bigSort){
        $conditions = [
            'company_id'=>$company_id,
            'goods_status' => GoodsConstantEnum::$activeStatusArr,
            'sort_1' => $bigSort,
        ];
        $goodsArr = (new Query())->from(Goods::tableName())->where($conditions)->all();
        return $goodsArr;
    }

    /**
     * 根据smallsort查询商品
     * @param $company_id
     * @param $smallSort
     * @return array
     */
    public static function getListBySmallSort($company_id,$smallSort){
        $conditions = [
            'company_id'=>$company_id,
            'goods_status' => GoodsConstantEnum::$activeStatusArr,
            'sort_2' => $smallSort,
        ];
        $goodsArr = (new Query())->from(Goods::tableName())->where($conditions)->all();
        return $goodsArr;
    }

    /**
     * 根据名称获取
     * @param $goodsName
     * @param $company_id
     * @param bool $model
     * @return array|bool|Goods|\yii\db\ActiveRecord|null
     */
    public static function getByGoodsName($goodsName,$company_id,$model=false){
        $conditions = ['goods_name' => $goodsName, 'goods_status' => GoodsConstantEnum::$activeStatusArr,'company_id'=>$company_id];
        if ($model){
            $result = Goods::find()->where($conditions)->one();
            return $result;
        }
        else{
            $result = (new Query())->from(Goods::tableName())->where($conditions)->one();
            return $result===false?null:$result;
        }

    }


    /**
     * @param $company_id
     * @param $goodsOwner
     * @return array
     */
    public static function getListByGoodsOwnerOptionsNoErr($company_id,$goodsOwner){
        $goodsArr = self::getAllGoodsByOwner($goodsOwner,$company_id);
        $goodsArr = ArrayUtils::map($goodsArr,'id','goods_name');
        return $goodsArr;
    }
    /**
     * @param $goodsOwner
     * @param $companyId
     * @return array
     */
    public static function getAllGoodsByOwner($goodsOwner, $companyId){
        $conditions = [
            'company_id'=>$companyId,
            'goods_status' => GoodsConstantEnum::$activeStatusArr,
            'goods_owner' => $goodsOwner,
        ];
        return (new Query())->from(Goods::tableName())->where($conditions)->all();
    }


    /**
     * @param $goodsId
     * @param $companyId
     * @param BootstrapFileUpload $bootstrapFileUpload
     * @return array
     */
    public static function addVideo($goodsId, $companyId, BootstrapFileUpload $bootstrapFileUpload){
        $config =[];
        try {
            $model = GoodsService::requireActiveGoods($goodsId,$companyId,BBusinessException::create("商品不存在"),true);
            list($success,$errorMsg,$successResult,$failedResult) = $bootstrapFileUpload->uploadFile("/uploads/files");
            $config = BootstrapFileInputConfigUtil::createResultConfig($successResult,$failedResult,$errorMsg,"/goods/video-file-upload");
            if (!$success){
                return $config;
            }
            $goodsVideo = [];
            if (StringUtils::isNotBlank($model->goods_video)){
                $goodsVideo = explode(",",$model->goods_video);
            }
            $goodsVideo = array_merge($goodsVideo,$successResult);
            $updateCount = Goods::updateAll(['goods_video'=>implode(",",$goodsVideo)],['id'=>$goodsId,'company_id'=>$companyId,'goods_video'=>$model->goods_video]);
            BExceptionAssert::assertTrue($updateCount>0,BBusinessException::create("商品更新失败"));
        }
        catch (\Exception $e){
            $config = BootstrapFileInputConfigUtil::createResultConfig([],[],$e->getMessage());
        }
        return $config;
    }

    /**
     * 移除video
     * @param $goodsId
     * @param $companyId
     * @param $fileKey
     * @return array|mixed
     */
    public static function removeVideo($goodsId, $companyId, $fileKey){
        $config =[];
        try {
            $model = GoodsService::requireActiveGoods($goodsId,$companyId,BBusinessException::create("商品不存在"),true);
            if (StringUtils::isNotBlank($model->goods_video)){
                $flag = false;
                $goodsVideo = explode(",",$model->goods_video);
                foreach ($goodsVideo as $k=>$v){
                    if ($v==$fileKey){
                        unset($goodsVideo[$k]);
                        $flag=true;
                    }
                }
                BExceptionAssert::assertTrue($flag,BBusinessException::create("商品移除失败，请刷新重试"));
                $updateCount = Goods::updateAll(['goods_video'=>implode(",",$goodsVideo)],['id'=>$goodsId,'company_id'=>$companyId,'goods_video'=>$model->goods_video]);
                BExceptionAssert::assertTrue($updateCount>0,BBusinessException::create("商品更新失败"));
            }
            (new BootstrapFileUpload())->removeFile($fileKey);
        }
        catch (\Exception $e){
            $config = BootstrapFileInputConfigUtil::createFailedResultConfig($e->getMessage());
        }
        return $config;
    }

}