<?php

use Teaching\CourseCompletion;
use Teaching\Enrollments;
use Teaching\Roles;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;

define("LOG_FILENAME", $_SERVER["DOCUMENT_ROOT"]."/log.txt");
define("MADELINE_ALLOW_COMPOSER", false);
//авторизация по E-mail

AddEventHandler("main", "OnEndBufferContent", "ChangeHTMLContent");

function ChangeHTMLContent(&$content)
{

    global $USER, $APPLICATION;
    if ($USER->IsAuthorized() && $USER->GetID() == 8344) {
        if(!CSite::InDir('/bitrix/admin/')) {
            $html = $content;
            $replacements = include($_SERVER["DOCUMENT_ROOT"] . "/upload/replace.php");
            foreach ($replacements as $search => $replace) {
                $html = str_replace($search, $replace, $html);
            }
            ob_end_clean();
            echo $html;
            die();
        }
    }
}


//Вешаем обработчик на событие создания списка пользовательских свойств OnUserTypeBuildList
AddEventHandler('iblock', 'OnIBlockPropertyBuildList', ['CUserTypeTimesheet', 'GetUserTypeDescription']);
AddEventHandler('iblock', 'OnIBlockPropertyBuildList', ['CUserTypeDealerApps', 'GetUserTypeDescription']);


AddEventHandler("main", "OnBeforeUserLogin", "OnBeforeUserLoginHandler");
function OnBeforeUserLoginHandler(&$arFields) {
    /*if (isset($_POST['USER_LOGIN'])&&filter_var($_POST['USER_LOGIN'], FILTER_VALIDATE_EMAIL)) {
        $filter = Array("EMAIL" => $_POST['USER_LOGIN']);
        $rsUsers = CUser::GetList(($by="id"), ($order="desc"), $filter);
        $res = $rsUsers->Fetch();
        AddMessage2Log($res);
        $arFields["LOGIN"] = $res['LOGIN'];
    }*/
}
AddEventHandler("main", "OnBeforeUserDelete", "OnBeforeUserDeleteHandler");
function OnBeforeUserDeleteHandler($user_id) {
    global $USER, $APPLICATION;
    if ($USER->GetID() != 2) {
        $APPLICATION->throwException("Удаление пользователей запрещено");
        return false;
    }
}
AddEventHandler("iblock", "OnAfterIBlockElementAdd", "SetCountToSections");
AddEventHandler("iblock", "OnAfterIBlockElementAdd", "processFormConstructor");
AddEventHandler("iblock", "OnAfterIBlockElementUpdate", "processFormConstructor");
function SetCountToSections(&$arFields)
{
    if($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getMaterialsIBlock()) {
        $materials_obj = new \Materials\Sections();
        $materials_obj->setCountToSection($arFields['ID']);
    }
}
function processFormConstructor(&$arFields)
{
    if($arFields['IBLOCK_ID'] == 35) {
        Loader::IncludeModule('highloadblock');
        $filter = array(
            'NAME' => 'DealerApplication'.$arFields['ID'],
            'TABLE_NAME' => 'dealer_application'.$arFields['ID']
        );
        $exists = HL\HighloadBlockTable::getList(['filter' => $filter])->fetch();
        if(check_full_array($exists) && $exists['ID'] > 0) {

            $UFObject = 'HLBLOCK_'.$exists['ID'];
            $exists_fields = [];
            $exists_fields_result = CUserTypeEntity::GetList([], ['ENTITY_ID' => $UFObject]);
            while ($propFields = $exists_fields_result->Fetch()){
                $exists_fields[] = $propFields['FIELD_NAME'];
            }
            $new_fields = [
                'UF_USER_ID',
                'UF_DEALER_ID',
                'UF_CREATED_AT',
                'UF_APPROVED_AT',
                'UF_APPROVED_BY',
                'UF_UPDATED_AT',
                'UF_APPROVED',
                'UF_DECLINED'
            ];
            if(check_full_array($arFields['PROPERTY_VALUES'][204])) {
                foreach ($arFields['PROPERTY_VALUES'][204] as $key => $field) {
                    if(!empty($field['VALUE']['FIELD_CODE']))
                        $new_fields[] = 'UF_'.$field['VALUE']['FIELD_CODE'];
                }
            }
            if(check_full_array(array_diff($exists_fields, $new_fields))) {
                foreach(array_diff($exists_fields, $new_fields) as $delete_code){
                    $prp = CUserTypeEntity::GetList([], ['ENTITY_ID' => $UFObject, 'FIELD_NAME' => $delete_code])->Fetch();
                    if (check_full_array($prp)) {
                        (new CUserTypeEntity)->delete($prp['ID']);
                    }
                }
            }


            if(check_full_array($arFields['PROPERTY_VALUES'][204])) {
                $addFields['UF_USER_ID'] = [
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_USER_ID',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => 'N',
                    "EDIT_FORM_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_COLUMN_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_FILTER_LABEL" => Array('ru'=>'', 'en'=>''),
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                ];
                $addFields['UF_DEALER_ID'] = [
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_DEALER_ID',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => 'N',
                    "EDIT_FORM_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_COLUMN_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_FILTER_LABEL" => Array('ru'=>'', 'en'=>''),
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                ];
                $addFields['UF_CREATED_AT'] = [
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_CREATED_AT',
                    'USER_TYPE_ID' => 'datetime',
                    'MANDATORY' => 'N',
                    "EDIT_FORM_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_COLUMN_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_FILTER_LABEL" => Array('ru'=>'', 'en'=>''),
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                ];
                $addFields['UF_APPROVED_AT'] = [
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_APPROVED_AT',
                    'USER_TYPE_ID' => 'datetime',
                    'MANDATORY' => 'N',
                    "EDIT_FORM_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_COLUMN_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_FILTER_LABEL" => Array('ru'=>'', 'en'=>''),
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                ];
                $addFields['UF_APPROVED_BY'] = [
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_APPROVED_BY',
                    'USER_TYPE_ID' => 'string',
                    'MANDATORY' => 'N',
                    "EDIT_FORM_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_COLUMN_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_FILTER_LABEL" => Array('ru'=>'', 'en'=>''),
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                ];
                $addFields['UF_UPDATED_AT'] = [
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_UPDATED_AT',
                    'USER_TYPE_ID' => 'datetime',
                    'MANDATORY' => 'N',
                    "EDIT_FORM_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_COLUMN_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_FILTER_LABEL" => Array('ru'=>'', 'en'=>''),
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                ];
                $addFields['UF_APPROVED'] = [
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_APPROVED',
                    'USER_TYPE_ID' => 'boolean',
                    'MANDATORY' => 'N',
                    "EDIT_FORM_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_COLUMN_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_FILTER_LABEL" => Array('ru'=>'', 'en'=>''),
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                ];
                $addFields['UF_DECLINED'] = [
                    'ENTITY_ID' => $UFObject,
                    'FIELD_NAME' => 'UF_DECLINED',
                    'USER_TYPE_ID' => 'boolean',
                    'MANDATORY' => 'N',
                    "EDIT_FORM_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_COLUMN_LABEL" => Array('ru'=>'', 'en'=>''),
                    "LIST_FILTER_LABEL" => Array('ru'=>'', 'en'=>''),
                    "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                ];
                foreach ($arFields['PROPERTY_VALUES'][204] as $key => $field){
                    $field = $field['VALUE'];
                    if(!empty($field['FIELD_CODE'])) {
                        $addFields['UF_'.$field['FIELD_CODE']] = [
                            'ENTITY_ID' => $UFObject,
                            'FIELD_NAME' => 'UF_'.$field['FIELD_CODE'],
                            'USER_TYPE_ID' => 'string',
                            'MANDATORY' => 'N',
                            "EDIT_FORM_LABEL" => Array('ru'=>$field['NAME'], 'en'=>$field['NAME']),
                            "LIST_COLUMN_LABEL" => Array('ru'=>$field['NAME'], 'en'=>$field['NAME']),
                            "LIST_FILTER_LABEL" => Array('ru'=>$field['NAME'], 'en'=>$field['NAME']),
                            "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                            "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                        ];

                    }
                }
                foreach ($addFields as $addField){
                    (new CUserTypeEntity)->Add($addField);
                }
            }
        } else {
            $arLangs = array(
                'ru' => $arFields['NAME'],
                'en' => $arFields['NAME'],
            );
            $result = HL\HighloadBlockTable::add(array(
                'NAME' => 'DealerApplication' . $arFields['ID'],
                'TABLE_NAME' => 'dealer_application' . $arFields['ID']
            ));
            if ($result->isSuccess()) {
                $id = $result->getId();
                foreach ($arLangs as $lang_key => $lang_val) {
                    HL\HighloadBlockLangTable::add(array(
                        'ID' => $id,
                        'LID' => $lang_key,
                        'NAME' => $lang_val
                    ));
                }
                $UFObject = 'HLBLOCK_'.$id;
                if(check_full_array($arFields['PROPERTY_VALUES'][204])) {
                    $addFields['UF_USER_ID'] = [
                        'ENTITY_ID' => $UFObject,
                        'FIELD_NAME' => 'UF_USER_ID',
                        'USER_TYPE_ID' => 'string',
                        'MANDATORY' => 'N',
                        "EDIT_FORM_LABEL" => Array('ru'=>'', 'en'=>''),
                        "LIST_COLUMN_LABEL" => Array('ru'=>'', 'en'=>''),
                        "LIST_FILTER_LABEL" => Array('ru'=>'', 'en'=>''),
                        "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                        "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    ];
                    $addFields['UF_DEALER_ID'] = [
                        'ENTITY_ID' => $UFObject,
                        'FIELD_NAME' => 'UF_DEALER_ID',
                        'USER_TYPE_ID' => 'string',
                        'MANDATORY' => 'N',
                        "EDIT_FORM_LABEL" => Array('ru'=>'', 'en'=>''),
                        "LIST_COLUMN_LABEL" => Array('ru'=>'', 'en'=>''),
                        "LIST_FILTER_LABEL" => Array('ru'=>'', 'en'=>''),
                        "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                        "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    ];
                    $addFields['UF_CREATED_AT'] = [
                        'ENTITY_ID' => $UFObject,
                        'FIELD_NAME' => 'UF_CREATED_AT',
                        'USER_TYPE_ID' => 'datetime',
                        'MANDATORY' => 'N',
                        "EDIT_FORM_LABEL" => Array('ru'=>'', 'en'=>''),
                        "LIST_COLUMN_LABEL" => Array('ru'=>'', 'en'=>''),
                        "LIST_FILTER_LABEL" => Array('ru'=>'', 'en'=>''),
                        "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                        "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    ];
                    $addFields['UF_APPROVED_AT'] = [
                        'ENTITY_ID' => $UFObject,
                        'FIELD_NAME' => 'UF_APPROVED_AT',
                        'USER_TYPE_ID' => 'datetime',
                        'MANDATORY' => 'N',
                        "EDIT_FORM_LABEL" => Array('ru'=>'', 'en'=>''),
                        "LIST_COLUMN_LABEL" => Array('ru'=>'', 'en'=>''),
                        "LIST_FILTER_LABEL" => Array('ru'=>'', 'en'=>''),
                        "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                        "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    ];
                    $addFields['UF_APPROVED_BY'] = [
                        'ENTITY_ID' => $UFObject,
                        'FIELD_NAME' => 'UF_APPROVED_BY',
                        'USER_TYPE_ID' => 'string',
                        'MANDATORY' => 'N',
                        "EDIT_FORM_LABEL" => Array('ru'=>'', 'en'=>''),
                        "LIST_COLUMN_LABEL" => Array('ru'=>'', 'en'=>''),
                        "LIST_FILTER_LABEL" => Array('ru'=>'', 'en'=>''),
                        "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                        "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    ];
                    $addFields['UF_UPDATED_AT'] = [
                        'ENTITY_ID' => $UFObject,
                        'FIELD_NAME' => 'UF_UPDATED_AT',
                        'USER_TYPE_ID' => 'datetime',
                        'MANDATORY' => 'N',
                        "EDIT_FORM_LABEL" => Array('ru'=>'', 'en'=>''),
                        "LIST_COLUMN_LABEL" => Array('ru'=>'', 'en'=>''),
                        "LIST_FILTER_LABEL" => Array('ru'=>'', 'en'=>''),
                        "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                        "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                    ];
                    foreach ($arFields['PROPERTY_VALUES'][204] as $key => $field){
                        $field = $field['VALUE'];
                        if(!empty($field['FIELD_CODE'])) {
                            $addFields['UF_'.$field['FIELD_CODE']] = [
                                'ENTITY_ID' => $UFObject,
                                'FIELD_NAME' => 'UF_'.$field['FIELD_CODE'],
                                'USER_TYPE_ID' => 'string',
                                'MANDATORY' => 'N',
                                "EDIT_FORM_LABEL" => Array('ru'=>$field['NAME'], 'en'=>$field['NAME']),
                                "LIST_COLUMN_LABEL" => Array('ru'=>$field['NAME'], 'en'=>$field['NAME']),
                                "LIST_FILTER_LABEL" => Array('ru'=>$field['NAME'], 'en'=>$field['NAME']),
                                "ERROR_MESSAGE" => Array('ru'=>'', 'en'=>''),
                                "HELP_MESSAGE" => Array('ru'=>'', 'en'=>''),
                            ];

                        }
                    }
                    foreach ($addFields as $addField){
                        (new CUserTypeEntity)->Add($addField);
                    }
                }
            } else {
                $errors = $result->getErrorMessages();
            }
        }

    }
}
AddEventHandler("iblock", "OnBeforeIBlockElementAdd", "CheckFreeSchedule");
AddEventHandler("iblock", "OnBeforeIBlockElementAdd", "setZoomRedirectUrl");
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", "CheckFreeSchedule");
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", "addElementUpdateLog");
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", "checkDatesBySchedule");
AddEventHandler("iblock", "OnAfterIBlockElementAdd", "GenerateScheduleCodeAfterAdd");
AddEventHandler("iblock", "OnAfterIBlockElementAdd", "addElementAddLog");
AddEventHandler("iblock", "OnBeforeIBlockElementDelete", "addElementDeleteLog");
function CheckFreeSchedule(&$arFields)
{
    if($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getShedulesIBlock()) {
        $course_id = is_array($arFields['PROPERTY_VALUES']['13'])?$arFields['PROPERTY_VALUES']['13']['n0']['VALUE']:$arFields['PROPERTY_VALUES']['COURSE'];
        if($course_id>0&& \Teaching\Courses::isFreeSheduleCourse($course_id)) {
            global $APPLICATION;
            $APPLICATION->throwException("Создание расписания к этому курсу не возможно. Курс предназначен для прохождения без расписаний. Измените тип курса, а затем получите возможность создавать для него расписания");
            return false;
        }
    }
}
AddEventHandler("iblock", "OnBeforeIBlockElementUpdate", "checkDealerCode");
function checkDealerCode(&$arFields) {
    if($arFields['IBLOCK_ID'] == \Models\Dealer::getIblockId()) {
        if (\Helpers\EventHandlerHelper::wasChanged($arFields, 'CODE')) {
            $users = \Models\User::get(['UF_DEALER' => $arFields['ID']], ['ID', 'NAME', 'LAST_NAME', 'UF_DEALER', 'UF_DEALER_CODE']);
            foreach ($users as $user) {
                if($user["UF_DEALER_CODE"]!=$arFields['CODE']) {
                    \Models\User::update($user['ID'], ['UF_DEALER_CODE' => $arFields['CODE']]);
                }
            }
        }
    }
}
function setZoomRedirectUrl(&$arFields)
{
    if($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getZoomAccountsIBlock()) {
        $arFields['PROPERTY_VALUES']['REDIRECT_URL'] = 'https://lms.geely-motors.com/zoom/'.$arFields['CODE'].'/check.php';
    }
}
function GenerateScheduleCodeAfterAdd(&$arFields)
{
    if($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getShedulesIBlock()) {
        $code = \Teaching\SheduleCourses::generateCode($arFields);
        \Teaching\SheduleCourses::setCode($arFields['ID'], $code);
    }
}
function GenerateScheduleCodeBeforeUpdate(&$arFields)
{
    if($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getShedulesIBlock()) {
        $code = \Teaching\SheduleCourses::generateCode($arFields);
        if($arFields['CODE']!=$code) {
            $arFields['CODE']=$code;
        }
    }
}

//операции с телегой
AddEventHandler("main", "OnBeforeUserAdd", "checkOPRoles");
AddEventHandler("main", "OnBeforeUserAdd", "checkDealerRoles");
AddEventHandler("main", "OnBeforeUserUpdate", "checkOPRoles");
AddEventHandler("main", "OnBeforeUserUpdate", "checkDealerRoles");
AddEventHandler("main", "OnBeforeUserUpdate", "checkDealers");
AddEventHandler("main", "OnBeforeUserUpdate", "addUserUpdateLog");
AddEventHandler("main", "OnAfterUserAdd", "AddRecruit");
AddEventHandler("main", "OnBeforeUserAdd", "setDC");



AddEventHandler("main", "OnAdminListDisplay", "MyOnAdminListDisplay");
function MyOnAdminListDisplay(&$list)
{
    if ($list->table_id=="tbl_course_completion") {//если это страница списка заказов, для других страниц админки будет   свой table_id - чтобы его узнать, распечатайте входящий массив $list на нужной странице
        //dump($list->aRows);
        foreach ($list->aRows as $row){ // здесь мы вклиниваемся в контекстное меню каждой строки таблицы

            if($row->aFields["UF_IS_COMPLETE"]['view']['value'] == 'да') {
                $row->aActions["all_orders"]["ICON"] = "seo_adv_menu";
                $row->aActions["all_orders"]["TEXT"] = "Открыть сертификат";
                $row->aActions["all_orders"]["ACTION"] = "BX.adminPanel.Redirect([], '/upload/certificates/new/82/50352.pdf', event);";
            }
      }
      // $list->arActions["status_draft"] = "Все заказы пользователя"; // а здесь попадаем в меню групповых действий  над элементами над элементами
    }
}




function checkDealerRoles($arFields) {

    global $USER;
    $dealer_id = $arFields['UF_DEALER']??0;
    if($dealer_id == 0 && $arFields['ID'] > 0)
        $dealer_id = \Models\Dealer::getByEmployee($arFields['ID']);
        if($dealer_id > 0) {
            $roles = $arFields['UF_ROLE']??[];
            foreach ($roles as $role_id) {
                $rolesCount = Roles::getMaxOnDealer($role_id);
                if ($rolesCount > 0) {
                    $existsDealerRoleUsers = \Models\User::get(['UF_ROLE' => $role_id, 'UF_DEALER' => $dealer_id, 'ACTIVE' => 'Y']);
                    $currentCount = count($existsDealerRoleUsers);
                    if(!check_full_array($existsDealerRoleUsers[$arFields['ID']]))
                        $currentCount++;
                    if( $currentCount > $rolesCount ) {
                        $role = current(Roles::getRolesList(['ID' => $role_id], ['ID', 'NAME']));
                        global $APPLICATION;
                        $APPLICATION->throwException("Ошибка! Присвоение сотруднику роли \"".$role['NAME']."\" невозможно, так как в дилерском центре может быть только ".$rolesCount." ".\Helpers\StringHelpers::plural($rolesCount, ["сотрудник", "сотрудника", "сотрудников"])." с этой ролью.");
                        return false;
                    }

                }
            }
        }
}

function setDC(&$arFields){
    if((int)$arFields['UF_DEALER']>0){
        $dealer = \Models\Dealer::find((int)$arFields['UF_DEALER'], ['ID', 'NAME', 'CODE']);
        if(check_full_array($dealer)){
            $arFields['UF_DEALER_CODE'] = $dealer['CODE'];
            $arFields['UF_DEALER_NAME'] = $dealer['NAME'];
        }
    }
}

$eventManager = \Bitrix\Main\EventManager::getInstance();

//Лог создание заявки
$eventManager->addEventHandler('', 'CourseRegistrationOnBeforeAdd', 'CourseRegistrationCheckUser');
$eventManager->addEventHandler('', 'CourseRegistrationOnAfterAdd', 'CourseRegistrationAdd');
function CourseRegistrationCheckUser(\Bitrix\Main\Entity\Event $event) {
    $arFields = $event->getParameter("fields");
    if((int)$arFields['UF_USER_ID'] == 0)
        return false;
    if((int)$arFields['UF_COURSE_ID'] == 0)
        return false;
}
function CourseRegistrationAdd(\Bitrix\Main\Entity\Event $event) {
    global $USER;
    // получаем массив полей хайлоад блока
    $arFields = $event->getParameter("fields");

    \Helpers\Log::add(30, 37, $event->getParameter("id"), $USER->GetID(), [], $arFields);
}

//Лог создание прохождения
$eventManager->addEventHandler('', 'CourseCompletionOnAfterAdd', 'CourseCompletionAdd');
function CourseCompletionAdd(\Bitrix\Main\Entity\Event $event) {
    global $USER;
    // получаем массив полей хайлоад блока
    $arFields = $event->getParameter("fields");
    \Helpers\Log::add(31, 37, $event->getParameter("id"), $USER->GetID(), [], $arFields);
}

//Лог изменение заявки
$eventManager->addEventHandler('', 'CourseRegistrationOnUpdate', 'CourseRegistrationUpdate');
function CourseRegistrationUpdate(\Bitrix\Main\Entity\Event $event) {
    global $USER;
    $id = $event->getParameter("id");
    //id обновляемого элемента
    $id = $id["ID"];
    $enrollments = new \Teaching\Enrollments();
    $fields = $enrollments->getById($id);
    // получаем массив полей хайлоад блока
    $arFields = $event->getParameter("fields");
    \Helpers\Log::add(30, 38, $id, $USER->GetID(), $fields, $arFields);
}

//Лог удаления заявки
$eventManager->addEventHandler('', 'CourseRegistrationOnDelete', 'CourseRegistrationDelete');
function CourseRegistrationDelete(\Bitrix\Main\Entity\Event $event) {
    global $USER;
    $id = $event->getParameter("id");
    //id обновляемого элемента
    $id = $id["ID"];
    // получаем массив полей хайлоад блока
    $arFields = $event->getParameter("fields");
    \Helpers\Log::add(30, 39, $id, $USER->GetID(), [], []);
}

//Лог изменение прохождения
$eventManager->addEventHandler('', 'CourseCompletionOnUpdate', 'CourseCompletionUpdate');
$eventManager->addEventHandler('', 'CourseCompletionOnBeforeUpdate', 'CheckRemoteLMSCompletions');

function CheckRemoteLMSCompletions(\Bitrix\Main\Entity\Event $event) {
    $id = $event->getParameter("id");
    //id обновляемого элемента
    $id = $id["ID"];
    $arFields = $event->getParameter("fields");
    $old_completion = current((new \Teaching\CourseCompletion())->get(['ID' => $id]));
    if(array_key_exists('UF_IS_COMPLETE', $arFields)){
        $old_value = $old_completion['UF_IS_COMPLETE'];
        $new_value = $arFields['UF_IS_COMPLETE'];
        if($new_value==1 && $new_value != $old_value){
            $link_rows = \Settings\Synchronization::getRemoteList('completion', $old_completion['ID']);
            if(check_full_array($link_rows)) {
                foreach ($link_rows as $link_row){
                    \Settings\Synchronization::setCompleteRemoteCompletion($link_row, $old_completion['UF_POINTS']);
                }
            }
        }
    }
    if(array_key_exists('UF_FAILED', $arFields)){
        $old_value = $old_completion['UF_FAILED'];
        $new_value = $arFields['UF_FAILED'];
        if($new_value==1 && $new_value != $old_value){
            $link_rows = \Settings\Synchronization::getRemoteList('completion', $old_completion['ID']);
            if(check_full_array($link_rows)) {
                foreach ($link_rows as $link_row){
                    \Settings\Synchronization::setFailedRemoteCompletion($link_row);
                }
            }
        }
    }

}
function CourseCompletionUpdate(\Bitrix\Main\Entity\Event $event) {
    global $USER;
    $id = $event->getParameter("id");
    //id обновляемого элемента
    $id = $id["ID"];
    $enrollments = new \Teaching\CourseCompletion();
    $fields = $enrollments->get(['ID' => $id]);
    // получаем массив полей хайлоад блока
    $arFields = $event->getParameter("fields");
    if(!\Models\Course::isScormCourse($arFields['UF_COURSE_ID']))
        \Helpers\Log::add(31, 38, $id, $USER->GetID(), $fields, $arFields);
}

//Лог удаления прохождения
$eventManager->addEventHandler('', 'CourseCompletionOnDelete', 'CourseCompletionDelete');
function CourseCompletionDelete(\Bitrix\Main\Entity\Event $event) {
    global $USER;
    $id = $event->getParameter("id");
    //id обновляемого элемента
    $id = $id["ID"];
    // получаем массив полей хайлоад блока
    $arFields = $event->getParameter("fields");
    \Helpers\Log::add(31, 39, $id, $USER->GetID(), [], []);
}



/*логи*/
function addElementAddLog(&$arFields){
    global $USER;
    if($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getDealersIBlock()) {
        \Helpers\Log::add(35, 37, $arFields['ID'], $USER->GetID(), [], $arFields);
    }
    if($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getRolesIBlock()) {
        \Helpers\Log::add(34, 37, $arFields['ID'], $USER->GetID(), [], $arFields);
    }
    if($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getCoursesIBlock()) {
        \Helpers\Log::add(32, 37, $arFields['ID'], $USER->GetID(), [], $arFields);
    }
    if($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getShedulesIBlock()) {
        \Helpers\Log::add(33, 37, $arFields['ID'], $USER->GetID(), [], $arFields);
    }
}
function addElementDeleteLog($ID) {
    $arFields = CIBlockElement::GetByID($ID)->Fetch();
    if($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getDealersIBlock()) {
        global $APPLICATION;
        global $USER;
        if ($USER->GetID() != 2) {
            $APPLICATION->throwException("Невозможно удалить");
            return false;
        }
    } elseif($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getRolesIBlock()) {
        global $APPLICATION;
        $APPLICATION->throwException("Невозможно удалить");
        return false;
    } elseif($arFields['IBLOCK_ID']==COURSES_IBLOCK) {
        global $APPLICATION;
        $APPLICATION->throwException("Невозможно удалить");
        return false;
    } elseif($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getShedulesIBlock()) {
        global $APPLICATION;
        $APPLICATION->throwException("Невозможно удалить");
        return false;
    }
}
function addElementUpdateLog(&$arFields){
    global $USER;
    $element = \CIBlockElement::GetByID($arFields['ID'])->Fetch();
    $res = \CIBlockElement::GetProperty($element['IBLOCK_ID'], $arFields['ID'], "sort", "asc", array("ACTIVE" => "Y"));
    while ($ob = $res->GetNext()){
        $element['PROPERTY_VALUES'][$ob['ID']][] = ['VALUE' => $ob['VALUE']];
    }
    if($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getDealersIBlock()) {
        \Helpers\Log::add(35, 38, $arFields['ID'], $USER->GetID(), $arFields, $element);
    }
    if($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getRolesIBlock()) {
        \Helpers\Log::add(34, 38, $arFields['ID'], $USER->GetID(), $arFields, $element);
    }
    if($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getCoursesIBlock()) {
        \Helpers\Log::add(32, 38, $arFields['ID'], $USER->GetID(), $arFields, $element);
    }
    if($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getShedulesIBlock()) {
        \Helpers\Log::add(33, 38, $arFields['ID'], $USER->GetID(), $arFields, $element);
    }
}

function checkDatesBySchedule(&$arFields){
    if($arFields['IBLOCK_ID']==\Helpers\IBlockHelper::getShedulesIBlock()&&check_full_array($arFields['PROPERTY_VALUES'][14])) {
        $element = \CIBlockElement::GetByID($arFields['ID'])->Fetch();
        $res = \CIBlockElement::GetProperty($element['IBLOCK_ID'], $arFields['ID'], "sort", "asc", array("ACTIVE" => "Y"));
        while ($ob = $res->GetNext()){
            $element['PROPERTY_VALUES'][$ob['ID']][] = ['VALUE' => $ob['VALUE']];
        }
        $old_value = current($element['PROPERTY_VALUES'][14]);
        $old_date = date('d.m.Y', strtotime($old_value['VALUE']));
        $new_value = current($arFields['PROPERTY_VALUES'][14]);
        $new_date = date('d.m.Y', strtotime($new_value['VALUE']));
        if($old_date!=$new_date) {
            $schedule_array['COMPLETIONS'] = (new CourseCompletion())->get(['UF_SHEDULE_ID' => $arFields['ID']]);
            $schedule_array['ENROLLMENTS'] = (new Enrollments())->get(['UF_SHEDULE_ID' => $arFields['ID']]);
            if(check_full_array($schedule_array['COMPLETIONS'])){
                foreach ($schedule_array['COMPLETIONS'] as $completion){
                    (new CourseCompletion())->update($completion['ID'], ['UF_DATE' => $new_date]);
                }
            }
            if(check_full_array($schedule_array['ENROLLMENTS'])){
                foreach ($schedule_array['ENROLLMENTS'] as $enrollment){
                    (new Enrollments())->update($enrollment['ID'], ['UF_DATE' => $new_date]);
                }
            }
        }
    }
}
function addUserUpdateLog(&$arFields){
    global $USER;
    $user = [];
    if($arFields['ID']>0)
        $user = \Models\User::getById($arFields['ID']);
    \Helpers\Log::add(29, 38, $arFields['ID'], $USER->GetID(), $arFields, $user);
}
/*логи*/


function checkOPRoles(&$arFields)
{
    global $APPLICATION;
    $new_roles_temp = $arFields['UF_ROLE']??[];
    $roles = Roles::getOPRoles();
    if(count(array_intersect($new_roles_temp, array_keys($roles)))>1){
        global $APPLICATION;
        $APPLICATION->throwException("Пользователю нельзя указать более одной роли из отдела продаж");
        return false;
    }
}
function checkDealers(&$arFields)
{
    if(array_key_exists('UF_DEALER', $arFields)) {
        global $APPLICATION;
        $recruits = new \Teaching\Recruitment();
        $old_user = \Models\User::find($arFields['ID'], ['ID', 'UF_DEALER', 'UF_ROLE']);
        $old_dealer = (int)$old_user['UF_DEALER'];
        $old_roles = check_full_array($old_user['UF_ROLE']) ? $old_user['UF_ROLE'] : [];
        $new_dealer = (int)$arFields['UF_DEALER'];
        $new_roles_temp = $arFields['UF_ROLE'];
        $new_roles = [];
        if (check_full_array($new_roles_temp)) {
            foreach ($new_roles_temp as $new_role) {
                if ((int)$new_role > 0) {
                    $new_roles[] = (int)$new_role;
                }
            }
        }
        AddMessage2Log($new_dealer);
        if ($old_dealer != $new_dealer) {
            if ($old_dealer > 0) {
                $recruits->addDismiss($arFields['ID'], $old_dealer, $old_roles);
            }
            if ($new_dealer > 0) {
                $recruits->addRecruit($arFields['ID'], $new_dealer, $new_roles);
            } else {
                $completions = (new CourseCompletion())->get([
                    'UF_USER_ID' => $arFields['ID'],
                    'UF_IS_COMPLETE' => false,
                    'UF_FAILED' => false,
                    'UF_DIDNT_COM' => false,
                    '!UF_SHEDULE_ID' => false,
                    '!UF_COURSE_ID' => false,
                ]);
                foreach ($completions as $completion) {
                    $enrolls_filter = [
                        'UF_USER_ID' => $completion['UF_USER_ID'],
                        'UF_COURSE_ID' => $completion['UF_COURSE_ID'],
                        'UF_DATE' => $completion['UF_DATE']->format('d.m.Y')
                    ];
                    if ((int)$completion['UF_SHEDULE_ID'] > 0) {
                        $enrolls_filter['UF_SHEDULE_ID'] = (int)$completion['UF_SHEDULE_ID'];
                    }
                    $enrolls = (new Enrollments())->get($enrolls_filter);
                    foreach ($enrolls as $enroll) {
                        (new Enrollments())->delete($enroll['ID']);
                    }
                    (new CourseCompletion())->delete($completion['ID']);
                }
            }
        }
        if ((int)$arFields['UF_DEALER'] > 0) {
            $dealer = \Models\Dealer::find((int)$arFields['UF_DEALER'], ['ID', 'NAME', 'CODE']);
            if (check_full_array($dealer)) {
                $arFields['UF_DEALER_CODE'] = $dealer['CODE'];
                $arFields['UF_DEALER_NAME'] = $dealer['NAME'];
            } else {
                $arFields['UF_DEALER_CODE'] = false;
                $arFields['UF_DEALER_NAME'] = false;
            }
        }
    }
}
function AddRecruit(&$arFields)
{
    global $USER;
    \Helpers\Log::add(29, 37, $arFields['ID'], $USER->GetID(), [], $arFields);
    $recruits = new \Teaching\Recruitment();
    $old_user = \Models\User::find($arFields['ID'], ['ID', 'UF_DEALER', 'UF_ROLE']);
    $old_dealer = (int)$old_user['UF_DEALER'];
    $old_roles = $old_user['UF_ROLE'];

    $recruits->addRecruit($arFields['ID'], $old_dealer, $old_roles);
    $s = \Notifications\EmailNotifications::sendToEmployee($old_user['ID']);
    if( $s==false ) {
    } else {
        \Helpers\UserHelper::setUserValue('UF_INVITE_MAILING', 21, $old_user['ID']);
    }
}


AddEventHandler("iblock", "OnAfterIBlockElementAdd", "createZoomMeet");
function createZoomMeet(&$arFields){
    if($arFields['IBLOCK_ID'] == \Helpers\IBlockHelper::getShedulesIBlock()){
        if((int)$arFields['PROPERTY_VALUES']['COURSE']>0){
            $course = \Models\Course::find((int)$arFields['PROPERTY_VALUES']['COURSE'], ['PROPERTY_ZOOM']);
            if(!empty($course['PROPERTY_ZOOM_ENUM_ID'])&&$course['PROPERTY_ZOOM_ENUM_ID']!=14){
                \Integrations\Zoom::createMeetingFromShedule($arFields);
            }
        }
    }
}

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler('', "CourseCompletionOnAfterAdd", 'addMemberToZoom');
AddEventHandler("highloadblock", "CourseCompletionOnAfterAdd", "addMemberToZoom");
function addMemberToZoom(\Bitrix\Main\Entity\Event $event){
    $arFields = $event->getParameter('fields');
    if((int)$arFields['UF_COURSE_ID']>0&&(int)$arFields['UF_USER_ID']>0&&(int)$arFields['UF_SHEDULE_ID']>0){
        $course = \Models\Course::find((int)$arFields['UF_COURSE_ID'], ['PROPERTY_ZOOM']);
        if(!empty($course['PROPERTY_ZOOM_ENUM_ID'])&&$course['PROPERTY_ZOOM_ENUM_ID']==16){
            $schedule = current(\Teaching\SheduleCourses::getById($arFields['UF_SHEDULE_ID']));
            if(!empty($schedule['PROPERTIES']['ZOOM_MEET_ID'])) {
                \Integrations\Zoom::addEmployeeToMeetByShedule((int)$arFields['UF_USER_ID'], $arFields['UF_SHEDULE_ID']);
            }
        }
    }

}