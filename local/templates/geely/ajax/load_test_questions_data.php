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
$request = Application::getInstance()->getContext()->getRequest()->getValues();
$arIMAGE = $_FILES['file'];
if($_FILES['file']['name']){
$arIMAGE['MODULE_ID'] = 'main';
$fid = CFile::SaveFile($arIMAGE, "tests");
$oSpreadsheet = IOFactory::load($_SERVER["DOCUMENT_ROOT"] . CFile::GetPath($fid));
$oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();
$cells = $oSpreadsheet->getSheetByName('Лист1')->getCellCollection();
$max = $cells->getHighestRow();?>
<div class="table-block">
    <table class="table table-bordered table-striped table-responsive-stack table--borders" id="table-report">
        <thead class="thead-dark">
        <tr>
            <th>№</th>
            <th>Вопрос</th>
            <th>Баллов за правильный ответ</th>
            <th style="width: 50%">Ответы</th>
            <th>Номер(а) правильного ответа</th>
        </tr>
        </thead>
        <tbody>
            <?php
            $key = 1;
            $errors = [];
            for ($row = 2; $row <= $max; $row++) {
                echo "<tr>";
                echo "<td>".$key."</td>";
                if($cells->get('A' . $row))
                    echo "<td>".$cells->get('A' . $row)->getValue()."</td>";
                else {
                    echo "<td> - </td>";
                    $errors[] = "Вопрос №" . $key . ". Отсутствует обязательное поле 'Вопрос'";
                }
                if($cells->get('B' . $row))
                    echo "<td>".$cells->get('B' . $row)->getValue()."</td>";
                else {
                    echo "<td> - </td>";
                    $errors[] = "Вопрос №" . $key . ". Отсутствует обязательное поле 'Баллов за правильный ответ'";
                }
                $answers_message = [];
                $num_answer = 1;
                foreach (range('C', 'V') as $keyq => $letter) {
                    if (!$cells->get($letter . $row))
                        break;
                    $answers_message[] = $num_answer.") ".$cells->get($letter . $row)->getValue();
                    $num_answer++;
                }
                if(check_full_array($answers_message)) {
                    echo "<td>" . implode("<br/>", $answers_message) . "</td>";
                } else {
                    echo "<td> - </td>";
                    $errors[] = "Вопрос №" . $key . ". Отсутствует хотя бы один вариант ответа";
                }
                if($cells->get('W' . $row))
                    echo "<td>".str_replace('.', ',', $cells->get('W' . $row)->getValue())."</td>";
                else {
                    echo "<td> - </td>";
                    $errors[] = "Вопрос №" . $key . ". Отсутствует обязательное поле 'Номер(а) правильного ответа'";
                }
                echo "</tr>";
                $key++;
            }
            ?>
        </tbody>

    </table>
    <?php if($fid>0){
        if(!check_full_array($errors)){?>
            <div class="form-group" style="display: flex; flex-direction: column; padding-top: 1rem; justify-content: center">
                <p style="color: red; text-align: center; padding-bottom: 10px">Внимание!!! Существующие вопросы теста удалятся и загрузятся вопросы из файла. Внимательно проверьте данные в таблице</p>
                <div class="btn-center">
                    <button class="btn load_test_questions" data-course-id="<?=(int)$request['course_id']?>" data-file-id="<?=$fid?>">Загрузить вопросы</button>
                </div>
            </div>
        <?php } else {?>
            <p style="color: red; text-align: left; padding-bottom: 10px; padding-top: 10px">
                Загрузка невозможна, найденные ошибки:<br /><br />
                <?=implode('<br />', $errors)?>
            </p>
        <?php }?>
    <?php }?>
</div>

<?php } else {?>
    <p style="color: red">Добавьте файл!!!</p>
<?php }?>
