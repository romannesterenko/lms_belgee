<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER;
if($USER->IsAdmin()){
    if($_REQUEST['action']=='regenerate') {
        $completions = new \Teaching\CourseCompletion();
        foreach ($completions->getAllCompletedItems() as $item) {
            \Helpers\Pdf::generateCertFromCompletionId($item['ID']);
        }?>
        <h2>Сертификаты перегенерированы</h2>
        <a href="https://lms.geely-motors.com/bitrix/admin/">Вернуться в админпанель</a>
        <?php
    } else{
        ?>
        <h2>Регенерация сертификатов</h2>
        <a href="?action=regenerate">Генерировать сертификаты</a>
        <?php
    }
} else {
    LocalRedirect('/');
}














































































