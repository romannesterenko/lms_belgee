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
$fid = CFile::SaveFile($arIMAGE, "regionals");
$oSpreadsheet = IOFactory::load($_SERVER["DOCUMENT_ROOT"] . CFile::GetPath($fid));
$oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();
$cells = $oSpreadsheet->getSheet(0)->getCellCollection();
$max = $cells->getHighestRow();?>
<div class="table-block">
    <table class="table table-bordered table-striped table-responsive-stack table--borders" id="table-report">
        <thead class="thead-dark">
        <tr>
            <th>№</th>
            <th>Dealer Name</th>
            <th>Code</th>
            <th style="width: 50%">City</th>
            <th>REGION</th>
            <th>Current Manager</th>
            <th>New Manager</th>
            <th style="white-space: nowrap">Направление</th>
        </tr>
        </thead>
        <tbody>
            <?php
            $key = 1;
            $errors = [];
            $regionals = [];
            foreach (\Models\Dealer::getAll() as $one_dealer){
                if($one_dealer['PROPERTY_REGIONAL_VALUE'])
                    $regionals[] = $one_dealer['PROPERTY_REGIONAL_VALUE'];
                if($one_dealer['PROPERTY_REGIONAL_PPO_VALUE'])
                    $regionals[] = $one_dealer['PROPERTY_REGIONAL_PPO_VALUE'];
                if($one_dealer['PROPERTY_REGIONAL_MARKETING_VALUE'])
                    $regionals[] = $one_dealer['PROPERTY_REGIONAL_MARKETING_VALUE'];
            }
            $errors = [];
            for ($row = 2; $row <= $max; $row++) {
                if (empty($cells->get('F' . $row))||$cells->get('F' . $row)=='TERM'||$cells->get('F' . $row)=='SPD'||$cells->get('B' . $row)=='TOTAL')
                    continue;
                $current_user = 0;
                $new_user = 0;
                echo "<tr>";
                if($cells->get('A' . $row))
                    echo "<td>".$cells->get('A' . $row)->getValue()."</td>";
                if($cells->get('B' . $row))
                    echo "<td>".$cells->get('B' . $row)->getValue()."</td>";
                if($cells->get('C' . $row))
                    echo "<td>".$cells->get('C' . $row)->getValue()."</td>";
                if($cells->get('D' . $row))
                    echo "<td>".$cells->get('D' . $row)->getValue()."</td>";
                if($cells->get('E' . $row))
                    echo "<td>".$cells->get('E' . $row)->getValue()."</td>";
                if($cells->get('F' . $row)) {
                    $fio = explode(' ', trim($cells->get('F' . $row)));
                    $dealer = \Models\Dealer::getIdByCode($cells->get('C' . $row)->getValue());
                    $user = \Models\User::getRegionalByFullName($fio[0], $fio[1], $dealer);
                    if($dealer == 0){
                        if(trim($cells->get('C' . $row)->getValue()) == "")
                            $errors[$cells->get('B' . $row)->getValue()][] = "Код дилера не указан. Обновление не выполнится";
                        else
                            $errors[$cells->get('B' . $row)->getValue()][] = "Дилер не найден в системе. Обновление не выполнится";
                        //$style = "style='color:red'";
                    }
                    if($user['ID']>0) {
                        $current_user = $user['ID'];
                        $style="";
                    }/* else {
                        $errors[$cells->get('B' . $row)->getValue()][] = "Сотрудник <b>".$cells->get('F' . $row)."</b> не указан в дилере как текущий региональный менеджер. Обновление не выполнится";
                        $style = "style='color:red'";
                    }*/
                    //$text = $user['ID']>0?" (ID: ".$user['ID'].")":"<br/>(Не найден в этом дилере)";
                    echo "<td ".$style.">" . $cells->get('F' . $row)->getValue() . $text ."</td>";
                }
                if($cells->get('G' . $row)) {
                    $fio = explode(' ', trim($cells->get('G' . $row)));
                    $dealer = \Models\Dealer::getIdByCode($cells->get('C' . $row)->getValue());
                    $users = \Models\User::getArrayByFullName($fio[0], $fio[1]);
                    if(check_full_array($users)) {
                        if (count($users) == 1) {
                            $user = $users[0];
                            if ($user['ID'] > 0) {
                                $new_user = $user['ID'];
                                if ($new_user != $current_user) {
                                    $style = "style='color:green'";
                                } else {
                                    $style = "";
                                }
                            } else {
                                //$style = "style='color:red'";
                            }
                        } else {
                            foreach ($users as $s) {
                                if(in_array($s['ID'], $regionals)){
                                    $new_user = $s['ID'];
                                    if ($new_user != $current_user) {
                                        $style = "style='color:green'";
                                    } else {
                                        $style = "";
                                    }
                                }
                            }
                        }
                    }
                    echo "<td ".$style.">" . $cells->get('G' . $row)->getValue() . " (ID: ".$new_user.")</td>";
                    unset($style);
                }
                if($cells->get('H' . $row)) {
                    if (trim($cells->get('H' . $row)->getValue())!="ОП" || trim($cells->get('H' . $row)->getValue())!="ППО" || trim($cells->get('H' . $row)->getValue())!="Marketing") {
                        echo "<td>" . $cells->get('H' . $row)->getValue() . "</td>";
                    }else{
                        echo "<td>" . $cells->get('H' . $row)->getValue(). "</td>";
                        $errors[$cells->get('B' . $row)->getValue()][] = "Указано неверное направление на которое устанавливается новый менеджер. Варианты - ОП, ППО. Обновление не выполнится";
                    }
                } else {
                    echo "<td>-</td>";
                    $errors[$cells->get('B' . $row)->getValue()][] = "Не указано направление на которое устанавливается новый менеджер. Обновление не выполнится";
                }
                echo "</tr>";
                $key++;
            }
            ?>
        </tbody>

    </table>
    <?php if($fid>0) {?>
        <?php if(!check_full_array($errors)) {?>
            <div class="form-group" style="display: flex; flex-direction: column; padding-top: 1rem; justify-content: center">
                <p style="color: red; text-align: center; padding-bottom: 10px">Внимание!!! Внимательно просмотрите предварительные данные! Данные загрузятся так как вы видите в предварителном окне. То что помечено красным обновлено не будет, то что зеленым - будет обновлено.</p>
                <div class="btn-center">
                    <button class="btn load_test_regionals" data-file-id="<?=$fid?>">Загрузить данные</button>
                </div>
            </div>
        <?php } else {?>
            <div class="form-group" style="display: flex; flex-direction: column; padding-top: 1rem; justify-content: center">
                <p style="color: red; text-align: center; padding-bottom: 10px">Найдены ошибки в файле.</p>
            </div>
            <table class="table table-bordered table-striped table-responsive-stack table--borders" id="table-report">
                <thead class="thead-dark">
                    <tr>
                        <th>Дилер</th>
                        <th>Ошибка</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($errors as $dealer_name => $dealer_errors) {
                    foreach ($dealer_errors as $key => $dealer_error){?>
                        <tr>
                            <?=$key==0?"<td rowspan=".count($dealer_errors).">".$dealer_name."</td>":"";?>
                            <td class="text-left"><?=$dealer_error?></td>
                        </tr>
                    <?php }
                    }?>
                </tbody>
            </table>
            <div class="form-group" style="display: flex; flex-direction: column; padding-top: 1rem; justify-content: center">
                <div class="btn-center">
                    <button class="btn load_test_regionals" data-file-id="<?=$fid?>">Загрузить данные</button>
                </div>
            </div>
        <?php }?>
    <?php }?>
</div>

<?php } else {?>
    <p style="color: red">Добавьте файл!!!</p>
<?php }?>
