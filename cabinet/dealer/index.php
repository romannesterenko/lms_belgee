<?php const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION, $USER;
?>
<div class="main-content">
    <aside>
        <div class="aside-sticky aside-sticky--title">
            <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
        </div>
    </aside>
    <div class="content">
        <h2 class="h2"><?php $APPLICATION->ShowTitle();?></h2>
        <?php /*$notifications_main_filter['UF_USER_ID']=$USER->GetID();
        $notifications_main_filter['UF_IS_READ']=false;
        $APPLICATION->IncludeComponent("bitrix:highloadblock.list","notifications_cabinet_admin",Array(
                "BLOCK_ID" => "3",
                "CHECK_PERMISSIONS" => "Y",
                "DETAIL_URL" => "",
                "FILTER_NAME" => "notifications_main_filter",
                "PAGEN_ID" => "page",
                "ROWS_PER_PAGE" => "4"
            )
        );*/?>
        <?php $GMRMessages_main_filter['UF_USER_ID']=$USER->GetID();
        $GMRMessages_main_filter['UF_IS_READ']=false;
        $APPLICATION->IncludeComponent("bitrix:highloadblock.list","GMRMessages_cabinet_admin",Array(
                "BLOCK_ID" => "5",
                "CHECK_PERMISSIONS" => "Y",
                "DETAIL_URL" => "",
                "FILTER_NAME" => "GMRMessages_main_filter",
                "PAGEN_ID" => "page",
                "ROWS_PER_PAGE" => "6"
            )
        );
        ?>
        <?php /*$APPLICATION->IncludeComponent(
            "lms:cabinet.admin.completions.info",
            "",
            Array(),
            false
        );
        $APPLICATION->IncludeComponent(
            "lms:cabinet.admin.enrolls.confirm",
            "",
            Array(),
            false
        );*/?>
        <h3 class="h3 center">Пройденные курсы</h3>
        <?php $completed_courses_filter['UF_USER_ID']=$USER->GetID();
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
        );
        $APPLICATION->IncludeComponent("lms:shedule.courses.list",
            "admin_calendar_list",
            array(
                "MONTH" => $_REQUEST['month']??date('m'),
                "YEAR" => $_REQUEST['year']??date('Y'),
                "FOR_ROLE" => $_REQUEST['role']?? \Teaching\Roles::getByCurrentUser(),
                "PAGE_COUNT" => 3,
            ),
            false
        );?>
    </div>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>