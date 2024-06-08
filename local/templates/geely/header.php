<?php
if (!defined ('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
	die();
use \Bitrix\Main\Page\Asset;
CJSCore::Init(array("jquery"));
global $APPLICATION, $USER;
$user = \Models\User::find($USER->GetID(), ['UF_DEALER']);
/*if( (int)$_REQUEST['auth_user_id']>0 ){
    $USER->Authorize((int)$_REQUEST['auth_user_id']);
    LocalRedirect("/");
}*/
if(!$user['UF_DEALER']>0) {
    $USER->Logout();
    LocalRedirect("/?type_no_login=no_dealer");
}?>
<!doctype html>
<html>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, user-scalable=no">
    <title><?php $APPLICATION->ShowTitle();?></title>
    <?php
        $APPLICATION->ShowHead();
        Asset::getInstance()->addCss(SITE_TEMPLATE_PATH.'/css/styles.css?1');
        Asset::getInstance()->addCss('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.css');
        Asset::getInstance()->addCss('https://cdnjs.cloudflare.com/ajax/libs/jquery-modal/0.9.1/jquery.modal.min.css');
        Asset::getInstance()->addCss('https://cdnjs.cloudflare.com/ajax/libs/air-datepicker/2.2.3/css/datepicker.css');
        Asset::getInstance()->addCss(SITE_TEMPLATE_PATH.'/css/custom.css?1');
    ?>
</head>
<body>
<?php
if($_GET["font"]=="new"):
?>
<style>
    body {
        font-family: "Open Sans Cyr";
    }
</style>
<?php endif;?>
<?php
if($USER->IsAuthorized()){
    if($USER->GetID()==2)
        $APPLICATION->ShowPanel();
?>
<header>
    <div class="container">
        <div class="header-content">
            <a href="/" class="logo"><img src="<?=\Bitrix\Main\Config\Option::get('common.settings', 'header_logo')?>" alt=""></a>

            <nav class="main-menu">
                <?php $APPLICATION->IncludeComponent(
                    "bitrix:menu",
                    "top_menu",
                    array(
                        "ROOT_MENU_TYPE" => "top",
                        "MAX_LEVEL" => "1",
                        "CHILD_MENU_TYPE" => "top",
                        "USE_EXT" => "N",
                        "DELAY" => "N",
                        "ALLOW_MULTI_SELECT" => "Y",
                        "MENU_CACHE_TYPE" => "N",
                        "MENU_CACHE_TIME" => "3600",
                        "MENU_CACHE_USE_GROUPS" => "Y",
                        "MENU_CACHE_GET_VARS" => array(
                        ),
                        "COMPONENT_TEMPLATE" => "top_menu"
                    ),
                    false
                );?>
            </nav>

            <div class="header-content__right">
                <div class="header-user">
                    <?php if($USER->IsAuthorized()){?>
                        <a href="/profile/">
                            <span class="icon icon-profile"></span>
                            <?=\Helpers\UserHelper::getFullName();?>
                        </a>
                    <?php }else{?>
                        <a href="#">
                            <?=GetMessage('HEADER_LOGIN')?>
                        </a>
                    <?php }?>
                </div>
                <a class="burger"><span></span></a>
            </div>
        </div>
    </div>
</header>

<main>
    <div class="<?=$_REQUEST['report_id']>0?'':'container'?>">
<?php }?>