<?php

use Bitrix\Main\Localization\Loc;

$aMenuLinks = Array(
	Array(
		Loc::getMessage('COMMON_DATA'),
		"/profile/",
		Array(), 
		Array(), 
		"" 
	),
	Array(
		Loc::getMessage('LOGOUT'),
		"/profile/?logout=yes&".bitrix_sessid_get(),
		Array(),
		Array(),
		""
	),
);