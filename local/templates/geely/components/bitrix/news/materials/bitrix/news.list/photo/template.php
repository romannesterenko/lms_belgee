<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?php if(count($arResult["ITEMS"])>0):?>
	<div class="news-list">
		<b><?=$arResult["NAME"]?></b>
		<table cellpadding="5" cellspacing="0" border="0">
		<tr valign="center">
            <?php foreach($arResult["ITEMS"] as $arItem):?>
			<td align="center">
                <?php if(is_array($arItem["PREVIEW_PICTURE"])):?>
				<a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><img border="0" src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>" width="<?=$arItem["PREVIEW_PICTURE"]["WIDTH"]?>" height="<?=$arItem["PREVIEW_PICTURE"]["HEIGHT"]?>" alt="<?=$arItem["NAME"]?>" title="<?=$arItem["NAME"]?>" /></a>
                <?php else:?>
				&nbsp;
                <?php endif?>
			</td>
            <?php endforeach;?>
		</tr>
		<tr valign="top">
            <?php foreach($arResult["ITEMS"] as $arItem):?>
			<td align="center">
			<a href="<?=$arItem["DETAIL_PAGE_URL"]?>"><?=$arItem["NAME"]?></a>
			</td>
            <?php endforeach;?>
		</tr>
		</table>
	</div>
<?php endif?>
