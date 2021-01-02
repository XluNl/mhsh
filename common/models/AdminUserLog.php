<?php

namespace common\models;

/**
 * This is the model class for table "{{%admin_user_log}}".
 *
 * @property integer $id
 * @property integer $company_id
 * @property integer $user_id
 * @property string $username
 * @property string $module
 * @property string $controller
 * @property string $action
 * @property string $ip
 * @property string $remark
 * @property string $create_time
 */
class AdminUserLog extends \yii\db\ActiveRecord
{
    public static $module_arr =[
        'admin'=>'管理模块',
    ];
    public static $controller_arr =[
        'activity'=>'活动',
        'app'=>'APP',
        'blacklist'=>'黑名单',
        'captcha'=>'验证码',
        'company'=>'全部公司',
        'coupon'=>'优惠券',
        'couponlog'=>'优惠券记录',
        'couponnew'=>'新用户优惠',
        'couponsys'=>'系统优惠',
        'courier'=>'配送员',
        'cpandsg'=>'投诉与建议',
        'earnings'=>'余额理财',
        'evaluate'=>'评价',
        'goods'=>'商品',
        'goodsrelative'=>'商品相关度',
        'goodssuppliersort'=>'商品供应商分类',
        'map'=>'地图',
        'noinvitelog'=>'取消推荐',
        'options'=>'配置',
        'order2'=>'补单',
        'order'=>'订单',
        'payment'=>'支付方式',
        'pushpaymenttype'=>'结算方式',
        'site'=>'主页',
        'userlog'=>'用户日志',
        'user'=>'用户',
        'selfcompany'=>'公司信息',
        
    ];
    public static $action_arr = [
        'alertmsg'=>[
            'index'=>'查看',
            'list'=>'列表',
            'read'=>'已读',
            'allread'=>'全部已读',
        ],
        'appstore'=>[
            'appstandard'=>'主页',
        ],
        'area'=>[
            'index'=>'查看',
            'modify_area'=>'修改/新增区域',
            'delete_area'=>'删除区域',
            'modify_virtual'=>'修改/新增虚拟表',
            'delete_virtual_meter'=>'删除虚拟表',
            'modify_meter'=>'修改/新增表',
            'delete_meter'=>'删除表',
            'copy_meter'=>'复制表',
        ],
        'company'=>[
            'index'=>'查看',
            'list'=>'列表',
            'modify'=>'修改/新增',
            'userlist'=>'列表',
            'usermodify'=>'用户修改/新增',
            'status'=>'修改状态',
            'activate'=>'激活用户',
            'inactivate'=>'禁用用户',
            'setsuperadmin'=>'设置用户为超级管理员',
        ],
        'data'=>[
            'area'=>'区域查询',
            'meter'=>'表查询',
            'virtual'=>'虚拟表查询',
            'alertuptypeval'=>'更新峰谷平警报值',
            'alertupstrangetime'=>'更新异常时间段',
            'alertconnecterror'=>'更新通讯异常的设置',
            'alertupbaseline'=>'更新警报值',
            'deletealerttime'=>'删除异常时间段',
            'follow'=>'关注区域',
        ],
        'energyanalyze'=>[
            'energyconversion'=>'能耗转换',
            'energypredict'=>'能耗预测',
            'energyabnormalrunning'=>'运行异常设置',
            'delete'=>'删除通知人',
            'alertaddphone'=>'新增电话通讯人',
            'alertconnecterror'=>'新增邮件通讯人',
        ],
        'energyconversioncoefficienttypes'=>[
            'index'=>'查看',
            'list'=>'列表',
            'modify'=>'修改/新增',
            'delete'=>'删除',
            'setval'=>'快速设置值',
        ],
        'energy'=>[
            'index'=>'查看',
            'list'=>'列表',
            'modify'=>'修改/新增',
            'delete'=>'删除',
        ],
        'fieldtypes'=>[
            'index'=>'查看',
            'list'=>'列表',
            'modify'=>'修改/新增',
            'delete'=>'删除',
        ],
        'maintenance'=>[
            'index'=>'查看',
            'list'=>'列表',
            'modify'=>'修改/新增',
            'delete'=>'删除',
        ],
        'metermodels'=>[
            'index'=>'查看',
            'list'=>'列表',
            'modify'=>'修改/新增',
            'delete'=>'删除',
            'copy'=>'复制',
        ],
        'pricetypes'=>[
            'index'=>'查看',
            'list'=>'列表',
            'modify'=>'修改/新增',
            'delete'=>'删除',
        ],
        'pricetypeconfig'=>[
            'index'=>'查看',
            'list'=>'列表',
            'modify'=>'修改/新增',
            'delete'=>'删除',
        ],
        'report'=>[
            'reporthomepage'=>'主页',
            'reportenergyuse'=>'能耗报表',
            'reportenergyfee'=>'用费报表',
            'reportenergypeak'=>'峰谷平报表',
            'reportbytime'=>'抄点报表',
        ],
        'site'=>[
            'index'=>'主页',
        ],
        'userlog'=>[
            'index'=>'查看',
            'list'=>'列表',
        ],
        'user'=>[
            'signup'=>'添加新用户',  
            'logout'=>'退出登录',
            'change-password'=>'修改密码',
            'index'=>'列表',
            'view'=>'查看',
            'delete'=>'删除',
            'activate'=>'激活',
        ],
        'selfcompany'=>[
            'index'=>'查看',
            'modify'=>'修改',
        ],
    ];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%admin_user_log}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['company_id', 'user_id'], 'integer'],
            [['username', 'module', 'controller', 'action', 'ip', 'remark'], 'string'],
            [['create_time'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'company_id' => '公司ID',
            'user_id' => '用户ID',
            'username' => '用户名',
            'module' => '模块',
            'controller' => '控制器',
            'action' => '动作',
            'ip' => 'IP',
            'remark' => '说明',
            'create_time' => '创建时间',
        ];
    }
    
    public static function saveLog()
    {
        $model = new self;
        $company_id = \Yii::$app->user->identity->company_id;
        $user_id = \Yii::$app->user->identity->id;
        $username = \Yii::$app->user->identity->username;
        $module = \Yii::$app->controller->module->id;
        $controller = \Yii::$app->controller->id;
        $action = \Yii::$app->controller->action->id;
        $ip = \Yii::$app->request->userIP;
        $create_time = date('Y-m-d H:i:s',time());
        $remark="";
//         if (empty($module)||!ArrayHelper::keyExists($module, self::$module_arr)){
            
//         }
//         else {
//             $remark = self::$module_arr[$module].'-';
//         }
//         if (empty($controller)||!ArrayHelper::keyExists($controller, self::$controller_arr)){
//             return true;
//         }
//         else {
//             $remark = $remark.self::$controller_arr[$controller].'-';
//         }
//         if (empty($action)||!ArrayHelper::keyExists($action, self::$action_arr[$controller])){
//             return true;
//         }
//         else {
//             $remark = $remark.self::$action_arr[$controller][$action];
//         }
        $remark = $controller.'-'.$action;
        $userLog = new AdminUserLog();
        $userLog->company_id = $company_id;
        $userLog->user_id = $user_id;
        $userLog->username = $username;
        $userLog->module = $module;
        $userLog->controller = $controller;
        $userLog->action = $action;
        $userLog->ip = $ip;
        $userLog->remark = $remark;
        $userLog->create_time = $create_time;
        $userLog->save();
        return true;
    }
}
