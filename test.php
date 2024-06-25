<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$filename = $_SERVER["DOCUMENT_ROOT"] . "/upload/stop_words.txt"; // Укажите путь к вашему файлу
$wordsArray = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

dump($wordsArray);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");