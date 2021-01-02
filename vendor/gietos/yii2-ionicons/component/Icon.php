<?php
/**
 * Icon.php
 * @author Sergey Kasatkin
 */

namespace gietos\yii\ionicons\component;

use gietos\yii\ionicons\Ion;
use yii\helpers\Html;

/**
 * Class Icon
 * @package gietos\yii\ionicons\component
 */
class Icon
{
    /** @var array */
    private $options = [];

    /**
     * @param string $name
     * @param array $options
     */
    public function __construct($name, $options = [])
    {
        Html::addCssClass($options, Ion::$cssPrefix . ' ' . Ion::$cssPrefix . '-' . $name);

        $this->options = $options;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * @param string $class
     * @return self
     */
    public function addCssClass($class)
    {
        Html::addCssClass($this->options, $class);

        return $this;
    }

    /**
     * @return string
     */
    public function render()
    {
        return Html::tag('i', null, $this->options);
    }
}