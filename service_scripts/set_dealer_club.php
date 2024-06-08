<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
/*if ($USER->IsAdmin()) {
    $oSpreadsheet = IOFactory::load("files/gmrclub.xlsx");
    $oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();
    $cells = $oSpreadsheet->getSheetByName('Лист1')->getCellCollection();
    //получаем кол-во строк
    $max = $cells->getHighestRow();
    //Начинаем со второй строки, если первая - это заголовки
    $user = new CUser;
    for ($row = 2; $row <= $max; $row++) {
        $filter = Array
        (
            "UF_DEALER_CLUB" => true,
            "UF_ZOOM_LOGIN" => $cells->get('H' . $row)->getValue(),
        );
        $user->Update($cells->get('E' . $row)->getValue(), $filter);
        dump($filter);
        //dump($cells->get('H' . $row)->getValue());
        //делаем что - то с данными
    }
}

$filter = Array
(
    "!UF_DEALER_CLUB" => false,
);
$fields = Array
(
    "UF_DEALER_CLUB" => false,
);
$user2 = new CUser;
$rsUsers = CUser::GetList(($by="ID"), ($order="DESC"), $filter, ['FIELDS' => ['ID', 'NAME', 'EMAIL'], 'SELECT' => ['UF_DEALER_CLUB', 'UF_ZOOM_LOGIN']]); // выбираем пользователей
while ($user = $rsUsers->Fetch()){
    dump($user);
    //$user2->Update($user['ID'], $fields);

}*/

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");