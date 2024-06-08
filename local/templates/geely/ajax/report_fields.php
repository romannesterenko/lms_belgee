<?php

use Bitrix\Main\Application;
const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$request = Application::getInstance()->getContext()->getRequest()->getValues();
if(check_full_array($request['dealer_codes']))
    $filter['UF_DEALER'] = $request['dealer_codes'];
if(check_full_array($request['role']))
    $filter['UF_ROLE'] = $request['role'];
$users = \Models\Employee::getList($filter, ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE', 'UF_DEALER', 'WORK_POSITION']);
$response['users_getlist'] = $users;
if( $request['type_input']=='ppo' ) {
    if(check_full_array($request['regional_ppo'])) {
        $dealers = \Models\Dealer::getByRegionalPPO($request['regional_ppo']);
        $response['dealers'] = array_values($dealers);
        $response['users'] = \Models\Employee::getListByDealer(array_keys($dealers), ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE']);
        $roles = [];
        foreach ($response['users'] as $user) {
            if (check_full_array($user['UF_ROLE'])) {
                $roles = array_merge($roles, $user['UF_ROLE']);
            }
        }
        $response['role_ids'] = array_values(array_unique($roles));
        if (check_full_array($response['role_ids'])) {
            $roles = \Models\Role::getArray(['ID' => $response['role_ids'], 'ACTIVE' => 'Y']);
            foreach ($roles as $id => $role)
                $response['roles'][] = ['id' => $id, 'name' => $role];
        } else {
            $roles = \Models\Role::getArray(['ACTIVE' => 'Y']);
            foreach ($roles as $id => $role)
                $response['roles'][] = ['id' => $id, 'name' => $role];
        }
    }else {
        $dealers = \Models\Dealer::getAll();
        $response['dealers'] = array_values($dealers);
        $response['users'] = \Models\Employee::getListByDealer(array_keys($dealers), ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE']);
        $roles = [];
        foreach ($response['users'] as $user) {
            if (check_full_array($user['UF_ROLE'])) {
                $roles = array_merge($roles, $user['UF_ROLE']);
            }
        }
        $response['role_ids'] = array_values(array_unique($roles));
        if (check_full_array($response['role_ids'])) {
            $roles = \Models\Role::getArray(['ID' => $response['role_ids'], 'ACTIVE' => 'Y']);
            foreach ($roles as $id => $role)
                $response['roles'][] = ['id' => $id, 'name' => $role];
        } else {
            $roles = \Models\Role::getArray(['ACTIVE' => 'Y']);
            foreach ($roles as $id => $role)
                $response['roles'][] = ['id' => $id, 'name' => $role];
        }
    }
} else {
    if($request['type_input']=='role'){
        if (check_full_array($request['role'])) {
            if(check_full_array($request['dealer_codes'])){
                $response['users'] = \Models\Employee::getList(['UF_DEALER' => $request['dealer_codes'], 'UF_ROLE' => $request['role']], ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE']);
            } elseif (check_full_array($request['regional_ppo'])){
                $dealers = \Models\Dealer::getByRegionalPPO($request['regional_ppo']);
                $response['users'] = \Models\Employee::getList(['UF_DEALER' => array_keys($dealers), 'UF_ROLE' => $request['role']], ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE']);
            }else{
                $response['users'] = \Models\Employee::getList(['UF_ROLE' => $request['role']], ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE']);
            }
        }else{
            if (check_full_array($request['dealer_codes'])) {
                $response['users'] = \Models\Employee::getListByDealer($request['dealer_codes'], ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE']);
            } elseif (check_full_array($request['regional_ppo'])){
                $dealers = \Models\Dealer::getByRegionalPPO($request['regional_ppo']);
                $response['users'] = \Models\Employee::getListByDealer(array_keys($dealers), ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE']);
            }else{
                $response['users'] = \Models\Employee::getByRole($request['role'], ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE']);
            }
        }
    }else {

        if (check_full_array($request['dealer_codes'])) {
            $response['users'] = \Models\Employee::getListByDealer($request['dealer_codes'], ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE']);
            $roles = [];
            foreach ($response['users'] as $user) {
                if (check_full_array($user['UF_ROLE'])) {
                    $roles = array_merge($roles, $user['UF_ROLE']);
                }
            }
            $response['role_ids'] = array_values(array_unique($roles));
            if (check_full_array($response['role_ids'])) {
                $roles = \Models\Role::getArray(['ID' => $response['role_ids'], 'ACTIVE' => 'Y']);
                foreach ($roles as $id => $role)
                    $response['roles'][] = ['id' => $id, 'name' => $role];
            } else {
                $roles = \Models\Role::getArray(['ACTIVE' => 'Y']);
                foreach ($roles as $id => $role)
                    $response['roles'][] = ['id' => $id, 'name' => $role];
            }
        } else {
            if (check_full_array($request['regional_ppo'])) {
                $dealers = \Models\Dealer::getByRegionalPPO($request['regional_ppo']);
                $response['dealers'] = array_values($dealers);
                $response['users'] = \Models\Employee::getListByDealer(array_keys($dealers), ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE']);
                $roles = [];
                foreach ($response['users'] as $user) {
                    if (check_full_array($user['UF_ROLE'])) {
                        $roles = array_merge($roles, $user['UF_ROLE']);
                    }
                }
                $response['role_ids'] = array_values(array_unique($roles));
                if (check_full_array($response['role_ids'])) {
                    $roles = \Models\Role::getArray(['ID' => $response['role_ids'], 'ACTIVE' => 'Y']);
                    foreach ($roles as $id => $role)
                        $response['roles'][] = ['id' => $id, 'name' => $role];
                } else {
                    $roles = \Models\Role::getArray(['ACTIVE' => 'Y']);
                    foreach ($roles as $id => $role)
                        $response['roles'][] = ['id' => $id, 'name' => $role];
                }
            } else {
                $dealers = \Models\Dealer::getAll();
                $response['users'] = \Models\Employee::getListByDealer(array_keys($dealers), ['ID', 'NAME', 'LAST_NAME', 'UF_ROLE']);
                $roles = [];
                foreach ($response['users'] as $user) {
                    if (check_full_array($user['UF_ROLE'])) {
                        $roles = array_merge($roles, $user['UF_ROLE']);
                    }
                }
                $response['role_ids'] = array_values(array_unique($roles));
                if (check_full_array($response['role_ids'])) {
                    $roles = \Models\Role::getArray(['ID' => $response['role_ids'], 'ACTIVE' => 'Y']);
                    foreach ($roles as $id => $role)
                        $response['roles'][] = ['id' => $id, 'name' => $role];
                } else {
                    $roles = \Models\Role::getArray(['ACTIVE' => 'Y']);
                    foreach ($roles as $id => $role)
                        $response['roles'][] = ['id' => $id, 'name' => $role];
                }
            }

        }
    }
}
$response['request'] = $request;
echo json_encode($response);


