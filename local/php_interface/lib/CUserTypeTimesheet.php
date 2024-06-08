<?php
use Bitrix\Main\Loader,
    Bitrix\Main\Localization\Loc,
    Bitrix\Iblock;

/**
 * Реализация свойство «Расписание врача»
 * Class CUserTypeTimesheet
 * @package lib\usertype
 */
class CUserTypeTimesheet
{
    /**
     * Метод возвращает массив описания собственного типа свойств
     * @return array
     */
    public static function GetUserTypeDescription()
    {
        return array(
            'USER_TYPE_ID' => 'form_field_type', //Уникальный идентификатор типа свойств
            'USER_TYPE' => 'FORMFIELDTYPE',
            'CLASS_NAME' => __CLASS__,
            'DESCRIPTION' => 'Поле формы',
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

        if (!empty($value['VALUE']['NAME']) && $value['VALUE']['NAME'] != '' && !empty($value['VALUE']['FIELD_CODE']) && $value['VALUE']['FIELD_CODE'] != '')
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
        $load_types = [
            'dealer' => 'Текущий дилер',
            'user' => 'Текущий пользователь',
        ];

        $field_types = [
            'string' => 'Строковое поле',
            'number' => 'Числовое поле',
        ];
        $iblockId = 10;
        $properties = Bitrix\Iblock\PropertyTable::getList([
            'filter' => ['IBLOCK_ID' => $iblockId],
            'select' => ['ID', 'CODE', 'NAME']
        ])->fetchAll();
        $dealer_fields['NAME'] = 'Имя';
        $dealer_fields['CODE'] = 'Код';
        foreach ($properties as $property) {
            $dealer_fields[$property['CODE']] = $property['NAME'];
        }

        $itemId = 'row_' . substr(md5($arHtmlControl['VALUE']), 0, 10); //ID для js
        $fieldName =  htmlspecialcharsbx($arHtmlControl['VALUE']);

        $arValue = unserialize(htmlspecialcharsback($value['VALUE']), [stdClass::class]);

        $html = '<div class="property_row" style="border: 1px solid; border-radius: 5px; padding: 5px;" id="'. $itemId .'">';

        $html .= '<div class="reception_time"><table style="width: 100%;">';

        $name = $arValue['NAME']??'';
        $form_code = $arValue['FIELD_CODE']??'';
        $required_value = $arValue['REQUIRED'] == 'on'?' checked':'';
        $from_checked_value = $arValue['AUTOMATIC'] == 'on'?' checked':'';
        $hidden_value = $arValue['HIDDEN'] == 'on'?' checked':'';
        $disabled = $arValue['AUTOMATIC'] == 'on'?'':' disabled';
        $dealer_fields_displayed_tr = $arValue['AUTOMATIC'] == 'on' && !empty($arValue['DEALER_FIELDS'])?'':' style="display: none;"';
        $dealer_fields_disabled = $arValue['AUTOMATIC'] == 'on' && !empty($arValue['DEALER_FIELDS'])?'':' disabled';
        $user_fields_displayed_tr = $arValue['AUTOMATIC'] == 'on' && !empty($arValue['USER_FIELDS'])?'':' style="display: none;"';
        $user_fields_disabled = $arValue['AUTOMATIC'] == 'on' && !empty($arValue['USER_FIELDS'])?'':' disabled';
        $select = '<select style="width: 100%" class="week_day" name="'. $fieldName .'[FIELD_TYPE]">';
        foreach ($field_types as $key => $field_type) {
            if($arValue['FIELD_TYPE'] == $key) {
                $select .= '<option value="'. $key .'" selected="selected">'. $field_type .'</option>';
            } else {
                $select .= '<option value="'. $key .'">'. $field_type .'</option>';
            }

        }
        $select .= '</select>';

        $select_load_types = '<select style="width: 100%" onchange="changeSelect(this);" class="load_from" id="load_from" name="'. $fieldName .'[LOAD_FROM]"'.$disabled.'>';
        foreach ($load_types as $key => $load_type){
            if($arValue['LOAD_FROM'] == $key){
                $select_load_types .= '<option value="'. $key .'" selected="selected">'. $load_type .'</option>';
            } else {
                $select_load_types .= '<option value="'. $key .'">'. $load_type .'</option>';
            }

        }
        $select_load_types .= '</select>';

        $dealer_fields_select = '<select style="width: 100%" class="dealer_fields" id="dealer_fields" name="'. $fieldName .'[DEALER_FIELDS]"'.$dealer_fields_disabled.'>';
        foreach ($dealer_fields as $code => $dealer_field){
            if($arValue['DEALER_FIELDS'] == $code){
                $dealer_fields_select .= '<option value="'. $code .'" selected="selected">'. $dealer_field .'</option>';
            } else {
                $dealer_fields_select .= '<option value="'. $code .'">'. $dealer_field .'</option>';
            }
        }
        $dealer_fields_select .= '</select>';

        $user_fields_select = '<select style="width: 100%" class="user_fields" id="user_fields" name="'. $fieldName .'[USER_FIELDS]"'.$user_fields_disabled.'>';
        $user_fields_select .= '<option value="FIO">Имя и фамилия</option>';
        foreach (\Bitrix\Main\UserTable::getEntity()->getFields() as $fieldName__ => $field) {
            if($arValue['USER_FIELDS'] == $fieldName__){
                $user_fields_select .= '<option value="'. $fieldName__ .'" selected="selected">'. $field->getTitle() .'</option>';
            } else {
                $user_fields_select .= '<option value="'. $fieldName__ .'">'. $field->getTitle() .'</option>';
            }
        }
        $user_fields_select .= '</select>';



        $html .= '<tr><td>Имя поля*:</td><td><input style="width: 95%;" type="text" name="'. $fieldName .'[NAME]" value="'. $name . '"></td></tr>';
        $html .= '<tr><td>Тип поля:</td><td>'.$select.'</td></tr>';
        $html .= '<tr><td>Код поля (для формы)*:</td><td><input style="width: 95%;" type="text" name="'. $fieldName .'[FIELD_CODE]" value="'. $form_code . '"></td></tr>';
        $html .= '<tr><td>Обязательное:</td><td><input type="checkbox" name="'. $fieldName .'[REQUIRED]"'.$required_value.'></td></tr>';
        $html .= '<tr><td>Скрытое (Не нужно заполнять):</td><td><input type="checkbox" name="'. $fieldName .'[HIDDEN]"'.$hidden_value.'></td></tr>';
        $html .= '<tr><td>Заполняется автоматически:</td><td><input onchange="changeCheck(this);" type="checkbox" name="'. $fieldName .'[AUTOMATIC]"'.$from_checked_value.'></td></tr>';
        $html .= '<tr><td>Откуда берется:</td><td>'.$select_load_types.'</td></tr>';
        $html .= '<tr'.$dealer_fields_displayed_tr.'><td>Поля дилера:</td><td>'.$dealer_fields_select.'</td></tr>';
        $html .= '<tr'.$user_fields_displayed_tr.'><td>Поля пользователя:</td><td>'.$user_fields_select.'</td></tr>';
        if( $name != '' ) {
            $html .= '<tr><td></td><td style="text-align: end"><input type="button" style="height: auto;" value="Удалить поле" title="Удалить" onclick="document.getElementById(\''. $itemId .'\').parentNode.parentNode.remove()" /></td></tr>';
        }
        $html .= '</table></div>';

        $html .= '</div><br/>';
        $html .= '<script>
            function changeCheck(obj) {
                let parent_tbody = obj.parentNode.parentNode.parentNode;
                let load_from = parent_tbody.querySelector(\'.load_from\');
                load_from.disabled = !obj.checked
                if(load_from.value == "dealer") {
                    if(obj.checked) {
                        showDealerFields(parent_tbody)
                    } else {
                        hideAllFields(parent_tbody)
                    }
                }
                if(load_from.value == "user") {
                    if(obj.checked) {
                        showUserFields(parent_tbody)
                    } else {
                        hideAllFields(parent_tbody)
                    }
                }
            }
            function changeSelect(obj) {
                let parent_tbody = obj.parentNode.parentNode.parentNode;
                console.log(parent_tbody);
                console.log(obj);
                console.log(obj.value);
                if(obj.value == "dealer") {
                    hideAllFields(parent_tbody)
                    showDealerFields(parent_tbody)
                } else {
                    hideAllFields(parent_tbody)
                    showUserFields(parent_tbody)
                }
            }
            function showUserFields(parent_tbody){
                parent_tbody.querySelector(\'.user_fields\').parentNode.parentNode.removeAttribute(\'style\');
                parent_tbody.querySelector(\'.dealer_fields\').parentNode.parentNode.style.display = \'none\';
                parent_tbody.querySelector(\'.dealer_fields\').disabled = true;
                parent_tbody.querySelector(\'.user_fields\').disabled = false;
            }
            function showDealerFields(parent_tbody){
                parent_tbody.querySelector(\'.user_fields\').parentNode.parentNode.style.display = \'none\';
                parent_tbody.querySelector(\'.dealer_fields\').parentNode.parentNode.removeAttribute(\'style\');
                parent_tbody.querySelector(\'.dealer_fields\').disabled = false;
                parent_tbody.querySelector(\'.user_fields\').disabled = true;
            }
            function hideAllFields(parent_tbody) {
                parent_tbody.querySelector(\'.user_fields\').parentNode.parentNode.style.display = \'none\';
                parent_tbody.querySelector(\'.dealer_fields\').parentNode.parentNode.style.display = \'none\';
                parent_tbody.querySelector(\'.dealer_fields\').disabled = true;
                parent_tbody.querySelector(\'.user_fields\').disabled = true;
            }
        </script>';

        return $html;
    }
}