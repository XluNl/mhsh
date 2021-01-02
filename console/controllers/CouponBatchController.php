<?php
namespace console\controllers;

use common\models\CouponBatch;
use yii\console\Controller;

class CouponBatchController extends Controller {
	
  public function actionUpdate(){
    $this->stdout("开始时间:" . date('Y-m-d H:i:s'));
    $num = 0;
    try{
       $soures =  CouponBatch::find()->where(['coupon_type'=>1]);
       
       foreach ($soures->each(50) as $key => $item) {
          $user_time_type   = CouponBatch::USER_TIME_FEATURE_RANG;
          $coupon_type      = CouponBatch::COUPON_PLAN;
          $use_time_feature = json_encode(['start_time'=>$item->use_start_time,'end_time'=>$item->use_end_time]);
          $data  = ['use_time_feature'=>$use_time_feature,'user_time_type'=>$user_time_type,'coupon_type'=>$coupon_type];
          $res = CouponBatch::updateAll($data,['and', ['id' =>$item->id]]);
          if($res===false){
            $this->stdout("\n id=:".$item->id.'---errors='.json_encode($res,JSON_UNESCAPED_UNICODE));
          }else{
            $num++;
          }
       }

    }catch (\Throwable $exception){
        $msg = "error:" . $exception->getMessage()
            . "\nfile:" . $exception->getFile()
            . "\nline:" . $exception->getLine();
        $this->stdout("\n报错信息" . $msg);
    }
    $this->stdout("\n成功:" . $num.'条');
    $this->stdout("\n结束时间:" . date('Y-m-d H:i:s'));

  }
}