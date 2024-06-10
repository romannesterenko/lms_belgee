<?php
const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $USER;
use Bitrix\Main\Web\HttpClient;
if ($_REQUEST['method'] && $_REQUEST['method'] == 'getFilteredUsers'){
    $users = [];
    if($_REQUEST['dealer']!='all'){
        $user_filter = ['ACTIVE' => 'Y', 'UF_DEALER' => (int)$_REQUEST['dealer']];
        if($_REQUEST['direction']!='all'){
            $roles = match ($_REQUEST['direction']) {
                'op' => array_keys(\Models\Role::getOPRoles()),
                'ppo' => array_keys(\Models\Role::getPPORoles()),
                'marketing' => array_keys(\Models\Role::getMarketingRoles()),
                default => false,
            };
            if($roles) {
                $user_filter['UF_ROLE'] = $roles;
            }
        }
        $users = \Models\User::get($user_filter, ['ID', 'NAME', 'LAST_NAME', 'EMAIL', 'UF_ROLE']);
        $roles = \Teaching\Roles::getAll();
        foreach ($users as &$user){
            $arr = [];
            foreach ($user['UF_ROLE'] as $role){
                $arr[] = $roles[$role];
            }
            $user['ROLES'] = implode('<br/>', $arr);
        }
    }
    echo json_encode(['request' => $_REQUEST, 'users' => $users]);
} else {
    if ($_REQUEST['id'] && $_REQUEST['lms'] > 0) {
        $lms = \Settings\Synchronization::getLMSById($_REQUEST['lms']);
        $userId = $_REQUEST['id'];
        // Массив полей для выборки, включая пользовательские поля (UF_*)
        $selectFields = array(
            "*",
            "UF_*" // Это включает все пользовательские поля
        );
        // Получение данных пользователя по его ID
        $rsUser = CUser::GetList(
            ($by = "id"),
            ($order = "asc"),
            array("ID" => $userId),
            array("SELECT" => $selectFields)
        );
        $user = $rsUser->Fetch();
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
        if($_REQUEST['migrated_roles'] != 'true')
            unset($user["UF_ROLE"]);
        unset($user["UF_REQUIRED_COURSES"]);
        $user["ACTIVE"] = $_REQUEST['migrated_user_actions']=='activate'?"Y":"N";
        if (check_full_array($lms)) {
            if (!empty($lms['PROPERTY_HANDLER_PATH_VALUE']) && !empty($lms['PROPERTY_URL_VALUE'])) {
                $url = $lms['PROPERTY_URL_VALUE'] . $lms['PROPERTY_HANDLER_PATH_VALUE'];
                $httpClient = new HttpClient();
                $data = array(
                    'method' => 'createUser',
                    'data' => [
                        'user_id' => $userId,
                        'user' => $user,
                        'from' => \Settings\Synchronization::getCurrentLMS()['CODE'],
                        'linkIfExists' => $_REQUEST['linkIfExists'],
                    ]
                );
                $httpClient->setHeader('Content-Type', 'application/x-www-form-urlencoded', true);
                $response = $httpClient->post($url, http_build_query($data));
                $response_array = json_decode($response, true);
                if ($user_id == 3027 && $_REQUEST['migrated_user_actions_this_lms'] === 'deactivate'){
                    \Models\User::resetDealer($user_id);
                    \Models\User::deactivate($user_id);
                }
                /**
                 * Обработка прохождений пользователя
                 */

                if ($_REQUEST['migrate_op_completions'] == 'true') {
                    $courses = \Models\Course::getOPList(true);
                    $response_array['completions_migrating'] = migrate_completions($userId, $courses, $httpClient, $url, $response_array, $lms);
                    unset($courses);
                }

                if ($_REQUEST['migrate_ppo_completions'] == 'true') {
                    $courses = \Models\Course::getPPOList(true);
                    $response_array['completions_migrating'] = migrate_completions($userId, $courses, $httpClient, $url, $response_array, $lms);
                    unset($courses);
                }

                if ($_REQUEST['migrate_marketing_completions'] == 'true') {
                    $courses = \Models\Course::getMarketingList(true);
                    $response_array['completions_migrating'] = migrate_completions($userId, $courses, $httpClient, $url, $response_array, $lms);
                    unset($courses);
                }
                //связка
                if ($response_array['exists'] != 1) {
                    \Settings\Synchronization::addLinkingRecord('user', $userId, $response_array['user_id'], $lms['CODE']);
                } else {
                    if ($_REQUEST['linkIfExists'] == 'true') {
                        \Settings\Synchronization::addLinkingRecord('user', $user_id, $response_array['user_id'], $lms['CODE']);
                    }
                }
                echo $response;
            }
        }
    }
}
function migrate_completions($userId, $courses, $httpClient, $url, $response_array, $lms): array
{
    $return_array = array();
    $completions = (new \Teaching\CourseCompletion())
        ->get(['UF_USER_ID' => $userId, 'UF_COURSE_ID' => $courses]);
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
            $completions_response = $httpClient->post($url, http_build_query($completions_data));
            $return_array[] = $completions_response_array = json_decode($completions_response, true);
            if($response_array['exists']!=1 || $_REQUEST['linkIfExists']=='true') {
                if((int)$completions_response_array['completion_id']>0)
                    \Settings\Synchronization::addLinkingRecord('completion', $completion['ID'], $completions_response_array['completion_id'], $lms['CODE']);
            }
        }
    }
    return $return_array;
}
