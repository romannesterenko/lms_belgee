<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */

use Bitrix\Main\Localization\Loc;
use Helpers\StringHelpers;
use Teaching\Enrollments;
use Teaching\SheduleCourses;

$this->setFrameMode(true);
$enrollments = new Enrollments();?>
<div class="table-block">
    <?php if(check_full_array($arResult['ITEMS'])){?>
    <table class="table table-bordered table-striped table-responsive-stack" id="table-1">
        <thead class="thead-dark">
        <tr>
            <th><?= Loc::getMessage('COURSES_COMPL_COURSE') ?></th>
            <th><?= Loc::getMessage('COURSES_COMPL_FORMAT') ?></th>
            <th><?= Loc::getMessage('COURSES_COMPL_EMPL') ?></th>
            <th><?= Loc::getMessage('COURSES_COMPL_OGR') ?></th>
            <th><?= Loc::getMessage('COURSES_COMPL_DATE') ?></th>
            <th><?= Loc::getMessage('COURSES_COMPL_TEST') ?></th>
            <th><?= Loc::getMessage('COURSES_COMPL_GO') ?></th>
            <th><?= Loc::getMessage('COURSES_COMPL_CANCEL') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($arResult['ITEMS'] as $item){?>
            <tr class="courses_enrolled_item" data-id="<?=$item['ID']?>">
                <td><a href="/courses/<?=$item['COURSE']['CODE']?>/"><?=$item['COURSE']['NAME']?></a></td>
                <td>
                    <span class="<?=strtolower($item['COURSE']['PROPERTIES']['COURSE_FORMAT'])?>"> <?=strtolower($item['COURSE']['PROPERTIES']['COURSE_FORMAT'])?></span>
                </td>
                <td>
                    <?php if(check_full_array($item['SCHEDULE'])){?>
                        <span class="table-place"><span><a href=""><?= SheduleCourses::getFreePlaces($item['SCHEDULE']['ID'], $item['SCHEDULE']['PROPERTY_LIMIT_VALUE'])?></a> </span> / <?=$item['SCHEDULE']['PROPERTY_LIMIT_VALUE']?></span>
                    <?php }?>
                </td>
                <td><?php if(check_full_array($item['SCHEDULE'])){?><?=$item['SCHEDULE']['PROPERTY_LIMIT_VALUE']?> <?= StringHelpers::plural($item['SCHEDULE']['PROPERTY_LIMIT_VALUE'], [Loc::getMessage('MAN'), Loc::getMessage('MEN'), Loc::getMessage('MANY_MEN')])?><?php }?></td>
                <td><?php if(check_full_array($item['SCHEDULE'])){?><?=$item['SCHEDULE']['PROPERTY_BEGIN_DATE_VALUE']?><?php } else {?><?=$item['UF_DATE']?$item['UF_DATE']->toString():''?><?php }?></td>
                <td><?php if($item['IS_HAS_TEST']){?><span class="table-check"><img src="<?=SITE_TEMPLATE_PATH?>/images/table-check.svg" alt=""></span><?php }?></td>
                <td>
                    <a class="" href="/cabinet/courses/completions/<?=$item['COURSE']['ID']?>/"><?= Loc::getMessage('COURSES_COMPL_TO_COMPLETION') ?></a>
                </td>
                <td>
                    <?php
                    $item['SCHEDULE']['ALLOW_CANCEL'] = true;
                    if($item['SCHEDULE']['PROPERTIES']['NOT_UNENROLL_DATE']) {
                        $item['SCHEDULE']['ALLOW_CANCEL'] = time() < strtotime($item['SCHEDULE']['PROPERTIES']['NOT_UNENROLL_DATE']);
                    }

                    if($item['SCHEDULE']['ALLOW_CANCEL']){?>
                    <a class="cancel unenroll_completion" data-id="<?=$item['ID']?>"><span class="icon-cancel"></span></a>
                    <?php }?>
                </td>
            </tr>
        <?php }?>
        </tbody>
    </table>
    <?php } else {?>
        <p><?= Loc::getMessage('NO_ROWS') ?></p>
    <?php }?>
</div>