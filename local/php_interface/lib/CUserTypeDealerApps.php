<?php
use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Iblock;

/**
 * Реализация свойство «Расписание врача»
 * Class CUserTypeDealerApps
 * @package lib\usertype
 */
class CUserTypeDealerApps
{
    /**
     * Метод возвращает массив описания собственного типа свойств
     * @return array
     */
    public static function GetUserTypeDescription()
    {
        return array(
            'USER_TYPE_ID' => 'applications_count', //Уникальный идентификатор типа свойств
            'USER_TYPE' => 'APPLICATIONS_COUNT',
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => 'Количество заявок',
            'PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_STRING,
            'ConvertToDB' => [__CLASS__, 'ConvertToDB'],
            'ConvertFromDB' => [__CLASS__, 'ConvertFromDB'],
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
        );
    }

    /**
     * Конвертация данных перед сохранением в БД
     * @param $arProperty
     * @param $value
     * @return mixed
     */
    public static function ConvertToDB($arProperty, $value)
    {

        if (!empty($value['VALUE']['COUNT']) && $value['VALUE']['COUNT'] != '' && !empty($value['VALUE']['APP']) && $value['VALUE']['APP'] != '')
        {
            try {
                $value['VALUE'] = base64_encode(serialize($value['VALUE']));
            } catch(Bitrix\Main\ObjectException $exception) {
                echo $exception->getMessage();
            }
        } else {
            $value['VALUE'] = '';
        }
        return $value;
    }

    /**
     * Конвертируем данные при извлечении из БД
     * @param $arProperty
     * @param $value
     * @param string $format
     * @return mixed
     */
    public static function ConvertFromDB($arProperty, $value, $format = '')
    {
        if ($value['VALUE'] != '')
        {
            try {
                $value['VALUE'] = base64_decode($value['VALUE']);

            } catch(Bitrix\Main\ObjectException $exception) {
                echo $exception->getMessage();
            }
        }

        return $value;
    }

    /**
     * Представление формы редактирования значения
     * @param $arUserField
     * @param $arHtmlControl
     */
    public static function GetPropertyFieldHtml($arProperty, $value, $arHtmlControl)
    {

        $applications_array = \Models\Application::getAll(['ID', 'NAME']);
        $applications = [];
        foreach ($applications_array as $application){
            $applications[$application['ID']] = $application['NAME'];
        }
        $itemId = 'row_' . substr(md5($arHtmlControl['VALUE']), 0, 10); //ID для js
        $fieldName =  htmlspecialcharsbx($arHtmlControl['VALUE']);
        $arValue = unserialize(htmlspecialcharsback($value['VALUE']), [stdClass::class]);

        $select = '<select class="week_day" name="'. $fieldName .'[APP]">';
        foreach ($applications as $key => $application){
            if($arValue['APP'] == $key){
                $select .= '<option value="'. $key .'" selected="selected">'. $application .'</option>';
            } else {
                $select .= '<option value="'. $key .'">'. $application .'</option>';
            }

        }
        $select .= '</select>';

        $html = '<div class="property_row" id="'. $itemId .'">';

        $html .= '<div class="reception_time">';
        $html .= $select;
        $app = ($arValue['APP']) ? $arValue['COUNT'] : '';
        $count = ($arValue['COUNT']) ? $arValue['COUNT'] : '';

        $html .='&nbsp;Количество: &nbsp;<input type="text" name="'. $fieldName .'[COUNT]" value="'. $count . '">';
        if($app!='' && $count!=''){
            $html .= '&nbsp;&nbsp;<input type="button" style="height: auto;" value="x" title="Удалить" onclick="document.getElementById(\''. $itemId .'\').parentNode.parentNode.remove()" />';
        }
        $html .= '</div>';

        $html .= '</div><br/>';

        return $html;
    }
}