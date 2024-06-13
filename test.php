<?php

use Teaching\Tests;

const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$questions = Tests::getQuestionsByTest(124007);
dump($questions);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");