<?php
/** @var array $arResult */

$rm = new \Materials\Materials;
$arResult['arUser']["DEALER"] = $rm->getDealerInfo($arResult['arUser']["UF_DEALER"])[0];
