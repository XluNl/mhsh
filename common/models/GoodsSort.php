<?php

namespace common\models;
use yii\behaviors\BlameableBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "{{%goods_sort}}".
 *
 * @property integer $id
 * @property string $sort_name
 * @property integer $sort_order
 * @property integer $parent_id
 * @property integer $sort_show
 * @property integer $sort_status
 * @property string $created_at
 * @property string $updated_at
 * @property string $pic_name
 * @property integer $company_id
 * @property integer $sort_owner
 */
class GoodsSort extends ActiveRecord{

    const SHOW_STATUS_SHOW = 1;
    const SHOW_STATUS_HIDE = 0;

    public static $showStatusArr=[
        self::SHOW_STATUS_SHOW=>'显示',
        self::SHOW_STATUS_HIDE=>'隐藏',
    ];

    public static $showStatusCssArr=[
        self::SHOW_STATUS_SHOW=>'label label-success',
        self::SHOW_STATUS_HIDE=>'label label-danger',
    ];


    public static function getSortOwnerArr(){
        return GoodsConstantEnum::$ownerArr;
    }

    public static function tableName() {
		return "{{%goods_sort}}";
	}

    public function behaviors()
    {
        return [
            [  // 匿名行为, 配置数组
                'class' => BlameableBehavior::className(),  // 行为类
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at','updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules()
    {
        return [
            [['sort_name'], 'required'],
            [['sort_order', 'parent_id', 'sort_show', 'sort_status', 'company_id', 'sort_owner'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['sort_name'], 'string', 'max' => 10],
            [['pic_name','pic_icon'], 'string', 'max' => 255],
            ['parent_id','default',"value" => 0],
            ['sort_show','default',"value" =>self::SHOW_STATUS_SHOW],
            ['sort_owner','default',"value" => GoodsConstantEnum::OWNER_SELF],
            [['sort_name'], 'unique', 'targetAttribute' => ['company_id', 'parent_id', 'sort_name'], 'message' => '分类名称不能重复'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'sort_name' => '分类名称',
            'sort_order' => '菜单排序',
            'parent_id' => '父类菜单ID',
            'sort_show' => '是否显示',
            'sort_status' => '状态值',
            'created_at' => '创建时间',
            'updated_at' => '修改时间',
            'pic_name' => '图片名称',
            'pic_icon' => 'icon名称',
            'company_id' => 'Company ID',
            'sort_owner' => '分类类型',
        ];
    }

	public function beforeSave($insert){
		if (parent::beforeSave($insert)) {
			if ($this->isNewRecord) {
				$this->sort_status = CommonStatus::STATUS_ACTIVE;
			}
			return true;
		}else{
			return false;
		}
	}

	public static function showStatus($sort_id){
		$row = GoodsSort::findOne($sort_id);
		if (empty($row)) {
			return false;
		}
		return ($row->sort_show==GoodsSort::SHOW_STATUS_SHOW) ? true : false;
	}

    public function getSubSort(){
        return $this->hasMany(GoodsSort::className(),['parent_id' => 'id'])
            ->where(['sort_status'=>CommonStatus::STATUS_ACTIVE])->orderBy("sort_order desc");
    }
	
}