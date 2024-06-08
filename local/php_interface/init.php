<?php

ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

/*error_reporting(E_ERROR);
ini_set('display_errors', 'On');
ini_set("error_log","/home/u520251/lms.geely-motors.com/www/log_php.txt");*/

if (!function_exists('custom_mail') && COption::GetOptionString("webprostor.smtp", "USE_MODULE") == "Y")
{
    function custom_mail($to, $subject, $message, $additional_headers='', $additional_parameters='')
    {
        if(CModule::IncludeModule("webprostor.smtp"))
        {
            $smtp = new CWebprostorSmtp("s1");
            $result = $smtp->SendMail($to, $subject, $message, $additional_headers, $additional_parameters);

            if($result)
                return true;
            else
                return false;
        }
    }
}
// composer for local
if (file_exists($_SERVER["DOCUMENT_ROOT"]."/local/vendor/autoload.php")) {
	require_once($_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php");
}

require_once($_SERVER["DOCUMENT_ROOT"].'/local/php_interface/lib/fpdf/fpdf.php');
require_once($_SERVER["DOCUMENT_ROOT"].'/local/php_interface/lib/fpdi/autoload.php');


//подключение своих функций
require_once ($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/functions.php");
//подключение констант
require_once ($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/constants.php");
//подключение собственных классов
require_once ($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/lib/autoload.php");
//подключение обработчиков событий
require_once ($_SERVER["DOCUMENT_ROOT"] . "/local/php_interface/include/event_handlers.php"); 
