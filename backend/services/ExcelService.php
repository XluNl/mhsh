<?php


namespace backend\services;


use common\utils\ArrayUtils;
use common\utils\DateTimeUtils;
use common\utils\StringUtils;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExcelService
{
    //横向34行  纵向47行
    const EVERY_A4_PAGE_ROWS = 34;
    /**
     * @param $sheet Worksheet
     * @param $lRange
     * @param $rRange
     * @throws
     */
    public static function mergeCellAndCenter(&$sheet,$lRange,$rRange){
        $styleArray = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->mergeCells("{$lRange}:{$rRange}");
        $existStyleArray = $sheet->getStyles();
        $sheet->getStyle($lRange)->applyFromArray(array_merge($existStyleArray,$styleArray));
    }

    /**
     * @param $sheet Worksheet
     * @param $lRange
     * @param $rRange
     * @param $rowNo
     * @throws
     */
    public static function mergeCellOneRow(&$sheet,$lRange,$rRange,$rowNo){
        $styleArray = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->mergeCells("{$lRange}{$rowNo}:{$rRange}{$rowNo}");
        $existStyleArray = $sheet->getStyles();
        $sheet->getStyle("{$lRange}{$rowNo}:{$rRange}{$rowNo}")->applyFromArray(array_merge($existStyleArray,$styleArray));
    }

    /**
     * @param $sheet Worksheet
     * @param $lRange
     * @param $rRange
     * @param $rowNo
     * @throws
     */
    public static function boldCellOneRow(&$sheet,$lRange,$rRange,$rowNo){
        $sheet->getStyle("{$lRange}{$rowNo}:{$rRange}{$rowNo}")->getFont()->setBold(true);;
    }

    public static function mergeCell(&$sheet,$lRange,$rRange){
        $sheet->mergeCells("{$lRange}:{$rRange}");
    }

    /**
     * @param $sheet Worksheet
     * @param $pCoordinate
     * @throws
     */
    public static function cellHorizontalCenter(&$sheet, $pCoordinate){
        $styleArray = [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $existStyleArray = $sheet->getStyles();
        $sheet->getStyle($pCoordinate)->applyFromArray(array_merge($existStyleArray,$styleArray));
    }

    /**
     * @param $sheet Worksheet
     * @param $pCoordinate
     * @throws
     */
    public static function cellVerticalCenter(&$sheet, $pCoordinate){
        $styleArray = [
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $existStyleArray = $sheet->getStyles();
        $sheet->getStyle($pCoordinate)->applyFromArray(array_merge($existStyleArray,$styleArray));
    }


    public static function borderCenter(&$sheet,$lCoordinate,$rCoordinate){
        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '000'],
                ],
            ],
        ];
        $existStyleArray = $sheet->getStyles();
        $sheet->getStyle("{$lCoordinate}:{$rCoordinate}")->applyFromArray(array_merge($existStyleArray,$styleArray));
    }

    public static function borderCenterOneRow(&$sheet,$lRange,$rRange,$rowNo){
        $styleArray = [
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '000'],
                ],
            ],
        ];
        $existStyleArray = $sheet->getStyles();
        $sheet->getStyle("{$lRange}{$rowNo}:{$rRange}{$rowNo}")->applyFromArray(array_merge($existStyleArray,$styleArray));
    }


    /**
     *
     * @param $sheet Worksheet
     * @param $pCoordinate
     * @param $rowNo
     * @param $pValue
     */
    public static function setCellValueAndCenter(&$sheet,$pCoordinate,$rowNo, $pValue){

        self::cellHorizontalCenter($sheet,$pCoordinate.$rowNo);
        self::borderCenterOneRow($sheet,$pCoordinate,$pCoordinate,$rowNo);
        $sheet->setCellValue($pCoordinate.$rowNo,$pValue);
    }

    /**
     * @param $sheet  Worksheet
     * @param $lRange
     * @param $rRange
     * @param $rowNo
     * @param $pValue
     */
    public static function setCellValueAndMergeCellAndCenter(&$sheet,$lRange,$rRange,$rowNo,$pValue){
        self::mergeCellAndCenter($sheet,"{$lRange}{$rowNo}","{$rRange}{$rowNo}");
        self::borderCenterOneRow($sheet,$lRange,$rRange,$rowNo);
        $sheet->setCellValue("{$lRange}{$rowNo}",$pValue);
    }


    public static function setCellSign(&$sheet,$lCoordinate,$rCoordinate,$pValue){
        self::mergeCell($sheet,$lCoordinate,$rCoordinate);
        self::borderCenter($sheet,$lCoordinate,$rCoordinate);
        self::cellVerticalCenter($sheet,$lCoordinate);
        $sheet->setCellValue($lCoordinate,$pValue);
    }

    /**
     * 计算 A B C ....Z AA AB
     * @param $first
     * @param $num
     * @return mixed
     */
    public static function addColNum($first,$num){
        for ($i=0;$i<$num;$i++){
            $first++;
        }
        return $first;
    }

    /**
     * 校验是否存在值，空也是false
     * @param $data
     * @param $key
     * @return bool
     */
    public static function checkExcelValueExist($data,$key){
        if (empty($data)){
            return false;
        }
        if (!array_key_exists($key,$data)){
            return false;
        }
        if ($data[$key]==null||$data[$key]==''){
            return false;
        }
        return true;
    }

    /**
     * 校验是否存在值，不存在则设置为默认值
     * @param $data
     * @param $key
     * @param $default
     * @return mixed
     */
    public static function checkExcelValueWithDefault($data,$key,$default){
        if (self::checkExcelValueExist($data,$key)){
            return $data[$key];
        }
        else{
            return $default;
        }
    }


    /**
     * 默认值替换
     * @param $model
     * @param $data
     * @param $key
     * @param string $default
     * @param array $enumArr
     * @return mixed
     */
    public static function setValueIfSetWithDefault($model,$data,$key,$default="",$enumArr=[]){
        if (empty($data)){
            $model->$key = $default;
            return $model;
        }
        if (!array_key_exists($key,$data)){
            $model->$key = $default;
            return $model;
        }
        if (empty($data[$key])){
            $model->$key = $default;
        }
        else{
            if (count($enumArr)>0){
                $enumKey = ArrayUtils::getArrayKey($data[$key],$enumArr);
                if ($enumKey!==null){
                    $model->$key = $enumKey;
                }else{
                    $model->$key = $default;
                }
            }
            else{
                $model->$key = $data[$key];
            }
        }
        return $model;
    }

    /**
     * 默认值替换
     * @param $model
     * @param $data
     * @param $key
     * @param string $default
     * @return mixed
     */
    public static function setValueIfSetWithTimeDefault($model,$data,$key,$default=""){
        if (empty($data)){
            return $model;
        }
        if (!array_key_exists($key,$data)){
            return $model;
        }
        if (empty($data[$key])){
            $model->$key = $default;
        }
        else{
            $model->$key = null;
            $r = strtotime($data[$key]);
            if ($r!=false){
                $model->$key = $data[$key];
            }
            else{
                $timeArr = explode(',',$data[$key]);
                if (count($timeArr)==3){
                    $r = mktime(0,0,0,$timeArr[1],$timeArr[2],$timeArr[0]);
                }
                if ($r!=false){
                    $model->$key = date('Y-m-d',$r);
                }
            }
        }
        return $model;
    }

    /**
     * 组装错误信息
     * @param $errors
     * @return string
     */
    public static function assembleErrorMessage($errors)
    {
        if (empty($errors)){
            return "";
        }
        $errorStr = "";
        foreach ($errors as $error){
            $errorStr .= $error[0].';';
        }
        return $errorStr;
    }

    /**
     * @param $sheet Worksheet
     * @param $columnNo
     * @return false|string
     * @throws \Exception
     */
    public static function getExcelValue($sheet,$columnNo){
        $cell = $sheet->getCell($columnNo);
        $val = trim($cell -> getValue());
        if ($cell->getDataType()==DataType::TYPE_NUMERIC){
            $cellStyleFormat = $cell->getStyle($cell->getCoordinate())->getNumberFormat();
            $formatCode = $cellStyleFormat->getFormatCode();
            if (preg_match('/^(\[\$[A-Z]*-[0-9A-F]*\])*[hmsdy]/i', $formatCode)) {
                $val = gmdate("Y-m-d H:i:s", Date::excelToTimestamp($val));
            }
            else{
                $val= NumberFormat::toFormattedString($val,$formatCode);
            }
        }
        return $val;
    }


    /**
     * 根据中文名查找变量名
     * @param $constants
     * @param $title
     * @return int|string|null
     */
    public static function getTitleKeyByTitle($constants,$title){
        foreach ($constants as $k=> $v){
            if ($v['title']==$title){
                return $k;
            }
        }
        return null;
    }

    /**
     * 格式化日期cell值
     * @param $data
     * @param $key
     * @param null $default
     * @return false|string|null
     */
    public static function formatCellYearAndMonthAndDayIfExist($data,$key,$default=null){
        $date = ArrayUtils::getArrayValue($key,$data,null);
        if (StringUtils::isBlank($date)){
            return $default;
        }
        return DateTimeUtils::formatYearAndMonthAndDay($date);
    }

    /**
     * 处理行数
     * @param $nextRowNo
     */
    public static function processRowNo(&$nextRowNo){
        if ($nextRowNo%self::EVERY_A4_PAGE_ROWS==0){
            $nextRowNo = $nextRowNo+1;
        }
        else if ($nextRowNo%self::EVERY_A4_PAGE_ROWS==1){
            $nextRowNo = $nextRowNo;
        }
        else {
            $nextRowNo = (((int)($nextRowNo/self::EVERY_A4_PAGE_ROWS))+1)*self::EVERY_A4_PAGE_ROWS+1;
        }

    }

}