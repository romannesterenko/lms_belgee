<?php

use Helpers\DealerHelper;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
$result = "";
// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exists = false;
    $userId = 0;
    if(check_full_array($_REQUEST['data'])){
        if($_REQUEST['method'] === 'createUser') {
            $user = new CUser;
            // Массив данных для нового пользователя
            $arFields = $_REQUEST['data']['user'];
            $arFields['CONFIRM_PASSWORD'] = $_REQUEST['data']['user']['PASSWORD'];
            $arFields['UF_DEALER'] = \Settings\Synchronization::getLinkedDealerId($arFields['UF_DEALER'], $_REQUEST['data']['from']);
            $arFields['UF_ROLE'] = ($arFields['UF_ROLE']&&check_full_array($arFields['UF_ROLE']))?\Settings\Synchronization::getLinkedRoles($arFields['UF_ROLE'], $_REQUEST['data']['from']):[];
            // Создание пользователя
            $userId = $user->Add($arFields);

            if (intval($userId) > 0) {
                $result = "пользователь успешно создан. ID пользователя: " . $userId;
                \Settings\Synchronization::addLinkingRecord('user', $userId, $_REQUEST['data']['user_id'], $_REQUEST['data']['from']);
            } else {
                $arFilter = array("EMAIL" => $arFields['EMAIL']);
                $exists_user = \CUser::GetList(($by = "id"), ($order = "asc"), $arFilter)->fetch();
                if (check_full_array($exists_user)) {
                    if($arFields['ACTIVE'] != $exists_user['ACTIVE']){
                        $update_user = new CUser;
                        $update_user->Update($exists_user['ID'], ['ACTIVE' => $arFields['ACTIVE']]);
                    }
                    $userId = $exists_user['ID'];
                    $exists = true;
                    if ($_REQUEST['data']['linkIfExists']=='true' || $_REQUEST['data']['linkIfExists']) {
                        \Settings\Synchronization::addLinkingRecord('user', $userId, $_REQUEST['data']['user_id'], $_REQUEST['data']['from']);
                    }
                }
                $result = $user->LAST_ERROR;
            }
            echo json_encode(['status' => 'ok', 'data' => $_REQUEST, 'result' => $result, 'user_id' => (int)$userId, 'exists' => $exists]);

        }
        if($_REQUEST['method'] === 'createDealer') {
            if(check_full_array($_REQUEST['data']['dealer'])){
                $exists_dealer = current(\Helpers\DealerHelper::getList(['CODE' => $_REQUEST['data']['dealer']['CODE']], ["ID", 'NAME']));
                if(check_full_array($exists_dealer) && $exists_dealer['ID'] > 0 ){
                    $result = 'Дилер "'.$_REQUEST['data']['dealer']['NAME'].'" уже внесен в базу '.\Settings\Synchronization::getCurrentLMS()['NAME'];
                    $exists = true;
                    if ($_REQUEST['data']['linkIfExists'] == 'true') {
                        \Settings\Synchronization::addLinkingRecord('dealer', $exists_dealer['ID'], $_REQUEST['data']['dealer_id'], $_REQUEST['data']['from']);
                    }
                    $PRODUCT_ID = $exists_dealer['ID'];
                } else {
                    $result = $_REQUEST['data']['dealer'];
                    $_REQUEST['data']['dealer']["IBLOCK_SECTION_ID"] = false;
                    $_REQUEST['data']['dealer']["IBLOCK_ID"] = \Models\Dealer::getIblockId();
                    \CModule::IncludeModule("iblock");
                    $el = new \CIBlockElement;
                    if($PRODUCT_ID = $el->Add($_REQUEST['data']['dealer'])) {
                        $result = "В LMS ".\Settings\Synchronization::getCurrentLMS()['NAME']." создан дилер \"".$_REQUEST['data']['dealer']["NAME"]."\" c id " . $PRODUCT_ID;
                        \Settings\Synchronization::addLinkingRecord('dealer', $PRODUCT_ID, $_REQUEST['data']['dealer_id'], $_REQUEST['data']['from']);
                    } else {
                        $result = "Ошибка: " . $el->LAST_ERROR;

                    }
                }
            }
            echo json_encode(['status' => 'ok', 'data' => $_REQUEST, 'result' => $result, 'dealer_id' => (int)$PRODUCT_ID, 'exists' => $exists]);
        }
        if($_REQUEST['method'] === 'setCompleteCompletion') {
            if($_REQUEST['data']['completion_id'] > 0){
                (new \Teaching\CourseCompletion())->setCompleted($_REQUEST['data']['completion_id'], $_REQUEST['data']['points']);
            }
            echo json_encode(['status' => 'ok', 'data' => $_REQUEST, 'result' => $result, 'dealer_id' => (int)$PRODUCT_ID, 'exists' => $exists]);
        }
        if($_REQUEST['method'] === 'getSheduleCompletions') {
            if($_REQUEST['data']['scheduleId'] > 0) {
                $list = (new \Teaching\CourseCompletion())->get(['UF_SHEDULE' => $_REQUEST['data']['scheduleId']], ['ID', 'UF_USER_ID']);
            }
            echo json_encode(['status' => 'ok', 'data' => $list]);
        }
        if($_REQUEST['method'] === 'setFailedCompletion') {
            if($_REQUEST['data']['completion_id'] > 0){
                (new \Teaching\CourseCompletion())->setFailedCourse($_REQUEST['data']['completion_id']);
            }
            echo json_encode(['status' => 'ok', 'data' => $_REQUEST, 'result' => $result, 'dealer_id' => (int)$PRODUCT_ID, 'exists' => $exists]);
        }
        if($_REQUEST['method'] === 'createCompletions') {
            $response = ['success' => true, 'created' => false];

            if(check_full_array($_REQUEST['data']['completion'])){
                $fields = $_REQUEST['data']['completion'];
                $fields['UF_USER_ID'] = \Settings\Synchronization::getLinkedUserId($fields['UF_USER_ID'], $_REQUEST['data']['from']);
                $text = $fields['UF_USER_ID'];
                if($fields['UF_USER_ID']) {
                    $fields['UF_COURSE_ID'] = \Settings\Synchronization::getLinkedCourseId($fields['UF_COURSE_ID'], $_REQUEST['data']['from']);
                    if($fields['UF_COURSE_ID']){
                        $remote_id = $fields['ID'];
                        unset($fields['ID']);
                        $completionId = 0;
                        $exists_completion = (new \Teaching\CourseCompletion())->get([
                            'UF_USER_ID' => $fields['UF_USER_ID'],
                            'UF_COURSE_ID' => $fields['UF_COURSE_ID'],
                            'UF_DATE' => $fields['UF_DATE'],
                            'UF_IS_COMPLETE' => $fields['UF_IS_COMPLETE'],
                        ]);
                        if(!check_full_array($exists_completion)){
                            $result = (new \Teaching\CourseCompletion())->add($fields, false);
                            if ($result->isSuccess()){
                                if($fields['UF_IS_COMPLETE'] == 1){
                                    \Helpers\Pdf::generateCertFromCompletionId($remote_id);
                                }
                                $completionId = $result->getId();
                                $response['created'] = true;
                            } else {
                                $response['success'] = false;
                                $response['error'] = $result->LAST_ERROR;
                            }
                        } else {
                            $response['exists'] = true;
                            $completionId = current($exists_completion)['ID'];
                        }
                        $response['completion_id'] = $completionId;
                        \Settings\Synchronization::addLinkingRecord('completion', $completionId, $remote_id, $_REQUEST['data']['from']);
                    } else {
                        $response['success'] = false;
                        $response['error'] = "Linked course not found";
                    }
                } else {
                    $response['success'] = false;
                    $response['error'] = "Linked user not exists";
                }
                echo json_encode($response);
            }
        }
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Bad request']);
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");

