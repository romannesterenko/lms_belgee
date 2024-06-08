<?php
const NEED_AUTH=true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

use Bitrix\Main\Localization\Loc;

$dealer_id = (int)$_REQUEST['report_id']?(int)$_REQUEST['report_id']:\Helpers\UserHelper::getDealerId();
$_REQUEST['report_id'] = $dealer_id;
$rows = [];
$settings = \Settings\Reports::getHalfYearReport();
$directions = \Teaching\Courses::getDirectionsList();
$directions[] = [
        'UF_NAME' => 'OTHERS',
        'UF_XML_ID' => false,
];
$select_directions = $directions;
if(!empty($_REQUEST['dir'])&&$_REQUEST['dir']!=0){
    unset($directions);
    if($_REQUEST['dir']=='OTHERS'){
        $directions[] = [
            'UF_NAME' => 'OTHERS',
            'UF_XML_ID' => false,
        ];
    }else{
        $directions = \Teaching\Courses::getDirectionsByName($_REQUEST['dir']);
    }
}
$period = (int)$_REQUEST['period']>0?(int)$_REQUEST['period']:6;
//$dealers = \Helpers\DealerHelper::getList(['ACTIVE' => 'Y'], ['ID', 'NAME']);
$completions = new \Teaching\CourseCompletion();
foreach($directions as $direction){
    $courses = \Teaching\Courses::getByDirection($direction['UF_XML_ID']);
    foreach ($courses as $course){
        $complets = [];
        $count = 0;
        for ($i = $period; $i >= 1; $i--) {

            $count+=count($completions->getCompletedItemsByCourseIDAndMonth($course['ID'], strtotime("-$i month")));
            $complets[date('M/y', strtotime("-$i month"))] = $count;
            $course['COMPLETES'] = $complets;
            $course['LAST_WEEK'] =  count($completions->getLastWeekCompletedItems($course['ID']));
            $course['THIS_WEEK'] = count($completions->getThisWeekCompletedItems($course['ID']));
            $course['TTL'] = $count + $course['LAST_WEEK'] + $course['THIS_WEEK'];
            $course['TARGET'] = $course['TTL']>0?$course['TTL']:1;
            $course['PERF'] = $course['TTL']/$course['TARGET']*100;

        }
        $direction['COURSES'][] = $course;
    }
    $rows[] = $direction;
}
$_REQUEST['report_id'] = 1;?>
<div class="main-content">
    <aside>
        <div class="aside-sticky aside-sticky--title">
            <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
        </div>
    </aside>
    <div class="content">
        <div class="content-block">
            <input type="hidden" id="is_adaptive" value="<?=$settings['PROPERTIES']['IS_ADAPTIVE']?>">
                <div class="text-content text-content--long">
                    <h2 class="h2 center"><?= Loc::getMessage('TITLE') ?></h2>
                    <form style="display: flex; justify-content: space-between">
                        <div class="form-group" style="display: flex;">
                            <div class="form-group mr-20">
                                <label for=""><?= Loc::getMessage('CATEGORY_FILTER') ?></label>
                                <div class="select">
                                    <select class="select2" name="dir">
                                        <option value="0"><?= Loc::getMessage('TYPE_COURSE') ?></option>
                                        <?php foreach ($select_directions as $dir){?>
                                            <option value="<?=$dir['UF_NAME']?>" <?=$dir['UF_NAME']==$_REQUEST['dir']?'selected':''?>><?=$dir['UF_NAME']?></option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>
                            <?php /*<div class="form-group">
                                <label for="">Дилер:</label>
                                <div class="select">
                                    <select class="select2" name="report_id">
                                        <?php foreach ($dealers as $dealer){?>
                                            <option value="<?=$dealer['ID']?>"><?=$dealer['NAME']?></option>
                                        <?php }?>
                                    </select>
                                </div>
                            </div>*/?>
                            <div class="form-group">
                                <label for=""><?= Loc::getMessage('PERIOD') ?></label>
                                <div class="select">
                                    <select class="select2" name="period">
                                        <option value="6">6 <?= Loc::getMessage('MONTHS') ?></option>
                                        <option value="9"<?=$_REQUEST['period']==9?' selected':''?>>9 <?= Loc::getMessage('MONTHS') ?></option>
                                        <option value="12"<?=$_REQUEST['period']==12?' selected':''?>>12 <?= Loc::getMessage('MONTHS') ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" style="display: flex; padding-top: 1rem;">
                            <div class="btn-center">
                                <button class="btn"><?= Loc::getMessage('SUBMIT') ?></button>
                            </div>
                            <div class="btn-center">
                                <a role="button" href="<?=$APPLICATION->GetCurPage()?>" class="btn"><?= Loc::getMessage('RESET') ?></a>
                            </div>
                        </div>
                    </form>
                    <div class="table-block">
                        <table class="table table-bordered table-striped table--white" id="table-report" style="padding-top: 25px">
                            <thead class="thead-dark">
                                <tr>
                                    <th></th>
                                    <?php for ($i = $period; $i >= 1; $i--) {?>
                                        <th><?=date('M/y', strtotime("-$i month"))?></th>
                                    <?php }?>
                                    <th>Last Week</th>
                                    <th>This Week</th>
                                    <th>TTL</th>
                                    <th>Target</th>
                                    <th>Perf</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row){?>
                                    <tr style="height: 50px">
                                        <td class="text-left"><b><?=$row['UF_NAME']?></b></td>
                                        <?php for ($i = $period; $i >= 1; $i--) {?>
                                            <td></td>
                                        <?php }?>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <?php foreach($row['COURSES'] as $course){?>
                                        <tr style="height: 50px">
                                            <td class="text-left"><a href="/cabinet/diller/reports/course_completions/<?=$course['ID']?>/" target="_blank"><?=$course['NAME']?></a></td>
                                            <?php for ($i = $period; $i >= 1; $i--) {?>
                                                <td><?=$course['COMPLETES'][date('M/y', strtotime("-$i month"))]?></td>
                                            <?php }?>
                                            <td>+<?=$course['LAST_WEEK']?></td>
                                            <td>+<?=$course['THIS_WEEK']?></td>
                                            <td><?=$course['TTL']?></td>
                                            <td><?=$course['TARGET']?></td>
                                            <td><?=$course['PERF']?>%</td>
                                        </tr>
                                    <?php }?>
                                <?php }?>
                            </tbody>
                        </table>
                        <button class="dt-button buttons-pdf buttons-html5" id="gen"><span>Excel</span></button>

                    </div>
                </div>

            </div>
    </div>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
