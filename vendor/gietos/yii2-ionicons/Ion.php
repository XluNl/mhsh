<?php
/**
 * Ion.php
 * @author Sergey Kasatkin
 */

namespace gietos\yii\ionicons;

/**
 * Class Ion
 * @package gietos\yii\ionicons
 */
class Ion
{
    /** @var string CSS Class prefix */
    public static $cssPrefix = 'ion';

    /**
     * @param string $name
     * @param array $options
     * @return component\Icon
     */
    public static function icon($name, $options = [])
    {
        return new component\Icon($name, $options);
    }
}