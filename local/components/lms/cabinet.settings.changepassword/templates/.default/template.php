<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);?>
<div class="content-block content-block--margin">

    <h3 class="h3 lowercase"><?=GetMessage('CABINET_CHANGE_PASSWORD_TITLE')?></h3>

    <div class="profile-edit profile-edit--top-border">
        <div class="profile-edit-item">
            <div class="profile-edit-item__label"><?=GetMessage('CABINET_OLD_PASSWORD_FIELD')?></div>
            <div class="profile-edit-item__content">
                <input type="password" value="парольпароль" name="old_pass">
            </div>
        </div>

        <div class="profile-edit-item">
            <div class="profile-edit-item__label"><?=GetMessage('CABINET_NEW_PASSWORD_FIELD')?></div>
            <div class="profile-edit-item__content">
                <input type="password" name="new_pass">
            </div>
        </div>

        <div class="profile-edit-item">
            <div class="profile-edit-item__label"><?=GetMessage('CABINET_REPEAT_NEW_PASSWORD_FIELD')?></div>
            <div class="profile-edit-item__content">
                <input type="password" name="renew_pass">
            </div>
        </div>
        <div class="profile-edit-item message_info hidden"></div>
        <div class="btn-center margin flex-start">
            <button class="btn send_change_password"><?=GetMessage('CABINET_SAVE_BUTTON')?></button>
        </div>
    </div>

</div>