<?php use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;
/** @var array $arResult */
/** @var array $arParams */
if(count($arResult['rows'])>0){?>
<h3 class="h3 center"><?=GetMessage('COMPLETED_COURSES_TITLE')?></h3>
<div class="table-block">
    <table class="table table-bordered table-striped table-responsive-stack" id="table-3">
        <thead class="thead-dark">
            <tr>
                <th><?=GetMessage('COMPLETED_COURSES_COURSE')?></th>
                <th><?=GetMessage('COMPLETED_COURSES_TYPE')?></th>
                <th><?=GetMessage('COMPLETED_COURSES_STATUS')?></th>
                <th><?=GetMessage('COMPLETED_COURSES_ATTEMPTS')?></th>
                <th><?=GetMessage('COMPLETED_COURSES_POINTS')?></th>
                <th><?=GetMessage('COMPLETED_COURSES_DATE')?></th>
                <th><?=GetMessage('COMPLETED_COURSES_CERT')?></th>
            </tr>
        </thead>
        <tbody>
        <?php if(count($arResult['rows'])==0){?>
            <tr>
                <td colspan="7"><?= Loc::getMessage('NO_ROWS') ?></td>
            </tr>
        <?php }else{
        foreach ($arResult['rows'] as $row){?>
            <tr>
                <td>
                    <a href="<?=$row['COURSE_LINK']?>"><?=$row['COURSE_NAME']?></a>
                </td>
                <td><?=$row['COURSE_TYPE']?></td>
                <td>
                    <span class="status status--passed"><span class="icon"><img src="<?=SITE_TEMPLATE_PATH?>/images/check2.svg" alt=""></span> <?=GetMessage('COMPLETED_COURSES_COMPLETED')?></span>
                </td>
                <td><?=$row['UF_MADE_ATTEMPTS']>0?$row['UF_MADE_ATTEMPTS']:1?>/<?=$row['UF_TOTAL_ATTEMPTS']>0?$row['UF_TOTAL_ATTEMPTS']:Loc::getMessage('NOT_LIMITED')?></td>
                <td><?=$row['UF_POINTS']?></td>
                <td><?=$row['UF_DATE']?></td>
                <td>
                    <?php $path = $_SERVER["DOCUMENT_ROOT"]."/upload/certificates/".$row['UF_COURSE_ID']."/".$row['UF_COURSE_ID']."_".$row['UF_USER_ID']."_".str_replace('.', '_', $row['UF_ORIGINAL_DATE']).'.pdf';
                    if(file_exists($path)){
                        $download_link = "/upload/certificates/".$row['UF_COURSE_ID']."/".$row['UF_COURSE_ID']."_".$row['UF_USER_ID']."_".str_replace('.', '_', $row['UF_ORIGINAL_DATE']).'.pdf';?>
                        <a class="download" download="certificate" href="<?=$download_link?>"><span class="icon-download"></span></a>
                    <?php } else {
                        $row['UF_ORIGINAL_DATE'] = $row['UF_COMPLETED_TIME']?\Helpers\DateHelper::getHumanDate($row['UF_COMPLETED_TIME'], 'd.m.Y'):$row['UF_DATE'];
                        $path = $_SERVER["DOCUMENT_ROOT"]."/upload/certificates/".$row['UF_COURSE_ID']."/".$row['UF_COURSE_ID']."_".$row['UF_USER_ID']."_".str_replace('.', '_', $row['UF_ORIGINAL_DATE']).'.pdf';
                        if(file_exists($path)){
                            $download_link = "/upload/certificates/".$row['UF_COURSE_ID']."/".$row['UF_COURSE_ID']."_".$row['UF_USER_ID']."_".str_replace('.', '_', $row['UF_ORIGINAL_DATE']).'.pdf';?>
                            <a class="download" download="certificate" href="<?=$download_link?>"><span class="icon-download"></span></a>
                        <?php }
                    }?>
                </td>
            </tr>
        <?php }
        }?>
        </tbody>
    </table>
    <?php if(count($arResult['rows'])>10){?>
        <div class="content-show-link">
            <a href="/cabinet/completed_courses/">
                <?=GetMessage('SHOW_ALL_LINK')?>
                <span class="icon icon-arrow-link"></span>
            </a>
        </div>
    <?php }?>
</div>
<?php }?>