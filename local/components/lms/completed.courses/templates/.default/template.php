<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
$this->setFrameMode(true);
?>
<h3 class="h3">ближайшие курсы</h3>

<div class="upcoming-courses aside-block aside-block--border">
    <div class="upcoming-course">
        <a href="">
            <span class="icon icon-calendar"></span>
            <span class="upcoming-course__content">
                    <span class="upcoming-course__top">
                      <span class="upcoming-course__date">12 ноября</span>
                      <span class="upcoming-course__places"><span>8</span> / 12 мест</span>
                    </span>
                    <span class="upcoming-course__title">«Информация о бренде»</span>
                  </span>
        </a>
    </div>

    <div class="upcoming-course">
        <a href="">
            <span class="icon icon-calendar"></span>
            <span class="upcoming-course__content">
                    <span class="upcoming-course__top">
                      <span class="upcoming-course__date">20 ноября</span>
                      <span class="upcoming-course__status offline">Offine</span>
                    </span>
                    <span class="upcoming-course__title">«Рестайл версия Atlas Pro,
                      основые отличия»</span>
                  </span>
        </a>
    </div>
    <div class="upcoming-course">
        <a href="">
            <span class="icon icon-calendar"></span>
            <span class="upcoming-course__content">
                    <span class="upcoming-course__top">
                      <span class="upcoming-course__date">28 ноября</span>
                      <span class="upcoming-course__status online">Online</span>
                    </span>
                    <span class="upcoming-course__title">«Правила работы
                      с клиентами корпсегмента»</span>
                  </span>
        </a>
    </div>


</div>