<?php
const NEED_AUTH = true;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
use setasign\Fpdi\Fpdi;
global $USER, $APPLICATION;
$is_access = false;

function encrypt($data) {
    $key = "GeelyMotorsRussiaHiddenKey";
    // Генерация и установка инициализирующего вектора (IV)
    $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));

    // Шифрование данных
    $encryptedData = openssl_encrypt($data, 'aes-256-cbc', $key, 0, $iv);

    // Кодирование в base64 для удобства передачи
    return base64_encode($encryptedData . '::' . $iv);
}

function addHiddenTextToPdf($inputPdfPath, $outputPdfPath) {
    $pdf = new FPDI();
    global $USER;
    // Добавление исходного PDF
    $pageCount = $pdf->setSourceFile($inputPdfPath);
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $text = "page:".$pageNo.";user_id:".$USER->GetID().";date:".date('d.m.Y_H:i:s');
        $tplIdx = $pdf->importPage($pageNo);
        $pdf->addPage();
        $pdf->useTemplate($tplIdx);

        // Добавление скрытого текста
        $pdf->SetFont('helvetica', '', 1);
        $pdf->SetTextColor(255, 255, 255); // Белый цвет текста
        $pdf->SetXY(0, 0);
        $pdf->Write(0, "=====".encrypt($text));
    }

    $pdf->Output('F', $outputPdfPath);
}
function addWatermark($inputPdfPath) {
    $pdf = new FPDI();
    // Добавление исходного PDF
    $pageCount = $pdf->setSourceFile($inputPdfPath);
    $watermarkImage = $_SERVER["DOCUMENT_ROOT"]."/upload/watermark.png";
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        // Импортируем страницу
        $templateId = $pdf->importPage($pageNo);
        // Определяем размеры страницы
        $size = $pdf->getTemplateSize($templateId);

        // Создаем новую страницу с теми же размерами
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        // Используем импортированный шаблон
        $pdf->useTemplate($templateId);

        // Позиционируем водяной знак по центру страницы
        $pdf->Image($watermarkImage, ($size['width'] - 50) / 2, ($size['height'] - 50) / 2, 45, 30);
    }
    $pdf->Output('F', $inputPdfPath);
}



if((int)$_REQUEST['id']>0) {
    $completion = (new \Teaching\CourseCompletion())->find((int)$_REQUEST['id']);
    if(check_full_array($completion)) {
        if ($completion['UF_IS_COMPLETE'] == 1){
            if($USER->GetID() == $completion['UF_USER_ID']){
                $course = \Models\Course::find($completion['UF_COURSE_ID'], ['ID', 'NAME', 'CODE', 'PROPERTY_WORK_BOOK']);
                if((int)$course['PROPERTY_WORK_BOOK_VALUE'] > 0) {
                    $pdf_link = $_SERVER["DOCUMENT_ROOT"].CFile::GetPath($course['PROPERTY_WORK_BOOK_VALUE']);
                    if (!is_dir($_SERVER["DOCUMENT_ROOT"]."/upload/modified/".$USER->GetID()."/")){
                        mkdir($_SERVER["DOCUMENT_ROOT"]."/upload/modified/".$USER->GetID()."/");
                    }
                    $now_time = time();
                    $modifiedFilePath = $_SERVER["DOCUMENT_ROOT"]."/upload/modified/".$USER->GetID()."/".$course['CODE'].$now_time."_work_book.pdf";
                    addHiddenTextToPdf($pdf_link, $modifiedFilePath);
                    addWaterMark($modifiedFilePath);
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: attachment; filename="' . basename($modifiedFilePath) . '"');
                    LocalRedirect("/upload/modified/".$USER->GetID()."/".$course['CODE'].$now_time."_work_book.pdf");
                    /*readfile($modifiedFilePath);
                    $is_access = true;*/
                }

            }
        }
    }
}
if (!$is_access){
    echo "<h2 style='color: red'>У вас нет доступа к файлам</h2>";
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");