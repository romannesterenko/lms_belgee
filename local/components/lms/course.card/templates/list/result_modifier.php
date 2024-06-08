<?php
/** @var array $arResult */

use Teaching\Courses;
use Teaching\SheduleCourses;

$arResult['SCHEDULES_TO_ENROLL'] = [];
if (!Courses::isFreeSheduleCourse($arResult['ITEM']['ID'])) {
    $arResult['SCHEDULES_TO_ENROLL'] = SheduleCourses::getAvailableByCourseByDate($arResult['ITEM']['ID']);
}