<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Сотрудники");
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
                    <a href="/cabinet/dealer/employees/create/" class="btn ">Создать сотрудника</a>
                </div>
                <div class="workers">
                    <?php
                    $ids = \Models\Employee::getEmployeesIdsByAdmin();
                    $nav = new \Bitrix\Main\UI\AdminPageNavigation("nav");
                    $nav->allowAllRecords(true)
                        ->setPageSize(8);
                    $result = \Bitrix\Main\UserTable::getList(array(
                        'filter' => array('ID' => $ids), // выберем идентификатор и генерируемое (expression) поле SHORT_NAME
                        'select' => array('ID','NAME', 'LAST_NAME', 'SECOND_NAME', 'PERSONAL_PHOTO', 'UF_WORK_START_DATE', 'DATE_REGISTER'), // выберем идентификатор и генерируемое (expression) поле SHORT_NAME
                        'order' => array('ID'=>'ASC'), // все группы, кроме основной группы администраторов,
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
                            $experience_string = $years.' '.\Helpers\StringHelpers::plural($years, ['год', 'года', 'лет']);
                        elseif ($months>0)
                            $experience_string = $months.' '.\Helpers\StringHelpers::plural($months, ['месяц', 'месяца', 'месяцев']);
                        else
                            $experience_string = $days.' '.\Helpers\StringHelpers::plural($days, ['день', 'дня', 'дней']);?>
                        <div class="worker-item">
                            <a href="/cabinet/dealer/employees/<?=$employee['ID']?>/" class="worker-item__content">
                                <span class="worker-item__avatar"><img src="<?=$employee['PERSONAL_PHOTO']>0?CFile::GetPath($employee['PERSONAL_PHOTO']):SITE_TEMPLATE_PATH.'/images/No-photo-m.png'?>" alt=""></span>
                                <span class="worker-item__name"><?=$employee['LAST_NAME']?> <br>
                                <?=$employee['NAME']?> <?=$employee['SECOND_NAME']?></span>
                            </a>
                            <div class="worker-item__position"><?=$employee['PERSONAL_PROFESSION']?></div>
                            <ul>
                                <li>Стаж работы: <span> <?=$experience_string?></span></li>
                                <li>Пройдено курсов: <span><?= \Teaching\Courses::getCountOfCompetedCourses($employee['ID'])?></span></li>
                                <li>Назначено курсов: <span><?=count(\Teaching\Roles::GetRequiredCourseIdsByUser($employee['ID']))?></span></li>
                                <?php if(\Models\User::isCertifiedEmployee($employee['ID'])){?>
                                    <li>Сертифицирован: <span>Да</span></li>
                                    <li>Дата сертификации: <span><?=\Models\User::getCertifiedDate($employee['ID'], true)?></span></li>
                                <?php }else{?>
                                    <li>Сертифицирован: <span>Нет</span></li>
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