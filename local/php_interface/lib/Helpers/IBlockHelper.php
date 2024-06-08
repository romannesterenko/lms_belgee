<?php

namespace Helpers;
class IBlockHelper
{
    private static int $courses_iblock = COURSES_IBLOCK;
    private static int $roles_iblock = ROLES_IBLOCK;
    private static int $shedules_iblock = SHEDULES_IBLOCK;
    private static int $zoom_accounts_iblock = ZOOM_ACCOUNTS_IBLOCK;
    private static int $materials_iblock = MATERIALS_IBLOCK;
    private static int $speakers_iblock = SPEAKERS_IBLOCK;
    private static int $materials_files_iblock = MATERIALS_FILES_IBLOCK;
    private static int $news_iblock = NEWS_IBLOCK;
    private static int $polls_iblock = POLLS_IBLOCK;
    private static int $dealers_iblock = DEALER_IBLOCK;
    private static int $cert_iblock = CERT_IBLOCK;
    private static int $feedback_poll_iblock = FEEDBACK_POLL_IBLOCK;
    private static int $completions_iblock = COMPLETIONS_IBLOCK;
    private static int $tests_iblock = TESTS_IBLOCK;
    private static int $test_questions_iblock = TEST_QUESTIONS_IBLOCK;

    public static function includeIBlockModule()
    {
        return \CModule::IncludeModule('iblock');
    }

    /**
     * @return int
     */
    public static function getCoursesIBlock()
    {
        return self::$courses_iblock;
    }

    /**
     * @return int
     */
    public static function getSpeakersIBlock()
    {
        return self::$speakers_iblock;
    }

    /**
     * @return int
     */
    public static function getRolesIBlock()
    {
        return self::$roles_iblock;
    }

    /**
     * @return int
     */
    public static function getDealersIBlock()
    {
        return self::$dealers_iblock;
    }

    /**
     * @return int
     */
    public static function getShedulesIBlock()
    {
        return self::$shedules_iblock;
    }

    /**
     * @return int
     */
    public static function getZoomAccountsIBlock()
    {
        return self::$zoom_accounts_iblock;
    }

    public static function getById($id, $collect_properties = false, $select = [])
    {
        self::includeIBlockModule();
        $item = \CIBlockElement::GetList(['ID' => 'ASC'], ['ACTIVE' => 'Y', 'ID' => $id], false, false, $select)->Fetch();
        if(!$item['ID']>0)
            return [];
        if ($collect_properties)
            return \Helpers\PropertyHelper::collectFields($item);
        return $item;
    }

    public static function getElements($filter, $order = [], $select = [])
    {
        $list = [];
        \CModule::IncludeModule('iblock');
        $res = \CIBlockElement::GetList($order, $filter, false, array(), $select);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $list[$arFields['ID']] = $arFields;
        }
        return $list;
    }

    public static function getSections($filter, $select = [], $order = array('ID' => 'ASC'))
    {
        $list = [];
        $rsSections = \CIBlockSection::GetList($order, $filter, false, $select);
        while ($arSection = $rsSections->Fetch()) {
            $list[] = $arSection;
        }
        return $list;
    }

    public static function updateSection($id, $fields)
    {
        self::includeIBlockModule();
        $bs = new \CIBlockSection;
        return $bs->Update($id, $fields);
    }

    public static function getMaterialsIBlock()
    {
        return self::$materials_iblock;
    }

    public static function getMaterialsFilesIBlock()
    {
        return self::$materials_files_iblock;
    }

    public static function getNewsIBlock()
    {
        return self::$news_iblock;
    }

    public static function getPollsIBlock()
    {
        return self::$polls_iblock;
    }

    public static function getCertIBlock()
    {
        return self::$cert_iblock;
    }

    public static function getFeedbackPollIBlock()
    {
        return self::$feedback_poll_iblock;
    }

    public static function getProperty($ib_id, $el_id, $code)
    {
        self::includeIBlockModule();
        $db_props = \CIBlockElement::GetProperty($ib_id, $el_id, array("sort" => "asc"), Array("CODE"=>$code));
        if($ar_props = $db_props->Fetch())
            return $ar_props;
    }
    /**
     * @return int
     */

    public static function getCompletionIblock()
    {
        return self::$completions_iblock;
    }

    /**
     * @return int
     */
    public static function getTestsIblock()
    {
        return self::$tests_iblock;
    }

    /**
     * @return int
     */
    public static function getTestQuestionsIblock()
    {
        return self::$test_questions_iblock;
    }
}