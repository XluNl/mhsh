<?php
/**
 * AssetBundle.php
 * @author Sergey Kasatkin spam@onsky.ru
 */

namespace gietos\yii\ionicons;

/**
 * Class AssetBundle
 * @package gietos\yii\ionicons
 */
class AssetBundle extends \yii\web\AssetBundle
{
    /**
     * @inherit
     */
    public $sourcePath = '@vendor/driftyco/ionicons';

    /**
     * @inherit
     */
    public $css = [
        'css/ionicons.min.css',
    ];

    /**
     * Initializes the bundle.
     * Set publish options to copy only necessary files (in this case css and font folders)
     */
    public function init()
    {
        parent::init();

        $this->publishOptions['beforeCopy'] = function ($from, $to) {
            return preg_match('%/(fonts|css)%', $from);
        };
    }
}