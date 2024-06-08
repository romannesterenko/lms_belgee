<?php use Bitrix\Main\Localization\Loc;
use Teaching\Enrollments;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);
$enrollments = new Enrollments();
?>
<h3 class="h3 center"><?= Loc::getMessage('H3_TITLE') ?></h3>

<div class="table-block">
    <table class="table table-bordered table-striped table-responsive-stack" id="table-2">
        <thead class="thead-dark">
            <tr>
                <th><?= Loc::getMessage('COURSES_MUST_COURSE') ?></th>
                <th><?= Loc::getMessage('COURSES_MUST_TYPE') ?></th>
                <th><?= Loc::getMessage('COURSES_MUST_EMPL') ?></th>
                <th><?= Loc::getMessage('COURSES_MUST_OGR') ?></th>
                <th><?= Loc::getMessage('COURSES_MUST_DATE') ?></th>
                <th><?= Loc::getMessage('COURSES_MUST_TEST') ?></th>
                <th><?= Loc::getMessage('COURSES_MUST_ENROLL') ?></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($arResult['ITEMS'] as $item){?>
            <tr>
                <td><a href="<?=$item['CODE']?>"><?=$item['NAME']?></a></td>
                <td>
                    <span class="online"> online</span>
                </td>
                <td>
                    <span class="table-place"><span><a href="">8</a> </span> / 20</span>
                </td>
                <td></td>
                <td></td>
                <td><span class="table-check"><img src="images/table-check.svg" alt=""></span></td>
                <td>
                    <a href="" class="underline"><?= Loc::getMessage('COURSES_MUST_ENROLL') ?></a>
                </td>
            </tr>
        <?php }?>
        </tbody>
    </table>
    <div class="content-show-link">
        <a href="">
            <?= Loc::getMessage('COURSES_MUST_SHOW_ALL') ?>
            <span class="icon icon-arrow-link"></span>
        </a>
    </div>
</div>