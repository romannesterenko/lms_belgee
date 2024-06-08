<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<h3 class="h3 mobile-hide"><?=GetMessage('PROFILE')?></h3>
<div class="materials-menu move-1 aside-block">
    <?php if (!empty($arResult)):?>
        <ul>
            <?php foreach($arResult as $arItem):
                if($arParams["MAX_LEVEL"] == 1 && $arItem["DEPTH_LEVEL"] > 1)
                    continue;?>
                <?php if($arItem["SELECTED"]):?>
                <li class="active"><a href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?></a></li>
            <?php else:?>
                <li><a href="<?=$arItem["LINK"]?>"><?=$arItem["TEXT"]?></a></li>
            <?php endif?>
            <?php endforeach?>
        </ul>
    <?php endif?>
</div>
