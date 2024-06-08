<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);
global $APPLICATION;?>
<h3 class="h3"><?=GetMessage('COURSES_SECTION_TITLE');?></h3>

<div class="my-course-aside aside-block">
    <ul>
<!--        <li <?=$APPLICATION->GetCurPage()=='/courses/new/'?'class="active"':''?>>
            <a href="/courses/new/">
                <span class="icon icon-education"></span>
                <?=GetMessage('COURSES_SECTION_NEW_COURSES');?>
            </a>
            <?php if($arResult['NEW_COURSES']>0){?><span class="my-course-number"><?=$arResult['NEW_COURSES']?></span><?php }?>
        </li> -->
        <li <?=$APPLICATION->GetCurPage()=='/cabinet/courses/assigned/'?'class="active"':''?>>
            <a href="/cabinet/courses/assigned/">
                <span class="icon icon-education"></span>
                <?=GetMessage('COURSES_SECTION_ASSIGNED_COURSES');?>
            </a>
            <?php if($arResult['ASSIGNED_COURSES']>0){?><span class="my-course-number"><?=$arResult['ASSIGNED_COURSES']?></span><?php }?>
        </li>
        <li <?=$APPLICATION->GetCurPage()=='/cabinet/courses/enrollments/'?'class="active"':''?>>
            <a href="/cabinet/courses/enrollments/">
                <span class="icon icon-education"></span>
                <?=GetMessage('COURSES_SECTION_ENROLLMENTS');?>
            </a>
        </li>
        <li <?=$APPLICATION->GetCurPage()=='/cabinet/courses/completions/'?'class="active"':''?>>
            <a href="/cabinet/courses/completions/">
                <span class="icon icon-education"></span>
                <?=GetMessage('COURSES_SECTION_COMPLETIONS');?>
            </a>
        </li>
        <li <?=$APPLICATION->GetCurPage()=='/cabinet/courses/completed/'?'class="active"':''?>>
            <a href="/cabinet/courses/completed/">
                <span class="icon icon-education"></span>
                <?=GetMessage('COURSES_SECTION_COMPLETED_COURSES');?>
            </a>
            <?php if($arResult['COMPLETED_COURSES']>0){?><span class="my-course-number"><?=$arResult['COMPLETED_COURSES']?></span><?php }?>
        </li>
    </ul>
</div>
