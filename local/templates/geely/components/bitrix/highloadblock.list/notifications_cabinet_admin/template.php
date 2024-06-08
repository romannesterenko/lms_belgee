<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;
/** @var array $arResult */
/** @var array $arParams */
?>
    <div class="course-information">
        <?php foreach ($arResult['rows'] as $row){?>
            <a href="#" class="notification-item">
                  <span class="notification-item__icon">
                    <svg class="icon" width="36px" height="31px">
                      <use xlink:href="#education-icon"></use>
                    </svg>
                  </span>
                    <span class="notification-item__content">
                    <span class="notification-item__date"><?=$row['UF_DATE']?></span>
                    <span class="notification-item__text"><?=$row['UF_TEXT']?></span>
        </span>
            <span class="notification-item__status <?=$row['ICON']?>">
                <span class="icon">!</span>
                <?=$row['UF_TYPE']?>
            </span>
                <span class="notification-item__delete "></span>
            </a>
        <?php }?>
    </div>
<?php /*
if ($arParams['ROWS_PER_PAGE'] > 0) {
    $APPLICATION->IncludeComponent("bitrix:main.pagenavigation", "navigation", Array(
        "NAV_OBJECT" => $arResult["nav_object"],
        "SEF_MODE" => "N"
    ),
        false
    );
}*/?>