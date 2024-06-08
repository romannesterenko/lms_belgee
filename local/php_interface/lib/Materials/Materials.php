<?php

namespace Materials;

use \CIBlockElement;

class Materials
{
    public function __construct()
    {
        \Helpers\IBlockHelper::includeIBlockModule();
    }

    public function getSection($id)
    {
        $list = array_values(\Helpers\IBlockHelper::getElements(['IBLOCK_ID' => \Helpers\IBlockHelper::getMaterialsIBlock(), 'ID' => $id], ['ID', 'IBLOCK_SECTION_ID']));
        return $list[0]['IBLOCK_SECTION_ID'];
    }

    public function getDealerInfo($id)
    {
        $list = array_values(\Helpers\IBlockHelper::getElements(['IBLOCK_ID' => \Helpers\IBlockHelper::getDealersIBlock(), 'ID' => $id], [], ['ID', 'NAME', 'CODE',
            'PROPERTY_ORG_NAME',
            'PROPERTY_COMM_NAME',
            'PROPERTY_ENG_NAME',
            'PROPERTY_CITY',
            'PROPERTY_ORG_ADDRESS',
            'PROPERTY_PLACE_ADDRESS'
        ]));
        return $list;
    }
}