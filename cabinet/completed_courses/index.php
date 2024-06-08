<?php

use Bitrix\Main\Localization\Loc;

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
            $completed_courses_filter['UF_USER_ID']=$USER->GetID();
            $completed_courses_filter['UF_IS_COMPLETE']=1;
            $completed_courses_filter['UF_WAS_ARCHIVED']=false;
            $APPLICATION->IncludeComponent("bitrix:highloadblock.list","completed_courses",Array(
                    "BLOCK_ID" => "2",
                    "CHECK_PERMISSIONS" => "Y",
                    "DETAIL_URL" => "detail.php?BLOCK_ID=#BLOCK_ID#&ROW_ID=#ID#",
                    "FILTER_NAME" => "completed_courses_filter",
                    "PAGEN_ID" => "page",
                    "ROWS_PER_PAGE" => "15"
                )
            );?>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>