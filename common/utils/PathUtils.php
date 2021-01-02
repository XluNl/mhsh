<?php


namespace common\utils;


use yii\helpers\FileHelper;

class PathUtils
{
    public static function join($path1,$path2){
        $last = substr($path1,-1);
        $first = substr( $path2, 0, 1 );
        if ($first=='/'&&$last=='/'){
            return $path1 . substr( $path2, 1, strlen($path2)-1);
        }
        else if ($first!='/'&&$last!='/'){
            return $path1.'/'.$path2;
        }
        return  $path1.$path2;
    }

    public static function joins($path1,...$paths){
        $finalPaths = $path1;
        foreach ($paths as $path){
            $finalPaths = self::join($finalPaths,$path);
        }
        return $finalPaths;
    }

    public static function createDir($path1,...$paths){
        $dir = self::join($path1,...$paths);
        FileHelper::createDirectory($dir,777,true);
    }
}