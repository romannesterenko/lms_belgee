<?php
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
                <div class="content-nav content-nav--top">
                    <span class="active"><a href="#">Курсы</a></span>
                    <span><a href="/cabinet/diller/reports/employess/">Сотрудники</a></span>
                    <span><a href="/cabinet/diller/reports/dilers/">Дилеры</a></span>
                    <span><a href="/cabinet/diller/reports/polls/">Опросы</a></span>
                </div>

                <h3 class="h3 center">Курсы</h3>
                <?php $dealer = \Settings\Reports::getCoursesDealerReport();?>
                <div class="reports">
                    <div class="table-block">
                        <input type="hidden" id="is_adaptive" value="<?=$dealer['SETTINGS']['PROPERTIES']['IS_ADAPTIVE']?>">

                        <table class="table table-bordered table-striped table--white" id="table-report" style="padding-top: 25px">
                            <thead class="thead-dark">
                            <tr>
                                <th>Город</th>
                                <th>Наименование дилера</th>
                                <?php foreach ($dealer['ROLES_LIST'] as $role){?>
                                    <th><b>Зарегистрировано "<?=$role['NAME']?>"</b></th>
                                    <?php foreach ($role['COURSES'] as $course){?>
                                        <th><?=$course['NAME']?></th>
                                    <?php }?>
                                <?php }?>
                            </tr>
                            </thead>
                            <tbody>
                            <tr style="height: 50px">
                                <td class="text-left"><?=$dealer['PROPERTY_CITY_VALUE']?></td>
                                <td class="text-left"><?=$dealer['NAME']?></td>
                                <?php foreach ($dealer['ROLES_LIST'] as $role){?>
                                    <td><b><?=count($role['USERS'])?></b></td>
                                    <?php foreach ($role['COURSES'] as $course){?>
                                        <td><?=$course['COMPLETED']?> (<?=$course['COMPLETED_PERCENTS']?>)</td>
                                    <?php }?>
                                <?php }?>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>


            </div>
        </div>
    </div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>