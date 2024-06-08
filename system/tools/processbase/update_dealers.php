<?
//const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

CModule::IncludeModule('iblock');
// Загружаем список диллеров

$dealers = array();

$arSelect = array("ID", "NAME", "CODE");
$arFilter = array("IBLOCK_ID" => 10);
$res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
while ($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();
    $dealers[$arFields["CODE"]] = $arFields;
}

//print_r($dealers); die();


$filter = array
(
//    "UF_DEALER" => false,
);
$arParams["SELECT"] = array("UF_DEALER", "ID");
$rsUsers = CUser::GetList(($by = "ID"), ($order = "desc"), $filter, $arParams); // выбираем пользователей
$is_filtered = $rsUsers->is_filtered; // отфильтрована ли выборка ?
$rsUsers->NavStart(9000); // разбиваем постранично по 50 записей
while ($res_u = $rsUsers->NavNext(true)) :

    $useritem = new CUser;
    $fields = array(
        "UF_DEALER" => $dealers[$res_u["WORK_COMPANY"]]["ID"],
    );
    echo $useritem->Update($res_u["ID"], $fields);



endwhile;


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>