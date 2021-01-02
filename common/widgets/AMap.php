<?php
/**
 * huzhengguang
 */

namespace common\widgets;

use yii\bootstrap\Widget;

/**
 */
class AMap extends Widget
{

    public $key = 'dffcbf3570256cfdb8fd94502c26537b';

    public $plugin = null;

    public function init()
    {
        parent::init();
        $view = $this->getView();
        $view->registerJsFile("https://webapi.amap.com/maps?v=1.4.15&key={$this->key}&plugin={$this->plugin}");
    }
}
