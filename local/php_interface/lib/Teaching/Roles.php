<?php

namespace Teaching;

use CIBlockElement;
use CIBlockSection;
use Helpers\IBlockHelper;
use Helpers\UserHelper;
use Models\Course;
use Models\User;
use Settings\Common;
use Settings\Reports;
use Teaching\CourseCompletion;
use Teaching\Courses;

class Roles
{
    /**
     * @return array
     */
    public static function getAllRolesList($filter = [], $select = [])
    {
        if (!IBlockHelper::includeIBlockModule())
            return [];
        if (is_array($filter['ID'])&&count($filter['ID'])==0)
            return [];

        $roles_list = [];
        $arSelect = array_merge(array("ID", "NAME"), $select);
        $arFilter = array_merge(array("IBLOCK_ID" => IBlockHelper::getRolesIBlock()), $filter);

        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);

        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            if (count($select) > 0) {
                $roles_list[$arFields['ID']] = $arFields;
            }else {
                $roles_list[$arFields['ID']] = $arFields['NAME'];
            }
        }

        return $roles_list;
    }
    public static function getRolesList($filter = [], $select = [])
    {
        if (!IBlockHelper::includeIBlockModule())
            return [];
        if (array_key_exists('ID', $filter) && is_array($filter['ID']) && count($filter['ID'])==0)
            return [];

        $roles_list = [];
        $arSelect = array_merge(array("ID", "NAME"), $select);
        $arFilter = array_merge(array("IBLOCK_ID" => IBlockHelper::getRolesIBlock(), "ACTIVE" => "Y"), $filter);

        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);

        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            if (count($select) > 0) {
                $roles_list[$arFields['ID']] = $arFields;
            }else {
                $roles_list[$arFields['ID']] = $arFields['NAME'];
            }
        }

        return $roles_list;
    }
    public static function getRolesListByAdminDc()
    {
        global $USER;
        if (!IBlockHelper::includeIBlockModule())
            return [];
        $roles_list = [];
        $arSelect = array("ID", "NAME");
        $arFilter = array("IBLOCK_ID" => IBlockHelper::getRolesIBlock(), "ACTIVE" => "Y", 'ID' => Roles::getRoleIdsByTeachingType(User::getTeachingType($USER->GetID())));
        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $roles_list[$arFields['ID']] = $arFields['NAME'];
        }
        return $roles_list;
    }
    public static function getTGChannelLinkByRole($role_id){
        $db_props = CIBlockElement::GetProperty(IBlockHelper::getRolesIBlock(), $role_id, array("sort" => "asc"), array("CODE" => "TG_CHANEL"));
        if ($ar_props = $db_props->Fetch())
            return $ar_props["VALUE"];
    }
    public static function getChannelListFromRoleIds($role_ids){
        $channels = [];
        foreach ($role_ids as $role_id)
            $channels[] = self::getTGChannelLinkByRole($role_id);
        return array_unique($channels);
    }
    public static function getRolesWithTG(){
        if (!IBlockHelper::includeIBlockModule())
            return [];
        $group = [];
        if(!empty(\Settings\Common::getTestTGChannel())){
            $group[\Settings\Common::getTestTGChannel()][] = ['ID' => 385, 'NAME' => 'Тестовая роль', 'PROPERTY_TG_CHANEL_VALUE' => \Settings\Common::getTestTGChannel()];
        }else{
            $arSelect = array("ID", "NAME", "PROPERTY_TG_CHANEL");
            $arFilter = array("IBLOCK_ID" => IBlockHelper::getRolesIBlock(), "ACTIVE" => "Y", '!PROPERTY_TG_CHANEL' => false);
            $res = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $group[$arFields['PROPERTY_TG_CHANEL_VALUE']][] = $arFields;

            }
        }
        return $group;
    }
    public static function getGenitiveForm($role_id)
    {
        if(count($role_id)==0){
            return ['всех ролей'];
        }else {
            $role = self::getRolesList(['ID' => $role_id], ['PROPERTY_GENITIVE']);
            if (is_array($role_id)) {
                $roles = [];
                foreach ($role as $item)
                    $roles[] = $item['PROPERTY_GENITIVE_VALUE'] ? mb_strtolower($item['PROPERTY_GENITIVE_VALUE']) : mb_strtolower($item['NAME']);
                return $roles;
            } else {
                return $role[$role_id]['PROPERTY_GENITIVE_VALUE'];
            }
        }
    }

    /**
     * @param int $user_id
     * @return array
     */
    public static function GetRequiredCourseIdsByUser($user_id = 0)
    {
        $user_id = UserHelper::prepareUserId($user_id);
        $by_user = UserHelper::getUserValue($user_id, 'UF_REQUIRED_COURSES');

        $by_user = is_array($by_user)?$by_user:[];
        $by_role = self::GetRequiredCourseIdsByRole(UserHelper::getRoleByUser($user_id));

        $completion = new CourseCompletion();
        $complete_ids = [];
        foreach ($completion->getCompletedItems($user_id)->getArray() as $completedItem) {
            if(!Courses::isExpired($completedItem['UF_COURSE_ID']))
                $complete_ids[] = $completedItem['UF_COURSE_ID'];
        }
        $merge_ids = array_unique(array_merge($by_user, $by_role));
        $filter_ids = array_diff($merge_ids, $complete_ids);
        $ids = [];
        if(count($filter_ids)>0) {
            foreach (Courses::getList(['ACTIVE' => 'Y', 'ID' => $filter_ids], ['ID']) as $item)
                $ids[] = $item['ID'];
        }
        return $ids;
    }

    /**
     * @param $role_id
     * @return array
     */
    public static function GetRequiredCourseIdsByRole($role_id)
    {
        if (!IBlockHelper::includeIBlockModule())
            return [];
        $ids = [];
        if(is_array($role_id)){
            foreach ($role_id as $id){
                $db_props = CIBlockElement::GetProperty(IBlockHelper::getRolesIBlock(), $id, array("sort" => "asc"), array("CODE" => "COURSES"));
                while ($ar_props = $db_props->Fetch()) {
                    $ids[] = IntVal($ar_props["VALUE"]);
                }
            }
        }else{
            $db_props = CIBlockElement::GetProperty(IBlockHelper::getRolesIBlock(), $role_id, array("sort" => "asc"), array("CODE" => "COURSES"));
            while ($ar_props = $db_props->Fetch()) {
                $ids[] = IntVal($ar_props["VALUE"]);
            }
        }
        $ids = array_unique(array_diff($ids, [0]));
        return count($ids) == 1 && $ids[0] == 0 ? [] : $ids;
    }

    /**
     * @param $course_id
     * @param bool $genitive
     * @return string
     */
    public static function getRolesForCourse($course_id, $genitive = true)
    {
        if (!IBlockHelper::includeIBlockModule())
            return '';
        $ids = [];
        $values = [];
        $db_props = CIBlockElement::GetProperty(IBlockHelper::getCoursesIBlock(), $course_id, array("sort" => "asc"), array("CODE" => "ROLES"));
        while ($ar_props = $db_props->Fetch())
            $ids[] = $ar_props['VALUE'];
        $arFilter = array_merge(["IBLOCK_ID" => IBlockHelper::getRolesIBlock()], ['ID' => $ids]);
        $arSelect = ['NAME'];
        if ($genitive)
            $arSelect[] = 'PROPERTY_GENITIVE';
        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $values[] = $arFields['PROPERTY_GENITIVE_VALUE']?mb_strtolower($arFields['PROPERTY_GENITIVE_VALUE']):mb_strtolower($arFields['NAME']);
        }
        return implode(' / ', $values);
    }
    public static function getMustRolesForCourse($course_id, $genitive = true)
    {
        if (!IBlockHelper::includeIBlockModule())
            return '';
        $ids = [];
        $values = [];
        $roles = self::getRolesForReqCourse($course_id);
        if(!check_full_array($roles))
            return '';
        foreach ($roles as $one_role){
            $ids[] = $one_role['ID'];
        }
        $arFilter = array_merge(["IBLOCK_ID" => IBlockHelper::getRolesIBlock()], ['ID' => $ids]);
        $arSelect = ['NAME'];
        if ($genitive)
            $arSelect[] = 'PROPERTY_GENITIVE';
        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), $arSelect);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $values[] = $arFields['PROPERTY_GENITIVE_VALUE']?mb_strtolower($arFields['PROPERTY_GENITIVE_VALUE']):mb_strtolower($arFields['NAME']);
        }
        return implode(' / ', $values);
    }

    /**
     * @param $course_id
     * @param bool $genitive
     * @return string
     */
    public static function getRoleIdsForCourse($course_id)
    {
        if (!IBlockHelper::includeIBlockModule())
            return [];
        $ids = [];
        $db_props = CIBlockElement::GetProperty(IBlockHelper::getCoursesIBlock(), $course_id, array("sort" => "asc"), array("CODE" => "ROLES"));
        while ($ar_props = $db_props->Fetch()) {
            if($ar_props['VALUE']>0)
                $ids[] = $ar_props['VALUE'];
        }
        foreach (Roles::getRolesForReqCourse($course_id) as $r){
            $ids[] = $r['ID'];
        }
        return $ids;
    }

    /**
     * @return int
     */
    public static function getByCurrentUser()

    {
        return UserHelper::getRoleByCurrentUser();
    }

    /**
     * @return int
     */
    public static function getByUser($user_id = 0)
    {
        $user_id = UserHelper::prepareUserId($user_id);
        return UserHelper::getRoleByUser($user_id);
    }

    public static function getRolesForReqCourse($course_id)
    {
        return self::getRolesList(['PROPERTY_COURSES' => $course_id], ['ID', 'NAME']);
    }

    public static function getById($ids)
    {
        if(!check_full_array($ids))
            return [];
        return self::getRolesList(['ID' => $ids]);
    }

    public static function getRoleIdsByTeachingType($getTeachingType)
    {
        $role_sections = [
            15 => 2,
            16 => 3,
            53 => 139
        ];
        if(empty($getTeachingType))
            return [];
        $return_role_sections = [];
        $return_roles = [];
        $return_role_ids = [];
        foreach ($getTeachingType as $id) {
            $return_role_sections[] = $role_sections[$id];
        }
        if(in_array(15, $getTeachingType) && Common::get('sale_admin_has_marketing_rights') == 'Y')
            $return_role_sections[] = 139;
        foreach ($return_role_sections as $section){
            foreach (Roles::getBySection($section) as $id => $role){
                $return_roles[$id] = $role;
            }
        }
        return array_keys($return_roles);
    }
    public static function getTeachingTypeByRole($role_id){
        $role_sections = [
            2 => 15,
            3 => 16,
        ];
        foreach ($role_sections as $section => $admin_type){
            foreach (Roles::getBySection($section) as $id => $role){
                if(in_array($id, $role_id))
                    return $admin_type;
            }
        }
    }
    private static function getBySection($section)
    {
        return self::getRolesList(['SECTION_ID' => $section, 'INCLUDE_SUBSECTIONS' => 'Y']);
    }
    public static function getAll()
    {
        return self::getRolesList(['ACTIVE' => 'Y']);
    }

    public static function getOPRoles()
    {
        return self::getBySection(2);
    }

    public static function getMarketingRoles()
    {
        return self::getBySection(139);
    }

    public static function getPPORoles()
    {
        return self::getBySection(3);
    }

    public static function getRequiredCoursesForRoles($roles)
    {
        $course_ids = [];
        $list = self::getRolesList(["ID" => $roles, '!PROPERTY_COURSES' => false], ['ID', 'NAME', 'PROPERTY_COURSES']);
        foreach ($list as $role){
            $course_ids = array_merge($course_ids, $role['PROPERTY_COURSES_VALUE']);
        }
        return array_unique($course_ids);
    }

    public static function getRequiredCoursesForRolesArray($roles)
    {
        $course_ids = [];
        $list = self::getRolesList(["ID" => $roles, '!PROPERTY_COURSES' => false], ['ID', 'NAME', 'PROPERTY_COURSES']);
        foreach ($list as $role){
            $course_ids[$role['ID']] = $role['PROPERTY_COURSES_VALUE'];
        }
        return $course_ids;
    }

    public static function getMaxOnDealer(mixed $role_id): int
    {
        $db_props = CIBlockElement::GetProperty(IBlockHelper::getRolesIBlock(), $role_id, array("sort" => "asc"), array("CODE" => "MAX_USERS_ON_DEALER"))->fetch();
        return (int)$db_props['VALUE'];
    }

    public static function parseFromStringIds(mixed $code): string
    {
        return implode("<br />", Roles::getById(explode(", ", $code)));
    }

    private static function getSectionList($filter, $select)
    {
        $dbSection = CIBlockSection::GetList(
            Array(
                'LEFT_MARGIN' => 'ASC',
            ),
            array_merge(
                Array(
                    'ACTIVE' => 'Y',
                    'GLOBAL_ACTIVE' => 'Y'
                ),
                is_array($filter) ? $filter : Array()
            ),
            false,
            array_merge(
                Array(
                    'ID',
                    'IBLOCK_SECTION_ID'
                ),
                is_array($select) ? $select : Array()
            )
        );

        while( $arSection = $dbSection-> GetNext(true, false) ){

            $SID = $arSection['ID'];
            $PSID = (int) $arSection['IBLOCK_SECTION_ID'];

            $arLincs[$PSID]['CHILDS'][$SID] = $arSection;

            $arLincs[$SID] = &$arLincs[$PSID]['CHILDS'][$SID];
        }

        return array_shift($arLincs);
    }
    public static function getAllTree()
    {
        $roles_po_servis = self::getSectionList(['IBLOCK_ID' => IBlockHelper::getRolesIBlock()], ['ID', 'NAME']);

        foreach ($roles_po_servis['CHILDS'] as $key => $sect) {
            $qwerty[$sect['ID']] = $sect['NAME'];
            if(check_full_array($sect['CHILDS'])) {
                foreach ($sect['CHILDS'] as $child)
                    $qwerty[$child['ID']] = $sect['NAME'];
            }
        }
        return $qwerty;
    }
}