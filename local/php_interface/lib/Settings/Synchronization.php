<?php

namespace Settings;
use CIBlockElement;
use CModule;
use Helpers\HLBlockHelper;

class Synchronization
{
    public static function getIblock()
    {
        CModule::IncludeModule('iblock');
        return \CIBlock::GetList(
            Array(),
            Array(
                "CODE"=>'remote'
            ), false
        )->Fetch();
    }

    public static function getRelatedIblock()
    {
        CModule::IncludeModule('iblock');
        return \CIBlock::GetList(
            Array(),
            Array(
                "CODE"=>'related'
            ), false
        )->Fetch();
    }

    public static function getLMSList($filter=[]): array
    {
        CModule::IncludeModule('iblock');
        $iblock = self::getIblock();
        $list = [];
        if(check_full_array($iblock)) {
            $arFilter = array_merge(["IBLOCK_ID" => $iblock['ID'], 'ACTIVE' => 'Y'], $filter);
            $res = CIBlockElement::GetList(array(), $arFilter, false, array(), array_merge(['ID'], ['ID', 'NAME', 'CODE', 'PROPERTY_URL', 'PROPERTY_HANDLER_PATH']));
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $list[$arFields['ID']] = $arFields;
            }
        }
        return $list;
    }

    public static function getLMSById(mixed $lms): array
    {
        CModule::IncludeModule('iblock');
        $iblock = self::getIblock();
        if(check_full_array($iblock)) {
            $arFilter = ["IBLOCK_ID" => $iblock['ID'], 'ACTIVE' => 'Y', 'ID' => $lms];
            $res = CIBlockElement::GetList(array(), $arFilter, false, array(), array_merge(['ID'], ['ID', 'NAME', 'CODE', 'PROPERTY_URL', 'PROPERTY_HANDLER_PATH']));
            if ($ob = $res->GetNextElement())
                return $ob->GetFields();
        }
        return [];
    }

    public static function getLMSByCode($code)
    {
        CModule::IncludeModule('iblock');
        $iblock = self::getIblock();
        if(check_full_array($iblock)) {
            $arFilter = ["IBLOCK_ID" => $iblock['ID'], 'ACTIVE' => 'Y', 'CODE' => $code];
            $res = CIBlockElement::GetList(array(), $arFilter, false, array(), array_merge(['ID'], ['ID', 'NAME', 'CODE', 'PROPERTY_URL', 'PROPERTY_HANDLER_PATH']));
            if ($ob = $res->GetNextElement())
                return $ob->GetFields();
        }
        return [];
    }

    public static function getCurrentLMS()
    {
        $iblock = self::getIblock();
        if(check_full_array($iblock)) {
            $arFilter = ["IBLOCK_ID" => $iblock['ID'], 'ACTIVE' => 'Y', 'PROPERTY_IS_CURRENT' => self::getCurrentLMSPropertyId()];
            $res = CIBlockElement::GetList(array(), $arFilter, false, array(), array_merge(['ID'], ['ID', 'CODE', 'NAME', 'PROPERTY_URL', 'PROPERTY_HANDLER_PATH']));
            if ($ob = $res->GetNextElement())
                return $ob->GetFields();
        }
        return [];
    }

    private static function getCurrentLMSPropertyId()
    {
        CModule::IncludeModule('iblock');
        $propertyEnumId = false;
        $iblock = self::getIblock();
        if(check_full_array($iblock)) {
            $rsEnum = \CIBlockPropertyEnum::GetList(
                array(),
                array(
                    "IBLOCK_ID" => $iblock['ID'],
                    "XML_ID" => 'yes'
                )
            );
            if ($arEnum = $rsEnum->GetNext()) {
                $propertyEnumId = $arEnum["ID"];
            }
        }
        return $propertyEnumId;
    }

    private static function getRelatedTypeIdByXML($code)
    {
        $propertyEnumId = false;
        $iblock = self::getRelatedIblock();
        if(check_full_array($iblock)) {
            $rsEnum = \CIBlockPropertyEnum::GetList(
                array(),
                array(
                    "IBLOCK_ID" => $iblock['ID'],
                    "XML_ID" => $code
                )
            );
            if ($arEnum = $rsEnum->GetNext()) {
                $propertyEnumId = $arEnum["ID"];
            }
        }
        return $propertyEnumId;
    }

    public static function addLinkingRecord($entity, $local_id, $remote_id, $lms_code): void
    {
        if(self::getRelatedTypeIdByXML($entity) > 0) {
            $arSelect = Array("ID");
            $arFilter = Array(
                "IBLOCK_ID"=>self::getRelatedIblock()['ID'],
                'PROPERTY_LMS' => self::getLMSByCode($lms_code)['ID'],
                'PROPERTY_ENTITY' => self::getRelatedTypeIdByXML($entity),
                'PROPERTY_LOCAL_ID' => $local_id,
                'PROPERTY_REMOTE_ID' => $remote_id,
            );
            $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
            if($ob = $res->GetNextElement()) {

            } else {
                $el = new CIBlockElement;
                $fields = [
                    'NAME' => $entity . " " . $local_id . " " . $remote_id . " " . $lms_code,
                    'ACTIVE' => "Y",
                    'IBLOCK_ID' => self::getRelatedIblock()['ID'],
                    "IBLOCK_SECTION_ID" => false,
                    'PROPERTY_VALUES' => [
                        'LMS' => self::getLMSByCode($lms_code)['ID'],
                        'ENTITY' => self::getRelatedTypeIdByXML($entity),
                        'LOCAL_ID' => $local_id,
                        'REMOTE_ID' => $remote_id,
                    ]
                ];
                $el->Add($fields);
            }
        }
    }

    public static function getLinkedLocalID($entity, $remote_id, $lms_code)
    {
        $arSelect = Array("ID", "PROPERTY_LOCAL_ID");
        $arFilter = Array(
            "IBLOCK_ID"=>self::getRelatedIblock()['ID'],
            'PROPERTY_LMS' => self::getLMSByCode($lms_code)['ID'],
            'PROPERTY_ENTITY' => self::getRelatedTypeIdByXML($entity),
            'PROPERTY_REMOTE_ID' => $remote_id,
        );

        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
        if($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            dump($arFields);
            return $arFields['PROPERTY_LOCAL_ID_VALUE'];
        }
        return false;
    }
    public static function getLinkedEntity($entity, $remote_id)
    {
        $arSelect = Array("ID", "PROPERTY_LOCAL_ID", "PROPERTY_REMOTE_ID");
        $arFilter = Array(
            "IBLOCK_ID"=>self::getRelatedIblock()['ID'],
            'PROPERTY_ENTITY' => self::getRelatedTypeIdByXML($entity),
            'PROPERTY_LOCAL_ID' => $remote_id,
        );

        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
        if($ob = $res->GetNextElement()) {
            return $ob->GetFields();
        }
        return false;
    }

    public static function getLinkedRemoteID($entity, $local_id, $lms_code)
    {
        $arSelect = Array("ID", "PROPERTY_REMOTE_ID");
        $arFilter = Array(
            "IBLOCK_ID"=>self::getRelatedIblock()['ID'],
            'PROPERTY_LMS' => self::getLMSByCode($lms_code)['ID'],
            'PROPERTY_ENTITY' => self::getRelatedTypeIdByXML($entity),
            'PROPERTY_LOCAL_ID' => $local_id,
        );
        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
        if($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            return $arFields['PROPERTY_REMOTE_ID_VALUE'];
        }
        return false;
    }

    public static function getLinkedUserId($remote_id, $lms_code)
    {
        return self::getLinkedLocalID('user', $remote_id, $lms_code);
    }
    public static function getLinkedCourseId($remote_id, $lms_code)
    {
        return self::getLinkedLocalID('course', $remote_id, $lms_code);
    }
    public static function getLinkedDealerId($remote_id, $lms_code)
    {
        return self::getLinkedLocalID('dealer', $remote_id, $lms_code);
    }
    public static function getLinkedRoles($roles, $lms_code): array
    {
        $return_array = [];
        foreach ($roles as $role_id){
            $linked_role_id = self::getLinkedLocalID('role', $role_id, $lms_code);
            if($linked_role_id)
                $return_array[] = $linked_role_id;
        }
        return $return_array;
    }

    public static function getRemoteList($entity, $local_id): array
    {
        $return = [];
        $arSelect = Array("ID", "PROPERTY_REMOTE_ID", 'PROPERTY_LMS');
        $arFilter = Array(
            "IBLOCK_ID"=>self::getRelatedIblock()['ID'],
            'PROPERTY_ENTITY' => self::getRelatedTypeIdByXML($entity),
            'PROPERTY_LOCAL_ID' => $local_id,
        );
        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
        while($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $return[] = $arFields;
        }
        return $return;
    }

    public static function setCompleteRemoteCompletion($link_row, $points)
    {
        $lms = \Settings\Synchronization::getLMSById($link_row['PROPERTY_LMS_VALUE']);
        if(check_full_array($lms) && $lms['ID'] == $link_row['PROPERTY_LMS_VALUE']) {
            if(!empty($lms['PROPERTY_HANDLER_PATH_VALUE']) && !empty($lms['PROPERTY_URL_VALUE'])) {
                $url = $lms['PROPERTY_URL_VALUE'] . $lms['PROPERTY_HANDLER_PATH_VALUE'];
                $httpClient = new \Bitrix\Main\Web\HttpClient();
                $data = array(
                    'method' => 'setCompleteCompletion',
                    'data' => [
                        'completion_id' => $link_row['PROPERTY_REMOTE_ID_VALUE'],
                        'points' => $points,
                        'from' => \Settings\Synchronization::getCurrentLMS()['CODE'],
                    ]
                );
                $httpClient->setHeader('Content-Type', 'application/x-www-form-urlencoded', true);
                $response = $httpClient->post($url, http_build_query($data));
                $response_array = json_decode($response, true);
            }
        }
    }
    public static function setFailedRemoteCompletion($link_row)
    {
        $lms = \Settings\Synchronization::getLMSById($link_row['PROPERTY_LMS_VALUE']);
        if(check_full_array($lms) && $lms['ID'] == $link_row['PROPERTY_LMS_VALUE']) {
            if(!empty($lms['PROPERTY_HANDLER_PATH_VALUE']) && !empty($lms['PROPERTY_URL_VALUE'])) {
                $url = $lms['PROPERTY_URL_VALUE'] . $lms['PROPERTY_HANDLER_PATH_VALUE'];
                $httpClient = new \Bitrix\Main\Web\HttpClient();
                $data = array(
                    'method' => 'setFailedCompletion',
                    'data' => [
                        'completion_id' => $link_row['PROPERTY_REMOTE_ID_VALUE'],
                        'from' => \Settings\Synchronization::getCurrentLMS()['CODE'],
                    ]
                );
                $httpClient->setHeader('Content-Type', 'application/x-www-form-urlencoded', true);
                $response = $httpClient->post($url, http_build_query($data));
                $response_array = json_decode($response, true);
            }
        }
    }
    public static function getRemoteCompletionsBySchedule($schedule_id)
    {
        $linkedSchedule = self::getLinkedLocalID('schedule', $schedule_id, self::getCurrentLMS()['CODE']);
        dump($linkedSchedule);
    }
}