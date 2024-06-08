<?php use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;
/** @var array $arResult */
/** @var array $arParams */
?>
<div class="table-block">
    <?php if(check_full_array($arResult['rows'])){?>
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
        <?php foreach ($arResult['rows'] as $row){?>
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
                <?php $status = \Models\Course::getStatusByCompletion($row['UF_COURSE_ID'], $row['UF_USER_ID'], $row['ID']);?>
                <td<?=in_array($status, ['expired', 'expired_date'])?" style='color:red'":"";?>><?=$row['UF_DATE']?></td>
                <td>
                    <?php
                    if(\Models\Course::isOP($row['UF_COURSE_ID']) || \Models\Course::isMarketing($row['UF_COURSE_ID']))
                        $download_link = "/upload/certificates/".$row['UF_COURSE_ID']."/".$row['UF_COURSE_ID']."_".$row['UF_USER_ID']."_".str_replace('.', '_', $row['UF_ORIGINAL_DATE']).'.pdf';
                    else
                        $download_link = "/upload/certificates/new/".$row['UF_COURSE_ID']."/".$row['ID'].'.pdf';

                    $path = $_SERVER["DOCUMENT_ROOT"].$download_link;


                    if(file_exists($path)){
                        //$download_link = "/upload/certificates/".$row['UF_COURSE_ID']."/".$row['UF_COURSE_ID']."_".$row['UF_USER_ID']."_".str_replace('.', '_', $row['UF_ORIGINAL_DATE']).'.pdf';?>
                        <a class="download" download="certificate" href="<?=$download_link?>"><span class="icon-download"></span></a>
                    <?php }?>
                </td>
            </tr>
        <?php }?>
        </tbody>
    </table>
    <?php }else{?>
        <p><?= Loc::getMessage('NO_COURSES') ?></p>
    <?php }?>
</div>

<?php
if ($arParams['ROWS_PER_PAGE'] > 0) {
    $APPLICATION->IncludeComponent("bitrix:main.pagenavigation", "navigation", Array(
	"NAV_OBJECT" => $arResult["nav_object"],
		"SEF_MODE" => "N"
	),
	false
);
}?>