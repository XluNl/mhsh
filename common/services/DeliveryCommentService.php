<?php


namespace common\services;


use common\models\DeliveryComment;
use yii\db\Query;

class DeliveryCommentService
{
    /**
     * 获取可展示的团长评论
     * @param $goodsId
     * @param $companyId
     * @param int $pageNo
     * @param int $pageSize
     * @return array
     */
    public static function getShowList($goodsId,$companyId=null,$pageNo=1,$pageSize=20){
        $conditions = ['goods_id'=>$goodsId,
            'is_show'=>DeliveryComment::IS_SHOW_TRUE,
            'status'=>DeliveryComment::STATUS_ACCEPT,
        ];
        if (!empty($companyId)){
            $conditions['company_id'] = $companyId;
        }
        $commentLists = DeliveryComment::find()
            ->where($conditions)->with(['goods','goodsSku','delivery'])
            ->offset(($pageNo-1)*$pageSize)
            ->limit($pageSize)
            ->orderBy('id desc')
            ->asArray()->all();
        return $commentLists;
    }


    /**
     * 根据id查询详情
     * @param $id
     * @param null $companyId
     * @return array|DeliveryComment|\yii\db\ActiveRecord|null
     */
    public static function getShowModel($id,$companyId=null){
        $conditions = [
            'id'=>$id,
            'is_show'=>DeliveryComment::IS_SHOW_TRUE,
            'status'=>DeliveryComment::STATUS_ACCEPT,
        ];
        if (!empty($companyId)){
            $conditions['company_id'] = $companyId;
        }
        $comment = DeliveryComment::find()->where($conditions)->with(['goods','goodsSku','delivery'])->asArray()->one();
        return $comment===false?null:$comment;
    }
}