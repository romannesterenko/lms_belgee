<?php

namespace Helpers;


use CIBlockElement;
use CModule;

class EventHandlerHelper
{
    public static function wasChanged($event_fields, $field_code):bool
    {
        if(array_key_exists($field_code, $event_fields) && array_key_exists("ID", $event_fields) && $event_fields['ID'] > 0){
            CModule::includeModule('iblock');
            $old_item = CIBlockElement::GetByID($event_fields['ID'])->fetch();
            if(array_key_exists($field_code, $old_item))
                return $old_item[$field_code] != $event_fields[$field_code];
            return false;
        }
        return false;
    }
}