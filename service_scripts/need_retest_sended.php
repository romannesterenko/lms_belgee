<?php

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
use Bitrix\Main\Application;

// Получаем подключение к базе данных
$connection = Application::getConnection();

// Подготавливаем SQL запрос
$sql = "SELECT * FROM b_event WHERE EVENT_NAME='NEED_RETEST' ORDER BY ID DESC";

// Выполняем запрос
$recordSet = $connection->query($sql);
?>
    <div class="main-content">
        <div class="content">
            <div class="content-block">
                <div class="text-content text-content--long">
                    <h2 class="h2 center">Отправка писем по ретесту</h2>
                    <div class="table-block">
                        <table class="table table-bordered table-striped table--white" id="table-report" style="padding-top: 25px">
                            <thead>
                                <tr>
                                    <th>ФИО</th>
                                    <th>Email</th>
                                    <th>Курс</th>
                                    <th>Дата отправки</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php // Обрабатываем результаты запроса
                                while ($record = $recordSet->fetch()) {
                                    $fields = unserialize($record['C_FIELDS'])?>
                                    <tr>
                                        <td class="text-left"><?=$fields['USER_NAME']?></td>
                                        <td class="text-left"><?=$fields['TO_EMAIL']?></td>
                                        <td class="text-left"><?=$fields['COURSE_NAME']?></td>
                                        <td class="text-left"><?=$record['DATE_EXEC']?></td>
                                    </tr>
                                <?php }?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
