<?php

use Bitrix\Main\Application;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

const NO_KEEP_STATISTIC = true;
const NO_AGENT_STATISTIC = true;
const NOT_CHECK_PERMISSIONS = true;
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
global $APPLICATION;
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$arIMAGE = $_FILES['file'];
$arIMAGE['MODULE_ID'] = 'main';
$fid = (int)$request['file_id'];
$oSpreadsheet = IOFactory::load($_SERVER["DOCUMENT_ROOT"] . CFile::GetPath($fid));
$oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();
$cells = $oSpreadsheet->getSheetByName('Лист1')->getCellCollection();
$max = $cells->getHighestRow();
$test = current(\Teaching\Tests::getTestByCourse((int)$request['course_id']));
$course = \Models\Course::find((int)$request['course_id'], ['ID', 'NAME']);
if((int)$request['max_points'] > 0) {
    if ($test['ID']) {
        $questions = \Teaching\Tests::getQuestionsByTest($test['ID']);
        foreach ($questions as $id => $question) {
            \CIBlockElement::Delete($id);
        }
        $arFilter = array('IBLOCK_ID' => 12, 'UF_TEST' => $test['ID']);
        $section = \CIBlockSection::GetList(array($by => $order), $arFilter)->Fetch();
        if ($section['ID'] > 0) {
            $section_id = $section['ID'];
        } else {
            $bs = new \CIBlockSection;
            $arFields = Array(
                "ACTIVE" => 'Y',
                "IBLOCK_ID" => 12,
                "NAME" => "Вопросы к тесту: ".$course['NAME'],
                "UF_TEST" => $test['ID']
            );
            $section_id = $bs->Add($arFields);
        }
        for ($row = 2; $row <= $max; $row++) {
            $fields = [];
            $fields['IBLOCK_ID'] = 12;
            $fields['IBLOCK_SECTION_ID'] = $section_id;
            $fields['NAME'] = $cells->get("A" . $row)->getValue();
            $fields['PROPERTY_VALUES']['POINTS'] = $cells->get("B" . $row)->getValue();
            $fields['PROPERTY_VALUES']['TEST'] = $test['ID'];
            $answers = [];
            foreach (range('C', 'V') as $letter) {
                if (!$cells->get($letter . $row))
                    continue;
                $answers[] = $cells->get($letter . $row)->getValue();
            }
            $fields['PROPERTY_VALUES']['ANSWERS'] = $answers;
            $answers_array = explode(',', (string)$cells->get("W" . $row)->getValue());
            if(check_full_array($answers_array)==1){
                $answers_array = explode('.', (string)$cells->get("W" . $row)->getValue());
            }
            if(check_full_array($answers_array)>0){
                $fields['PROPERTY_VALUES']['CORRECT_NUM'] = implode(',', $answers_array);
            } else {
                $fields['PROPERTY_VALUES']['CORRECT_NUM'] = (string)$cells->get("W" . $row)->getValue();
            }
            $element = (new CIBlockElement())->add($fields);
        }
        echo "Вопросы добавлены";
    } else {
        $el = new \CIBlockElement;
        $PROP = array();
        $section_id = 83;
        if(\Models\Course::isOP((int)$request['course_id']))
            $section_id = 27;
        $PROP["POINTS"] = (int)$request['max_points'];  // свойству с кодом 12 присваиваем значение "Белый"
        $PROP["COURSE"] = (int)$request['course_id'];        // свойству с кодом 3 присваиваем значение 38
        $arLoadProductArray = Array(
            "IBLOCK_SECTION_ID" => $section_id,          // элемент лежит в корне раздела
            "IBLOCK_ID"      => 11,
            "PROPERTY_VALUES"=> $PROP,
            "NAME"           => "Тест ".$course['NAME'],
            "ACTIVE"         => "Y",            // активен
        );
        $test_id = $el->Add($arLoadProductArray);
        if($test_id>0) {
            $bs = new \CIBlockSection;
            $arFields = Array(
                "ACTIVE" => 'Y',
                "IBLOCK_ID" => 12,
                "NAME" => "Вопросы к тесту: ".$course['NAME'],
                "UF_TEST" => $test_id
            );
            $new_section_id = $bs->Add($arFields);
            if($new_section_id>0){
                for ($row = 2; $row <= $max; $row++) {
                    $fields = [];
                    $fields['IBLOCK_ID'] = 12;
                    $fields['IBLOCK_SECTION_ID'] = $new_section_id;
                    $fields['NAME'] = $cells->get("A" . $row)->getValue();
                    $fields['PROPERTY_VALUES']['POINTS'] = $cells->get("B" . $row)->getValue();
                    $fields['PROPERTY_VALUES']['TEST'] = $test_id;
                    $answers = [];
                    foreach (range('C', 'V') as $letter) {
                        if (!$cells->get($letter . $row))
                            continue;
                        $answers[] = $cells->get($letter . $row)->getValue();
                    }
                    $fields['PROPERTY_VALUES']['ANSWERS'] = $answers;
                    $answers_array = explode(',', (string)$cells->get("W" . $row)->getValue());
                    if(check_full_array($answers_array)==1){
                        $answers_array = explode('.', (string)$cells->get("W" . $row)->getValue());
                    }
                    if(check_full_array($answers_array)>0){
                        $fields['PROPERTY_VALUES']['CORRECT_NUM'] = implode(',', $answers_array);
                    } else {
                        $fields['PROPERTY_VALUES']['CORRECT_NUM'] = (string)$cells->get("W" . $row)->getValue();
                    }
                    $element = (new CIBlockElement())->add($fields);
                }
                echo "Вопросы добавлены";
            } else {
                echo "Создание раздела для теста не удалось. ".$bs->LAST_ERROR;
            }
        } else {
            echo "Создание теста не удалось. ".$el->LAST_ERROR;
        }

    }
} else {
    echo "Укажите количество баллов для прохождения";
}
?>


