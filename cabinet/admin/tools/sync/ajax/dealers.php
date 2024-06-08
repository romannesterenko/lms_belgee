<?php
const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;
use Bitrix\Main\Web\HttpClient;
if ($_REQUEST['id'] && $_REQUEST['lms'] > 0) {
    $lms = \Settings\Synchronization::getLMSById($_REQUEST['lms']);
    $dealerId = $_REQUEST['id'];
    // Массив полей для выборки, включая пользовательские поля (UF_*)
    $selectFields = array(
        "NAME",
        "CODE",
        "PROPERTY_ORG_NAME",
        "PROPERTY_COMM_NAME",
        "PROPERTY_ENG_NAME",
        "PROPERTY_CITY",
        "PROPERTY_ORG_ADDRESS",
        "PROPERTY_PLACE_ADDRESS",
    );
    // Получение данных пользователя по его ID
    $dealer = \Models\Dealer::find($dealerId, $selectFields);
    $fields = [
        "NAME" => $dealer["NAME"],
        "CODE" => $dealer["CODE"],
        "ACTIVE" => $_REQUEST['addActive']=='true'?"Y":"N",
        "PROPERTY_VALUES" => [
            "ORG_NAME" => $dealer['PROPERTY_ORG_NAME_VALUE'],
            "COMM_NAME" => $dealer['PROPERTY_COMM_NAME_VALUE'],
            "ENG_NAME" => $dealer['PROPERTY_ENG_NAME_VALUE'],
            "CITY" => $dealer['PROPERTY_CITY_VALUE'],
            "ORG_ADDRESS" => $dealer['PROPERTY_ORG_ADDRESS_VALUE'],
            "PLACE_ADDRESS" => $dealer['PROPERTY_PLACE_ADDRESS_VALUE'],
        ],
    ];
    if(check_full_array($lms)) {
        if(!empty($lms['PROPERTY_HANDLER_PATH_VALUE']) && !empty($lms['PROPERTY_URL_VALUE'])){
            $url = $lms['PROPERTY_URL_VALUE'].$lms['PROPERTY_HANDLER_PATH_VALUE'];
            $httpClient = new HttpClient();
            $data = array(
                'method' => 'createDealer',
                'data' => [
                    'dealer_id' => $dealerId,
                    'dealer' => $fields,
                    'from' => \Settings\Synchronization::getCurrentLMS()['CODE'],
                    'linkIfExists' => $_REQUEST['linkIfExists'],
                ]
            );
            $httpClient->setHeader('Content-Type', 'application/x-www-form-urlencoded', true);
            $response = $httpClient->post($url, http_build_query($data));
            $response_array = json_decode($response, true);
            if($response_array['exists']!=1) {
                \Settings\Synchronization::addLinkingRecord('dealer', $dealerId, $response_array['dealer_id'], $lms['CODE']);
                migrateUsers($dealerId, $lms);
            } else {
                if($_REQUEST['linkIfExists']==='true') {
                    \Settings\Synchronization::addLinkingRecord('dealer', $dealerId, $response_array['dealer_id'], $lms['CODE']);
                    migrateUsers($dealerId, $lms);
                }
            }
            echo $response;
        }
    }
}
function migrateUsers($dealerId, $lms) {
    if($_REQUEST['migrate_users'] == 'true') {
        if($_REQUEST['migrate_op_users'] == 'true' || $_REQUEST['migrate_ppo_users'] == 'true' || $_REQUEST['migrate_marketing_users'] == 'true'){
            if($_REQUEST['migrate_op_users'] == 'true') {
                $roles = \Models\Role::getOPRoles();
                processUsers($roles, $dealerId, $lms);
            }
            if($_REQUEST['migrate_ppo_users'] == 'true') {
                $roles = \Models\Role::getPPORoles();
                processUsers($roles, $dealerId, $lms);
            }
            if($_REQUEST['migrate_marketing_users'] == 'true') {
                $roles = \Models\Role::getMarketingRoles();
                processUsers($roles, $dealerId, $lms);
            }
        }
    }
}
function processUsers($roles, $dealerId, $lms): void
{
    $url = $lms['PROPERTY_URL_VALUE'].$lms['PROPERTY_HANDLER_PATH_VALUE'];
    $users = \Models\User::get(['UF_ROLE' => array_keys($roles), 'UF_DEALER' => $dealerId], ['*', 'UF_*']);
    if(check_full_array($users)) {
        foreach ($users as $user) {
            $user_id = $user['ID'];
            unset($user["ID"]);
            unset($user["TIMESTAMP_X"]);
            unset($user["LAST_LOGIN"]);
            unset($user["DATE_REGISTER"]);
            unset($user["LID"]);
            unset($user["CHECKWORD_TIME"]);
            unset($user["CHECKWORD"]);
            unset($user["BX_USER_ID"]);
            unset($user["UF_OLD_ROLES"]);
            unset($user["UF_ROLES"]);
            unset($user["UF_ROLE"]);
            unset($user["UF_REQUIRED_COURSES"]);
            $user["ACTIVE"] = $_REQUEST['migrated_user_actions']=='activate'?"Y":"N";
            $httpClient = new Bitrix\Main\Web\HttpClient();
            $data = array(
                'method' => 'createUser',
                'data' => [
                    'user_id' => $user_id,
                    'user' => $user,
                    'from' => \Settings\Synchronization::getCurrentLMS()['CODE'],
                    'linkIfExists' => true,
                ]
            );
            $httpClient->setHeader('Content-Type', 'application/x-www-form-urlencoded', true);
            $response = $httpClient->post($url, http_build_query($data));
            $response_array = json_decode($response, true);
            if ($user_id == 3027 && $_REQUEST['migrated_user_actions_this_lms'] === 'deactivate'){
                \Models\User::resetDealer($user_id);
                \Models\User::deactivate($user_id);
            }
            \Settings\Synchronization::addLinkingRecord('user', $user_id, $response_array['user_id'], $lms['CODE']);
            migrateCompletions($user_id, $url, $lms);
        }
    }
}
function migrateCompletions($user_id, $url, $lms): void
{
    if ($_REQUEST['migrate_completions'] == 'true') {
        $completions = (new \Teaching\CourseCompletion())
            ->get(['UF_USER_ID' => $user_id]);
        if(check_full_array($completions)) {
            foreach ($completions as $completion) {
                $completion["UF_DATE"] = (string)$completion["UF_DATE"];
                $completion["UF_DATE_CREATE"] = (string)$completion["UF_DATE_CREATE"];
                $completion["UF_DATE_UPDATE"] = (string)$completion["UF_DATE_UPDATE"];
                $completion["UF_EXPIRED_DATE"] = (string)$completion["UF_EXPIRED_DATE"];
                $completion["UF_COMPLETED_TIME"] = (string)$completion["UF_COMPLETED_TIME"];
                $completions_data = array(
                    'method' => 'createCompletions',
                    'data' => [
                        'completion' => $completion,
                        'from' => \Settings\Synchronization::getCurrentLMS()['CODE'],
                    ]
                );
                $httpClient = new Bitrix\Main\Web\HttpClient();
                $completions_response = $httpClient->post($url, http_build_query($completions_data));
                $completions_response_array = json_decode($completions_response, true);
                if($completions_response_array['completion_id'] > 0)
                    \Settings\Synchronization::addLinkingRecord('completion', $completion['ID'], $completions_response_array['completion_id'], $lms['CODE']);
            }
        }
    }
}

