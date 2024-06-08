<?php


namespace Helpers;


class PropertyHelper
{
    public static function collectFields(array $item)
    {
        if(!$item)
            return [];
        \Helpers\IBlockHelper::includeIBlockModule();
        $res = \CIBlockElement::GetProperty($item['IBLOCK_ID'], $item['ID'], 'sort', 'asc');
        while ($property = $res->fetch()) {
            if ($property['MULTIPLE'] == 'Y') {
                if (!empty($property['VALUE'])) {
                    $new_prop['VALUE'] = $property['VALUE'];
                    if (!empty($property['DESCRIPTION']))
                        $new_prop['DESCRIPTION'] = $property['DESCRIPTION'];
                    $item['PROPERTIES'][$property['CODE']][] = $new_prop;
                    unset($new_prop['DESCRIPTION']);
                }
            } else {
                if (!empty($property['VALUE'])) {
                    if ($property['PROPERTY_TYPE'] == 'L')
                        $item['PROPERTIES'][$property['CODE']] = $property['VALUE_ENUM'];
                    else
                        $item['PROPERTIES'][$property['CODE']] = $property['VALUE'];
                }
            }
        }
        return $item;
    }

    public static function getPropertyValue($iblock_id, $id, $code)
    {
        \Helpers\IBlockHelper::includeIBlockModule();
        $res = \CIBlockElement::GetProperty($iblock_id, $id, 'sort', 'asc', ['CODE' => $code]);
        if ($property = $res->fetch())
            return $property['VALUE'];
    }

    public static function getPropertyValues($iblock_id, $id, $code)
    {
        \Helpers\IBlockHelper::includeIBlockModule();
        $res = \CIBlockElement::GetProperty($iblock_id, $id, 'sort', 'asc', ['CODE' => $code]);
        $return = [];
        while ($property = $res->fetch())
            $return[] = $property['VALUE'];
        return $return;
    }

    public static function getPropertyValuesList($ib_id, $code)
    {
        $values = [];
        \Helpers\IBlockHelper::includeIBlockModule();
        $property_enums = \CIBlockPropertyEnum::GetList(
            ["DEF"=>"DESC", "SORT"=>"ASC"],
            ["IBLOCK_ID"=>$ib_id, "CODE"=>$code]
        );
        while($enum_fields = $property_enums->GetNext()){
            $values[$enum_fields["ID"]] = $enum_fields["VALUE"];
        }
        return $values;
    }
}