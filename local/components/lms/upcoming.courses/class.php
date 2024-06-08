<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc as Loc;

class UpcomingCoursesComponent extends CBitrixComponent
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
            'MONTH' => $params['MONTH'],
            'YEAR' => !empty($arr[1])?$arr[0]:$params['YEAR'],
            'USER' => $params['USER'],
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
    /**
     * получение результатов
     */
    protected function getResult()
    {
        $upcoming_enrolls = $this->getNearestSchedules();
        $approved_enrolls = $this->getApprovedEnrolls();
        foreach ($approved_enrolls as &$approved_enroll){
            $approved_enroll['SCHEDULE'] = current(\Teaching\SheduleCourses::getById($approved_enroll['UF_SHEDULE_ID']));
            $approved_enroll['COURSE'] = \Teaching\Courses::getById($approved_enroll['UF_COURSE_ID']);
        }
        foreach ($upcoming_enrolls as &$upcoming_enroll){
            $upcoming_enroll['COURSE'] = \Teaching\Courses::getById($upcoming_enroll['PROPERTIES']['COURSE']);
        }
        $this->arResult = [
            'UPCOMING_ITEMS' => $upcoming_enrolls,
            'ALREADY_ITEMS' => $approved_enrolls,
            'PARAMS' => $this->arParams,
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

    private function getApprovedEnrolls()
    {
        $enroll = new \Teaching\Enrollments();
        return $enroll->getApprovedEnrollsWithDate();
    }

    private function getNearestSchedules()
    {
        return \Teaching\SheduleCourses::getNearestForUser(5);
    }
}
?>