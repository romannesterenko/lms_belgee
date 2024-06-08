<?php
/** @var array $arResult */
/** @var array $arParams */
global $courses_for_role_filter;
$roles = \Teaching\Roles::getGenitiveForm(\Helpers\UserHelper::getRoleByCurrentUser());
$arResult['TITLE'] = implode(', ', $roles);
