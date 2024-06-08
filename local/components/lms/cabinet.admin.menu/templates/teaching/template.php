<?php use Teaching\Enrollments;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);
global $APPLICATION;?>
<h3 class="h3"><?=GetMessage('ADMIN_TEACHING_MENU_TITLE_')?></h3>

<div class="side-menu aside-block">
    <ul>
        <li<?=$APPLICATION->GetCurPage()=='/cabinet/common/'?" class='active'":""?>>
            <a href="/cabinet/common/">
                    <span class="icon">
                      <svg width="17px" height="17px">
                        <use xlink:href="#work-table"></use>
                      </svg>
                    </span>
                <?=GetMessage('ADMIN_TEACHING_MENU_WORKSPACE')?>
            </a>
        </li>
        <li<?=$APPLICATION->GetCurPage()=='/cabinet/teaching/training_attendance/'?" class='active'":""?>>
            <a href="/cabinet/teaching/training_attendance/">
                    <span class="icon">
                      <svg width="20px" height="20px">
                        <use xlink:href="#message"></use>
                      </svg>
                    </span>
                <?=GetMessage('ADMIN_TEACHING_MENU_TRAINING_ATTENDANCE')?>
            </a>
            <?php /*
            <span class="side-menu-number">15</span>*/?>
        </li>
        <li<?=$APPLICATION->GetCurPage()=='/cabinet/teaching/approving/'||CSite::InDir('/cabinet/confirmation/')?" class='active'":""?>>
            <a href="/cabinet/teaching/approving/">
                    <span class="icon">
                      <svg width="20px" height="20px">
                        <use xlink:href="#message"></use>
                      </svg>
                    </span>
                <?=GetMessage('ADMIN_TEACHING_MENU_APPROVING')?>
            </a>
            <?php $enrolls = new Enrollments();?>
            <span class="side-menu-number"><?=count($enrolls->getAllNoneApprovedEnrolls());?></span>
        </li>
        <li<?=$APPLICATION->GetCurPage()=='/cabinet/teaching/courses/'||CSite::InDir('/cabinet/teaching/courses/')||CSite::InDir('/cabinet/teaching/course/')?" class='active'":""?>>
            <a href="/cabinet/teaching/courses/">
                    <span class="icon">
                      <svg width="20px" height="20px">
                        <use xlink:href="#report"></use>
                      </svg>
                    </span>
                Прохождение курсов
            </a>
        </li>

    </ul>
</div>


