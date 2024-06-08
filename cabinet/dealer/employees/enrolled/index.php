<?php

use Bitrix\Main\Localization\Loc;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('EMPLOYEES'));
$_REQUEST['report_id'] = 9999999;
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
            </div>

        </aside>
        <div class="content">
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
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>