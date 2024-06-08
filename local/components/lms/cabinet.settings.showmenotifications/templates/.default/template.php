<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);?>
<div class="content-block content-block--margin">
    <h3 class="h3 lowercase"><?=GetMessage('CABINET_SHOWMENOTIFICATIONS_TITLE')?></h3>
    <div class="profile-edit profile-edit--top-border">
        <div class="profile-edit-item">
            <div class="profile-edit-item__label"><?=GetMessage('CABINET_SHOWMENOTIFICATIONS_FIELD')?></div>
            <div class="profile-edit-item__content">
                <div class="checkbox-item show_me_nots">
                    <input type="checkbox" id="setting-show-me-notifies" name="setting-notice" <?=$arResult['IS_CHECKED']?> />
                    <label for="setting-show-me-notifies"></label>
                </div>
            </div>
        </div>
        <div class="profile-edit-item show_me_nots_message hidden"></div>
        <div class="btn-center margin flex-start">
            <button class="btn save_send_me_nots"><?=GetMessage('CABINET_SHOWMENOTIFICATIONS_SAVE_BUTTON')?></button>
        </div>
    </div>


</div>