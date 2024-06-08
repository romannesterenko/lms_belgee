<?php
$_SERVER["DOCUMENT_ROOT"] = "/home/u520251/lms.geely-motors.com/www";
$_SERVER['HTTP_HOST'] = 'lms.geely-motors.com';
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$directory = $_SERVER["DOCUMENT_ROOT"] . "/ftp1c/out"; // Укажите путь к нужной папке
$log = $_SERVER["DOCUMENT_ROOT"].'/upload/logs/invoice/'.date('dmY').'.log';
$string = 'Дата записи: '.date('d.m.Y H:i:s').PHP_EOL;

if (is_dir($directory)) { 
    $string.= 'Директория найдена'.PHP_EOL;
    $files = scandir($directory);
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            $filePath = $directory . DIRECTORY_SEPARATOR . $file;
            if (pathinfo($filePath, PATHINFO_EXTENSION) === 'json') {
                $jsonContent = file_get_contents($filePath);
                $dataArray = json_decode($jsonContent, true);
                // Если удалось успешно декодировать JSON
                if ($dataArray !== null) {
                    \Helpers\Log::writeCommon($dataArray);
                    $result = \Models\Invoice::addFromFileData($dataArray);
                    if ($result) {
                        $string.= "Добавление информации из файла $file.<br>".PHP_EOL;
                        unlink($filePath);
                    } else {
                        $string.= "Ошибка добавления информации из файла $file.<br>".PHP_EOL;
                    }
                } else {
                    echo "Ошибка декодирования файла $file.<br>";
                }
            }
        }
    }
}

