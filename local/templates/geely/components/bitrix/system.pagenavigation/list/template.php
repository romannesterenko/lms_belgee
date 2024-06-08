<?php use Bitrix\Main\Localization\Loc;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?php /** @var array $arResult */
?>
<div class="pagination">
    <div class="pagination__nav">
        <a href="" class="prev"><span class="icon icon-arrow-link"></span><?= Loc::getMessage('PREVIOUS') ?></a>
        <a href="" class="next"><?= Loc::getMessage('NEXT') ?><span class="icon icon-arrow-link"></span></a>
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
</div>
