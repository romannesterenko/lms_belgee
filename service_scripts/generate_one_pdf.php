<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;

\Helpers\Pdf::generateCertFromCompletionId(31871);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");