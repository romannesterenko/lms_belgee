<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?php
global $APPLICATION;
/** @var array $arResult */
if ($arResult['NavPageCount']>$arResult['NavPageNomer']) {?>
    <div class="btn-center">
        <?php if($_REQUEST['cat']=="op" || $_REQUEST['cat']=="ppo") {
            $str = "cat=".$_REQUEST['cat']?>
            <a href="#" class="btn load_elements" data-cur-page = "<?=$arResult['NavPageNomer']?>" data-max-page = "<?=$arResult['NavPageCount']?>" data-default-url = "<?=$APPLICATION->GetCurPage()?>?<?php echo $str?>&" data-url = "<?=$APPLICATION->GetCurPage()."?".$str."&PAGEN_1=".++$arResult['NavPageNomer']?>"><?=GetMessage('LOAD_MORE')?></a>
        <?php } else {?>
            <a href="#" class="btn load_elements" data-cur-page = "<?=$arResult['NavPageNomer']?>" data-max-page = "<?=$arResult['NavPageCount']?>" data-default-url = "<?=$APPLICATION->GetCurPage()?>?" data-url = "<?=$APPLICATION->GetCurPage()."?PAGEN_1=".++$arResult['NavPageNomer']?>"><?=GetMessage('LOAD_MORE')?></a>
        <?php }?>
    </div>
<?php }?>