<?php

namespace Helpers;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Models\Course;
use Models\Sertificate;
use Models\User;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\CrossReference\CrossReferenceException;
use setasign\Fpdi\PdfParser\Filter\FilterException;
use setasign\Fpdi\PdfParser\PdfParserException;
use setasign\Fpdi\PdfParser\Type\PdfTypeException;
use setasign\Fpdi\PdfReader\PdfReaderException;
use Teaching\Courses;

class Pdf
{
    /**
     * @throws CrossReferenceException
     * @throws SystemException
     * @throws ObjectPropertyException
     * @throws PdfTypeException
     * @throws PdfReaderException
     * @throws ArgumentException
     * @throws PdfParserException
     * @throws FilterException
     */
    public static function generateCertFromCompletionIdTest($completion_id): void
    {
        $completions = new \Teaching\CourseCompletion();
        $compl_info = $completions->find($completion_id);
        if(!$compl_info['UF_COURSE_ID']>0)
            return;
        $course = \Models\Course::find($compl_info['UF_COURSE_ID'], [
            'NAME',
            'PROPERTY_CERTIFICATE_TEMPLATE',
            'PROPERTY_CERT_NUMBER_TEMPLATE',
            'PROPERTY_LAST_CERT_NUMBER',
            'PROPERTY_CERT_EXP',
            'PROPERTY_COUNT_SYMBOLS',
            'PROPERTY_COURSE_CATEGORY'
        ]);
        if(\Models\Course::isOP($course['ID']) || \Models\Course::isMarketing($course['ID'])) {
            $user = User::find($compl_info['UF_USER_ID'], ['ID', 'NAME', 'LAST_NAME']);
            if(!$user['ID']>0)
                return;

            $user = $user['LAST_NAME'].' '.$user['NAME'];
            $start_cert_date = $compl_info['UF_COMPLETED_TIME']?date('d.m.Y', strtotime($compl_info['UF_COMPLETED_TIME'])):(string)$compl_info['UF_DATE'];
            $file_date = (string)$compl_info['UF_DATE'];
            $template_xml = \Models\Course::getTemplateXML($course['ID']);

            $template = $template_xml == 'template3' ? 4 : 3;
            $template = $template_xml == 'template4' ? 5 : $template;
            if($template_xml == 'template4') {
                $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/new_op_template.pdf';
                $pdf = new Fpdi();
                $pdf->AddPage();
                $pdf->AddFont('Ubuntu-Medium', '', 'Ubuntu-Medium.php');

                $pdf->setSourceFile($file);

                $tplId = $pdf->importPage(1);
                $pdf->useTemplate($tplId, 5, 5, 210);
                $pdf->SetTextColor(0, 0, 0); // RGB


                $pdf->SetFont('Ubuntu-Medium', '', 22);
                $text = iconv('utf-8', 'windows-1251', $course['NAME']);
                $separator = '\n';
                $array_strings = explode($separator, wordwrap('"'.$text.'"', 40, $separator));
                $st = 125;
                foreach ($array_strings as $string) {
                    $pdf->SetXY(75, $st); // X start, Y start in mm
                    $pdf->SetX(22.6);
                    $pdf->Cell(0, 0, $string, 0, 0, 'C');
                    $st = $st + 9;
                }
                $st = $st + 9;
                $pdf->SetXY(65, $st); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', $user);
                $pdf->SetX(22.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');
                $st = $st + 20;

                $pdf->SetXY(65, $st); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', $start_cert_date);
                $pdf->SetX(22.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');
                if(Course::isMarketing($course['ID'])){
                    $need_dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/certificates/new/' . $course['ID'];
                    $filename = $compl_info['ID'] . '.pdf';
                } else {
                    $need_dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/certificates/' . $course['ID'];
                    $filename = $compl_info['UF_COURSE_ID'] . '_' . $compl_info['UF_USER_ID'] . '_' . str_replace('.', '_', $file_date) . '.pdf';
                }
                if (!is_dir($need_dir))
                    mkdir($need_dir);
                $pdf->Output('F', $need_dir . '/' . $filename);
            } elseif ($template_xml == 'template3') {

                $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/'.$template.'-pdf.pdf';
                $pdf = new Fpdi();
                $pdf->AddPage();
                $pdf->AddFont('Ubuntu-Medium', '', 'Ubuntu-Medium.php');

                $pdf->setSourceFile($file);
                $tplId = $pdf->importPage(1);
                $pdf->useTemplate($tplId, 5, 5, 210);
                $pdf->SetTextColor(0, 0, 0); // RGB


                $pdf->SetFont('Ubuntu-Medium', '', 22);
                $text = iconv('utf-8', 'windows-1251', $course['NAME']);
                $separator = '\n';
                $array_strings = explode($separator, wordwrap('"'.$text.'"', 30, $separator));
                $st = 120;
                foreach ($array_strings as $string) {
                    $pdf->SetXY(75, $st); // X start, Y start in mm
                    $pdf->SetX(22.6);
                    $pdf->Cell(0, 0, $string, 0, 0, 'C');
                    $st = $st + 9;
                }
                $st = $st + 9;
                $pdf->SetXY(65, $st); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', $start_cert_date);
                $pdf->SetX(22.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');
                $st = $st + 20;

                $pdf->SetXY(65, $st); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', $user);
                $pdf->SetX(22.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');

                if(Course::isMarketing($course['ID'])){
                    $need_dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/certificates/new/' . $course['ID'];
                    $filename = $compl_info['ID'] . '.pdf';
                } else {
                    $need_dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/certificates/' . $course['ID'];
                    $filename = $compl_info['UF_COURSE_ID'] . '_' . $compl_info['UF_USER_ID'] . '_' . str_replace('.', '_', $start_cert_date) . '.pdf';
                }
                if (!is_dir($need_dir))
                    mkdir($need_dir);
                $pdf->Output('F', $need_dir . '/' . $filename);


            } else {
                $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/'.$template.'-pdf.pdf';
                $pdf = new Fpdi();
                $pdf->AddPage();
                $pdf->AddFont('Arial', '', 'arial.php');

                $pdf->setSourceFile($file);
                $tplId = $pdf->importPage(1);
                $pdf->useTemplate($tplId, 5, 5, 210);
                $pdf->SetTextColor(0, 0, 0); // RGB


                $pdf->SetFont('Arial', '', 22);
                $text = iconv('utf-8', 'windows-1251', $course['NAME']);
                $separator = '\n';
                $array_strings = explode($separator, wordwrap('"'.$text.'"', 30, $separator));
                $st = 120;
                foreach ($array_strings as $string) {
                    $pdf->SetXY(75, $st); // X start, Y start in mm
                    $pdf->SetX(12.6);
                    $pdf->Cell(0, 0, $string, 0, 0, 'C');
                    $st = $st + 9;
                }
                $pdf->SetXY(65, 145); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', $user);
                $pdf->SetX(12.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');

                if(Course::isMarketing($course['ID'])){
                    $need_dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/certificates/new/' . $course['ID'];
                    $filename = $compl_info['ID'] . '.pdf';
                } else {
                    $need_dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/certificates/' . $course['ID'];
                    $filename = $compl_info['UF_COURSE_ID'] . '_' . $compl_info['UF_USER_ID'] . '_' . str_replace('.', '_', $start_cert_date) . '.pdf';
                }
                if (!is_dir($need_dir))
                    mkdir($need_dir);
                $pdf->Output('F', $need_dir . '/' . $filename);
            }
        } else {
            if (!empty($course['PROPERTY_CERT_NUMBER_TEMPLATE_VALUE'])) {
                $user = User::getFullName($compl_info['UF_USER_ID']);
                if (!$user)
                    return;
                $last_num = $course['PROPERTY_LAST_CERT_NUMBER_VALUE'] ?? 0;
                $cur_num = ++$last_num;
                $compl_info['UF_DATE'] = Courses::isFreeSheduleCourse($compl_info['UF_COURSE_ID']) && !empty($compl_info['UF_COMPLETED_TIME'])?DateHelper::getHumanDate((string)$compl_info['UF_COMPLETED_TIME'], "d.m.Y"):$compl_info['UF_DATE'];

                if ((int)$course['PROPERTY_COUNT_SYMBOLS_VALUE'] > 0) {
                    $cur_num = str_pad($cur_num, (int)$course['PROPERTY_COUNT_SYMBOLS_VALUE'], 0, STR_PAD_LEFT);
                }
                $cert_number = str_replace('#NUMBER#', $cur_num, $course['PROPERTY_CERT_NUMBER_TEMPLATE_VALUE']);

                $months = $course['PROPERTY_CERT_EXP_VALUE'] > 0 ? $course['PROPERTY_CERT_EXP_VALUE'] : 12;
                $start_cert_date = Courses::isFreeSheduleCourse($compl_info['UF_COURSE_ID'])&&$compl_info['UF_COMPLETED_TIME']?(string)$compl_info['UF_COMPLETED_TIME']:(string)$compl_info['UF_DATE'];
                $start_cert_date = date('d.m.Y', strtotime($start_cert_date));

                $end_cert_date = $compl_info['UF_EXPIRED_DATE']?(string)$compl_info['UF_EXPIRED_DATE']:date('d.m.Y', strtotime("+" . $months . " months", strtotime($start_cert_date)));
                $end_cert_date = date('d.m.Y', strtotime($end_cert_date));

                \Models\Course::setLastNumber($compl_info['UF_COURSE_ID'], $last_num);
                $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/oop-pdf.pdf';
                $pdf = new Fpdi();
                $pdf->AddPage();
                $pdf->AddFont('Arial', '', 'arial.php');

                $pdf->setSourceFile($file);
                $tplId = $pdf->importPage(1);
                $pdf->useTemplate($tplId, 5, 5, 210);
                $pdf->SetFont('Arial', 'B', 11); // Font Name, Font Style (eg. 'B' for Bold), Font Size
                $pdf->SetTextColor(0, 0, 0); // RGB

                $pdf->SetFont('Times', '', 11); // Font Name, Font Style (eg. 'B' for Bold), Font Size

                $pdf->SetXY(140, 52.5); // X start, Y start in mm
                $text = $cert_number;
                $pdf->Write(0, $text);


                $pdf->SetXY(140, 58.5); // X start, Y start in mm
                $start_cert_date_text = iconv('utf-8', 'windows-1251', $start_cert_date);
                $pdf->Write(0, $start_cert_date_text);

                $pdf->SetXY(140, 64.5); // X start, Y start in mm
                $end_cert_date_text = iconv('utf-8', 'windows-1251', $end_cert_date);
                $pdf->Write(0, $end_cert_date_text);
                $pdf->SetTextColor(3, 101, 174); // RGB
                $pdf->SetFont('Times', '', 27); // Font Name, Font Style (eg. 'B' for Bold), Font Size

                $pdf->SetXY(65, 114); // X start, Y start in mm
                $category = "GMTC";
                if($course['PROPERTY_COURSE_CATEGORY_VALUE']=="GKPC") {
                    $category = "GKPC";
                    $pdf->SetFont('Times', '', 29); // Font Name, Font Style (eg. 'B' for Bold), Font Size
                    $text = iconv('utf-8', 'windows-1251', "Geely Key Position Certificate");
                } else {
                    $text = iconv('utf-8', 'windows-1251', "Geely Maintenance Technician Certificate");
                }
                $pdf->SetX(18.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');
                $pdf->SetTextColor(0, 0, 0); // RGB
                $pdf->SetFont('Arial', 'U', 21);
                $pdf->SetXY(65, 150); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', "     ".$user."     ");
                $pdf->SetX(18.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');
                $pdf->SetFont('Times', '', 20);
                $pdf->SetXY(65, 174); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', "in recognition of successfull $category");
                $pdf->SetX(18.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');


                $pdf->SetFont('Arial', 'U', 24);
                $text = iconv('utf-8', 'windows-1251', $course['NAME']);
                $separator = '\n';
                $array_strings = explode($separator, wordwrap($text, 30, $separator));
                $st = 185;
                foreach ($array_strings as $string) {
                    $pdf->SetXY(75, $st); // X start, Y start in mm
                    $pdf->SetX(12.6);
                    $pdf->Cell(0, 0, $string, 0, 0, 'C');
                    $st = $st + 11;
                }
                $pdf->SetFont('Times', '', 20); // Font Name, Font Style (eg. 'B' for Bold), Font Size

                $pdf->SetXY(65, $st); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', "course certification completion");
                $pdf->SetX(18.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');

                $need_dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/certificates/new/' . $course['ID'];
                if (!is_dir($need_dir))
                    mkdir($need_dir);
                $filename = $compl_info['ID'] . '.pdf';
                $add_fields['UF_COMPLETION_ID'] = $compl_info['ID'];
                $add_fields['UF_USER_ID'] = $compl_info['UF_USER_ID'];
                $add_fields['UF_COURSE_ID'] = $compl_info['UF_COURSE_ID'];
                $add_fields['UF_EXPIRED_AT'] = $end_cert_date;
                $add_fields['UF_EXPIRED_PERIOD'] = $months;
                $add_fields['UF_CREATED_AT'] = $start_cert_date;
                $add_fields['UF_CERT_LINK'] = str_replace($_SERVER["DOCUMENT_ROOT"], false, $need_dir . '/' . $filename);
                $add_fields['UF_CERT_NUMBER'] = $cert_number;
                if($exist_sertificate = Sertificate::getByCompletion($add_fields['UF_COMPLETION_ID'])){
                    Sertificate::update($exist_sertificate['ID'], ['UF_CREATED_AT' => $add_fields['UF_CREATED_AT'], 'UF_EXPIRED_AT' => $add_fields['UF_EXPIRED_AT'], 'UF_CERT_NUMBER' => $add_fields['UF_CERT_NUMBER'], 'UF_EXPIRED_PERIOD' => $months]);
                } else {
                    Sertificate::create($add_fields);
                }
                $pdf->Output('F', $need_dir . '/' . $filename);

            }
        }
    }
    public static function generateCertFromCompletionId($completion_id, $regen = false): void
    {
        $completions = new \Teaching\CourseCompletion();
        $compl_info = $completions->find($completion_id);
        if(!$compl_info['UF_COURSE_ID']>0)
            return;
        $course = \Models\Course::find($compl_info['UF_COURSE_ID'], [
            'NAME',
            'PROPERTY_CERTIFICATE_TEMPLATE',
            'PROPERTY_CERT_NUMBER_TEMPLATE',
            'PROPERTY_LAST_CERT_NUMBER',
            'PROPERTY_CERT_EXP',
            'PROPERTY_COUNT_SYMBOLS',
            'PROPERTY_COURSE_CATEGORY'
        ]);
        if(\Models\Course::isOP($course['ID']) || \Models\Course::isMarketing($course['ID'])) {
            $user = User::find($compl_info['UF_USER_ID'], ['ID', 'NAME', 'LAST_NAME']);
            if(!$user['ID']>0)
                return;
            $user = $user['LAST_NAME'].' '.$user['NAME'];
            $start_cert_date = $compl_info['UF_COMPLETED_TIME']?date('d.m.Y', strtotime($compl_info['UF_COMPLETED_TIME'])):(string)$compl_info['UF_DATE'];
            $file_date = (string)$compl_info['UF_DATE'];
            $template_xml = \Models\Course::getTemplateXML($course['ID']);

            $template = $template_xml == 'template3' ? 4 : 3;
            $template = $template_xml == 'template4' ? 5 : $template;
            if($template_xml == 'template4') {
                $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/new_op_template.pdf';
                $pdf = new Fpdi();
                $pdf->AddPage();
                $pdf->AddFont('Ubuntu-Medium', '', 'Ubuntu-Medium.php');

                $pdf->setSourceFile($file);

                $tplId = $pdf->importPage(1);
                $pdf->useTemplate($tplId, 5, 5, 210);
                $pdf->SetTextColor(0, 0, 0); // RGB


                $pdf->SetFont('Ubuntu-Medium', '', 22);
                $text = iconv('utf-8', 'windows-1251', $course['NAME']);
                $separator = '\n';
                $array_strings = explode($separator, wordwrap('"'.$text.'"', 40, $separator));
                $st = 125;
                foreach ($array_strings as $string) {
                    $pdf->SetXY(75, $st); // X start, Y start in mm
                    $pdf->SetX(22.6);
                    $pdf->Cell(0, 0, $string, 0, 0, 'C');
                    $st = $st + 9;
                }
                $st = $st + 9;
                $pdf->SetXY(65, $st); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', $user);
                $pdf->SetX(22.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');
                $st = $st + 20;

                $pdf->SetXY(65, $st); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', $start_cert_date);
                $pdf->SetX(22.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');
                if(Course::isMarketing($course['ID'])){
                    $need_dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/certificates/new/' . $course['ID'];
                    $filename = $compl_info['ID'] . '.pdf';
                } else {
                    $need_dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/certificates/' . $course['ID'];
                    $filename = $compl_info['UF_COURSE_ID'] . '_' . $compl_info['UF_USER_ID'] . '_' . str_replace('.', '_', $file_date) . '.pdf';
                }
                if (!is_dir($need_dir))
                    mkdir($need_dir);
                $pdf->Output('F', $need_dir . '/' . $filename);
            } elseif ($template_xml == 'template3') {

                $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/'.$template.'-pdf.pdf';
                $pdf = new Fpdi();
                $pdf->AddPage();
                $pdf->AddFont('Ubuntu-Medium', '', 'Ubuntu-Medium.php');

                $pdf->setSourceFile($file);
                $tplId = $pdf->importPage(1);
                $pdf->useTemplate($tplId, 5, 5, 210);
                $pdf->SetTextColor(0, 0, 0); // RGB


                $pdf->SetFont('Ubuntu-Medium', '', 22);
                $text = iconv('utf-8', 'windows-1251', $course['NAME']);
                $separator = '\n';
                $array_strings = explode($separator, wordwrap('"'.$text.'"', 30, $separator));
                $st = 120;
                foreach ($array_strings as $string) {
                    $pdf->SetXY(75, $st); // X start, Y start in mm
                    $pdf->SetX(22.6);
                    $pdf->Cell(0, 0, $string, 0, 0, 'C');
                    $st = $st + 9;
                }
                $st = $st + 9;
                $pdf->SetXY(65, $st); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', $start_cert_date);
                $pdf->SetX(22.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');
                $st = $st + 20;

                $pdf->SetXY(65, $st); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', $user);
                $pdf->SetX(22.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');

                if(Course::isMarketing($course['ID'])){
                    $need_dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/certificates/new/' . $course['ID'];
                    $filename = $compl_info['ID'] . '.pdf';
                } else {
                    $need_dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/certificates/' . $course['ID'];
                    $filename = $compl_info['UF_COURSE_ID'] . '_' . $compl_info['UF_USER_ID'] . '_' . str_replace('.', '_', $start_cert_date) . '.pdf';
                }
                if (!is_dir($need_dir))
                    mkdir($need_dir);
                $pdf->Output('F', $need_dir . '/' . $filename);


            } else {
                $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/'.$template.'-pdf.pdf';
                $pdf = new Fpdi();
                $pdf->AddPage();
                $pdf->AddFont('Arial', '', 'arial.php');

                $pdf->setSourceFile($file);
                $tplId = $pdf->importPage(1);
                $pdf->useTemplate($tplId, 5, 5, 210);
                $pdf->SetTextColor(0, 0, 0); // RGB


                $pdf->SetFont('Arial', '', 22);
                $text = iconv('utf-8', 'windows-1251', $course['NAME']);
                $separator = '\n';
                $array_strings = explode($separator, wordwrap('"'.$text.'"', 30, $separator));
                $st = 120;
                foreach ($array_strings as $string) {
                    $pdf->SetXY(75, $st); // X start, Y start in mm
                    $pdf->SetX(12.6);
                    $pdf->Cell(0, 0, $string, 0, 0, 'C');
                    $st = $st + 9;
                }
                $pdf->SetXY(65, 145); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', $user);
                $pdf->SetX(12.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');

                if(Course::isMarketing($course['ID'])){
                    $need_dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/certificates/new/' . $course['ID'];
                    $filename = $compl_info['ID'] . '.pdf';
                } else {
                    $need_dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/certificates/' . $course['ID'];
                    $filename = $compl_info['UF_COURSE_ID'] . '_' . $compl_info['UF_USER_ID'] . '_' . str_replace('.', '_', $start_cert_date) . '.pdf';
                }
                if (!is_dir($need_dir))
                    mkdir($need_dir);
                $pdf->Output('F', $need_dir . '/' . $filename);
            }
        } else {
            if (!empty($course['PROPERTY_CERT_NUMBER_TEMPLATE_VALUE'])) {
                $user = User::find($compl_info['UF_USER_ID'], ['ID', 'NAME', 'LAST_NAME']);
                if(!$user['ID']>0)
                    return;
                $user = $user['LAST_NAME'].' '.$user['NAME'];
                $last_num = $course['PROPERTY_LAST_CERT_NUMBER_VALUE'] ?? 0;
                $compl_info['UF_DATE'] = Courses::isFreeSheduleCourse($compl_info['UF_COURSE_ID']) && !empty($compl_info['UF_COMPLETED_TIME'])?DateHelper::getHumanDate((string)$compl_info['UF_COMPLETED_TIME'], "d.m.Y"):$compl_info['UF_DATE'];

                if ($compl_info['ID'] == 31871) {
                    if ($exist_sertificate = Sertificate::getByCompletion($compl_info['ID'])) {
                        $cur_num = $last_num;
                        $cert_number = $exist_sertificate['UF_CERT_NUMBER'];
                    } else {
                        $cur_num = ++$last_num;
                        if ((int)$course['PROPERTY_COUNT_SYMBOLS_VALUE'] > 0) {
                            $cur_num = str_pad($cur_num, (int)$course['PROPERTY_COUNT_SYMBOLS_VALUE'], 0, STR_PAD_LEFT);
                        }
                        $cert_number = str_replace('#NUMBER#', $cur_num, $course['PROPERTY_CERT_NUMBER_TEMPLATE_VALUE']);
                    }
                } else {
                    $cur_num = ++$last_num;

                    if ((int)$course['PROPERTY_COUNT_SYMBOLS_VALUE'] > 0) {
                        $cur_num = str_pad($cur_num, (int)$course['PROPERTY_COUNT_SYMBOLS_VALUE'], 0, STR_PAD_LEFT);
                    }
                    $cert_number = str_replace('#NUMBER#', $cur_num, $course['PROPERTY_CERT_NUMBER_TEMPLATE_VALUE']);
                }


                $months = $course['PROPERTY_CERT_EXP_VALUE'] > 0 ? $course['PROPERTY_CERT_EXP_VALUE'] : 12;
                $start_cert_date = Courses::isFreeSheduleCourse($compl_info['UF_COURSE_ID'])&&$compl_info['UF_COMPLETED_TIME']?(string)$compl_info['UF_COMPLETED_TIME']:(string)$compl_info['UF_DATE'];
                $start_cert_date = date('d.m.Y', strtotime($start_cert_date));

                $end_cert_date = $compl_info['UF_EXPIRED_DATE']?(string)$compl_info['UF_EXPIRED_DATE']:date('d.m.Y', strtotime("+" . $months . " months", strtotime($start_cert_date)));
                $end_cert_date = date('d.m.Y', strtotime($end_cert_date));

                \Models\Course::setLastNumber($compl_info['UF_COURSE_ID'], $last_num);
                $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/ppo_GMTC.pdf';
                if($course['PROPERTY_COURSE_CATEGORY_VALUE']=="GKPC") {
                    $file = $_SERVER["DOCUMENT_ROOT"] . '/upload/ppo_GKPC.pdf';
                }
                $pdf = new Fpdi();
                $pdf->AddPage();
                $pdf->AddFont('Arial', '', 'arial.php');

                $pdf->setSourceFile($file);
                $tplId = $pdf->importPage(1);
                $pdf->useTemplate($tplId, 5, 5, 210);
                $pdf->SetFont('Arial', 'B', 11); // Font Name, Font Style (eg. 'B' for Bold), Font Size
                $pdf->SetTextColor(0, 0, 0); // RGB

                $pdf->SetFont('Times', '', 11); // Font Name, Font Style (eg. 'B' for Bold), Font Size

                $pdf->SetXY(140, 52.5); // X start, Y start in mm
                $text = $cert_number;
                $pdf->Write(0, $text);


                $pdf->SetXY(140, 58.5); // X start, Y start in mm
                $start_cert_date_text = iconv('utf-8', 'windows-1251', $start_cert_date);
                $pdf->Write(0, $start_cert_date_text);

                $pdf->SetXY(140, 64.5); // X start, Y start in mm
                $end_cert_date_text = iconv('utf-8', 'windows-1251', $end_cert_date);
                $pdf->Write(0, $end_cert_date_text);
                $pdf->SetTextColor(3, 101, 174); // RGB
                $pdf->SetFont('Times', '', 27); // Font Name, Font Style (eg. 'B' for Bold), Font Size

                $pdf->SetXY(65, 114); // X start, Y start in mm
                $category = "GMTC";
                if($course['PROPERTY_COURSE_CATEGORY_VALUE']=="GKPC") {
                    $category = "GKPC";
                    $pdf->SetFont('Times', '', 29); // Font Name, Font Style (eg. 'B' for Bold), Font Size
                    $text = iconv('utf-8', 'windows-1251', "Geely Key Position Certificate");
                } else {
                    $text = iconv('utf-8', 'windows-1251', "Geely Maintenance Technician Certificate");
                }
                $pdf->SetX(18.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');
                $pdf->SetTextColor(0, 0, 0); // RGB
                $pdf->SetFont('Arial', 'U', 21);
                $pdf->SetXY(65, 150); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', "     ".$user."     ");
                $pdf->SetX(18.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');
                $pdf->SetFont('Times', '', 20);
                $pdf->SetXY(65, 174); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', "in recognition of successfull $category");
                $pdf->SetX(18.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');


                $pdf->SetFont('Arial', 'U', 24);
                $text = iconv('utf-8', 'windows-1251', $course['NAME']);
                $separator = '\n';
                $array_strings = explode($separator, wordwrap($text, 30, $separator));
                $st = 185;
                foreach ($array_strings as $string) {
                    $pdf->SetXY(75, $st); // X start, Y start in mm
                    $pdf->SetX(12.6);
                    $pdf->Cell(0, 0, $string, 0, 0, 'C');
                    $st = $st + 11;
                }
                $pdf->SetFont('Times', '', 20); // Font Name, Font Style (eg. 'B' for Bold), Font Size

                $pdf->SetXY(65, $st); // X start, Y start in mm
                $text = iconv('utf-8', 'windows-1251', "course certification completion");
                $pdf->SetX(18.6);
                $pdf->Cell(0, 0, $text, 0, 0, 'C');

                $need_dir = $_SERVER["DOCUMENT_ROOT"] . '/upload/certificates/new/' . $course['ID'];
                if (!is_dir($need_dir))
                    mkdir($need_dir);
                $filename = $compl_info['ID'] . '.pdf';
                $add_fields['UF_COMPLETION_ID'] = $compl_info['ID'];
                $add_fields['UF_USER_ID'] = $compl_info['UF_USER_ID'];
                $add_fields['UF_COURSE_ID'] = $compl_info['UF_COURSE_ID'];
                $add_fields['UF_EXPIRED_AT'] = $end_cert_date;
                $add_fields['UF_EXPIRED_PERIOD'] = $months;
                $add_fields['UF_CREATED_AT'] = $start_cert_date;
                $add_fields['UF_CERT_LINK'] = str_replace($_SERVER["DOCUMENT_ROOT"], false, $need_dir . '/' . $filename);
                $add_fields['UF_CERT_NUMBER'] = $cert_number;
                if($exist_sertificate = Sertificate::getByCompletion($add_fields['UF_COMPLETION_ID'])){
                    Sertificate::update($exist_sertificate['ID'], ['UF_CREATED_AT' => $add_fields['UF_CREATED_AT'], 'UF_EXPIRED_AT' => $add_fields['UF_EXPIRED_AT'], 'UF_CERT_NUMBER' => $add_fields['UF_CERT_NUMBER'], 'UF_EXPIRED_PERIOD' => $months]);
                } else {
                    Sertificate::create($add_fields);
                }
                $pdf->Output('F', $need_dir . '/' . $filename);

            }
        }
    }

    public static function setWaterMarkImage($pdfPath, $waterMarkPath)
    {
        $pdf = new FPDI();
        $pageCount = $pdf->setSourceFile($pdfPath);
        // Проходим через все страницы исходного PDF
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
            $pdf->Image($waterMarkPath, ($size['width'] - 50) / 1.2, ($size['height'] - 50) / 1.2, 50, 50);

            // Сбрасываем прозрачность
        }
        // Выводим готовый PDF файл для скачивания

    }
}