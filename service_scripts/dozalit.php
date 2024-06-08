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

if($USER->IsAdmin()){
    $enrolls = new \Teaching\Enrollments();
    $completions = new \Teaching\CourseCompletion();
    $oSpreadsheet = IOFactory::load("files/GMR_to_LMS.xlsx");
    $oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();
    $cells = $oSpreadsheet->getSheetByName('Для загрузки')->getCellCollection();
    $max = $cells->getHighestRow();
    $array = [];
    $us = new CUser;
    for ($row = 2; $row <= $max; $row++){
        $email = $cells->get('D' . $row)->getValue();
        $id = $cells->get('E' . $row)->getValue();
        $fields = [
            'WORK_POSITION' => $cells->get('F' . $row)->getValue(),
            'WORK_DEPARTMENT' => $cells->get('G' . $row)->getValue(),
        ];
        if((int)$id>0){
            /*$user = \Models\User::find($id, ['ID']);
            if(check_full_array($user)){
                $us->Update($id, $fields);
                if($us->LAST_ERROR)
                    echo $us->LAST_ERROR;
                else
                    echo "Пользователь ".$id." успешно обновлен.";
            }*/
        }else{
            $user = \Models\User::getByEmail(strtolower($email));

            if(check_full_array($user)){
                $fields['UF_DEALER'] = 292;
                $us->Update($user['ID'], $fields);
                if($us->LAST_ERROR!='')
                    echo $us->LAST_ERROR;
                else
                    echo 'Пользователь успешно обновлен';
            }

            dump($user);
            /*$fields['NAME'] = $cells->get('B' . $row)->getValue();
            $fields['LAST_NAME'] = $cells->get('C' . $row)->getValue();*/
            /*$fields['EMAIL'] = $fields['LOGIN'] = strtolower($email);
            $fields['PASSWORD'] = strtolower($email);
            $fields['ACTIVE'] = "Y";
            $fields["PASSWORD"] = $fields["CONFIRM_PASSWORD"] = "Geely123";
            $ID = $us->Add($fields);
            if (intval($ID) > 0)
                echo "Пользователь ".$fields['NAME']." ".$fields['LAST_NAME']." успешно добавлен.";
            else
                echo $us->LAST_ERROR;*/
            //$ID = $us->Add($fields);
        }
        dump($fields);
    }
}else{
    PageHelper::set404(Loc::getMessage('FORBIDDEN'));
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");