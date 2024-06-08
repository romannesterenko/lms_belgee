<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;

use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$courses = [
    'GMTC LEVEL-1' => 86,
    'Новая модель: Geely Tugella (FY-11)' => 89,
    'Новая модель: Geely Atlas Pro (NL-3B)' => 90,
    'Новая модель: Geely Coolray (SX-11)' => 88,
    'Новая модель: Geely Monjaro (KX-11)' => 9725,
    'Стандарты послепродажного обслуживания Geely' => 84,
    'Ключевые процессы сервиса. Навыки продаж в сервисе.' => 98,
    'Основы диагностики электрических систем GEELY' => 91,
    'Клиентоориентированный сервис' => 102,
    'Экономика СТОА' => 101,
    'Управление складскими запасами запчастей' => 100,
    'Работа с возражениями и конфликтами' => 99,
    'Двигатель 3G15TD / JLH-4G20TD ' => 93,
    'Трансмиссия 7DCT  /  AWF8F45' => 92,
    'Внутренние процессы сервиса' => 97,
    'Основы работы с запчастями' => 103,
    'Работа с жалобами и конфликтами' => 684,
    'Гарантийное сопровождение автомобилей GEELY' => 85,
    'Сервисный маркетинг' => 822,
    'Оригинальные запчасти и аксессуары' => 823,
    'Базовый тренинг Geely для технических специалистов' => 9727,
    'Новая модель: Coolray (SX11-A3)' => 115453,
    'Новая модель: Emgrand  (SS11-A1)' => 115454,
    'Новая модель: Atlas (FX11)' => 115455
];
$section_courses = [
    'GMTC LEVEL-1' => 38,
    'Новая модель: Geely Tugella (FY-11)' => 41,
    'Новая модель: Geely Atlas Pro (NL-3B)' => 39,
    'Новая модель: Geely Coolray (SX-11)' => 40,
    'Стандарты послепродажного обслуживания Geely' => 54,
    'Ключевые процессы сервиса. Навыки продаж в сервисе.' => 45,
    'Основы диагностики электрических систем GEELY' => 44,
    'Клиентоориентированный сервис' => 48,
    'Экономика СТОА' => 49,
    'Управление складскими запасами запчастей' => 50,
    'Работа с возражениями и конфликтами' => 46,
    'Двигатель 3G15TD / JLH-4G20TD ' => 42,
    'Трансмиссия 7DCT  /  AWF8F45' => 43,
    'Новая модель: Geely Monjaro (KX-11)' => 57,
    'Внутренние процессы сервиса' => 52,
    'Основы работы с запчастями' => 51,
    'Работа с жалобами и конфликтами' => 47,
    'Гарантийное сопровождение автомобилей GEELY' => 55,
    'Сервисный маркетинг' => 53,
    'Оригинальные запчасти и аксессуары' => 56,
    'Базовый тренинг Geely для технических специалистов' => 60,
		'Новая модель: Coolray (SX11-A3)' => 116,
		'Новая модель: Emgrand  (SS11-A1)' => 117,
		'Новая модель: Atlas (FX11)' => 118

];
$go = false;
if($USER->IsAdmin()){
    $oSpreadsheet = IOFactory::load("files/Коды_для_сертификатов_заказ_октября_2023_г_2.xlsx");
    $oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();
    $cells = $oSpreadsheet->getSheetByName('октябрь 2023')->getCellCollection();
    $max = $cells->getHighestRow();
    /*$go = true;
    if($end>=$max) {
        $end = $max;
        $go = false;
    }*/
    $last_id = 0;
    $course_names = [];
    for ($row = 1; $row <= $max; $row++){
        foreach (range('A', 'Z') as $letter){
            if(!$cells->get($letter . $row))
                continue;
            if($row==1) {
                $course_names[$letter] = $cells->get($letter . $row)->getValue();
            } else {
                $course = \Models\Course::find($courses[$course_names[$letter]], ['ID', 'NAME']);
								echo '<pre>';
                var_dump($courses[$course_names[$letter]]);
								echo '</pre>';
                $props['COURSE'] = $course['ID'];
                $array = [
                    'NAME' => $course['NAME'].' ('.$cells->get($letter . $row)->getValue().')',
                    'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
                    'IBLOCK_SECTION_ID' => $section_courses[$course_names[$letter]],
                    'CODE' => $cells->get($letter . $row)->getValue(),
                    "PROPERTY_VALUES"=> $props,
                ];
							/*	echo '<pre>';
                var_dump($array);
								echo '</pre>';*/

                if(!empty($array['CODE']))
                    \Models\Certificate::create($array);
            }

        }
        continue;
        $course = \Models\Course::find($courses[trim($cells->get('G' . $row)->getValue())], ['ID', 'NAME']);
        $props['COURSE'] = $course['ID'];
        $code = $cells->get('B' . $row)->getValue();
        if($cells->get('A' . $row)->getValue()=='погашен'){
            $props['ACTIVATION_DATE'] = date('d.m.Y H:i:s');
            $props['WHO_ACTIVATE'] = 1;
        }
        $array = [
            'NAME' => $course['NAME'].'  ('.$code.')',
            'IBLOCK_ID' => \Teaching\Coupons::getIblockID(),
            'IBLOCK_SECTION_ID' => $section_courses[trim($cells->get('G' . $row)->getValue())],
            'CODE' => $code,
            "PROPERTY_VALUES"=> $props,
        ];
        dump($array);
        //\Models\Certificate::updateOrCreate($array);
        unset($array);
        unset($props);
        $last_id = $row;
    }
}else{
    PageHelper::set404(Loc::getMessage('FORBIDDEN'));
}
dump($courses);
dump($course_names);
foreach ($course_names as $course_name) {
    if($section_courses[$course_name])
        dump($section_courses[$course_name]);
    else
        dump($course_name);
}
//if(!$go){
if($go){?>
    <script>
        $(function (){
            location.href = '<?=$APPLICATION->GetCurPage()?>?last_id=<?=$last_id?>'
        })
    </script>
<?php }
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");