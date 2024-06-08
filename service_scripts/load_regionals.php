<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;
use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
$regionals = [
    'Ganke' => 3277,
    'Karpenko' => 4229,
    'Kurashev' => 4615,
    'Kuzmin' => 3275,
    'Novikov' => 3273,
    'Ubaydulaev' => 3024,
    'Yazov' => 3272,
    'Zemlyanko' => 3274,
];
if($USER->IsAdmin()){
    $oSpreadsheet = IOFactory::load("files/regionals.xlsx");
    $oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();
    $cells = $oSpreadsheet->getSheetByName('2023')->getCellCollection();
    $max = $cells->getHighestRow();
    $array = [];
    for ($row = 4; $row <= $max; $row++){
        dump($cells->get('E' . $row)->getValue());
        dump($cells->get('K' . $row)->getValue());
        $dealer = Models\Dealer::getIdByCode($cells->get('K' . $row)->getValue());
        if(empty($cells->get('K' . $row)->getValue())||$dealer==0)
            $dealer = Models\Dealer::createFromNameAndCode($cells->get('F' . $row)->getValue(), $cells->get('K' . $row)->getValue());
        Models\Dealer::setRegional($dealer, $regionals[$cells->get('E' . $row)->getValue()]);
        /*try {
            dump($cells->get('E' . $row)->getValue());
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            dump($e->getMessage());
        }*/
        //dump($cells->get('K' . $row)->getValue());
    }
}else{
    PageHelper::set404(Loc::getMessage('FORBIDDEN'));
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");