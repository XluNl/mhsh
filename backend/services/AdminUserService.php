<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\BStatusCode;
use backend\utils\exceptions\BBusinessException;
use backend\utils\params\RedirectParams;
use common\models\AdminUser;
use common\models\AuthAssignment;
use Yii;

class AdminUserService extends \common\services\AdminUserService
{

    /**
     * 非空
     * @param $id
     * @param $companyId
     * @param $validateException RedirectParams
     * @param bool $model
     * @return array|bool|\common\models\AdminUser|\yii\db\ActiveRecord|null
     */
    public static function requireModel($id, $companyId, $validateException, $model = false){
        $model = self::getModel($id,$companyId,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }

    /**
     * @param $id
     * @param $companyId
     * @param bool $model
     * @return array|bool|\yii\db\ActiveRecord|null
     */
    public static function requireActiveModel($id, $companyId=null, $model = false){
        $model = self::getModel($id,$companyId,$model);
        BExceptionAssert::assertNotNull($model,BStatusCode::createExp(BStatusCode::ADMIN_USER_NOT_EXIST));
        BExceptionAssert::assertTrue($model['status']==AdminUser::STATUS_ACTIVE,BStatusCode::createExp(BStatusCode::ADMIN_USER_NOT_EXIST));
        return $model;
    }

    /**
     * @param $id
     * @param $validateException RedirectParams
     */
    public static function setAdminUser($id, $validateException){
        $transaction = Yii::$app->db->beginTransaction();
        try {
            $user = parent::getModel($id);
            $companyId = $user['company_id'];
            if (!empty($user)){
                AuthAssignment::deleteAll(['item_name'=>'超级管理员','company_id'=>$companyId]);
                AdminUser::updateAll(['is_super_admin'=> AdminUser::SUPER_ADMIN_NO],['company_id'=>$companyId]);
                $assign = new AuthAssignment();
                $assign->item_name = '超级管理员';
                $assign->company_id = $companyId;
                $assign->user_id = $user['id'];
                $assign->created_at = time();
                BExceptionAssert::assertNotNull($assign->save(),BBusinessException::create("权限注册失败"));
                $user = AdminUser::findOne($id);
                $user->is_super_admin = AdminUser::SUPER_ADMIN_YES;
                BExceptionAssert::assertNotNull($user->save(),BBusinessException::create("用户信息更新失败"));
                $transaction->commit();
            }
        } catch (\Exception $e) {
            $transaction->rollBack();
            BExceptionAssert::assertNotNull(false,$validateException->updateMessage($e->getMessage()));
        }
    }

    public static function operateStatus($id, $commander, $validateException){
        BExceptionAssert::assertTrue(in_array($commander,[AdminUser::STATUS_ACTIVE,AdminUser::STATUS_INACTIVE]),$validateException);
        $count = AdminUser::updateAll(['status'=>$commander,'updated_at'=>time()],['id'=>$id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }

}