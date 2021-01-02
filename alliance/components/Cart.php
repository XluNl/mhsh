<?php

namespace alliance\components;
use Yii;
use yii\base\Component;
use yii\web\Cookie;

class Cart extends Component {

	public $expire = 36000;
	private $goods_list = [];
	private $goods_total = 0;

	public function init() {
		parent::init();
		$cookies = Yii::$app->request->cookies;
		$cart = $cookies->getValue("cart", array());
		if (!empty($cart)) {
			$this->goods_total = $cart['goods_total'];
			$this->goods_list = $cart['goods_list'];
		}
	}

	public function addGoods($sku_id, $goods_num = 1) {
	    $key = $sku_id;
		$this->goods_total = $this->goods_total + $goods_num;
		if (empty($this->goods_list[$key])) {
			$this->goods_list[$key] = $goods_num;
		} else {
			$this->goods_list[$key] = $this->goods_list[$key] + $goods_num;
		}
	}

	public function listGoods() {
		return $this->goods_list;
	}

	public function delGoods($sku_id, $goods_num = 1) {

        $key = $sku_id;


		/* if ($this->goods_total > 0) {
			$this->goods_total -= 1;
		} */
		if (empty($this->goods_list[$key]) || $goods_num == 0) {
			unset($this->goods_list[$key]);
		} elseif (($this->goods_list[$key] - $goods_num) <= 0) {
		    $this->goods_total -= $this->goods_list[$key];
			unset($this->goods_list[$key]);
		} else {
			$this->goods_list[$key] -= $goods_num;
			$this->goods_total -= $goods_num;
		}
	}

	public function modifyGoods($sku_id, $goods_num = 1) {

        $key = $sku_id;
		$goods_num = empty($goods_num) ? 0 : $goods_num;
		if ($goods_num) {
			if (empty($this->goods_list[$key])) {
				$this->goods_list[$key] = 0;
			}
			$per_num = $this->goods_list[$key];
			$this->goods_total += $goods_num - $per_num;
			$this->goods_list[$key] = $goods_num;
		} else {
			if (!empty($this->goods_list[$key])) {
				$this->goods_total -= $this->goods_list[$key];
				unset($this->goods_list[$key]);
			}
		}
	}

	public function isEmpty() {
		if (!$this->goods_total || empty($this->goods_list)) {
			return true;
		} else {
			return false;
		}
	}
	public function count() {
		return $this->goods_total;
	}
	public function emptyGoods() {
		$this->goods_total = 0;
		$this->goods_list = [];
	}

	public function flushGoods(){
        $cookies = Yii::$app->response->cookies;
        $cookies->add(new Cookie(['name' => 'cart',
            'value' => array(
                'goods_total' => $this->count(),
                'goods_list' => $this->listGoods(),
            ),
        ]));
    }
}
