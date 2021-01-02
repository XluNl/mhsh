<?php


namespace frontend\services;
use Yii;


class LiveService
{

    public static function getRoomList($start, $limit,$cache=true){
        $cache = Yii::$app->cache;
        if ($cache){
            $roomList = $cache->get('roomList');
            if ($roomList){
                return $roomList;
            }
        }
        $liveList = Yii::$app->frontendWechat->miniProgram->live->getRooms($start,$limit);
        $roomList = [];
        foreach ($liveList['room_info'] as $room){
            if ( $room['live_status'] == 101){
                $roomList[] = $room;
            }
        }
        $cache->add('roomList',$roomList,600);
        return $roomList;
    }

    public static function getReplayList($roomId, $start, $limit){
        $cache = Yii::$app->cache;
        $replayList = $cache->get('replayList');
        if ($replayList){
            return $replayList;
        }
        $liveList = Yii::$app->frontendWechat->miniProgram->live->getPlaybacks($roomId,$start,$limit);
        $cache->add('replayList',$liveList['live_replay'],600);
        return $liveList['live_replay'];
    }
}