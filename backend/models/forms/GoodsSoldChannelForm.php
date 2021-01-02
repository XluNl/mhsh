<?php


namespace backend\models\forms;


use yii\base\Model;

/**
 * Class GoodsSoldChannelForm
 * @package backend\models\forms
 *
 * @property integer $sold_channel_type
 * @property string $sold_channel_ids
 */
class GoodsSoldChannelForm extends Model
{
    public $sold_channel_type;

    public $sold_channel_ids = [];

    public function rules()
    {
        return [
            [['sold_channel_type'],'required'],
            ['sold_channel_ids','safe']
        ];
    }

    public function attributeLabels()
    {
        return [
            'sold_channel_type' => '售卖渠道类型',
            'sold_channel_ids' => '售卖渠道',
        ];
    }
}