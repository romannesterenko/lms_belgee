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
$enrollments = new Enrollments();
if(count($arResult['ITEMS'])>0){?>
<h3 class="h3 center"><?=GetMessage('ALLREADY_ENROLLED_TITLE')?></h3>

<div class="table-block">
    <table class="table table-bordered table-striped table-responsive-stack" id="table-1">
        <thead class="thead-dark">
            <tr>
                <th><?= Loc::getMessage('COURSE_ENROLLED_COURSE') ?></th>
                <th><?= Loc::getMessage('COURSE_ENROLLED_FORMAT') ?></th>
                <th><?= Loc::getMessage('COURSE_ENROLLED_EMPL') ?></th>
                <th><?= Loc::getMessage('COURSE_ENROLLED_OGR') ?></th>
                <th><?= Loc::getMessage('COURSE_ENROLLED_DATE') ?></th>
                <th><?= Loc::getMessage('COURSE_ENROLLED_TEST') ?></th>
                <th><?= Loc::getMessage('COURSE_ENROLLED_CANCEL') ?></th>
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
                <td><?=$item['UF_DATE']?$item['UF_DATE']->toString():''?></td>
                <td><?php if($item['IS_HAS_TEST']){?><span class="table-check"><img src="<?=SITE_TEMPLATE_PATH?>/images/table-check.svg" alt=""></span><?php }?></td>
                <td>
                    <a class="cancel unenroll_by_completion" data-id="<?=$item['ID']?>"><span class="icon-cancel"></span></a>
                </td>
            </tr>
        <?php }?>
        </tbody>
    </table>
</div>
<?php }?>