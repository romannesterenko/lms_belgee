<?php

use Bitrix\Main\Application;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$arIMAGE = $_FILES['file'];
$arIMAGE['MODULE_ID'] = 'main';
$fid = (int)$request['file_id'];
$oSpreadsheet = IOFactory::load($_SERVER["DOCUMENT_ROOT"] . CFile::GetPath($fid));
$oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();
$cells = $oSpreadsheet->getSheet(0)->getCellCollection();
$max = $cells->getHighestRow();

$key = 1;
$errors = [];
$regionals = [];
foreach (\Models\Dealer::getAll() as $one_dealer){
    if($one_dealer['PROPERTY_REGIONAL_VALUE'])
        $regionals[] = $one_dealer['PROPERTY_REGIONAL_VALUE'];
    if($one_dealer['PROPERTY_REGIONAL_PPO_VALUE'])
        $regionals[] = $one_dealer['PROPERTY_REGIONAL_PPO_VALUE'];
    if($one_dealer['PROPERTY_REGIONAL_MARKETING_VALUE'])
        $regionals[] = $one_dealer['PROPERTY_REGIONAL_MARKETING_VALUE'];
}
for ($row = 2; $row <= $max; $row++) {
    //$current_user = [];
    $new_user = [];
    $dealer = 0;
    $dealer_info = [];
    if ($cells->get('C' . $row)) {
        $dealer = \Models\Dealer::getIdByCode($cells->get('C' . $row)->getValue());
        if ($dealer>0)
            $dealer_info = \Models\Dealer::find($dealer, ['ID', 'CODE', 'NAME', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL_MARKETING']);
        else
            continue;
    }

    /*if ($cells->get('F' . $row)) {
        $fio = explode(' ', trim($cells->get('F' . $row)));
        $dealer = \Models\Dealer::getIdByCode($cells->get('C' . $row)->getValue());
        $user = \Models\User::getRegionalByFullName($fio[0], $fio[1], $dealer);
        if ($user['ID'] > 0) {
            $current_user = $user;
        }
    }*/

    if ($cells->get('G' . $row)) {
        $fio = explode(' ', trim($cells->get('G' . $row)));
        $dealer = \Models\Dealer::getIdByCode($cells->get('C' . $row)->getValue());
        $users = \Models\User::getArrayByFullName($fio[0], $fio[1]);
        if(check_full_array($users)) {
            if (count($users) == 1) {
                $user = $users[0];
                if ($user['ID'] > 0) {
                    $new_user = $user;
                }
            } else {
                foreach ($users as $s) {
                    if(in_array($s['ID'], $regionals)){
                        $new_user = $s;
                    }
                }
            }
        }
    }

    /*if($current_user['ID']==$new_user['ID'])
        continue;*/

    if($dealer>0&&check_full_array($new_user)){
        if ($cells->get('H' . $row) && $cells->get('H' . $row)->getValue()) {
            $dealer_info = \Models\Dealer::find($dealer, ['ID', 'CODE', 'NAME', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL_MARKETING']);
            if(trim($cells->get('H' . $row)->getValue())=="ОП"){
                $title = "региональный менеджер ОП";
                \Models\Dealer::setRegional($dealer, $new_user['ID']);
                echo "<p>В дилере <b>".$dealer_info['NAME']." (".$dealer_info['CODE'].")</b> был обновлен ".$title." на <b>".$new_user['LAST_NAME']." ".$new_user['NAME']."</b></p>";

            } elseif (trim($cells->get('H' . $row)->getValue())=="ППО") {
                $title = "региональный менеджер ППО";
                \Models\Dealer::setPPORegional($dealer, $new_user['ID']);
                echo "<p>В дилере <b>".$dealer_info['NAME']." (".$dealer_info['CODE'].")</b> был обновлен ".$title." на <b>".$new_user['LAST_NAME']." ".$new_user['NAME']."</b></p>";

            } elseif (trim($cells->get('H' . $row)->getValue())=="Marketing") {
                $title = "региональный менеджер по маркетингу";
                \Models\Dealer::setMarketingRegional($dealer, $new_user['ID']);
                echo "<p>В дилере <b>".$dealer_info['NAME']." (".$dealer_info['CODE'].")</b> был обновлен ".$title." на <b>".$new_user['LAST_NAME']." ".$new_user['NAME']."</b></p>";
            } else {
                echo "<p>В дилере <b>".$dealer_info['NAME']." (".$dealer_info['CODE'].")</b> не был обновлен региональный менеджер из-за неправильного указания направления</p>";
            }
        }
    }
    $key++;
}


