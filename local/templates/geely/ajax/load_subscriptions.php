<?php

use Bitrix\Main\Application;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Helpers\HLBlockHelper as HLBlock;
use Settings\Common;
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$arIMAGE = $_FILES['file'];
$arIMAGE['MODULE_ID'] = 'main';
$fid = (int)$request['file_id'];
if(Common::get('enable_subscription_mode') == 'Y') {
    $oSpreadsheet = IOFactory::load($_SERVER["DOCUMENT_ROOT"] . CFile::GetPath($fid));
    $cells = $oSpreadsheet->getActiveSheet()->getCellCollection();
    $max = $cells->getHighestRow();
    for ($row = 3; $row <= $max; $row++) {
        if ($cells->get('H' . $row) && (int)$cells->get('H' . $row)->getValue() > 0) {
            $user = \Models\User::find((int)$cells->get('H' . $row)->getValue());
            if (check_full_array($user)) {
                foreach (range('I', 'Z') as $symbol) {
                    if ($cells->get($symbol . "2") && $cells->get($symbol . "2")->getValue()) {
                        $string = $cells->get($symbol . "2")->getValue();
                        $pattern = '/\((\d+)\)/';
                        $matches = [];
                        if (preg_match($pattern, $string, $matches)) {
                            $course_id = $matches[1];
                            $course = \Models\Course::find($course_id);
                            if (check_full_array($course)) {
                                $time_value = $cells->get($symbol . $row)->getFormattedValue();
                                if (time() < strtotime($time_value)) {
                                    $fields = [
                                        'UF_COURSE_ID' => $course_id,
                                        'UF_USER_ID' => $user['ID'],
                                        'UF_CREATED_AT' => date('d.m.Y H:i:s'),
                                        'UF_DATE' => date('d.m.Y H:i:s', strtotime($time_value)),
                                    ];
                                    $exists = HLBlock::get(HLBlock::initialize('course_subscription'), [
                                        'UF_COURSE_ID' => $course_id,
                                        'UF_USER_ID' => $user['ID'],
                                        'UF_DATE' => date('d.m.Y H:i:s', strtotime($time_value)),
                                    ]);
                                    if (!check_full_array($exists)) {
                                        HLBlock::add($fields, HLBlock::initialize('course_subscription'));
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    echo "Информация добавлена";
} else {
    echo "Функционал подписок выключен в настройках системы";
}




