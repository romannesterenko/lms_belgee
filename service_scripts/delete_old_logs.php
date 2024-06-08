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
if($USER->IsAdmin()) {
    $date = date("d.m.Y 23:59:59", strtotime("-6 months"));
    dump($date);
    $start = time();
    dump(date('H:i:s'));
    $res = \Helpers\Log::limit(["<UF_CREATED_AT" => $date], 10000);
    dump((string)$res[0]["UF_CREATED_AT"]);
    foreach ($res as $item) {
        \Helpers\Log::delete($item["ID"]);
    }
    dump(count($res));
    $time = time() - $start;
    dump("Время выполнения скрипта - ".$time." сек");
    if(check_full_array($res)){?>
        <script>
            setTimeout(function() {
                location.reload();
            }, 2000); // 2000 миллисекунд = 2 секунды
        </script>
    <?php }
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");