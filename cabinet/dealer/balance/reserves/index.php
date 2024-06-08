<?php

use Bitrix\Main\Localization\Loc;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle(Loc::getMessage('EMPLOYEES'));
?>
    <div class="main-content">
        <aside>
            <div class="aside-sticky aside-sticky--title">
                <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
            </div>
        </aside>
        <div class="content">
            <?php $dealer = \Models\Dealer::find(\Models\Dealer::getByEmployee());
            $reserves = \Models\Reserve::get(["UF_DEALER" => $dealer["CODE"], "UF_IS_COMPLETE" => false]);?>
            <h2 class="h2 text-left">История резервов</h2>
            <div class="table-block">
                <table class="table table-bordered table-striped table-responsive-stack">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Направление</th>
                            <th>Название курса</th>
                            <th>Начало обучения</th>
                            <th>Сотрудник</th>
                            <th>Дата регистрации</th>
                            <th>Сумма</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reserves as $key => $reserve){
                            $completion = (new \Teaching\CourseCompletion)->find($reserve["UF_COMPLETION_ID"]);
                            $course = \Models\Course::find($reserve["UF_COURSE_ID"], ["NAME"]);
                            $shedule = current(\Teaching\SheduleCourses::getById($completion["UF_SHEDULE_ID"]));
                            $user = \Models\User::find($reserve["UF_USER_ID"], ["NAME", "LAST_NAME"]); ?>
                            <tr>
                                <td class="text-center"><?=($key+1)?></td>
                                <td><?=\Models\Direction::getDirectionByCode($reserve["UF_DIRECTION"])['title']?></td>
                                <td><?=$course["NAME"]?></td>
                                <td><?=$shedule["PROPERTY_BEGIN_REGISTRATION_DATE_VALUE"]?></td>
                                <td><?=$user["NAME"]?> <?=$user["LAST_NAME"]?></td>
                                <td><?=$completion["UF_DATE"]?></td>
                                <td><?=\Helpers\StringHelpers::preparePrice($reserve["UF_PRICE"])?></td>
                            </tr>
                        <?php }?>
                    </tbody>
                </table>
            </div>


        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>