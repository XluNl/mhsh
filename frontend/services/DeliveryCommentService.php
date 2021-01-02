<?php


namespace frontend\services;


use common\models\DeliveryComment;

class DeliveryCommentService extends \common\services\DeliveryCommentService
{
    /**
     * 获取可展示的团长评论
     * @param $goodsId
     * @param int $pageNo
     * @param int $pageSize
     * @return array
     */
    public static function getShowListByGoodsId($goodsId,$pageNo=1,$pageSize=20){
        $commentLists =parent::getShowList($goodsId,null,$pageNo,$pageSize);
        return $commentLists;
    }

    /**
     * 展示详情
     * @param $id
     * @return array|DeliveryComment|\yii\db\ActiveRecord|null
     */
    public static function getShowModelById($id){
        $comment = parent::getShowModel($id);
        return $comment===false?null:$comment;
    }
}