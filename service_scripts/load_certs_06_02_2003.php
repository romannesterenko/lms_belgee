<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$go = false;
if($USER->IsAdmin()){
    $oSpreadsheet = IOFactory::load("2023_certs.xlsx");
    $oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();
    $cells = $oSpreadsheet->getSheetByName('2023')->getCellCollection();
    $max = $cells->getHighestRow();
    for ($row = 2; $row <= $max; $row++){
        if($cells->get('A' . $row)){
            $code = $cells->get('A' . $row)->getValue();
            $props['COURSE'] = 9725;
            $array = [
                'NAME' => 'Новая модель: Geely Monjaro (KX-11)  ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 57,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            \Models\Certificate::create($array);
            unset($array);
        }
        if($cells->get('B' . $row)){
            $code = $cells->get('B' . $row)->getValue();
            $props['COURSE'] = 9726;
            $array = [
                'NAME' => 'Шесть шагов диагностики. Электрические неисправности. ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 58,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            \Models\Certificate::create($array);
            unset($array);
        }
        if($cells->get('C' . $row)){
            $code = $cells->get('C' . $row)->getValue();
            $props['COURSE'] = 9727;
            $array = [
                'NAME' => 'Базовый тренинг Geely для технических специалистов ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 60,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            \Models\Certificate::create($array);
            unset($array);
        }
        if($cells->get('D' . $row)){
            $code = $cells->get('D' . $row)->getValue();
            $props['COURSE'] = 9728;
            $array = [
                'NAME' => 'Юридические аспекты взаимоотношений с клиентами ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 62,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            \Models\Certificate::create($array);
            unset($array);
        }
        if($cells->get('E' . $row)){
            $code = $cells->get('E' . $row)->getValue();
            $props['COURSE'] = 9735;
            $array = [
                'NAME' => 'Интерактивная приемка ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 61,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            \Models\Certificate::create($array);
            unset($array);
        }
    }
}else{
    PageHelper::set404(Loc::getMessage('FORBIDDEN'));
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");