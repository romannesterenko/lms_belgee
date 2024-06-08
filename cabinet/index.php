<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

use Bitrix\Main\Localization\Loc;

global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('TITLE'));?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php $APPLICATION->IncludeComponent(
                    "bitrix:menu",
                    "cabinet_menu",
                    array(
                        "ROOT_MENU_TYPE" => "cabinet",
                        "MAX_LEVEL" => "1",
                        "CHILD_MENU_TYPE" => "cabinet",
                        "USE_EXT" => "N",
                        "DELAY" => "N",
                        "ALLOW_MULTI_SELECT" => "Y",
                        "MENU_CACHE_TYPE" => "N",
                        "MENU_CACHE_TIME" => "3600",
                        "MENU_CACHE_USE_GROUPS" => "Y",
                        "MENU_CACHE_GET_VARS" => array(
                        ),
                        "COMPONENT_TEMPLATE" => "cabinet_menu"
                    ),
                    false
                );?>
                <?php $APPLICATION->IncludeComponent(
                    "lms:cabinet.courses.section",
                    "",
                    array(),
                    false
                );?>
                <?php $APPLICATION->IncludeComponent(
                    "lms:upcoming.courses",
                    "",
                    array(),
                    false
                );?>
            </div>
        </aside>
        <div class="content">
            <h2 class="h2"><?=$APPLICATION->ShowTitle();?></h2>
            <?php $APPLICATION->IncludeComponent(
                    "bitrix:main.profile",
                    "profile",
                    Array(),
                false
            );?>

            <?php if(\Helpers\UserHelper::isLocalAdmin()){?>
                <h2 class="h2">Записанные сотрудники</h2>
                <div class="content-block  content-block--margin">
                    <?php $users = \Models\Employee::getActiveEmployeesByDealerAdmin();
                    $new_users_array = [];
                    $ids = [];
                    foreach ($users as $user){
                        $new_users_array[$user['ID']] = $user;
                        $ids[] = $user['ID'];
                    }
                    $enrolls = (new \Teaching\Enrollments())->get(['UF_USER_ID' => $ids, '>UF_DATE'=>date('d.m.Y'), 'UF_IS_APPROVED' => 1]);
                    if(check_full_array($enrolls)){?>
                        <div class="table-block">
                            <table class="table table-bordered" id="table-report" style="padding-top: 25px">

                                <thead class="thead-dark">
                                <tr>
                                    <th style="vertical-align: middle;">ФИО</th>
                                    <th style="vertical-align: middle;">Курс</th>
                                    <th style="vertical-align: middle;">Дата</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($enrolls as $enroll){
                                    $course = \Models\Course::find($enroll['UF_COURSE_ID'], ['ID', 'NAME']);
                                    $shedule = $enroll['UF_SHEDULE_ID']>0?current(\Teaching\SheduleCourses::getById($enroll['UF_SHEDULE_ID'])):[];?>
                                    <tr>
                                        <td style="vertical-align: middle;"><?=$new_users_array[$enroll['UF_USER_ID']]['NAME']?> <?=$new_users_array[$enroll['UF_USER_ID']]['LAST_NAME']?></td>
                                        <td style="vertical-align: middle; text-align: left"><?=$shedule['NAME']??$course['NAME']?></td>
                                        <td style="vertical-align: middle;"><?=$enroll['UF_DATE']?></td>
                                    </tr>
                                <?php }?>
                                </tbody>
                            </table>
                        </div>
                    <?php } else {?>
                        <p>Записей нет</p>
                    <?php }?>

                </div>
            <?php }?>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>