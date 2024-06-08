<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);?>
<div class="content-block content-block--margin">
    <h4 class="h4"><?=GetMessage('CABINET_SHOWMATERIALS_TITLE')?></h4>
    <div class="settings">
        <?php foreach($arResult['ITEMS'] as $value){?>
            <div class="item">
                <div class="checkbox-item">
                    <input type="checkbox" id="setting-<?=$value['ID']?>" name="setting" value="<?=$value['ID']?>" <?=$value['CHECKED']?> />
                    <label for="setting-<?=$value['ID']?>"><?=$value['VALUE']?></label>
                </div>
            </div>
        <?php }?>
    </div>
</div>
