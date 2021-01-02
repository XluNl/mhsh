<?php

namespace backend\controllers;

use backend\utils\BootstrapFileInputConfigUtil;
use common\components\BootstrapFileUpload;
use common\components\ToolExtend;
use common\components\Upload;
use common\models\WechatImage;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\web\UploadedFile;

class UploadController extends BaseController
{
    public $enableCsrfValidation = false;//禁用Csrf验证


    /**
     * 上传图片到临时目录
     * @return string
     * @throws \yii\base\Exception
     */
    public function actionImage()
    {
        if (Yii::$app->request->isPost) {
            $company_id = \Yii::$app->user->identity->company_id;
            $res = [];
            $initialPreview = [];
            $initialPreviewConfig = [];
            $params = Yii::$app->request->post();
            $module = $params['module'];
            
            $images = UploadedFile::getInstancesByName("ImgSelect");
            if (count($images) > 0) {
                foreach ($images as $key => $image) {
                    if ($image->size > 2048 * 1024) {
                        $res = ['error' => '图片最大不可超过2M'];
                        return json_encode($res);
                    }
                    if (!in_array(strtolower($image->extension), array('gif', 'jpg', 'jpeg', 'png'))) {
                        $res = ['error' => '请上传标准图片文件, 支持gif,jpg,png和jpeg.'];
                        return json_encode($res);
                    }
                    $baseDir = Yii::getAlias('@imgPath') .'/'.$company_id;
                    if (!file_exists($baseDir)) {
                        FileHelper::createDirectory($baseDir, 0777);
                    }
                    $baseDir = $baseDir.'/wimg/';
                    if (!file_exists($baseDir)) {
                        FileHelper::createDirectory($baseDir, 0777);
                    }
                    //生成唯一uuid用来保存到服务器上图片名称
                    $picKey = ToolExtend::genuuid();
                    $filename = $picKey . '.' . $image->getExtension();

                    $filePath = realpath($baseDir) . '/';
                    $file = $filePath . $filename;

                    if ($image->saveAs($file)) {

                        $wechatImage = new WechatImage();
                        $wechatImage->url = $filename;
                        $wechatImage->company_id = $company_id;
                        $wechatImage->module = $module;
                        $wechatImage->updated_at = time();
                        $wechatImage->created_at = time();
                        if ($wechatImage->save()){
                            $baseUrl = Yii::getAlias('@img') .'/'.$company_id.'/wimg/';
                            $imgpath = $baseUrl . $filename;
                            /*Image::thumbnail($file, 100, 100)
                                ->save($file . '_100x100.jpg', ['quality' => 80]);
                            */
                            $initialPreview[$picKey] = "<img src='" . $imgpath . "' class='file-preview-image' alt='" . $filename . "' title='" . $filename . "'>";

                            // array_push($initialPreview, "<img src='" . $imgpath . "' class='file-preview-image' alt='" . $filename . "' title='" . $filename . "'>");
                            $config = [
                                'caption' => $filename,
                                'width' => '120px',
                                'url' => '/upload/delete-pic', // server delete action
                                'key' => $picKey,
                                'extra' => ['filename' => $filename]
                            ];
                            //array_push($initialPreviewConfig, $config);
                            $initialPreviewConfig[$picKey] = $config;
                            $res = [
                                "initialPreview" => $initialPreview,
                                "initialPreviewConfig" => $initialPreviewConfig,
                                "imgfile" => "<input name='ImageFilesPath[]' id='" . $picKey . "' type='hidden' value='" . $imgpath . "'/>"
                            ];
                        }
                        else{
                            $res = ['error' => '图片保存错误'];
                        }

                    }
                }
            }

            return json_encode($res);
        }
    }

    /**
     * 删除上传到临时目录的图片
     * @return string
     */
    public function actionDeletePic()
    {

        return json_encode(['error' => '']);
        list($r,$dir)=$this->getImagePath();
        if (!$r){
            return json_encode(['error' => $dir]);
        }
        $error = '';
        $params = Yii::$app->request->post();
        $filename = $params['filename'];
        $filePath = $dir . $filename;

        if (file_exists($filePath)) {
            unlink($filePath);
        }
        if (file_exists($dir. $filename . '_100x100.jpg')) {
            unlink($dir. $filename . '_100x100.jpg');
        }
        return json_encode($error);
    }

    private function getImagePath(){
        if (!Yii::$app->request->isPost){
            return [false,"只能POST请求"];
        }
        $imageType = Yii::$app->request->get("imageType");
        if (empty($imageType)||!in_array($imageType,['userInfoImage','peopleAvatarImage'])){
            return [false,"目标上传异常"];
        }
        $dir = $this->$imageType();
        return [true,$dir];
    }

    private function userInfoImage(){
        return '/uploads/user/';
    }

    private function peopleAvatarImage(){
        return '/uploads/peopleAvatar/';
    }

    private function getRealPath($path){
        return Yii::getAlias( '@backend') . '/web'.$path;
    }


    public function actionUpload()
    {
        try {
            $model = new Upload();
            $info = $model->upImage('/uploads/bac');
            if ($info && is_array($info)){
                return Json::htmlEncode($info);
            }
            return Json::htmlEncode([
                'code' => 1,
                'msg' => 'error'
            ]);
        } catch (\Exception $e) {
            return Json::htmlEncode([
                'code' => 1,
                'msg' => $e->getMessage()
            ]);
        }
    }



    public function actionFileUpload()
    {
        $request = Yii::$app->request;
        $model = new BootstrapFileUpload();
        if ($request->isAjax && $request->isPost) {
            $model->load(Yii::$app->request->post());
            list($success,$errorMsg,$successResult,$failedResult) = $model->uploadFile("/uploads/files");
            $config = BootstrapFileInputConfigUtil::createResultConfig($successResult,$failedResult,$errorMsg);
            return Json::encode($config);
        }
        return Json::encode([]);
    }

    public function actionFileRemove()
    {
        $request = Yii::$app->request;
        $model = new BootstrapFileUpload();
        if ($request->isAjax && $request->isPost) {
            $model->load(Yii::$app->request->post());
            list($success,$errorMsg,$successResult,$failedResult) = $model->uploadFile("/uploads/video");
            if (!$success){
                $config = BootstrapFileInputConfigUtil::createResultConfig($successResult,$failedResult,$errorMsg);
                return Json::encode($config);
            }
            $config = BootstrapFileInputConfigUtil::createResultConfig($successResult,$failedResult,$errorMsg);
            return Json::encode($config);
        }
        return Json::encode([]);
    }

}