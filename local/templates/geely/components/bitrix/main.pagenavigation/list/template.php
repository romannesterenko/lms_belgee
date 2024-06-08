<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/** @var array $arParams */
/** @var array $arResult */
/** @var CBitrixComponentTemplate $this */

/** @var PageNavigationComponent $component */
$component = $this->getComponent();

$this->setFrameMode(true);

$colorSchemes = array(
	"green" => "bx-green",
	"yellow" => "bx-yellow",
	"red" => "bx-red",
	"blue" => "bx-blue",
);
$colorScheme = $colorSchemes[$arParams["TEMPLATE_THEME"]] ?? "";
?>

<div class="pagination">
    <?php if($arResult["REVERSED_PAGES"] === true):?>

        <?php if ($arResult["CURRENT_PAGE"] < $arResult["PAGE_COUNT"]):?>
            <?php if (($arResult["CURRENT_PAGE"]+1) == $arResult["PAGE_COUNT"]):?>
			<li class="bx-pag-prev"><a href="<?=htmlspecialcharsbx($arResult["URL"])?>"><span><?php echo GetMessage("round_nav_back")?></span></a></li>
            <?php else:?>
			<li class="bx-pag-prev"><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]+1))?>"><span><?php echo GetMessage("round_nav_back")?></span></a></li>
            <?php endif?>
			<li class=""><a href="<?=htmlspecialcharsbx($arResult["URL"])?>"><span>1</span></a></li>
        <?php else:?>
			<li class="bx-pag-prev"><span><?php echo GetMessage("round_nav_back")?></span></li>
			<li class="bx-active"><span>1</span></li>
        <?php endif?>

        <?php
	$page = $arResult["START_PAGE"] - 1;
	while($page >= $arResult["END_PAGE"] + 1):
	?>
        <?php if ($page == $arResult["CURRENT_PAGE"]):?>
			<li class="bx-active"><span><?=($arResult["PAGE_COUNT"] - $page + 1)?></span></li>
    <?php else:?>
			<li class=""><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($page))?>"><span><?=($arResult["PAGE_COUNT"] - $page + 1)?></span></a></li>
    <?php endif?>

        <?php $page--?>
    <?php endwhile?>

        <?php if ($arResult["CURRENT_PAGE"] > 1):?>
            <?php if($arResult["PAGE_COUNT"] > 1):?>
			<li class=""><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate(1))?>"><span><?=$arResult["PAGE_COUNT"]?></span></a></li>
            <?php endif?>
			<li class="bx-pag-next"><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]-1))?>"><span><?php echo GetMessage("round_nav_forward")?></span></a></li>
        <?php else:?>
            <?php if($arResult["PAGE_COUNT"] > 1):?>
			<li class="bx-active"><span><?=$arResult["PAGE_COUNT"]?></span></li>
            <?php endif?>
			<li class="bx-pag-next"><span><?php echo GetMessage("round_nav_forward")?></span></li>
        <?php endif?>

    <?php else:?>
    <div class="pagination__nav">
        <?php if ($arResult["CURRENT_PAGE"] > 2):?>
            <a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]-1))?>" class="prev"><span class="icon icon-arrow-link"></span><?php echo GetMessage("round_nav_back")?></a>
        <?php else:?>
            <a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]-1))?>" class="prev"><span class="icon icon-arrow-link"></span><?php echo GetMessage("round_nav_back")?></a>
        <?php endif?>
        <?php if($arResult["CURRENT_PAGE"] < $arResult["PAGE_COUNT"]):?>
            <a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["CURRENT_PAGE"]+1))?>" class="next"><?php echo GetMessage("round_nav_forward")?><span class="icon icon-arrow-link"></span></a>
        <?php else:?>
            <a href="#" class="next"><?php echo GetMessage("round_nav_forward")?><span class="icon icon-arrow-link"></span></a>
        <?php endif?>
    </div>
    <div class="pagination__pages">
        <?php if ($arResult["CURRENT_PAGE"] > 1):?>
        <span><a href="<?=htmlspecialcharsbx($arResult["URL"])?>">1</a></span>
        <?php else:?>
        <span><a href="#"  class="active">1</a></span>
        <?php endif?>

        <?php $page = $arResult["START_PAGE"] + 1;
	while($page <= $arResult["END_PAGE"]-1):?>
        <?php if ($page == $arResult["CURRENT_PAGE"]):?>
            <span><a href="#"  class="active"><?=$page?></a></span>
        <?php else:?>
            <span><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($page))?>"><?=$page?></a></span>
        <?php endif?>
        <?php $page++?>
    <?php endwhile?>

        <?php if($arResult["CURRENT_PAGE"] < $arResult["PAGE_COUNT"]):?>
            <?php if($arResult["PAGE_COUNT"] > 1):?>
            <span><a href="<?=htmlspecialcharsbx($component->replaceUrlTemplate($arResult["PAGE_COUNT"]))?>"><?=$arResult["PAGE_COUNT"]?></a></span>
            <?php endif?>
        <?php else:?>
            <?php if($arResult["PAGE_COUNT"] > 1):?>
            <span><a href="#"  class="active"><?=$arResult["PAGE_COUNT"]?></a></span>
            <?php endif?>
        <?php endif?>
    </div>
    <?php endif?>

</div>
