<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc as Loc;

class SheduleCardComponent extends CBitrixComponent
{
    /**
     * кешируемые ключи arResult
     * @var array()
     */
    protected $cacheKeys = array();

    /**
     * дополнительные параметры, от которых должен зависеть кеш
     * @var array
     */
    protected $cacheAddon = array();

    /**
     * парамтеры постраничной навигации
     * @var array
     */
    protected $navParams = array();

    /**
     * вохвращаемые значения
     * @var mixed
     */
    protected $returned;
    private $shop;
    /**
     * тегированный кеш
     * @var mixed
     */
    protected $tagCache;

    /**
     * подключает языковые файлы
     */
    public function onIncludeComponentLang()
    {
        $this->includeComponentLang(basename(__FILE__));
        Loc::loadMessages(__FILE__);
    }

    /**
     * подготавливает входные параметры
     * @param array $arParams
     * @return array
     */
    public function onPrepareComponentParams($params)
    {
        global $USER;
        $result = array(
            'COURSE_ID' => $params['COURSE_ID'],
            'USER_ID' => $_REQUEST['user']??$USER->GetID(),
        );
        return $result;
    }
    /**
     * проверяет подключение необходиимых модулей
     * @throws LoaderException
     */
    protected function checkModules()
    {
        if (!Main\Loader::includeModule('iblock'))
            throw new Main\LoaderException("Ошибка подключения модуля инфоблоков");
    }
    private function getScheduleCourse()
    {
        $schedule_courses = array_values(\Teaching\SheduleCourses::getCoursesList(['ID'=>$this->arParams['COURSE_ID']]));
        $schedule = $schedule_courses[0];
        $courses = \Teaching\Courses::getList(['ID' => $schedule['PROPERTY_COURSE_VALUE']], ['ID', 'NAME', 'CODE', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', "PROPERTY_LIMIT", "PROPERTY_COURSE_MARERIALS_FILES", "PROPERTY_TEXT_BLOCK_1", "PROPERTY_TEXT_BLOCK_2", "PROPERTY_TEXT_SLIDER", "PROPERTY_TIME_START", "PROPERTY_DURING", "PROPERTY_TRAINERS", "PROPERTY_SCHEDULE", "PROPERTY_COST", "PROPERTY_COURSE_TYPE", "PROPERTY_COURSE_FORMAT", "PROPERTY_ROLES"]);
        $schedule['REGISTRATION_COURSE'] = $this->getRegistrationStatus();
        $schedule['FREE_PLACES'] = \Teaching\SheduleCourses::getFreePlaces($schedule['ID'], $courses[$schedule['PROPERTY_COURSE_VALUE']]['PROPERTY_LIMIT_VALUE']);
        $courses[$schedule['PROPERTY_COURSE_VALUE']]['DETAIL_PAGE_URL'] = '/courses/'.$schedule['CODE'].'/';
        if($courses[$schedule['PROPERTY_COURSE_VALUE']]['PREVIEW_PICTURE']>0)
            $courses[$schedule['PROPERTY_COURSE_VALUE']]['PREVIEW_PICTURE'] = CFile::GetPath($courses[$schedule['PROPERTY_COURSE_VALUE']]['PREVIEW_PICTURE']);
        else
            $courses[$schedule['PROPERTY_COURSE_VALUE']]['PREVIEW_PICTURE'] = SITE_TEMPLATE_PATH."/images/img-3.jpg";
        $schedule['PROPERTY_COURSE_ITEM'] = $courses[$schedule['PROPERTY_COURSE_VALUE']];
        $schedule['IS_FREE'] = $schedule['PROPERTY_COURSE_ITEM']['PROPERTY_COURSE_TYPE_ENUM_ID']==6;
        return $schedule;
    }

    /**
     * получение результатов
     */
    protected function getResult()
    {
        $this->arResult = [
            'PARAMS' => $this->arParams,
            'ITEM' => $this->getScheduleCourse()
        ];
    }
    /**
     * выполняет логику работы компонента
     */
    public function executeComponent()
    {
        global $APPLICATION;
        try
        {
            $this->checkModules();
            $this->getResult();
            $this->includeComponentTemplate();

            return $this->returned;
        }
        catch (Exception $e)
        {
            ShowError($e->getMessage());
        }
    }

    private function getRegistrationStatus()
    {
        $array['SHOW_REGISTER_BUTTON'] = false;
        $enrollments = new \Teaching\Enrollments();
        $exist_enroll_info = $enrollments->getInfoById($this->arParams['USER_ID'], $this->arParams['COURSE_ID']);
        if(count($exist_enroll_info)>0){
            if($exist_enroll_info[0]['UF_IS_APPROVED']==1) {
                $array['TEXT'] = GetMessage('APPROVED_COURSE');
            }else{
                $array['TEXT'] = GetMessage('IN_APPROVE_COURSE');
            }
        }else{
            $array['SHOW_REGISTER_BUTTON'] = true;
            $array['TEXT'] = GetMessage('MUST_HAVE_COURSE');
        }
        return $array;
    }
}
?>