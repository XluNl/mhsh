<?php

namespace yiichina\icheck;

use yii\bootstrap\Html;
use yii\helpers\Json;

/**
 * This is just an example.
 */
class ICheck extends \yii\widgets\InputWidget
{
    const TYPE_CHECBOX = 'checkbox';
    const TYPE_CHECBOX_LIST = 'checkboxList';
    const TYPE_RADIO = 'radio';
    const TYPE_RADIO_LIST = 'radioList';

    const SKIN_MIMIMAL = 'minimal';
    const SKIN_SQUARE = 'square';
    const SKIN_FLAT = 'flat';
    const SKIN_LINE = 'line';
    const SKIN_POLARIS = 'polaris';
    const SKIN_FUTURICO = 'futurico';

    const COLOR_RED = 'red';
    const COLOR_GREEN = 'green';
    const COLOR_BLUE = 'blue';
    const COLOR_AERO = 'aero';
    const COLOR_GREY = 'grey';
    const COLOR_ORANGE = 'orange';
    const COLOR_YELLOW = 'yellow';
    const COLOR_PINK = 'pink';
    const COLOR_PURPLE = 'purple';

    public $skin = self::SKIN_MIMIMAL;
    public $color = null;
    public $checked = false;
    public $type;
    public $items;
    public $selection;
    public $clientOptions = [];

    public function init()
    {
        parent::init();
        $view = $this->getView();
        $asset = ICheckAsset::register($view);
        $view->registerCssFile($asset->baseUrl . '/skins/' . $this->skin . '/' . ($this->color ? $this->color : $this->skin) . '.css');
        $color = $this->color ? "-{$this->color}" : null;
        Html::addCssClass($this->options, ['widget' => 'icheck']);
        $this->clientOptions = array_merge($this->clientOptions, ['checkboxClass' => "icheckbox_{$this->skin}{$color}", 'radioClass' => "iradio_{$this->skin}{$color}"]);
        $clientOptions = Json::encode($this->clientOptions);
        $view->registerJs("$('#{$this->options['id']}').iCheck($clientOptions)");
    }

    public function run()
    {
        $typeName = $this->type;
        $activeTypeName = 'active' . ucfirst($typeName);
        if($typeName == self::TYPE_RADIO || $typeName == self::TYPE_CHECBOX) {
            if ($this->hasModel()) {
                return Html::$activeTypeName($this->model, $this->attribute, $this->options);
            } else {
                return Html::$typeName($this->name, $this->checked, $this->options);
            }
        } elseif($typeName == self::TYPE_RADIO_LIST || $typeName == self::TYPE_CHECBOX_LIST) {
            if($this->hasModel()) {
                return Html::$activeTypeName($this->model, $this->attribute, $this->items, $this->options);
            } else {
                return Html::$typeName($this->name, $this->selection, $this->items, $this->options);
            }
        } else {
            throw new InvalidParamException('Type name must defined as a constant.');
        }
    }
}
