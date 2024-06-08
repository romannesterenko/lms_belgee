<?php

use Bitrix\Main\Localization\Loc;
use Helpers\IBlockHelper;
use Polls\ProcessPoll;
use Teaching\Roles;

const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION, $USER, $completed_courses_filter;
$APPLICATION->SetTitle(Loc::getMessage('COMPLETED_COURSES'));?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <h2 class="h2"><?=$APPLICATION->ShowTitle();?></h2>
            <?php
                global $pollsFilter;
                $pollsProcess = new ProcessPoll();
                $complete_polls = $pollsProcess->getCompletePollIdsByCurrentUser();
                $pollsFilter['UF_ROLES'] = Roles::getByUser();
                if(count($complete_polls)>0)
                    $pollsFilter['!ID'] = $complete_polls;
                //dump($pollsFilter);
                $APPLICATION->IncludeComponent("bitrix:catalog.section.list", "polls_cabinet", Array(
                    "VIEW_MODE" => "TEXT",	// Вид списка подразделов
                    "SHOW_PARENT_NAME" => "Y",	// Показывать название раздела
                    "IBLOCK_ID" => IBlockHelper::getPollsIBlock(),	// Инфоблок
                    "SECTION_ID" => false,	// ID раздела
                    "SECTION_CODE" => "",	// Код раздела
                    "SECTION_URL" => "",	// URL, ведущий на страницу с содержимым раздела
                    "COUNT_ELEMENTS" => "Y",	// Показывать количество элементов в разделе
                    "TOP_DEPTH" => "2",	// Максимальная отображаемая глубина разделов
                    "SECTION_FIELDS" => "",	// Поля разделов
                    "SECTION_USER_FIELDS" => "",	// Свойства разделов
                    "ADD_SECTIONS_CHAIN" => "Y",	// Включать раздел в цепочку навигации
                    "CACHE_TYPE" => "N",	// Тип кеширования
                    "CACHE_TIME" => "36000000",	// Время кеширования (сек.)
                    "CACHE_NOTES" => "",
                    "CACHE_GROUPS" => "Y",	// Учитывать права доступа
                    "FILTER_NAME" => "pollsFilter",	// Имя массива со значениями фильтра разделов
                ),
                    false
                );
            ?>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>