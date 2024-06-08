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
    $oSpreadsheet = IOFactory::load("files/certs_22_06_2023.xlsx");
    $oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();
    $cells = $oSpreadsheet->getSheetByName('печать 22 06')->getCellCollection();
    $max = $cells->getHighestRow();
    for ($row = 2; $row <= $max; $row++){
        if($cells->get('A' . $row)){
            $code = $cells->get('A' . $row)->getValue();
            $props['COURSE'] = 101;
            $array = [
                'NAME' => 'Экономика СТОА  ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 49,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            if(!empty($array['CODE'])){\Models\Certificate::create($array);}
            unset($array);
        }
        if($cells->get('B' . $row)){
            $code = $cells->get('B' . $row)->getValue();
            $props['COURSE'] = 100;
            $array = [
                'NAME' => 'Управление складскими запасами запчастей ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 50,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            if(!empty($array['CODE'])){\Models\Certificate::create($array);}
            unset($array);
        }
        if($cells->get('C' . $row)){
            $code = $cells->get('C' . $row)->getValue();
            $props['COURSE'] = 102;
            $array = [
                'NAME' => 'Клиентоориентированный сервис ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 48,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            if(!empty($array['CODE'])){\Models\Certificate::create($array);}
            unset($array);
        }
        if($cells->get('D' . $row)){
            $code = $cells->get('D' . $row)->getValue();
            $props['COURSE'] = 103;
            $array = [
                'NAME' => 'Основы работы с запчастями ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 51,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            if(!empty($array['CODE'])){\Models\Certificate::create($array);}
            unset($array);
        }
        if($cells->get('E' . $row)){
            $code = $cells->get('E' . $row)->getValue();
            $props['COURSE'] = 97;
            $array = [
                'NAME' => 'Внутренние процессы сервиса ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 52,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            if(!empty($array['CODE'])){\Models\Certificate::create($array);}
            unset($array);
        }
        if($cells->get('F' . $row)){
            $code = $cells->get('F' . $row)->getValue();
            $props['COURSE'] = 86;
            $array = [
                'NAME' => 'GMTC LEVEL-1 ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 38,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            if(!empty($array['CODE'])){\Models\Certificate::create($array);}
            unset($array);
        }
        if($cells->get('G' . $row)){
            $code = $cells->get('G' . $row)->getValue();
            $props['COURSE'] = 90;
            $array = [
                'NAME' => 'Новая модель: Geely Atlas Pro (NL-3B) ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 39,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            if(!empty($array['CODE'])){\Models\Certificate::create($array);}
            unset($array);
        }
        if($cells->get('H' . $row)){
            $code = $cells->get('H' . $row)->getValue();
            $props['COURSE'] = 88;
            $array = [
                'NAME' => 'Новая модель: Geely Coolray (SX-11) ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 40,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            if(!empty($array['CODE'])){\Models\Certificate::create($array);}
            unset($array);
        }
        if($cells->get('I' . $row)){
            $code = $cells->get('I' . $row)->getValue();
            $props['COURSE'] = 89;
            $array = [
                'NAME' => 'Новая модель: Geely Tugella (FY-11) ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 41,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            if(!empty($array['CODE'])){\Models\Certificate::create($array);}
            unset($array);
        }
        if($cells->get('J' . $row)){
            $code = $cells->get('J' . $row)->getValue();
            $props['COURSE'] = 9725;
            $array = [
                'NAME' => 'Новая модель: Geely Monjaro (KX-11) ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 57,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            if(!empty($array['CODE'])){\Models\Certificate::create($array);}
            unset($array);
        }
        if($cells->get('K' . $row)){
            $code = $cells->get('K' . $row)->getValue();
            $props['COURSE'] = 9727;
            $array = [
                'NAME' => 'Базовый тренинг Geely для технических специалистов ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 60,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            if(!empty($array['CODE'])){\Models\Certificate::create($array);}
            unset($array);
        }
        if($cells->get('L' . $row)){
            $code = $cells->get('L' . $row)->getValue();
            $props['COURSE'] = 91;
            $array = [
                'NAME' => 'Основы диагностики электрических систем GEELY (' . $code . ')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 44,
                'CODE' => $code,
                "PROPERTY_VALUES" => $props,
            ];
            if(!empty($array['CODE'])){\Models\Certificate::create($array);}
            unset($array);
        }
        if($cells->get('M' . $row)){
            $code = $cells->get('M' . $row)->getValue();
            $props['COURSE'] = 93;
            $array = [
                'NAME' => 'Двигатель 3G15TD / JLH-4G20TD ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 42,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            if(!empty($array['CODE'])){\Models\Certificate::create($array);}
            unset($array);
        }
        if($cells->get('N' . $row)){
            $code = $cells->get('N' . $row)->getValue();
            $props['COURSE'] = 92;
            $array = [
                'NAME' => 'Трансмиссия 7DCT  /  AWF8F45 ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 43,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            if(!empty($array['CODE'])){\Models\Certificate::create($array);}
            unset($array);
        }
        if($cells->get('O' . $row)){
            $code = $cells->get('O' . $row)->getValue();
            $props['COURSE'] = 64836;
            $array = [
                'NAME' => 'Практикум: закон о защите прав потребителей-клиентов Geely ('.$code.')',
                'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                'IBLOCK_SECTION_ID' => 93,
                'CODE' => $code,
                "PROPERTY_VALUES"=> $props,
            ];
            if(!empty($array['CODE'])){\Models\Certificate::create($array);}
            unset($array);
        }
    }
}else{
    PageHelper::set404(Loc::getMessage('FORBIDDEN'));
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");