<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Localization\Loc as Loc;
use Helpers\DateHelper;
use Helpers\IBlockHelper;
use Teaching\Courses;
use Teaching\Enrollments;
use Teaching\Roles;
use Teaching\SheduleCourses;

class CourseCardComponent extends CBitrixComponent
{

    /**
     * вохвращаемые значения
     * @var mixed
     */
    protected $returned;
    /**
     * тегированный кеш
     * @var mixed
     */
    protected $tagCache;
    private array $item;
    private array $enrolls;

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
    public function onPrepareComponentParams($arParams):array
    {
        global $USER;
        return array(
            'COURSE_ID' => $arParams['COURSE_ID'],
            'USER_ID' => $_REQUEST['user']??$USER->GetID(),
        );
    }
    /**
     * проверяет подключение необходиимых модулей
     * @throws LoaderException
     */
    protected function checkModules()
    {
        if (!Main\Loader::includeModule('iblock'))
            throw new Main\LoaderException(Loc::getMessage('IBLOCK_ERROR'));
    }
    private function getCourse()
    {
        $this->item = IBlockHelper::getById($this->arParams['COURSE_ID'],true,  ['IBLOCK_ID', 'ID', 'NAME', 'PREVIEW_PICTURE', 'PREVIEW_TEXT', 'CODE']);
        if(check_full_array($this->item)) {
            $this->processItemData();
        }

    }

    /**
     * получение результатов
     */
    protected function getResult()
    {
        $this->getCourse();
        $this->arResult = [
            'PARAMS' => $this->arParams,
            'ITEM' => $this->item
        ];
    }
    /**
     * выполняет логику работы компонента
     */
    public function executeComponent()
    {
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

    private function calculateSheduleData()
    {
        $this->item['IS_FOR_SINGLE_STUDY'] = Courses::isFreeSheduleCourse($this->item['ID']);
        $this->item['IS_COMPLETED_COURSE'] = Courses::isCompleted($this->item['ID']);
        $this->item['HAS_FREE_PLACES'] = false;
        $this->item['HAS_SCHEDULES'] = false;
        if(\Helpers\UserHelper::isTeachingAdmin()||!$this->item['IS_FOR_SINGLE_STUDY']&&!$this->item['IS_COMPLETED_COURSE']||SheduleCourses::isExistsCheckedByCourse($this->item['ID'])) {
            $schedules = SheduleCourses::getAvailableOrProcessByCourse($this->item['ID']);

            $this->item['AVAILABLE_SCHEDULES_BY_DATE'] = SheduleCourses::getAvailableByCourseByDate($this->item['ID']);
            if (count($schedules) > 0) {
                if (count($schedules) == 1) {
                    $schedule = array_values($schedules)[0];
                    $this->item['HAS_FREE_PLACES'] = true;
                    $this->item['HAS_DATES'] = false;
                    $this->item['ALLOW_TO_REGISTER_BY_DATE'] = true;
                    if (!empty($schedule['PROPERTIES']['BEGIN_DATE']) && !empty($schedule['PROPERTIES']['END_DATE'])) {
                        $this->item['HAS_DATES'] = true;
                        $this->item['BEGIN_TIME'] = DateHelper::getHumanDate($schedule['PROPERTIES']['BEGIN_DATE'], 'H:i');
                        $this->item['BEGIN_DATE'] = DateHelper::getHumanDate($schedule['PROPERTIES']['BEGIN_DATE'], 'd F');
                        $this->item['END_DATE'] = DateHelper::getHumanDate($schedule['PROPERTIES']['END_DATE']);
                        $this->item['BEGIN_DATE_WITHOUT_FORMATTING'] = DateHelper::getHumanDate($schedule['PROPERTIES']['BEGIN_DATE'], 'd.m.Y');
                        $this->item['END_DATE_WITHOUT_FORMATTING'] = DateHelper::getHumanDate($schedule['PROPERTIES']['END_DATE'], 'd.m.Y');
                    }
                    if((int)$schedule['PROPERTIES']['LIMIT']>0) {
                        $this->item['FREE_PLACES'] = (int)SheduleCourses::getFreePlaces($schedule['ID'], $schedule['PROPERTIES']['LIMIT']);
                        $this->item['LIMIT'] = $schedule['PROPERTY_LIMIT_VALUE'];
                        $this->item['HAS_FREE_PLACES'] = $this->item['FREE_PLACES'] > 0;
                    }else{
                        $this->item['HAS_FREE_PLACES'] = true;
                        $this->item['NO_LIMIT'] = true;
                    }
                    if($schedule['PROPERTIES']['BEGIN_REGISTRATION_DATE']){
                        $tmstmp = strtotime($schedule['PROPERTIES']['BEGIN_REGISTRATION_DATE']);
                        if(time()<$tmstmp) {
                            $this->item['ALLOW_TO_REGISTER_BY_DATE'] = false;
                        }

                    }
                    if($this->item['ALLOW_TO_REGISTER_BY_DATE']&&$schedule['PROPERTIES']['BEGIN_DATE']){
                        $tmstmp = strtotime($schedule['PROPERTIES']['BEGIN_DATE']);
                        if(time()>$tmstmp)
                            $this->item['ALLOW_TO_REGISTER_BY_DATE'] = false;
                    }
                    if($schedule['PROPERTIES']['END_REGISTRATION_DATE']){
                        $tmstmp = strtotime($schedule['PROPERTIES']['END_REGISTRATION_DATE']);
                        if(time()>$tmstmp)
                            $this->item['ALLOW_TO_REGISTER_BY_DATE'] = false;
                    }
                    if (!empty($schedule['PROPERTIES']['BEGIN_REGISTRATION_DATE']) && !empty($schedule['PROPERTIES']['END_REGISTRATION_DATE'])) {
                        $this->item['BEGIN_REGISTRATION'] = DateHelper::getHumanDate($schedule['PROPERTIES']['BEGIN_REGISTRATION_DATE'], 'd.m H:i');
                        $this->item['END_REGISTRATION'] = DateHelper::getHumanDate($schedule['PROPERTIES']['END_REGISTRATION_DATE'], 'd.m');
                    }
                }else{
                    $this->item['FREE_PLACES_CNT'] = 0;
                    foreach ($schedules as $schedule){
                        $this->item['FREE_PLACES_CNT']+=(int)SheduleCourses::getFreePlaces($schedule['ID'], $schedule['PROPERTIES']['LIMIT']);
                    }
                    $this->item['ALLOW_TO_REGISTER_BY_DATE'] = false;
                    $reg_found = false;
                    //если настало время для ренистрации хотя бы одного из расписаний, выводим кнопку
                    foreach ($schedules as $schedule){

                        if($schedule['PROPERTIES']['BEGIN_REGISTRATION_DATE']){
                            $reg_found = true;
                            $tmstmp = strtotime($schedule['PROPERTIES']['BEGIN_REGISTRATION_DATE']);
                            if(time()>$tmstmp) {
                                $this->item['ALLOW_TO_REGISTER_BY_DATE'] = true;
                                break;
                            }
                        } else {
                            $this->item['ALLOW_TO_REGISTER_BY_DATE'] = true;
                            break;
                        }
                    }
                    if(!$reg_found&&!$this->item['ALLOW_TO_REGISTER_BY_DATE']) {
                        foreach ($schedules as $schedule) {
                            if ($schedule['PROPERTIES']['BEGIN_DATE']) {
                                $tmstmp = strtotime($schedule['PROPERTIES']['BEGIN_DATE']);
                                if (time() < $tmstmp)
                                    $this->item['ALLOW_TO_REGISTER_BY_DATE'] = true;
                                    break;
                            }
                        }
                    }
                    $this->item['HAS_MANY_SHEDULES'] = true;
                    $this->item['HAS_FREE_PLACES'] = $this->item['FREE_PLACES_CNT']>0;
                }
                $this->item['HAS_SCHEDULES'] = true;
                $this->item['SCHEDULES'] = $schedules;
            }
        }
    }

    private function isRequired()
    {
        return in_array($this->item['ID'], Roles::GetRequiredCourseIdsByUser());
    }

    private function getLabel()
    {
        if(Courses::wasStarted($this->item['ID'])){
            $this->item['NEED_LABEL'] = true;
            $this->item['INFO'] = Loc::getMessage('IN_ACTIVE_INFO');
            $this->item['LABEL'] = Loc::getMessage('IN_ACTIVE_LABEL');
        } elseif (!\Models\Course::isIgnoreStatus($this->item['ID'])&&$this->item['IS_COMPLETED_COURSE']&&!SheduleCourses::isExistsCheckedByCourse($this->item['ID'])){
            $this->item['NEED_LABEL'] = true;
            $this->item['INFO'] = Loc::getMessage('COMPLETE_INFO');
            $this->item['LABEL'] = Loc::getMessage('COMPLETE_LABEL');
        } else {
            $enrollments = new Enrollments();
            $completions = new \Teaching\CourseCompletion();
            $this->item['NEED_LABEL'] = false;
            $this->enrolls = $this->item['ENROLLS'] = $enrollments->getByUserAndCourse($this->item['ID']);
            //$this->compls = $this->item['ENROLLS'] = $completions->getByUserAndCourse($this->item['ID']);
            if ($this->item['HAS_SCHEDULES']) {
                if (count($this->enrolls) > 0) {
                    $this->item['INFO'] = GetMessage("IN_APPROVE_COURSE");
                    $this->item['LABEL'] = GetMessage("IN_APPROVE_COURSE");
                    foreach ($this->enrolls as $enroll) {
                        if ($enroll['UF_IS_APPROVED'] == 1 && $enroll['UF_DIDNT_COM'] != 1) {
                            if ($enroll['UF_FAILED'] == 1){
                                $this->item['INFO'] = GetMessage("FAILED_COURSE");
                                $this->item['LABEL'] = GetMessage("FAILED_COURSE");
                            } else {
                                $this->item['ALREADY_ENROLLED'] = true;
                                $this->item['HAS_DATES'] = false;
                                $this->item['INFO'] = GetMessage("APPROVED_COURSE")." ".$enroll['UF_DATE'];
                                $this->item['LABEL'] = GetMessage("APPROVED_COURSE");
                            }
                            break;
                        }
                    }
                    $this->item['NEED_LABEL'] = true;
                }
            }
            if (!$this->item['NEED_LABEL'] && $this->isRequired()) {
                $this->item['NEED_LABEL'] = true;
                $this->item['LABEL'] = GetMessage("MUST_HAVE_COURSE");
            }
        }
    }

    private function processItemData()
    {
        $this->item['DETAIL_PAGE_URL'] = Courses::generateUrl($this->item['CODE']);
        $this->calculateSheduleData();
        $this->getLabel();
        $this->getForRoles();
        $this->calculateButtonInfo();
    }

    private function getForRoles()
    {
        if(!empty($this->item['PROPERTIES']['ROLES'])&&count($this->item['PROPERTIES']['ROLES'])>0) {
            $role_ids = [];
            foreach ($this->item['PROPERTIES']['ROLES'] as $one_role)
                $role_ids[] = $one_role['VALUE'];
            $this->item['PROPERTIES']['FOR_ROLES'] = Roles::getGenitiveForm($role_ids);
        }
    }

    private function calculateButtonInfo()
    {

        $this->item['REGISTER_BUTTON']['NEED_SHOW'] = false;
        if(Courses::wasStarted($this->item['ID'])) {
            $this->item['WAS_STARTED'] = true;
            $this->item['COMPLETION_LINK'] = '/cabinet/courses/completions/'.$this->item['ID'].'/';
        } elseif (Courses::isFreeSheduleCourse($this->item['ID'])){
            if (\Models\Course::isIgnoreStatus($this->item['ID'])||!$this->item['IS_COMPLETED_COURSE'])
                $this->item['REGISTER_BUTTON']['NEED_SHOW'] = true;
        } else {
            if ($this->item['HAS_SCHEDULES'] && count($this->item['SCHEDULES']) > 0) {
                if(\Models\User::isTeachingAdmin())
                    $this->item['REGISTER_EMPLOYEE_BUTTON']['NEED_SHOW'] = true;
                if (count($this->item['SCHEDULES']) == 1) {
                    $this->item['REGISTER_BUTTON']['POPUP_FUNCTION'] = 'showEnrollForm';
                } else {
                    $this->item['REGISTER_BUTTON']['POPUP_FUNCTION'] = 'showCalendar';
                }
                if($this->item['ALLOW_TO_REGISTER_BY_DATE']) {
                    $this->item['REGISTER_BUTTON']['NEED_SHOW'] = true;
                    if(\Models\User::isTeachingAdmin()&&\Helpers\UserHelper::isLocalAdmin()){
                        $this->item['REGISTER_EMPLOYEE_BUTTON']['NEED_SHOW'] = true;
                    }
                }
            }
            if (!empty($this->enrolls) && count($this->enrolls) > 0) {
                $this->item['REGISTER_BUTTON']['NEED_SHOW'] = false;
            }
        }

        if(!in_array($this->item['ID'], \Teaching\Courses::getCoursesByUser())){
            $this->item['REGISTER_BUTTON']['NEED_SHOW'] = false;
            $this->item['REGISTER_BUTTON']['NOT_NEED_SHOW'] = true;

        }
        $this->item['NOT_CHECK'] = false;
        foreach ($this->item['SCHEDULES'] as $SCHEDULE){
            if($SCHEDULE['PROPERTIES']['DONT_CHECK_COMPLETIONS']=='Y') {
                $this->item['NOT_CHECK'] = true;
                break;
            }
        }
        if($this->item['IS_COMPLETED_COURSE']&&!$this->item['NOT_CHECK']){
            $this->item['REGISTER_BUTTON']['NEED_SHOW'] = false;
            $this->item['COMPLETION_LINK'] = '/cabinet/courses/completions/'.$this->item['ID'].'/';
        }
    }
}
