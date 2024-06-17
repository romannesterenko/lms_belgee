<?php

use Teaching\Tests;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

if(Teaching\Courses::isAllowToEnrollByCountry(423)){
    dump('Можно');
} else {
    dump('Нельзя');
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");