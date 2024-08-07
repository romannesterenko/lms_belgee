<?php

use Bitrix\Main\Localization\Loc;

$aMenu = [array(
    'parent_menu' => 'global_menu_settings',
    'sort' => 150,
    'text' => Loc::getMessage('COMMON_SETTINGS_MENU_TITLE_TEXT'),
    'title' => "title",
    'icon' => 'sys_menu_icon',
    'url' => 'settings.php?lang=LANGUAGE_ID&mid=common.settings',
),array(
    'parent_menu' => 'global_menu_settings',
    'sort' => 150,
    'text' => "Регенерация сертификатов",
    'title' => "title",
    'icon' => 'fileman_sticker_icon',
    'url' => '/service_scripts/regenerate_certs.php',
)];

return (!empty($aMenu) ? $aMenu : false);
