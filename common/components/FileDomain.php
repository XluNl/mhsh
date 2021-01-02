<?php 
namespace common\components;
use common\utils\PathUtils;
use common\utils\StringUtils;
use yii\base\Component;

class FileDomain extends Component
{
    public $fileDomain;

    public function generateUrl($url){
        if (StringUtils::isBlank($url)){
            return "";
        }
        if (StringUtils::isBlank($this->fileDomain)){
            return $url;
        }
        return  PathUtils::join($this->fileDomain,$url);
    }

    public function removeUrl($url){
        if (StringUtils::isBlank($url)){
            return "";
        }
        if (StringUtils::isBlank($this->fileDomain)){
            return $url;
        }
        $res = str_replace($this->fileDomain,"",$url);
        if ($res===false){
            return $url;
        }
        return $res;
    }
}