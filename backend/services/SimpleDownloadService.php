<?php


namespace backend\services;


use common\utils\DateTimeUtils;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class SimpleDownloadService
{
    public static function simpleDownload($mainTitle, $headers, $rows){
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $writer = new Xls($spreadsheet);
        $nowRow = 1;
        DownloadService::outputContent($sheet,$nowRow,$headers,$rows);
        $fileName ="{$mainTitle}-".DateTimeUtils::parseStandardWLongDate(time());
        DownloadService::outputToBrowser($fileName,$writer,$spreadsheet);
    }

    public static function multipleDownload($fileName,$multipleSheetsData){
        if (empty($multipleSheetsData)){
            return;
        }
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);
        foreach ($multipleSheetsData as $v){
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($v['mainTitle']);
            $nowRow = 1;
            DownloadService::outputContent($sheet,$nowRow,$v['headers'],$v['rows']);
        }
        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xls($spreadsheet);
        $fileName ="{$fileName}-".DateTimeUtils::parseStandardWLongDate(time());
        DownloadService::outputToBrowser($fileName,$writer,$spreadsheet);
    }
}