<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $USER, $APPLICATION;
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <?php
                $request_fields = explode('?', $_REQUEST['new']);
                $completed_courses_filter['UF_SHEDULE_ID']=(int)$_REQUEST['id'];
                $completed_courses_filter['UF_IS_APPROVED']=$request_fields[0]==1?0:1;
                $completed_courses_filter['UF_USER_ID']=\Models\User::getEmployeesIdsByAdmin();
                //dump($completed_courses_filter);
                $template = $request_fields[0]==0?"approved_enrolls":"none_approved_enrolls";
                $APPLICATION->IncludeComponent(
                    "bitrix:highloadblock.list",
                    $template,
                    Array(
                        "BLOCK_ID" => "1",
                        "CHECK_PERMISSIONS" => "Y",
                        "DETAIL_URL" => "detail.php?BLOCK_ID=#BLOCK_ID#&ROW_ID=#ID#",
                        "FILTER_NAME" => "completed_courses_filter",
                        "PAGEN_ID" => "page",
                        "ROWS_PER_PAGE" => "6"
                    )
                );
            ?>


        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>