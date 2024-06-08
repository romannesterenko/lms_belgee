<?php use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION;
/** @var array $arResult */
/** @var array $arParams */
?>
<h2 class="h2"><?= Loc::getMessage('H2_TITLE') ?></h2>
<div class="notifications-content">
    <?php if(count($arResult['rows'])==0){?>
        <p><?= Loc::getMessage('NO_EVENTS') ?></p>
    <?php }else{
        foreach ($arResult['rows'] as $row){?>
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
            </a>
        <?php
        }
    }?>
</div>
<?php
if ($arParams['ROWS_PER_PAGE'] > 0) {
    $APPLICATION->IncludeComponent("bitrix:main.pagenavigation", "navigation", Array(
        "NAV_OBJECT" => $arResult["nav_object"],
        "SEF_MODE" => "N"
    ),
        false
    );
}/*?>
<div class="pagination">
    <div class="pagination__nav">
        <a href="" class="prev"><span class="icon icon-arrow-link"></span>предыдущая</a>
        <a href="" class="next">следующая<span class="icon icon-arrow-link"></span></a>
    </div>
    <div class="pagination__pages">
        <span><a href="" class="active">1</a></span>
        <span><a href="">2</a></span>
        <span><a href="">3</a></span>
        <span><a href="">4</a></span>
        <span><a href="">5</a></span>
        <span><a href="">6</a></span>
        <span><a href="">7</a></span>
        <span><a href="">8</a></span>
    </div>
</div>*/?>