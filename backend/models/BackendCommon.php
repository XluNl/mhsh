<?php
namespace backend\models;
use backend\services\CompanyService;
use common\models\AdminUser;
use common\models\AdminUserInfo;
use common\models\Common;
use Yii;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class BackendCommon  extends Common{

    /**
     * 获取公司ID
     * @return mixed
     */
    public static function getFCompanyId(){
        return Yii::$app->user->identity->company_id;
    }

    public static function getUserId(){
        return Yii::$app->user->identity->getId();
    }

    public static function getUserName(){
        return Yii::$app->user->identity->username;
    }

    /**
     * 默认空白下拉框
     * @param $options
     * @param array $blankOption
     * @return array
     */
    public static function addBlankOption($options,$blankOption=[''=>'请选择']){
        return ArrayHelper::merge($blankOption,$options);
    }

    /**
     * URL参数解析
     * @param $query
     * @return array
     */
    public static function  convertUrlQuery($query)
    {
        if (empty($query)){
            return [];
        }
        $queryParts = explode('&', $query);
        $params = array();
        foreach ($queryParts as $param)
        {
            $item = explode('=', $param);
            $params[$item[0]] = $item[1];
        }
        return $params;
    }


    public static function getMark() {
        $result = '没有添加个性签名';
        if (!Yii::$app->user->isGuest){
            $user_id = Yii::$app->user->identity->id;
            $user_info = AdminUserInfo::find()->where(['user_id'=>$user_id])->one();
            if (!empty($user_info->mark)){
                $result = $user_info->mark;
            }
        }
        return  $result;
    }
    
    public static function getPic(){
        $result = '';
        if (!Yii::$app->user->isGuest){
            $user_id = Yii::$app->user->identity->id;
            $user_info =  (new Query())->from(AdminUserInfo::tableName())->limit(1)->where(['user_id'=>$user_id])->one();
            $result = '/'.$user_info['pic'];
        }
        return  $result;
    }
    
    public static function getCompanyName(){
        if (!Yii::$app->user->isGuest){
            $companyId = self::getFCompanyId();
            if (!empty($companyId)) {
                $companyModel = CompanyService::getModel($companyId);
                return $companyModel['name'];
            }
            else {
                return '系统运行';
            }
        }
    }
    
    public static function getRoleName(){
        if (!Yii::$app->user->isGuest){
            $companyId = self::getFCompanyId();
            if (self::isSuperCompany($companyId)){
                return '系统运行管理员';
            }
            if (Yii::$app->user->identity->is_super_admin==AdminUser::SUPER_ADMIN_YES){
                return '超级管理员';
            }
            else{
                return '普通管理员';
            }
        }
        else{
            return "未登陆用户";
        }
    }


    /**
     * 成功提示信息
     * @param $info
     */
    public static function showSuccessInfo($info){
        Yii::$app->session->setFlash('success', $info);
    }


    /**
     * 失败提示信息
     * @param $err
     */
    public static function showErrorInfo($err){
        Yii::$app->session->setFlash('danger', $err);
    }

    /**
     * 失败警示信息
     * @param $err
     */
    public static function showWarningInfo($err){
        Yii::$app->session->setFlash('warning', $err);
    }


    /**
     * 增加companyId到search中
     * @param $searchClassName
     */
    public static function addCompanyIdToParams($searchClassName){
        if (BackendCommon::isSuperCompany(BackendCommon::getFCompanyId())){
            return;
        }
        if (key_exists($searchClassName,Yii::$app->request->queryParams)){
            $params = Yii::$app->request->queryParams[$searchClassName];
        }
        else{
            $params = [];
        }
        $params['company_id'] = BackendCommon::getFCompanyId();
        Yii::$app->request->queryParams = ArrayHelper::merge(Yii::$app->request->queryParams,[$searchClassName=>$params]);
    }

    /**
     * 设置值到search中
     * @param $key
     * @param $value
     * @param $searchClassName
     */
    public static function addValueIdToParams($key,$value,$searchClassName){
        if (key_exists($searchClassName,Yii::$app->request->queryParams)){
            $params = Yii::$app->request->queryParams[$searchClassName];
        }
        else{
            $params = [];
        }
        $params[$key] = $value;
        Yii::$app->request->queryParams = ArrayHelper::merge(Yii::$app->request->queryParams,[$searchClassName=>$params]);
    }

    /**
     * 从searchModel中获取name对应的值
     * @param $searchClassName
     * @param $name
     * @param $default
     * @return mixed
     */
    public static function getSearchParamsByName($searchClassName, $name, $default=''){
        if (key_exists($searchClassName,Yii::$app->request->queryParams)){
            $params = Yii::$app->request->queryParams[$searchClassName];
        }
        else{
            $params = [];
        }
        if (key_exists($name,$params)){
            return $params[$name];
        }
        else{
            return $default;
        }
    }

    /**
     * parse options
     * @param $models
     * @return mixed
     */
    public static function parseOptions($models)
    {
        $str = "";
        foreach ($models as $k=>$v){
            $str .= '<option value="'.$k.'">'.$v.'</option>';
        }
        return $str;
    }


}