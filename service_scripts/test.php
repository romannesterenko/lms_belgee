<?php

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
const NEED_AUTH = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$id = 10;
//$courses = \Models\Course::getPPOList();
foreach ($courses as $course) {
    \Models\Course::resetLastNumber($course['ID']);
    $list = (new \Teaching\CourseCompletion())->get(['UF_COURSE_ID' => $course['ID'], 'UF_IS_COMPLETE' => 1]);
    foreach ($list as $completion) {
        \Helpers\Pdf::generateCertFromCompletionId($completion['ID']);
    }
}
dump($courses);
die();
$list = (new \Teaching\CourseCompletion())->get(['UF_COURSE_ID' => 101, 'UF_IS_COMPLETE' => 1]);
dump($list);
foreach ($list as $course) {
    //\Helpers\Pdf::generateCertFromCompletionId($course['ID']);
}


//\Helpers\Pdf::generateCertFromCompletionId(7741);
dump($id);

die();
$code = "501504204-3";
$dealer = \Models\Dealer::getIdByCode($code);

$users = \Models\User::get(['UF_DEALER' => $dealer], ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'UF_DEALER_CODE']);

foreach ($users as $user) {
    if($user["UF_DEALER_CODE"]!=$code) {
        dump(\Models\User::update($user['ID'], ['UF_DEALER_CODE' => $code]));
    }
}

die();

$course = \Models\Course::find(82, ['ID', 'NAME', 'PROPERTY_CERT_EXP']);

$completions = (new \Teaching\CourseCompletion())->get(['UF_COURSE_ID' => $course['ID'], 'UF_IS_COMPLETE' => 1]);

foreach ($completions as $completion){
    $expired = $course['PROPERTY_CERT_EXP_VALUE']??12;
    //dump($completion);
    dump((string)$completion['ID']);
    //$new_date = $completion['UF_COMPLETED_TIME']->add($expired." months")->format('d.m.Y H:i:s');
    dump((string)$completion['UF_EXPIRED_DATE']);
    \Helpers\Pdf::generateCertFromCompletionId($completion['ID']);
    dump(' ');
    //(new \Teaching\CourseCompletion())->update($completion['ID'], ['UF_EXPIRED_DATE' => $new_date]);
}



dump($course);
//dump($completions);

die();

$certificates = [];

$all_certificates = \Models\Certificate::getList(['>ID' => 0], ['ID', 'NAME', 'CODE', 'PROPERTY_TEACHABLE']);
foreach ($all_certificates as $certificate) {
    if($certificate['CODE'] != "Номер билета")
        $certificates[$certificate['CODE']][] = $certificate;
}
dump($certificates);
die();
foreach ($certificates as $certificate) {
    if (count($certificate) > 1) {
        $used_ids = [];
        $need_id = 0;
        $certificate = array_reverse($certificate);
        foreach($certificate as $one_cert){
            if((int)$one_cert['PROPERTY_TEACHABLE_VALUE'] > 0) {
                $used_ids[] = $one_cert['ID'];
                $need_id = 0;
            } else {
                if(count($used_ids) == 0 && $need_id == 0) {
                    $need_id = $one_cert['ID'];
                }
            }
        }
        //dump($certificate);
        foreach ($certificate as $one_item) {
            if(!in_array($one_item['ID'], $used_ids) && $need_id!=$one_item['ID']){
                //\Models\Certificate::delete($one_item['ID']);
                dump("Удаляем ".$one_item['ID']);
            }
        }
    }
}
die();
$dealers_array = [];
$dealers  = \Models\Dealer::getList(['ACTIVE' => "N"], ['ID', 'ACTIVE', 'NAME', 'CODE']);

foreach ($dealers as $dealer){
    $reserves = \Models\Reserve::get(['UF_DEALER_ID' => $dealer['ID']]);
    if(check_full_array($reserves)) {
        foreach ($reserves as $reserve){
            dump($reserve);
            /*$one_dealer = current(\Models\Dealer::getList(['CODE' => $reserve['UF_DEALER'], 'ACTIVE' => 'Y'], ['ID', 'ACTIVE', 'NAME', 'CODE']));
            if(check_full_array($one_dealer) && $one_dealer['ID'] > 0){
                dump($dealers);
                //\Models\Reserve::update($reserve['ID'], ['UF_DEALER_ID' => $one_dealer['ID']]);
            }*/

        }

    }
}

//dump($dealers);

die();

$directory = $_SERVER["DOCUMENT_ROOT"] . "/ftp1c/in"; // Укажите путь к нужной папке

$reserves = \Models\Invoice::get(['>ID' => 0]);
$dealers = [];
foreach ($reserves as $reserve){
    if(check_full_array($dealers[$reserve['UF_ID_DEALER']])){
        $dealer_id = $dealers[$reserve['UF_ID_DEALER']];
    } else {
        $dealer_id = \Models\Dealer::getIdByCode($reserve['UF_ID_DEALER']);
        $dealers[$reserve['UF_ID_DEALER']] = $dealer_id;
    }
    //\Models\Invoice::update($reserve['ID'], ['UF_DEALER_ID' => $dealer_id]);
}
dump($dealers);
dump($reserves);
die();





$users = \Models\User::getArray(['filter' => ['UF_DEALER' => 9721]]);
$users_array = [];
$completions = [];
foreach ($users as $user){
    $users_array[$user['ID']] = $user;
    $completions = array_merge($completions, (new \Teaching\CourseCompletion)->get(['UF_USER_ID' => $user['ID'], '>=UF_DATE' => "01.01.2024 00:00:01"]));
}



foreach ($completions as $completion){
    $course = \Models\Course::find($completion['UF_COURSE_ID'], ['NAME', "PROPERTY_PAY_METHOD", "PROPERTY_COST"]);
    $cost = $course['PROPERTY_COST_VALUE']?$course['PROPERTY_COST_VALUE']." руб":"Бесплатно";
    $pay_method = '';
    if($course['PROPERTY_COST_VALUE']>0)
        $pay_method = $course['PROPERTY_PAY_METHOD_ENUM_ID'] == 160?". Метод оплаты - <b>С баланса</b>":". Метод оплаты - <b>Сертификат</b>";
    $string = "ID: ".$completion['ID'].". <b>".$users_array[$completion['UF_USER_ID']]['NAME']." ".$users_array[$completion['UF_USER_ID']]['LAST_NAME']."</b> ".\Helpers\DateHelper::getHumanDate($completion['UF_DATE_CREATE'])." записан на курс <b>".$course["NAME"]."</b>. Стоимость курса - <b>".$cost.$pay_method."</b><br/><br/>";
    echo $string;
    if(\Models\Course::isPaid($completion['UF_COURSE_ID'])&&\Models\Course::isBalancePayment($completion['UF_COURSE_ID'])) {
        if($completion["UF_WAS_ON_COURSE"] == 1 || $completion["UF_DIDNT_COM"] == 1) {
            \Models\Invoice::setPaid($completion["ID"]);
        }

        /*$directory = $_SERVER["DOCUMENT_ROOT"] . "/ftp1c/in"; // Укажите путь к нужной папке
39791
        $not_need_create = false;
        if (is_dir($directory)) {

            $files = scandir($directory);

            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $file_array = explode("_", $file);
                    if ((int)$file_array[0] == $completion['ID']) {
                        dump("Удаляем файл");
                        dump($file);
                        //unlink($directory.DIRECTORY_SEPARATOR.$file);
                    }
                    /*if($not_need_create) {
                        break;
                    }*/
                /*}
            }
        }*/
        /*if(!$not_need_create){
            \Models\Invoice::createFromCompletion($completion["ID"]);
            if($completion["UF_WAS_ON_COURSE"] == 1 || $completion["UF_DIDNT_COM"] == 1) {
                \Models\Invoice::setPaid($completion["ID"]);
            }
            dump("Нужно создать");
            dump($completion);
        }*/

    }
}