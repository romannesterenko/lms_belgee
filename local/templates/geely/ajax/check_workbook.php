<?php

use Bitrix\Main\Application;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$arIMAGE = $_FILES['file'];

use Smalot\PdfParser\Parser;
// Функция для декодирования информации из PDF

// Функция для извлечения текста из PDF
function extractTextFromPdf($filePath) {
    $parser = new Parser();
    $pdf = $parser->parseFile($filePath);
    $text = $pdf->getText();
    return $text;
}
// Функция для поиска строк, начинающихся с '====='
function findHiddenText($text) {
    $return_array = [];
    preg_match_all('/=====.*?(?=\s|$)/', $text, $matches);
    return $matches[0];
}

function decrypt($data)
{
    $key = "GeelyMotorsRussiaHiddenKey";
    // Декодирование из base64
    list($encryptedData, $iv) = explode('::', base64_decode($data), 2);

    // Дешифрование данных
    return openssl_decrypt($encryptedData, 'aes-256-cbc', $key, 0, $iv);
}
if ($arIMAGE['size'] > 0) {
    if($arIMAGE['type']=='application/pdf'){
        $fid = CFile::SaveFile($arIMAGE, "work_books");
        $extractedText = extractTextFromPdf($_SERVER["DOCUMENT_ROOT"] . CFile::GetPath($fid));
        $hiddenTextArray = findHiddenText($extractedText);


        if (check_full_array($hiddenTextArray)) {
            foreach ($hiddenTextArray as $line) {
                $line = str_replace("=====", false, $line);
                $decrypted = decrypt($line);
                $page_info = explode(';', $decrypted);
                if (check_full_array($page_info)) {
                    echo "<br/>";
                    foreach ($page_info as $info_item) {
                        $info_item_array = explode(':', $info_item);
                        if ($info_item_array[0] == 'page')
                            echo "Страница №" . $info_item_array[1] . "<br/>";
                        if ($info_item_array[0] == 'user_id') {
                            if ((int)$info_item_array[1] > 0) {
                                $user = \Models\User::find((int)$info_item_array[1], ['ID', 'NAME', 'LAST_NAME', 'EMAIL', 'UF_DEALER']);
                                if (check_full_array($user)) {
                                    if ($user['UF_DEALER'] > 0) {
                                        $dealer = \Models\Dealer::find($user['UF_DEALER']);
                                        if (check_full_array($dealer)) {
                                            $str = ". Дилер - " . $dealer['NAME'];
                                        }
                                    }
                                    echo "Пользователь - " . $user['LAST_NAME'] . " " . $user['NAME'] . " (" . $user['EMAIL'] . ")" . $str . "<br/>";
                                }
                            }
                        }
                    }
                }
            }
        } else {
            echo "Файл не зашифрован";
        }
    } else {
        echo "Формат файла не поддерживается. Загрузите файл формата PDF";
    }

} else {
    echo "Файл не добавлен";
}
//dump($hiddenTextArray);




