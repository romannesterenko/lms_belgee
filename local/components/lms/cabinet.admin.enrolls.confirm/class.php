<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc as Loc;

class CabinetAdminEnrollsConfirmComponent extends CBitrixComponent
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
        $enrollments = new \Teaching\Enrollments();

        $courses = \Models\Course::getByTeachingAdmin();
        $users = \Models\Employee::getEmployeesByAdmin();
        $us_ids=[];
        if(check_full_array($users)){
            foreach ($users as $us)
                $us_ids[] = $us['ID'];
        }
        $schedules = \Teaching\SheduleCourses::getByCourse(array_keys($courses));
        $enrolls = $enrollments->get(['UF_SHEDULE_ID' => array_keys($schedules), 'UF_USER_ID' => $us_ids]);
        $qwe = [];
        foreach ($enrolls as $enroll){
            if($enroll['UF_SHEDULE_ID']>0) {
                $qwe[$enroll['UF_SHEDULE_ID']][] = $enroll;
            }
        }
        $schedules = $qwe;
        if(check_full_array($schedules)) {
            $items = [];
            foreach (\Teaching\SheduleCourses::getById(array_keys($schedules)) as $schedule) {
                $schedule['COURSE_FORMAT'] = \Helpers\IBlockHelper::getProperty(\Helpers\IBlockHelper::getCoursesIBlock(), $schedule['PROPERTY_COURSE_VALUE'], 'COURSE_FORMAT');
                $schedule['DETAIL_PAGE_URL'] = '/cabinet/confirmation/new/' . $schedule['ID'] . '/';
                $schedule['FOR_ROLES'] = \Teaching\Roles::getMustRolesForCourse($schedule['PROPERTIES']['COURSE']);
                foreach ($schedules[$schedule['ID']] as $enroll) {
                    $schedule['USERS'][$enroll['ID']] = \Helpers\UserHelper::getList(['ID' => $enroll['UF_USER_ID']], ['NAME', 'LAST_NAME']);;
                }
                $schedule['ENROLLS']['NOT_APPROVED'] = $enrollments->getNotApprovedListByScheduleId($schedule['ID']);
                $schedule['ENROLLS']['APPROVED'] = $enrollments->getApprovedListByScheduleId($schedule['ID']);
                $schedule['ENROLLS']['APPROVED_DC'] = count($schedules[$schedule['ID']]);
                $schedule['ENDED'] = time()>strtotime($schedule['PROPERTIES']['END_DATE']);
                $items[$schedule['ID']] = $schedule;
            }
            $this->arResult['ITEMS'] = $items;
        }else{
            return [];
        }
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