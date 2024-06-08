<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
global $USER, $APPLICATION;
use Bitrix\Main\Localization\Loc;
use Helpers\PageHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
function file_force_contents($dir, $contents){
    $parts = explode('/', $dir);
    $file = array_pop($parts);
    $dir = '';
    foreach($parts as $part)
        if(!is_dir($dir .= "/$part")) mkdir($dir);
    file_put_contents("$dir/$file", $contents);
}

function prepare_path($path):string {
    $lang = 'en';
    $path = $_SERVER["DOCUMENT_ROOT"].$path;
    return str_replace('#LANG_ID#', $lang, $path);
}
//mkdir($path, 0755);

/*$str_array = explode('/lang/'.$lang.'/', $path);
$dir_path = $str_array[0].'/lang/'.$lang.'/';
$file_name = $str_array[1];
mkdir($dir_path, 0700);
$handle = fopen($dir_path.$file_name, "c+");
$handle->ftruncate(0);*/
$txt = "<?php\n";
//fwrite($handle, $txt);
$txt .= "\$MESS['HHHJJJ'] = 'asdasdasd';\n";
$txt .= "\$MESS['HHHJJJ2'] = 'asdasdasd';\n";
$txt .= "\$MESS['HHHJJJ3'] = 'asdasdasd';\n";
/*fwrite($handle, $txt);
fclose($handle);*/
//$content$content$contentfile_force_contents($path, $txt);
if($USER->IsAdmin()){
    $oSpreadsheet = IOFactory::load("files/local_ru_en-2.xlsx");
    $oCells = $oSpreadsheet->getActiveSheet()->getCellCollection();
    $cells = $oSpreadsheet->getSheetByName('Лист 1 - local_ru_en_br_fr_la_p')->getCellCollection();
    $max = $cells->getHighestRow();
    dump($max);
    $array = [];
    for ($row = 3; $row <= $max; $row++){
        $array[trim($cells->get('A' . $row)->getValue())][] = [
            'PATH' => trim($cells->get('A' . $row)->getValue()),
            'KEY' => trim($cells->get('B' . $row)->getValue()),
            'RU' => trim($cells->get('C' . $row)->getValue()),
            'EN' => trim($cells->get('D' . $row)->getValue()),
        ];
    }
    foreach ($array as $path => $langs){
        $content = "<?php\n";
        foreach ($langs as $lang_item)
            $content.="\$MESS['".$lang_item['KEY']."'] = \"".$lang_item['EN']."\";\n";
        file_force_contents(prepare_path($path), $content);
        //dump($path);
        //dump($content);
        unset($content);
        //$path = str_replace('#LANG_ID#', 'en', $path);
        //$path = $_SERVER["DOCUMENT_ROOT"] . "/local/components/lms/cabinet.admin.completions.info/lang/gh/";
    }
    dump($array);
}else{
    PageHelper::set404(Loc::getMessage('FORBIDDEN'));
}
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");