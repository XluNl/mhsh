<?php


namespace backend\services;


use backend\utils\BExceptionAssert;
use backend\utils\params\RedirectParams;
use common\models\CommonStatus;
use common\models\Company;
use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;

class CompanyService extends \common\services\CompanyService
{

    /**
     * 非空
     * @param $id
     * @param $validateException RedirectParams
     * @param bool $model
     * @return array|bool|\common\models\Company|\yii\db\ActiveRecord|null
     */
    public static function requireActiveModel($id,$validateException,$model = false){
        $model = self::getModel($id,$model);
        BExceptionAssert::assertNotNull($model,$validateException);
        return $model;
    }

    public static function operateStatus($id, $commander, $validateException){
        BExceptionAssert::assertTrue(in_array($commander,[CommonStatus::STATUS_ACTIVE,CommonStatus::STATUS_DISABLED]),$validateException);
        $count = Company::updateAll(['status'=>$commander,'updated_at'=>DateTimeUtils::parseStandardWLongDate()],['id'=>$id]);
        BExceptionAssert::assertTrue($count>0,$validateException);
    }


    public static function completeCompanyName($dataProvider){
        if (empty($dataProvider)){
            return $dataProvider;
        }
        $models = $dataProvider->getModels();
        if (!empty($models)){
            $ids = ArrayUtils::getColumnWithoutNull("company_id",$models);
            $companyModels = parent::getAllModel($ids);
            $companyModels = ArrayUtils::index($companyModels,'id');
            foreach ($models as $k=>$v){
                if (key_exists($v['company_id'],$companyModels)){
                    $v['company_name'] = $companyModels[$v['company_id']]['name'];
                    $models[$k] = $v;
                }
            }
        }
        $dataProvider->setModels($models);
        return $dataProvider;
    }

    public static function getAllCompanyOptions(){
        $companyModels = parent::getAllModel();
        return self::generateOptions($companyModels);
    }

    public static function generateOptions($models){
        if (empty($models)){
            return [];
        }
        $options = [];
        foreach ($models as $model){
            $options[$model['id']] = "{$model['name']}";
        }
        return $options;
    }

}