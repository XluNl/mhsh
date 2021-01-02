<?php


namespace common\utils;


use yii\data\ActiveDataProvider;

class PageModule
{

    public $pageNo = 1;

    public $pageSize = 20;

    public $items = [];

    public $total = 0;


    /**
     * PageModule constructor.
     * @param int $pageNo
     * @param int $pageSize
     * @param array $items
     * @param int $total
     */
    public function  __construct($pageNo, $pageSize, array $items, $total)
    {
        $this->pageNo = $pageNo;
        $this->pageSize = $pageSize;
        $this->items = $items;
        $this->total = $total;
    }


    /**
     * @param ActiveDataProvider $activeDataProvider
     * @return PageModule
     */
    public static function createModel(ActiveDataProvider $activeDataProvider){
        $total = $activeDataProvider->getTotalCount();
        $page = $activeDataProvider->getPagination();
        $pageNo = $page->getPage()+1;
        $pageSize = $page->getPageSize();
        $itemModels = $activeDataProvider->getModels();
        $items = self::toChildArray($itemModels);
        return new PageModule($pageNo,$pageSize,$items,$total);
    }

    /**
     * @param ActiveDataProvider $activeDataProvider
     * @return PageModule
     */
    public static function createArray(ActiveDataProvider $activeDataProvider){
        $total = $activeDataProvider->getTotalCount();
        $page = $activeDataProvider->getPagination();
        $pageNo = $page->getPage()+1;
        $pageSize = $page->getPageSize();
        $itemModels = $activeDataProvider->getModels();
        return new PageModule($pageNo,$pageSize,$itemModels,$total);
    }

    private static function toChildArray($itemModels){
        if (!is_array($itemModels)){
            $v = $itemModels;
            $r = self::objectToArray($v);
            return $r;
        }
        else{
            $res = [];
            foreach ($itemModels as $k=>$v){
                $r = self::objectToArray($v);
                $res[] = $r;
            }
            return $res;
        }
    }

    /**
     * @param $v
     * @return mixed
     */
    private static function objectToArray($v)
    {
        $r = $v->toArray();
        foreach ($v->getRelatedRecords() as $kk => $vv) {
            if (!empty($v[$kk])) {
                $rr = self::toChildArray($v[$kk]);
                $r[$kk] = $rr;
            }
        }
        return $r;
    }


}