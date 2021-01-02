<?php


namespace frontend\modules\customer\controllers;
use frontend\components\FController;
use frontend\services\LiveService;
use frontend\utils\RestfulResponse;
use Yii;

class LiveController extends FController {

    public function actionGetRoomList(){
        $start = Yii::$app->request->get("start", 0);
        $limit = Yii::$app->request->get("limit", 10);
        $roomList = LiveService::getRoomList($start, $limit,true);
        return RestfulResponse::success($roomList[0]);
    }

    public function actionConstraintGetRoomList(){
        $start = Yii::$app->request->get("start", 0);
        $limit = Yii::$app->request->get("limit", 10);
        $roomList = LiveService::getRoomList($start, $limit,false);
        return RestfulResponse::success($roomList[0]);
    }

    public function actionGetReplayList(){
        $start   = Yii::$app->request->get("start", 0);
        $limit   = Yii::$app->request->get("limit", 10);
        $room_id = Yii::$app->request->get("room_id", 1);



        $replayList = LiveService::getReplayList($room_id, $start, $limit);
        return RestfulResponse::success($replayList[0]);
    }

}