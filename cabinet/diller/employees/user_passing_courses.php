<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Проходит курсы");
$id = \Helpers\UrlParamsHelper::getParam('id');
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
            </div>

        </aside>
        <div class="content">
            <h2 class="h2"><?=$APPLICATION->ShowTitle();?></h2>
            <div class="content-block">
                <div class="content-nav content-nav--top">
                    <span><a href="/cabinet/dealer/employees/<?=$id?>/">Профиль сотрудника</a></span>
                    <span class="active"><a href="/cabinet/dealer/employees/passing/<?=$id?>/">Проходит курсы</a></span>
                    <span><a href="/cabinet/dealer/employees/passed/<?=$id?>/">Пройденные курсы</a></span>
                    <span><a href="/cabinet/dealer/employees/setted_courses/<?=$id?>/">Назначенные курсы</a></span>
                </div>
                <?php
                $completed_courses_filter['UF_USER_ID']=$id;
                $completed_courses_filter['UF_IS_COMPLETE']=0;
                $APPLICATION->IncludeComponent("bitrix:highloadblock.list","completing_cources",Array(
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
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>