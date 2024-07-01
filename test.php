<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$text = "У кого-были случаи на Атлас ПРО с г греБля, лох нущим мотором после заправки?
_______
Клиент дефект продемонстрировал. Заезжаем вместе на заправку, заливаем топливо, заводим мотор. Двигатель завелся и через 1-2 секунды заглох. Запускаешь повторно - все тоже самое, пока не нажмешь на педаль акселератора. Коды ошибок отсутствуют. На СТО дефект не проявляется. Пробовали заливать топливо из канистры - все работает. Склоняемся к вентиляции топливного бака. Визуально там все хорошо. Порекомендовали клиенту понаблюдать: чуть топлива выкатать, открыть горловину топливного бака и потом попробовать запустить.
Из допа установлена не нами Пандора";

function containsBannedWord($text, $bannedWords) {
    foreach ($bannedWords as $word) {
        $pattern = '/(?<!\p{L})' . preg_quote($word, '/') . '(?!\p{L})/iu';
        if (preg_match($pattern, $text)) {
            return $word;
        }
    }
    return false;
}

$filename = $_SERVER["DOCUMENT_ROOT"] . "/upload/stop_words.txt";
$bannedWords = file($filename, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
if($word = containsBannedWord($text, $bannedWords)){
    dump($word);
} else {
    dump("Не найдено");
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");