<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$min_points = \Models\Course::getMaxPoints(100);
dump($min_points);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");