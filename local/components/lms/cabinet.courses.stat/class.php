<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc as Loc;

class CabinetCoursesStatComponent extends CBitrixComponent
{
    private $completed_items;
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
            throw new Main\LoaderException("Ошибка подключения модуля инфоблоков");
    }
    /**
     * получение результатов
     */
    protected function getResult()
    {
        $this->getCompletedItems();
        $this->getLeftItems();
        $this->getNeededItems();
        $this->arResult = [
            'COMPLETED' => $this->completed_items->getCount(),
            'COMMON_SCORE' => $this->completed_items->getCommonScore(),
            'LEFT_COURSES' => count($this->new_items),
            'NEED_COURSES' => count($this->needed_items),
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

    private function getCompletedItems()
    {
        $completions = new \Teaching\CourseCompletion();
        $this->completed_items = $completions->getCompletedItems();
    }

    private function getLeftItems()
    {
        $this->new_items = \Teaching\Courses::getNeededCoursesIds();
    }

    private function getNeededItems()
    {
        $this->needed_items = \Teaching\Roles::GetRequiredCourseIdsByUser();
    }
}
?>