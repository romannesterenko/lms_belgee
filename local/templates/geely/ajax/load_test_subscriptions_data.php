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
use Settings\Common;
use Helpers\HLBlockHelper as HLBlock;

$request = Application::getInstance()->getContext()->getRequest()->getValues();
$arIMAGE = $_FILES['file'];
if($_FILES['file']['name']){
$arIMAGE['MODULE_ID'] = 'main';

if(Common::get('enable_subscription_mode') == 'Y') {
    echo "<h4>Будет добавлена следующая информация</h4>";
    $fid = CFile::SaveFile($arIMAGE, "subscriptions");

    $oSpreadsheet = IOFactory::load($_SERVER["DOCUMENT_ROOT"] . CFile::GetPath($fid));
    $sheetCount = $oSpreadsheet->getSheetCount();
    for($sheetIndex = 0; $sheetIndex < $sheetCount; $sheetIndex++){
        $cells = $oSpreadsheet->getSheet($sheetIndex)->getCellCollection();
        $max = $cells->getHighestRow();
        for ($row = 0; $row <= $max; $row++) {
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
                                $course = \Models\Course::find($course_id, ['ID', 'NAME']);
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
                                            echo $user['NAME']." ".$user["LAST_NAME"]." (ID:".$user['ID']."). Курс '".$course['NAME']."'. Дата ".date('d.m.Y', strtotime($fields['UF_DATE']))."<br>";
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
    if($fid>0) {?>
        <div class="form-group" style="display: flex; flex-direction: column; padding-top: 1rem; justify-content: center">
            <p style="color: red; text-align: center; padding-bottom: 10px">Внимание!!! Внимательно просмотрите предварительные данные! Данные загрузятся так как вы видите в предварительном окне. Если информации нет - ничего загружено не будет</p>
            <div class="btn-center">
                <button class="btn load_test_subscriptions" data-file-id="<?=$fid?>">Загрузить данные</button>
            </div>
        </div>
    <?php }
    //$cells = $oSpreadsheet->getActiveSheet()->getCellCollection();

}

?>

<?php } else {?>
    <p style="color: red">Добавьте файл!!!</p>
<?php }?>
