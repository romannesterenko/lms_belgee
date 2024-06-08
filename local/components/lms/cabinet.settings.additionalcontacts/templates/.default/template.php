<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
$this->setFrameMode(true);
global $USER;
?>
<div class="content-block content-block--margin">
    <h3 class="h3 lowercase"><?=GetMessage('CABINET_ADDITIONALCONTACTS_TITLE')?></h3>
    <?php if($_REQUEST['edit']=='Y'){?>
        <form class="profile-edit profile-edit--top-border" id="additional_contacts">
            <input type="hidden" name="user_id" value="<?=$USER->GetID()?>">
            <div class="profile-edit-item">
                <div class="profile-edit-item__label"><?=GetMessage('CABINET_ADDITIONALCONTACTS_PHONE')?></div>
                <div class="profile-edit-item__content">
                    <input type="text" value="<?=$arResult['USER']['PERSONAL_MOBILE']?>" name="PERSONAL_MOBILE">
                </div>
            </div>
            <div class="profile-edit-item">
                <div class="profile-edit-item__label"><?=GetMessage('CABINET_ADDITIONALCONTACTS_EMAIL')?></div>
                <div class="profile-edit-item__content">
                    <input type="text" value="<?=$arResult['USER']['EMAIL']?>" name="EMAIL">
                </div>
            </div>
            <div class="profile-edit-item">
                <div class="profile-edit-item__label"><?=GetMessage('CABINET_ADDITIONALCONTACTS_ZOOM')?></div>
                <div class="profile-edit-item__content">
                    <input type="text" value="<?=$arResult['USER']['UF_ZOOM_LOGIN']?>" name="UF_ZOOM_LOGIN">
                </div>
            </div>
            <div class="profile-edit-item">
                <div class="profile-edit-item__label"><?=GetMessage('CABINET_ADDITIONALCONTACTS_TELEGRAM')?></div>
                <div class="profile-edit-item__content">
                    <input type="text" value="<?=$arResult['USER']['UF_TELEGRAM']?>" name="UF_TELEGRAM">
                </div>
            </div>
            <div class="profile-edit-item message_info hidden"></div>
            <div class="btn-center margin flex-start">
                <button class="btn save_additional_contacts"><?=GetMessage('CABINET_ADDITIONALCONTACTS_SAVE_BUTTON')?></button>
            </div>
        </form>
    <?php }else{?>
        <div class="profile-edit profile-edit--top-border">
            <div class="profile-edit-item">
                <div class="profile-edit-item__label"><?=GetMessage('CABINET_ADDITIONALCONTACTS_PHONE')?></div>
                <div class="profile-edit-item__content">
                    <?=$arResult['USER']['PERSONAL_MOBILE']?>
                </div>
            </div>
            <div class="profile-edit-item">
                <div class="profile-edit-item__label"><?=GetMessage('CABINET_ADDITIONALCONTACTS_EMAIL')?></div>
                <div class="profile-edit-item__content">
                    <?=$arResult['USER']['EMAIL']?>
                </div>
            </div>
            <div class="profile-edit-item">
                <div class="profile-edit-item__label"><?=GetMessage('CABINET_ADDITIONALCONTACTS_ZOOM')?></div>
                <div class="profile-edit-item__content">
                    <?=$arResult['USER']['UF_ZOOM_LOGIN']?>
                </div>
            </div>
            <div class="profile-edit-item">
                <div class="profile-edit-item__label"><?=GetMessage('CABINET_ADDITIONALCONTACTS_TELEGRAM')?></div>
                <div class="profile-edit-item__content">
                    <?=$arResult['USER']['UF_TELEGRAM']?>
                </div>
            </div>
            <div class="btn-center margin flex-start">
                <a class="btn " href="/cabinet/settings/?edit=Y"><?=GetMessage('CABINET_ADDITIONALCONTACTS_EDIT_BUTTON')?></a>
                <?php /*<button class="btn "><?=GetMessage('CABINET_ADDITIONALCONTACTS_SAVE_BUTTON')?></button>*/?>
            </div>
        </div>
    <?php }?>
</div>