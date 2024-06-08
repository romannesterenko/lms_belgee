<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);?>
<h3 class="h3 center"><?=GetMessage('ADMIN_COURSES_STAT_TITLE')?></h3>

<div class="content-block content-block--margin">

    <div class="row">
        <div class="col-4">
            <div class="total-stat">
                <span class="total-stat__title"><?=GetMessage('ADMIN_COURSES_ACTIVE_NOW_TITLE')?></span>
                <ul>
                    <li><span><?=GetMessage('ADMIN_COURSES_ACTIVE_NOW_NEW_EMP')?></span><a href=""><?=$arResult['ACTIVE_NOW']['NEW_EMPLOYEES']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_ACTIVE_NOW_COUNT_APPS')?></span><a href=""><?=$arResult['ACTIVE_NOW']['COUNT_APPS']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_ACTIVE_NOW_COUNT_MEMBERS')?></span><a href=""><?=$arResult['ACTIVE_NOW']['COUNT_MEMBERS']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_ACTIVE_NOW_COUNT_ALL_ACTIVE')?></span><a href=""><?=$arResult['ACTIVE_NOW']['ALL_ACTIVE_COURSES']?></a></li>
                </ul>
            </div>
        </div>
        <div class="col-4">
            <div class="total-stat">
                <span class="total-stat__title"><?=GetMessage('ADMIN_COURSES_TOTAL_ENROLLED_TITLE')?></span>
                <ul>
                    <li><span><?=GetMessage('ADMIN_COURSES_TOTAL_ENROLLED_ACTIVE_COURSES')?></span><a href=""><?=$arResult['TOTAL_ENROLLED']['ACTIVE_COURSES']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_TOTAL_ENROLLED_ALL_COURSES')?></span><a href=""><?=$arResult['TOTAL_ENROLLED']['ALL_COURSES']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_TOTAL_ENROLLED_MEMBERS_ENROLLED')?></span><a href=""><?=$arResult['TOTAL_ENROLLED']['MEMBERS_ENROLLED']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_TOTAL_ENROLLED_NEW_REGISTRATIONS')?></span><a href=""><?=$arResult['TOTAL_ENROLLED']['NEW_REGISTRATIONS']?></a></li>
                </ul>
            </div>
        </div>
        <div class="col-4">
            <div class="total-stat">
                <span class="total-stat__title"><?=GetMessage('ADMIN_COURSES_NEW_REGISTRATIONS_TITLE')?></span>
                <ul>
                    <li><span><?=GetMessage('ADMIN_COURSES_NEW_REGISTRATIONS_NEW_EMPLOYEES')?></span><a href=""><?=$arResult['NEW_REGISTRATIONS']['NEW_EMPLOYEES']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_NEW_REGISTRATIONS_NEW_APPS')?></span><a href=""><?=$arResult['NEW_REGISTRATIONS']['NEW_APPS']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_NEW_REGISTRATIONS_MEMBERS')?></span><a href=""><?=$arResult['NEW_REGISTRATIONS']['MEMBERS']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_NEW_REGISTRATIONS_ACTIVE_COURSES')?></span><a href=""><?=$arResult['NEW_REGISTRATIONS']['ACTIVE_COURSES']?></a></li>
                </ul>
            </div>
        </div>
        <div class="col-4">
            <div class="total-stat">
                  <span class="total-stat__title"><?=GetMessage('ADMIN_COURSES_ACTIVE_APPS_TITLE')?></span>
                <ul>
                    <li><span><?=GetMessage('ADMIN_COURSES_ACTIVE_APPS_NEW_EMPLOYEES')?></span><a href=""><?=$arResult['ACTIVE_APPS']['NEW_EMPLOYEES']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_ACTIVE_APPS_NEW_APPS')?></span><a href=""><?=$arResult['ACTIVE_APPS']['NEW_APPS']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_ACTIVE_APPS_MEMBERS')?></span><a href=""><?=$arResult['ACTIVE_APPS']['MEMBERS']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_ACTIVE_APPS_ACTIVE_COURSES')?></span><a href=""><?=$arResult['ACTIVE_APPS']['ACTIVE_COURSES']?></a></li>
                </ul>
            </div>
        </div>
        <div class="col-4">
            <div class="total-stat">
                <span class="total-stat__title"><?=GetMessage('ADMIN_COURSES_TRAINING_ATTENDANCE_TITLE')?></span>
                <ul>
                    <li><span><?=GetMessage('ADMIN_COURSES_TRAINING_ATTENDANCE_RECORDED_PARTICIPANTS')?></span><a href=""><?=$arResult['TRAINING_ATTENDANCE']['RECORDED_PARTICIPANTS']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_TRAINING_ATTENDANCE_ATTENDED_TRAININGS')?></span><a href=""><?=$arResult['TRAINING_ATTENDANCE']['ATTENDED_TRAININGS']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_TRAINING_ATTENDANCE_APPROVED_APPLICATIONS')?></span><a href=""><?=$arResult['TRAINING_ATTENDANCE']['APPROVED_APPLICATIONS']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_TRAINING_ATTENDANCE_IN_QUEUE')?></span><a href=""><?=$arResult['TRAINING_ATTENDANCE']['IN_QUEUE']?></a></li>
                </ul>
            </div>
        </div>
        <div class="col-4">
            <div class="total-stat">
                  <span class="total-stat__title"><?=GetMessage('ADMIN_COURSES_CERTS_TITLE')?></span>
                <ul>
                    <li><span><?=GetMessage('ADMIN_COURSES_CERTS_ACTIVE')?></span><a href=""><?=$arResult['CERTS']['ACTIVE']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_CERTS_END')?></span><a href=""><?=$arResult['CERTS']['ENDED']?></a></li>
                    <li><span><?=GetMessage('ADMIN_COURSES_CERTS_OVERDUE')?></span><a href=""><?=$arResult['CERTS']['OVERDUE']?></a></li>
                </ul>
            </div>
        </div>
    </div>

</div>


