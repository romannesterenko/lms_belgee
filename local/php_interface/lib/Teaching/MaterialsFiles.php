<?php


namespace Teaching;


use Helpers\IBlockHelper;

class MaterialsFiles
{
    public static $icons = [
        'default' => SITE_TEMPLATE_PATH . '/images/zip-icon.svg',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => SITE_TEMPLATE_PATH . '/images/exel-icon.svg',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => SITE_TEMPLATE_PATH . '/images/word-icon.svg',
        'application/msword' => SITE_TEMPLATE_PATH . '/images/word-icon.svg',
        'application/msexel' => SITE_TEMPLATE_PATH . '/images/exel-icon.svg',
        'application/pdf' => SITE_TEMPLATE_PATH . '/images/pdf-color-icon.svg',
        'image/jpeg' => SITE_TEMPLATE_PATH . '/images/img-icon.svg',
        'image/gif' => SITE_TEMPLATE_PATH . '/images/img-icon.svg',
        'image/png' => SITE_TEMPLATE_PATH . '/images/img-icon.svg',
        'image/svg+xml' => SITE_TEMPLATE_PATH . '/images/img-icon.svg',
        'image/webp' => SITE_TEMPLATE_PATH . '/images/img-icon.svg',
    ];

    public static function getList($filter, $select)
    {
        IBlockHelper::includeIBlockModule();
        $list = [];
        $arFilter = array_merge(["IBLOCK_ID" => IBlockHelper::getMaterialsFilesIBlock()], $filter);
        $res = \CIBlockElement::GetList(array(), $arFilter, false, array(), $select);
        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $arFields['FILE_INFO'] = $arFields['PROPERTY_FILE_VALUE'] > 0 ? \CFile::GetFileArray($arFields['PROPERTY_FILE_VALUE']) : [];
            if ($arFields['FILE_INFO']['CONTENT_TYPE'])
                $arFields['FILE_INFO']['FILE_ICON'] = self::$icons[$arFields['FILE_INFO']['CONTENT_TYPE']] ?? self::$icons['default'];
            $list[$arFields['ID']] = $arFields;
        }
        return $list;
    }

    public static function resizeBytes($size, $type = 'M')
    {
        $result = $kb = ceil(((int)$size) / 1024);
        if ($type == 'M')
            $result = number_format($kb / 1024, '2', ',', ' ');
        return $result;
    }
}