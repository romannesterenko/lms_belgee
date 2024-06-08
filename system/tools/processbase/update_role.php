<?
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

CModule::IncludeModule('iblock');
// Загружаем список диллеров

$roles = array();

$arSelect = array("ID", "NAME", "CODE");
$arFilter = array("IBLOCK_ID" => 5);
$res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
while ($ob = $res->GetNextElement()) {
    $arFields = $ob->GetFields();
    $roles[$arFields["NAME"]] = $arFields;
}

//print_r($dealers); die();


$filter = array
(
    "UF_ROLE" => false,
);
$arParams["SELECT"] = array("UF_ROLE", "ID");
$rsUsers = CUser::GetList(($by = "ID"), ($order = "desc"), $filter, $arParams); // выбираем пользователей
$is_filtered = $rsUsers->is_filtered; // отфильтрована ли выборка ?
$rsUsers->NavStart(5000); // разбиваем постранично по 50 записей
while ($res_u = $rsUsers->NavNext(true)) :

    $useritem = new CUser;
    $fields = array(
        "UF_ROLE" => array($roles[$res_u["WORK_POSITION"]]["ID"]),
    );
    echo $useritem->Update($res_u["ID"], $fields);
    echo $res_u["ID"].": ".print_r($fields)."<br>";


endwhile;


require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>