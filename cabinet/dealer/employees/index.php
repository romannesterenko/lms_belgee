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
            <h2 class="h2"><?=$APPLICATION->ShowTitle();?></h2>
            <div class="content-block  content-block--margin">
                <?php /*?>
                <div class="content-menu">
                    <ul>
                        <li class="active"><a href="">Финансово-экономический отдел</a></li>
                        <li><a href="">Отдел продаж</a></li>
                        <li><a href="">Сборочный цех</a></li>
                        <li><a href="">Юридический отдел</a></li>
                        <li><a href="">Отдел закупок</a></li>
                        <li><a href="">Ремонтная служба</a></li>
                        <li><a href="">Отдел бухгалтерии</a></li>
                        <li><a href="">Отдел маркетинга</a></li>
                        <li><a href="">Гарантийное обеспечение</a></li>
                        <li><a href="">Кадровый отдел</a></li>
                        <li><a href="">Отдел контроля качества</a></li>
                        <li><a href="">Логистика и транспорт</a></li>
                    </ul>
                </div>

                <h3 class="h3 center">Финансово-экономический отдел</h3>
                <?*/?>
                <div class="btn-center" style="margin: 25px 0">
                    <a href="/cabinet/dealer/employees/create/" class="btn "><?= Loc::getMessage('CREATE') ?></a>
                </div>
                <div class="workers">
                    <?php
                    $ids = \Models\Employee::getActiveEmployeesIdsByDealerAdmin();
                    $nav = new \Bitrix\Main\UI\AdminPageNavigation("nav");
                    $nav->allowAllRecords(true)
                        ->setPageSize(8);
                    $result = \Bitrix\Main\UserTable::getList(array(
                        'filter' => array('ID' => $ids), // выберем идентификатор и генерируемое (expression) поле SHORT_NAME
                        'select' => array('ID','NAME', 'LAST_NAME', 'SECOND_NAME', 'PERSONAL_PHOTO', 'UF_WORK_START_DATE', 'DATE_REGISTER'), // выберем идентификатор и генерируемое (expression) поле SHORT_NAME
                        'order' => array('LAST_NAME'=>'ASC'), // все группы, кроме основной группы администраторов,
                        'count_total' => true,
                        'offset' => $nav->getOffset(),
                        'limit' => $nav->getLimit()));
                    $nav->setRecordCount($result->getCount());?>
                    <?php while ($employee = $result->fetch()) {
                        if(!empty($employee['UF_WORK_START_DATE'])) {
                            $experience = (int)time() - (int)$employee['UF_WORK_START_DATE']->getTimeStamp();
                        }else{
                            $experience = (int)time() - (int)$employee['DATE_REGISTER']->getTimeStamp();
                        }

                        $experience_string = '';
                        $days = round($experience/(60*60*24));
                        $years = 0;
                        $months = 0;
                        if($days>31){
                            if($days>365)
                                $years = round($days/365);
                            else{
                                $months = round($days/31);
                            }
                        }
                        if($years>0)
                            $experience_string = $years.' '.\Helpers\StringHelpers::plural($years, [Loc::getMessage('YEAR'), Loc::getMessage('YEARS'), Loc::getMessage('MANY_YEARS')]);
                        elseif ($months>0)
                            $experience_string = $months.' '.\Helpers\StringHelpers::plural($months, [Loc::getMessage('MONTH'), Loc::getMessage('MAONTHS'), Loc::getMessage('MANY_MONTHS')]);
                        else
                            $experience_string = $days.' '.\Helpers\StringHelpers::plural($days, [Loc::getMessage('DAY'), Loc::getMessage('DAYS'), Loc::getMessage('MANY_DAYS')]);?>
                        <div class="worker-item">
                            <a href="/cabinet/dealer/employees/<?=$employee['ID']?>/" class="worker-item__content">
                                <span class="worker-item__avatar"><img src="<?=$employee['PERSONAL_PHOTO']>0?CFile::GetPath($employee['PERSONAL_PHOTO']):SITE_TEMPLATE_PATH.'/images/No-photo-m.png'?>" alt=""></span>
                                <span class="worker-item__name"><?=$employee['LAST_NAME']?> <br>
                                <?=$employee['NAME']?> <?=$employee['SECOND_NAME']?></span>
                            </a>
                            <div class="worker-item__position"><?=$employee['PERSONAL_PROFESSION']?></div>
                            <ul>
                                <?
                                $ids = \Teaching\Roles::GetRequiredCourseIdsByUser($employee['ID']);
                                foreach ($ids as $key => $course_id) {
                                    $status = \Models\Course::getStatus($course_id, $employee['ID']);
                                    if($status == 'completed')
                                        unset($ids[$key]);
                                }

                                ?>
                                <li><?= Loc::getMessage('SATGE') ?><span> <?=$experience_string?></span></li>
                                <li><?= Loc::getMessage('COMPLETED_COURSES') ?><span><?= \Teaching\Courses::getCountOfCompetedCourses($employee['ID'])?></span></li>
                                <li><?= Loc::getMessage('SETTED_COURSES') ?><span><?=count($ids)?></span></li>
                                <?php if(\Models\User::isCertifiedEmployee($employee['ID'])){?>
                                    <li><?= Loc::getMessage('CERTIFICIED') ?><span><?= Loc::getMessage('YES') ?></span></li>
                                    <li><?= Loc::getMessage('CERTIFY_DATE') ?><span><?=\Models\User::getCertifiedDate($employee['ID'], true)?></span></li>
                                <?php }else{?>
                                    <li><?= Loc::getMessage('CERTIFICIED') ?> <span><?= Loc::getMessage('NO') ?></span></li>
                                <?php }?>
                            </ul>
                        </div>
                    <?php }?>
                </div>
            </div>
            <?php
            $APPLICATION->IncludeComponent(
                "bitrix:main.pagenavigation",
                "navigation",
                array(
                    "NAV_OBJECT" => $nav,
                ),
                false
            );
            ?>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>