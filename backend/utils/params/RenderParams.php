<?php
namespace backend\utils\params;
use common\models\Common;
use yii\base\Model;

/**
 * render 参数
 * Class RenderParams
 * @property string $message
 * @property \yii\web\Controller $controller;
 * @property string $view;
 * @property array $params;
 */
class RenderParams
{
    public $message;
    public $controller;
    public $view;
    public $params;

    /**
     * RenderParams constructor.
     * @param string $message
     * @param \yii\web\Controller $controller
     * @param string $view
     * @param array $params
     */
    public function __construct($message, \yii\web\Controller $controller, $view, array $params)
    {
        $this->message = $message;
        $this->controller = $controller;
        $this->view = $view;
        $this->params = $params;
    }

    public function updateMessage($message){
        $this->message = $message;
        return $this;
    }

    /**
     * @param $message
     * @param \yii\web\Controller $controller
     * @param $view
     * @param array $params
     * @return RenderParams
     */
    public static function create($message, \yii\web\Controller $controller, $view, array $params)
    {
        return new RenderParams($message,$controller, $view, $params);
    }

    /**
     * @param Model $model
     * @param \yii\web\Controller $controller
     * @param $view
     * @param array $params
     * @return RenderParams
     */
    public static function createModelError(Model $model, \yii\web\Controller $controller, $view, array $params)
    {
        return new RenderParams(Common::getExistModelErrors($model),$controller, $view, $params);
    }

}