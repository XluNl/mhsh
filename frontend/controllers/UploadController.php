<?php
namespace frontend\controllers;
use common\components\Upload;
use frontend\utils\exceptions\BusinessException;
use frontend\utils\RestfulResponse;
use yii\web\Controller;


class UploadController extends Controller {
    public $enableCsrfValidation = false;
	public function actions() {
		return [
			'error' => [
				'class' => 'yii\web\ErrorAction',
			],
		];
	}

    public function actionUpload()
    {
        try {
            $model = new Upload();
            $info = $model->upImage('/uploads/pub');
            if ($info && is_array($info)){
                if ($info['code']==0){
                    $result = [
                        'url'=>$info['url'],
                        'attachment'=>$info['attachment'],
                    ];
                    return RestfulResponse::success($result);
                }
                return RestfulResponse::error(BusinessException::create($info['msg']));
            }
            return RestfulResponse::error(BusinessException::create("error"));
        } catch (\Exception $e) {
            return RestfulResponse::error($e);
        }
    }

}
