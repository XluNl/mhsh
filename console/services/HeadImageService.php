<?php


namespace console\services;


use alliance\services\DownloadFileService;
use common\models\Delivery;
use common\models\User;
use common\models\UserInfo;
use common\utils\PathUtils;
use common\utils\StringUtils;

class HeadImageService
{
    /**
     * 刷新所有空头像
     * @param $baseDir
     * @return array[]
     */
    public static function flushAllZeroDataHeadImage($baseDir){
        $successList = [];
        $failedList = [];
        $dirList = self::getFileList($baseDir);
        foreach ($dirList as $dirOne){
            $dirOnePath = PathUtils::join($baseDir,$dirOne);
            $files = self::getFileList($dirOnePath);
            foreach ($files as $fileOne){
                $fileOnePath = PathUtils::join($dirOnePath,$fileOne);
                $fileSize = filesize($fileOnePath);
                if ($fileSize==0){
                    list($res,$err) = self::reDownloadZeroDataImage($dirOnePath,$fileOne);
                    if ($res){
                        $successList[] = ["file"=>$fileOnePath,"res"=>$res,"error"=>$err];
                    }
                    else{
                        $failedList[] = ["file"=>$fileOnePath,"res"=>$res,"error"=>$err];
                    }
                }
            }
        }
        return [$successList,$failedList];
    }




    /**
     * 刷新所有团长的头像信息
     * @return array[]
     */
    public static function flushAllDeliveryHeadUrl(){
        $successList = [];
        $failedList = [];
        $noHeadImageDeliveryQuery = Delivery::find()->where(['head_img_url'=>null]);
        foreach ($noHeadImageDeliveryQuery->each(20) as $noHeadDeliveryModel){
            list($res,$err) = self::reSetDeliveryImage($noHeadDeliveryModel->id,$noHeadDeliveryModel->user_id);
            if ($res){
                $successList[] = ["deliveryId"=>$noHeadDeliveryModel->id,"res"=>$res,"error"=>$err];
            }
            else{
                $failedList[] = ["deliveryId"=>$noHeadDeliveryModel->id,"res"=>$res,"error"=>$err];
            }
        }
        return [$successList,$failedList];
    }


    /**
     * 刷新用户基本头像信息任务
     * @return array[]
     */
    public static function flushAllUserInfoHeadUrl(){
        $successList = [];
        $failedList = [];
        $noHeadImageUserInfoQuery = UserInfo::find()->where([
            'or',
            ['head_img_url'=>null],
            ['head_img_url'=>""]
        ]);
        foreach ($noHeadImageUserInfoQuery->each(20) as $noHeadUserInfoModel){
            list($res,$err) = self::reDownloadUserInfoHeadImage($noHeadUserInfoModel->id);
            if ($res){
                $successList[] = ["userInfoId"=>$noHeadUserInfoModel->id,"res"=>$res,"error"=>$err];
            }
            else{
                $failedList[] = ["userInfoId"=>$noHeadUserInfoModel->id,"res"=>$res,"error"=>$err];
            }
        }
        return [$successList,$failedList];
    }

    public static function reDownloadUserInfoHeadImage($userId){
        try {
            $users = User::find()->where(['user_info_id'=>$userId])->all();
            if (empty($users)){
                return [false,"User不存在"];
            }
            $oneHeadUrl = "";
            foreach ($users as $user){
                if (StringUtils::isNotBlank($user->headimgurl)){
                    $oneHeadUrl = $user->headimgurl;
                    break;
                }
            }
            if (StringUtils::isBlank($oneHeadUrl)){
                return [false,"headImageUrl不存在"];
            }
            $downloadInfo = DownloadFileService::downloadFile($oneHeadUrl,"/uploads/pub","jpg");
            $updateCount = UserInfo::updateAll(['head_img_url'=>$downloadInfo['attachment']],['id'=>$userId]);
            if ($updateCount<1){
                return [false,'更新userInfo的头像失败'];
            }
            return [true,''];
        }
        catch (\Exception $e){
            return [false,$e->getMessage()];
        }
    }




    private static function reSetDeliveryImage($deliveryId,$userId){
        $userInfo = UserInfo::find()->where(['id'=>$userId])->one();
        if ($userInfo===false){
            return [false,"找不到userInfo,userId:{$userId},deliveryId:{$deliveryId}"];
        }
        if (StringUtils::isBlank($userInfo->head_img_url)){
            return [false,"userInfo的头像也是空的,userId:{$userId},deliveryId:{$deliveryId}"];
        }
        $updateCount = Delivery::updateAll(['head_img_url'=>$userInfo->head_img_url],['id'=>$deliveryId]);
        if ($updateCount<1){
            return [false,"delivery头像刷新失败,userId:{$userId},deliveryId:{$deliveryId}"];
        }
        return [true,''];
    }

    /**
     * 重新下载图片
     * @param $filePath
     * @param $fileName
     * @return array
     */
    public static function reDownloadZeroDataImage($filePath, $fileName){
        try {
            $userInfo = UserInfo::find()->where(['like','head_img_url',$fileName])->one();
            if ($userInfo==null){
                return [false,"UserInfo不存在"];
            }
            $users = User::find()->where(['user_info_id'=>$userInfo->id])->all();
            if (empty($users)){
                return [false,"User不存在"];
            }
            $oneHeadUrl = "";
            foreach ($users as $user){
                if (StringUtils::isNotBlank($user->headimgurl)){
                    $oneHeadUrl = $user->headimgurl;
                    break;
                }
            }
            if (StringUtils::isBlank($oneHeadUrl)){
                return [false,"headImageUrl不存在"];
            }
            self::downloadImageFromUrl($oneHeadUrl,PathUtils::join($filePath,$fileName));
            return [true,""];
        }
        catch (\Exception $e){
            return [false,$e->getMessage()];
        }
    }

    /**
     * 获取文件列表
     * @param $dir
     * @return array
     */
    private static function getFileList($dir){
        $res = [];
        $handle=opendir($dir);
        while(!!$file = readdir($handle)) {
            if (($file!=".")and($file!="..")) {
                $res[] = $file;
            }
        }
        closedir($handle);
        return $res;
    }

    /**
     * 下载图片
     * @param $url
     * @param $allPathFilename
     */
    private static function downloadImageFromUrl($url, $allPathFilename)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        $file = curl_exec($ch);
        curl_close($ch);
        self::saveAsImage( $file, $allPathFilename);
    }

    /**
     * 保存图片
     * @param $file
     * @param $allPathFilename
     */
    private static function saveAsImage($file, $allPathFilename)
    {
        $resource = fopen($allPathFilename, 'a');
        fwrite($resource, $file);
        fclose($resource);
    }
}