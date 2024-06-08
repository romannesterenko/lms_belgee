<?php
const NEED_AUTH = true;
use Bitrix\Main\Localization\Loc;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$course = \Teaching\Courses::getById($_REQUEST['id']);
$APPLICATION->SetTitle(Loc::getMessage('H2_TITLE').' '.$course['NAME']);
?>
<div class="main-content">
    <aside>
        <div class="aside-sticky aside-sticky--title">
            <?php require_once(\Helpers\PageHelper::getSideBarForCabinet());?>
        </div>
    </aside>
    <div class="content">

        <h2 class="h2"><?= Loc::getMessage('H2_TITLE') ?> <br/>"<?=$course['NAME']?>"</h2>
        <?php
        $completions = new \Teaching\CourseCompletion();
        $list = [];
        foreach ($completions->getItemsByCourseID($course['ID']) as $item){
            $item['USER'] = \Models\User::getFullName($item['UF_USER_ID']);
            $item['DEALER'] = \Helpers\DealerHelper::getByUser($item['UF_USER_ID']);
            $list[] = $item;
        }
        /*$list = \Settings\Reports::getEmployeesForCourse($_REQUEST['id']);
        $headers = [];
        foreach ($list[0] as $name=>$value)
            if(strpos($name, '.ID')===false) {
                if(strpos($name, 'VALUE_ID')===false)
                    $headers[] = $name;
            }*/
        ?>
        <div class="content-block">
            <div class="text-content text-content--long">
                <div class="table-block">
                    <table class="table table-bordered table-striped table--white" id="table-1" style="padding-top: 25px">
                        <thead class="thead-dark">
                            <tr>
                                <th><?= Loc::getMessage('TABLE_TH_CITY') ?></th>
                                <th><?= Loc::getMessage('TABLE_TH_DEALER') ?></th>
                                <th><?= Loc::getMessage('TABLE_TH_EMPLOYEE') ?></th>
                                <th><?= Loc::getMessage('TABLE_TH_CURRENT_STEP') ?></th>
                                <th><?= Loc::getMessage('TABLE_TH_ALL_STEPS') ?></th>
                                <th><?= Loc::getMessage('TABLE_TH_COMPLETED') ?></th>
                                <th><?= Loc::getMessage('TABLE_TH_COMPLETED_DATE') ?></th>
                                <th><?= Loc::getMessage('TABLE_TH_POINTS') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($list as $item){?>
                                <tr>
                                    <td><?=$item['DEALER']['PROPERTY_CITY_VALUE']?></td>
                                    <td><?=$item['DEALER']['NAME']?></td>
                                    <td><?=$item['USER']?></td>
                                    <?php if($item['UF_CURR_STEP']>0){?>
                                        <td><?=$item['UF_IS_COMPLETE']==1?$item['UF_ALL_STEPS']:$item['UF_CURR_STEP']?></td>
                                        <td><?=$item['UF_ALL_STEPS']?></td>
                                    <?php }else{?>
                                        <td> - </td>
                                        <td> - </td>
                                    <?php }?>
                                    <td <?if($item['UF_IS_COMPLETE']!=1):?>style="color: red" <?endif;?>><?=$item['UF_IS_COMPLETE']==1?Loc::getMessage('YES'):Loc::getMessage('NO')?></td>
                                    <td><?=$item['UF_COMPLETED_TIME']?></td>
                                    <td><?=(int)$item['UF_POINTS']?> из 9 <?if($item['UF_POINTS']>0):?>(<?=round(($item['UF_POINTS']/9)*100,0)?>%)<?endif;?></td>
                                </tr>
                        <?php }?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>