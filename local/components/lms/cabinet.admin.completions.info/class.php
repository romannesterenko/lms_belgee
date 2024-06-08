<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc as Loc;
use Helpers\UserHelper;
use Models\User;
use Teaching\Enrollments;
use Teaching\Roles;
use Teaching\SheduleCourses;

class CabinetAdminCompletionsInfoComponent extends CBitrixComponent
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
        $result = array(

        );
        return $result;
    }
    protected function checkModules()
    {
        if (!Main\Loader::includeModule('iblock'))
            throw new Main\LoaderException(Loc::getMessage('IB_INCL_ERROR'));
    }
    /**
     * Получение результатов
     */
    protected function getResult()
    {
        $enrollments = new Enrollments();
        $schedules = SheduleCourses::getScheduleIdsWithEnrolls();
        if(check_full_array($schedules)) {
            $items = [];
            foreach (SheduleCourses::getById(array_keys($schedules)) as $schedule) {
                $schedule['USERS'] = [];
                $ids = User::getEmployeesIdsByAdmin();
                $schedule['DETAIL_PAGE_URL'] = '/cabinet/confirmation/new/' . $schedule['ID'] . '/';
                $schedule['FOR_ROLES'] = Roles::getMustRolesForCourse($schedule['PROPERTIES']['COURSE']);
                foreach ($enrollments->getAllApprovedEnrollsBySheduleAndUserIds($schedule['ID'], $ids) as $enroll) {
                    $schedule['USERS'][$enroll['ID']] = UserHelper::getList(['ID' => $enroll['UF_USER_ID']], ['NAME', 'LAST_NAME']);
                }
                $schedule['ENROLLS_FROM_DC'] = $enrollments->getCountApprovedEnrollsByDC($schedule['ID']);
                $schedule['ENROLLS'] = $schedules[$schedule['ID']];
                $items[$schedule['ID']] = $schedule;
            }
            $this->arResult['ITEMS'] = $items;
        }else{
            $this->arResult['ITEMS'] = [];
        }
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
}