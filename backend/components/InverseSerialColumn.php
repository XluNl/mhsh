<?php


namespace backend\components;


use yii\grid\Column;

class InverseSerialColumn extends Column
{
    /**
     * @inheritdoc
     */
    public $header = '#';


    /**
     * @inheritdoc
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        $pagination = $this->grid->dataProvider->getPagination();
        if ($pagination !== false) {
            return $pagination->totalCount - $key;
        } else {
            return $key + 1;
        }
    }
}