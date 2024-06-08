<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc as Loc;

class SheduleCoursesListComponent extends CBitrixComponent
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
        $arr = explode('?', $params['YEAR']);
        $year = !empty($arr[1])?$arr[0]:$params['YEAR'];
        $result = array(
            'MONTH' => $params['MONTH'],
            'YEAR' => $year,
            'FOR_ROLE' => $params['FOR_ROLE'],
            'PAGE_COUNT' => $params['PAGE_COUNT']?(int)$params['PAGE_COUNT']:50,
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
    private function getScheduleCourses($month, $year)
    {
        $filter = [];
        $return_ids = [];
        if($this->arParams['FOR_ROLE']) {
            if(check_full_array($this->arParams['FOR_ROLE'])) {
                $courses_for_role = \Teaching\Courses::getIdsByRole($this->arParams['FOR_ROLE']);
            } else {
                if($this->arParams['FOR_ROLE']==0)
                    $this->arParams['FOR_ROLE'] = \Teaching\Roles::getByCurrentUser();
                else
                    $this->arParams['FOR_ROLE'] = [(int)$this->arParams['FOR_ROLE']];
                $courses_for_role = \Teaching\Courses::getIdsByRole($this->arParams['FOR_ROLE']);
                $courses_for_role = check_full_array($courses_for_role)?$courses_for_role:[];
            }
        }else{
            $this->arParams['FOR_ROLE'] = \Teaching\Roles::getByCurrentUser();
            $courses_for_role = \Teaching\Courses::getIdsByRole($this->arParams['FOR_ROLE']);
            $courses_for_role = check_full_array($courses_for_role)?$courses_for_role:[];
        }
        if(\Models\User::isTeachingAdmin()) {
            $user_id = \Helpers\UserHelper::prepareUserId(0);
            $courses_ids = array_keys(\Models\Course::getByTeachingAdmin($user_id));
            $courses_for_role = array_unique(array_merge($courses_for_role, $courses_ids));
        }

        if(count($courses_for_role)==0)
            return [];
        //получим курсы доступные для роли
        foreach(\Teaching\SheduleCourses::getSchedulesByMonthForList($this->arParams['MONTH'], $courses_for_role, false) as $schedule){
            if (!in_array($schedule['PROPERTY_COURSE_VALUE'], $return_ids))
                $return_ids[] = $schedule['PROPERTY_COURSE_VALUE'];
        }
        return $return_ids;
    }
    private function getSchedules($month, $year)
    {
        $filter = [];
        $return_ids = [];
        if($this->arParams['FOR_ROLE']) {
            if(check_full_array($this->arParams['FOR_ROLE'])) {
                $courses_for_role = \Teaching\Courses::getIdsByRole($this->arParams['FOR_ROLE']);
            } else {
                if($this->arParams['FOR_ROLE']==0)
                    $this->arParams['FOR_ROLE'] = \Teaching\Roles::getByCurrentUser();
                else
                    $this->arParams['FOR_ROLE'] = [(int)$this->arParams['FOR_ROLE']];
                $courses_for_role = \Teaching\Courses::getIdsByRole($this->arParams['FOR_ROLE']);
                $courses_for_role = check_full_array($courses_for_role)?$courses_for_role:[];
            }
        }else{
            $this->arParams['FOR_ROLE'] = \Teaching\Roles::getByCurrentUser();
            $courses_for_role = \Teaching\Courses::getIdsByRole($this->arParams['FOR_ROLE']);
            $courses_for_role = check_full_array($courses_for_role)?$courses_for_role:[];
        }
        if(\Models\User::isTeachingAdmin()) {
            $user_id = \Helpers\UserHelper::prepareUserId(0);
            $courses_ids = array_keys(\Models\Course::getByTeachingAdmin($user_id));
            $courses_for_role = array_unique(array_merge($courses_for_role, $courses_ids));
        }

        if(count($courses_for_role)==0)
            return [];
        //получим курсы доступные для роли
        foreach(\Teaching\SheduleCourses::getSchedulesByMonthForList($this->arParams['MONTH'], $courses_for_role, false) as $schedule){
            $return_ids[] = $schedule['ID'];
        }
        return $return_ids;
    }
    private function getSchedulesList($month=0)
    {
        if($month==0)
            $month = date('m');
        if(\Models\User::isTeachingAdmin()) {
            return \Teaching\SheduleCourses::getSchedulesByMonthToTeachingAdmin($month);
        }else {
            return \Teaching\SheduleCourses::getSchedulesByMonth($month);
        }
    }
    private function getRolesList()
    {
        $return_roles = [];
        $need_roles = \Helpers\UserHelper::getRoleByCurrentUser();
        if(count($need_roles)>0) {
            foreach (\Teaching\Roles::getRolesList() as $key => $role) {
                if (in_array($key, $need_roles))
                    $return_roles[$key] = $role;
            }
        }
        return $return_roles;
    }
    private function getMonthsList(){
        $list = [0 => Loc::getMessage('FREE_SCHEDULE')];
        return array_merge($list, \Helpers\DateHelper::getMonthArray(6));
    }
    /**
     * получение результатов
     */
    protected function getResult()
    {
        $month = $this->arParams['MONTH']??date('m');
        $year = $this->arParams['YEAR']??date('Y');
        $this->arResult = array(
            'PARAMS' => $this->arParams,
            'ROLES_SELECT' => $this->getRolesList(),
            'MONTH_SELECT' => $this->getMonthsList(),
            'ITEMS' => $this->getScheduleCourses($month, $year),
            'SHEDULES' => $this->getSchedules($month, $year),
        );

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





}
?>