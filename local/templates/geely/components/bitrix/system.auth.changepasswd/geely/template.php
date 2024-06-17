<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}
/**
 * @global CMain $APPLICATION
 * @var array $arParams
 * @var array $arResult
 */

//one css for all system.auth.* forms
$APPLICATION->SetAdditionalCSS("/bitrix/css/main/system.auth/flat/style.css");

if($arResult["PHONE_REGISTRATION"])
{
	CJSCore::Init('phone_auth');
}
?>
<div class="authorization-content">
    <span class="logo-title">
        <img src="<?=\Bitrix\Main\Config\Option::get('common.settings', 'common_logo')?>" alt="">
    </span>

    <?php if(!empty($arParams["~AUTH_RESULT"])):
	$text = str_replace(array("<br>", "<br />"), "\n", $arParams["~AUTH_RESULT"]["MESSAGE"]);
?>
	<div class="alert <?=($arParams["~AUTH_RESULT"]["TYPE"] == "OK"? "alert-success":"alert-danger")?>"><?=nl2br(htmlspecialcharsbx($text))?></div>
    <?php endif?>

    <?php if($arResult["SHOW_FORM"]):?>
<div class="authorization">
    <div class="authorization__image"><img src="<?=\Bitrix\Main\Config\Option::get('common.settings', 'logo_auth')?>" alt=""></div>

    <div class="authorization__form">
        <span class="authorization__title"><?=GetMessage("AUTH_CHANGE_PASSWORD")?></span>

	<form method="post" action="<?=$arResult["AUTH_URL"]?>" name="bform">
        <?php if ($arResult["BACKURL"] <> ''): ?>
		<input type="hidden" name="backurl" value="<?=$arResult["BACKURL"]?>" />
        <?php endif ?>
		<input type="hidden" name="AUTH_FORM" value="Y">
		<input type="hidden" name="TYPE" value="CHANGE_PWD">

        <?php if($arResult["PHONE_REGISTRATION"]):?>
        <?php else:?>
        <div class="form-group">
            <label for=""><?=GetMessage("AUTH_LOGIN")?></label>
            <input type="text" class="form-control" placeholder="soloviev@yandex.ru" name="USER_LOGIN" value="<?=$arResult["LAST_LOGIN"]?>" />
        </div>
            <?php if($arResult["USE_PASSWORD"]):?>
        <div class="form-group">
            <label for=""><?php echo GetMessage("system_change_pass_current_pass")?></label>
            <input type="password" class="form-control" placeholder="soloviev@yandex.ru" name="USER_CURRENT_PASSWORD" value="<?=$arResult["USER_CURRENT_PASSWORD"]?>" />
        </div>
            <?php else:?>
        <div class="form-group">
            <label for=""><?=GetMessage("AUTH_CHECKWORD")?></label>
            <input type="text" class="form-control" placeholder="soloviev@yandex.ru" name="USER_CHECKWORD" value="<?=$arResult["USER_CHECKWORD"]?>" />
        </div>
            <?php endif;?>
        <?php endif?>
        <div class="form-group">
            <label for=""><?=GetMessage("AUTH_NEW_PASSWORD_REQ")?></label>
            <input type="password" class="form-control" name="USER_PASSWORD" />
        </div>
        <div class="form-group">
            <label for=""><?=GetMessage("AUTH_NEW_PASSWORD_CONFIRM")?></label>
            <input type="password" class="form-control" name="USER_CONFIRM_PASSWORD" />
        </div>

        <div class="btn-center">
            <input type="submit" class="btn btn--white" name="change_pwd" value="<?=GetMessage("AUTH_CHANGE")?>" />
        </div>

	</form>

<script type="text/javascript">
document.bform.USER_CHECKWORD.focus();
</script>
        </div>
</div>

    <?php endif;?>

</div>

