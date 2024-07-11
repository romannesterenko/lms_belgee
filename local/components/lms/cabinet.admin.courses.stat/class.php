<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc as Loc;

class CabinetAdminCoursesStatComponent extends CBitrixComponent
{
    private $completed_items;
    /**
     * кешируемые ключи arResult
     * @var array() a
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
    private $new_items;
    private $needed_items;

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
        $result = array(

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
            throw new Main\LoaderException(Loc::getMessage('ERR_INCL_IB'));
    }
    /**
     * получение результатов
     */
    protected function getResult()
    {
        $this->arResult = [
            'ACTIVE_NOW' => [
                'NEW_EMPLOYEES' => $this->getActiveNowNewEmployees(),
                'COUNT_APPS' => $this->getActiveNowCountApps(),
                'COUNT_MEMBERS' => $this->getActiveNowCountMembers(),
                'ALL_ACTIVE_COURSES' => $this->getActiveNowAllActiveCourses()
            ],
            'TOTAL_ENROLLED' => [
                'ACTIVE_COURSES' => $this->getTotalEnrolledActiveCourses(),
                'ALL_COURSES' => $this->getTotalEnrolledAllCourses(),
                'MEMBERS_ENROLLED' => $this->getTotalEnrolledMembersEnrolled(),
                'NEW_REGISTRATIONS' => $this->getTotalEnrolledNewRegistrations(),
            ],
            'NEW_REGISTRATIONS' => [
                'NEW_EMPLOYEES' => $this->getTotalEnrolledActiveCourses(),
                'NEW_APPS' => $this->getTotalEnrolledAllCourses(),
                'MEMBERS' => $this->getTotalEnrolledMembersEnrolled(),
                'ACTIVE_COURSES' => $this->getTotalEnrolledNewRegistrations(),
            ],
            'ACTIVE_APPS' => [
                'NEW_EMPLOYEES' => $this->getTotalEnrolledActiveCourses(),
                'NEW_APPS' => $this->getTotalEnrolledAllCourses(),
                'MEMBERS' => $this->getTotalEnrolledMembersEnrolled(),
                'ACTIVE_COURSES' => $this->getTotalEnrolledNewRegistrations(),
            ],
            'TRAINING_ATTENDANCE' => [
                'RECORDED_PARTICIPANTS' => $this->getTotalEnrolledActiveCourses(),
                'ATTENDED_TRAININGS' => $this->getTotalEnrolledAllCourses(),
                'APPROVED_APPLICATIONS' => $this->getTotalEnrolledMembersEnrolled(),
                'IN_QUEUE' => $this->getTotalEnrolledNewRegistrations(),
            ],
            'CERTS' => [
                'ACTIVE' => $this->getTotalEnrolledActiveCourses(),
                'ENDED' => $this->getTotalEnrolledAllCourses(),
                'OVERDUE' => $this->getTotalEnrolledMembersEnrolled(),
            ],

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
    private function getActiveNowNewEmployees():int
    {
        return 12;
    }
    private function getActiveNowCountApps():int
    {
        return 8;
    }
    private function getActiveNowCountMembers():int
    {
        return 25;
    }
    private function getActiveNowAllActiveCourses():int
    {
        return 10;
    }

    private function getTotalEnrolledActiveCourses():int
    {
        return 12;
    }

    private function getTotalEnrolledAllCourses(): int
    {
        return 1;
    }

    private function getTotalEnrolledMembersEnrolled(): int
    {
        return 25;
    }

    private function getTotalEnrolledNewRegistrations(): int
    {
        return 13;
    }
}
?>