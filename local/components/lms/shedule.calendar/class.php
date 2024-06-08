<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Localization\Loc as Loc;

class SheduleCalendarComponent extends CBitrixComponent
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
        return array(
            'MONTH' => $params['MONTH'],
            'YEAR' => !empty($arr[1])?$arr[0]:$params['YEAR'],
            'USER' => $params['USER'],
        );
    }
    /**
     * проверяет подключение необходиимых модулей
     * @throws Main\LoaderException
     */
    protected function checkModules()
    {
        if (!Main\Loader::includeModule('iblock'))
            throw new Main\LoaderException("Ошибка подключения модуля инфоблоков");
    }
    private function getMonthsList(){
        return [
            1=>GetMessage("JANUARY"),
            GetMessage("FEBRUARY"),
            GetMessage("MARCH"),
            GetMessage("APRIL"),
            GetMessage("MAY"),
            GetMessage("JUNE"),
            GetMessage("JULY"),
            GetMessage("AUGUST"),
            GetMessage("SEPTEMBER"),
            GetMessage("OCTOBER"),
            GetMessage("NOVEMBER"),
            GetMessage("DECEMBER"),
        ];
    }
    private function getDaysList(){
        return [
            1=>GetMessage("MONDAY"),
            GetMessage("TUESDAY"),
            GetMessage("WEDNESDAY"),
            GetMessage("THURSDAY"),
            GetMessage("FRIDAY"),
            GetMessage("SATURDAY"),
            GetMessage("SUNDAY"),
        ];
    }
    private function getNextMonth(){
        $string = ('28.'.$this->arParams['MONTH'].'.'.$this->arParams['YEAR']);
        $tmstmp = strtotime($string);
        return date('/m/Y/', $tmstmp+432000);
    }
    private function getPrevMonth(){
        $string = '1.'.$this->arParams['MONTH'].'.'.$this->arParams['YEAR'];
        $tmstmp = strtotime($string);
        return date('/m/Y/', $tmstmp-86400);
    }
    private function generateCalendarArray(){

        $days_arr = $this->getDaysList();
        $days_month = date('t', mktime(0, 0, 0, $this->arParams['MONTH'], 1, (int)$this->arParams['YEAR']));

        $calendar_array = [];
        for ($i=1; $i<=$days_month; $i++){
            $timestamp = strtotime($i.'.'.$this->arParams['MONTH'].'.'.$this->arParams['YEAR']);
            $_week = date("W", $timestamp);
            $calendar_array[$_week][] = [
                'timestamp'=>$timestamp,
                'date'=>str_pad($i, 2, '0', STR_PAD_LEFT).'.'.$this->arParams['MONTH'].'.'.$this->arParams['YEAR'],
                'day'=>$days_arr[date("N", $timestamp)],
                'day_num'=>date('j', $timestamp),
                'date_format'=>date('d.m', $timestamp),
            ];
        }

        $prev_month = true;
        $count = 0;
        foreach ($calendar_array as &$days){
            if(count($days)<7){
                if($count>0)
                    $prev_month = false;
                $num = 7-count($days);
                $cnt = 1;
                if($prev_month)
                    $timestamp_t = (int)$days[0]['timestamp'];
                else
                    $timestamp_t = (int)$days[(count($days)-1)]['timestamp'];
                for($i=$num; $i>=1; $i--){
                    if($prev_month)
                        $timestamp_t = $timestamp_t - 86400;
                    else
                        $timestamp_t = $timestamp_t + 86400;
                    $temp = [
                        'timestamp'=>$timestamp_t,
                        'date'=>date('d.m.Y', $timestamp_t),
                        'day'=>$days_arr[date("N", $timestamp_t)],
                        'day_num'=>date('j', $timestamp_t),
                        'disabled'=>true,
                    ];
                    $cnt++;
                    if($prev_month)
                        array_unshift($days, $temp);
                    else
                        $days[] = $temp;
                }
                $prev_month = false;
            }
            $count++;
        }
        return $calendar_array;

    }

    /**
     * получение результатов
     */
    protected function getResult()
    {
        dump($this->arParams);
        $this->arResult = [
            'PARAMS' => $this->arParams,
            'MONTHS' => $this->getMonthsList(),
            'ENROLLS' => $this->getSchedulesList($this->arParams['MONTH']),
            'DAYS' => $this->generateCalendarArray(),
            'LINKS' => $this->generateMonthLinks(),
        ];
        //dump($this->arResult['ENROLLS']);
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

    private function generateMonthLinks()
    {
        $prev_month = '/shedule'.$this->getPrevMonth();
        $next_month = '/shedule'.$this->getNextMonth();
        $return_array = [
            'PREV_MONTH_LINK' => $prev_month,
            'NEXT_MONTH_LINK' => $next_month,
        ];
        return $return_array;
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
}
?>