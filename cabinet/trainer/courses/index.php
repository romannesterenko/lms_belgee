<?php use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
use Teaching\Courses;
use Teaching\SheduleCourses;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$course = Courses::getById($_REQUEST['id']);
if($course===[]){
    Helpers\PageHelper::set404();
    die();
}
$schedules = SheduleCourses::getArray(['PROPERTY_COURSE' => $course['ID']]);
?>
<div class="main-content">
    <aside>
        <div class="aside-sticky aside-sticky--title">
            <?php require_once(PageHelper::getSideBarForCabinet());?>
        </div>
    </aside>
    <div class="content">
        <h2 class="h2"><?= Loc::getMessage('COURSES_SHEDULES_TITLE') ?> "<?=$course['NAME']?>"</h2>
        <div class="table-block">
            <table class="table table-bordered table-striped table-responsive-stack" id="table-1">
                <thead class="thead-dark">
                    <tr>
                        <th class="text-left"><?= Loc::getMessage('COURSES_DATE') ?></th>
                        <th class="text-left"><?= Loc::getMessage('COURSES_STATUS') ?></th>
                        <th class="text-left"><?= Loc::getMessage('COURSES_EMPLOYEES') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedules as $schedule){?>
                        <tr>
                            <td><a href="/cabinet/trainer/schedule/<?=$schedule['ID']?>/"><?=Helpers\DateHelper::getHumanDate($schedule['PROPERTIES']['BEGIN_DATE'])?> - <?=Helpers\DateHelper::getHumanDate($schedule['PROPERTIES']['END_DATE'])?></a></td>
                            <td>
                                <span class="status status--passed"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check2.svg" alt=""></span> пройден</span>
                            </td>
                            <td><?= SheduleCourses::getExistsPlaces($schedule['ID'])?>/<?=$schedule['PROPERTIES']['LIMIT']?></td>
                        </tr>
                    <?php }?>
                </tbody>
            </table>

        </div>

        <div class="pagination">
            <div class="pagination__nav">
                <a href="" class="prev"><span class="icon icon-arrow-link"></span>предыдущая</a>
                <a href="" class="next">следующая<span class="icon icon-arrow-link"></span></a>
            </div>
            <div class="pagination__pages">
                <span><a href="" class="active">1</a></span>
                <span><a href="">2</a></span>
                <span><a href="">3</a></span>
                <span><a href="">4</a></span>
                <span><a href="">5</a></span>
                <span><a href="">6</a></span>
                <span><a href="">7</a></span>
                <span><a href="">8</a></span>
            </div>
        </div>
    </div>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>


