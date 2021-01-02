<?php

namespace frontend\components;
use Yii;
use yii\base\Component;
use yii\web\Cookie;

class Delivery extends Component {

	private $deliveryId = null;

	public function init() {
		parent::init();
		$cookies = Yii::$app->request->cookies;
		$this->deliveryId = $cookies->getValue("deliveryId", null);
	}



	public function change($deliveryId) {
	    $this->deliveryId = $deliveryId;
        $cookies = Yii::$app->response->cookies;
        $cookies->add(new Cookie(['name' => 'deliveryId',
            'value' => $deliveryId,
        ]));
	}

    public function clear() {
        $this->deliveryId = null;
        $cookies = Yii::$app->response->cookies;
        $cookies->remove("deliveryId");
    }

    /**
     * @return null
     */
    public function getDeliveryId()
    {
        return $this->deliveryId;
    }


}
