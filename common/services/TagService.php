<?php


namespace common\services;


use common\models\Tag;
use common\utils\StringUtils;
use yii\db\Query;

class TagService
{

    protected static function getCommonTagValue($companyId,$groupId,$tagId,$bizId){
        $tag = Tag::find()->where(['company_id'=>$companyId,'group_id'=>$groupId,'tag_id'=>$tagId,'biz_id'=>$bizId])->orderBy('id desc')->one();
        return StringUtils::isEmpty($tag)?Tag::$tagDefaultArr[$tagId]:$tag['biz_ext'];
    }

    protected static function getCommonTag($companyId,$groupId,$tagId,$bizId){
        $tag = Tag::find()->where(['company_id'=>$companyId,'group_id'=>$groupId,'tag_id'=>$tagId,'biz_id'=>$bizId])->orderBy('id desc')->one();
        return $tag;
    }

    protected static function getCommonWithTimeValue($companyId,$groupId,$tagId,$bizId,$time){
        $tag = Tag::find()->where([
            'and',
            ['company_id'=>$companyId,'group_id'=>$groupId,'tag_id'=>$tagId,'biz_id'=>$bizId],
            ['<=','start_time',$time],
            ['>=','end_time',$time]
        ])->orderBy('id desc')->one();
        return  StringUtils::isEmpty($tag)?Tag::$tagDefaultArr[$tagId]:$tag['biz_ext'];
    }

    protected static function getCommonWithTime($companyId,$groupId,$tagId,$bizId,$time){
        $tag = Tag::find()->where([
            'and',
            ['company_id'=>$companyId,'group_id'=>$groupId,'tag_id'=>$tagId,'biz_id'=>$bizId],
            ['<=','start_time',$time],
            ['>=','end_time',$time]
        ])->orderBy('id desc')->one();
        return $tag;
    }

    protected static function getCommonGroup($companyId,$groupId,$bizId,$time){
        $tags = (new Query())->from(Tag::tableName())->where([
            'and',
            ['company_id'=>$companyId,'group_id'=>$groupId,'biz_id'=>$bizId],
            ['<=','start_time',$time],
            ['>=','end_time',$time]
        ])->select(['company_id','group_id','tag_id','start_time','end_time','biz_id','biz_ext'])
            ->orderBy('id desc')->all();
        return $tags;
    }


    protected static function setAddOrUpdate($id,$companyId,$groupId,$tagId,$bizId,$bizName,$bizExt){
        if (StringUtils::isNotBlank($id)){
            self::setCommonValue($id,$companyId,$groupId,$tagId,$bizId,$bizExt);
        }
        else{
            $tag = new Tag();
            $tag->start_time = Tag::START_TIME;
            $tag->end_time = Tag::END_TIME;
            $tag->company_id = $companyId;
            $tag->group_id = $groupId;
            $tag->tag_id = $tagId;
            $tag->biz_id = $bizId;
            $tag->biz_name = $bizName;
            $tag->biz_ext = (string)$bizExt;
            if (!$tag->save()){
                return [false,'打标失败'];
            }
        }
        return [true,''];
    }

    protected static function setCommonValue($id,$companyId,$groupId,$tagId,$bizId,$bizExt){
        Tag::updateAll(['biz_ext'=>$bizExt],[
            'id'=>$id,
            'company_id'=>$companyId,'group_id'=>$groupId,'tag_id'=>$tagId,'biz_id'=>$bizId
        ]);
    }

    protected static function setCommonValueAndTime($id,$companyId,$groupId,$tagId,$bizId,$bizExt,$startTime,$endTime){
        Tag::updateAll(['biz_ext'=>$bizExt,'start_time'=>$startTime,$endTime],[
            'id'=>$id,
            'company_id'=>$companyId,'group_id'=>$groupId,'tag_id'=>$tagId,'biz_id'=>$bizId
        ]);
    }
}