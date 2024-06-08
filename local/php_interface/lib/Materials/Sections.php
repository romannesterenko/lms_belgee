<?php

namespace Materials;

use \CIBlockElement;

class Sections
{
    public function __construct()
    {
        \Helpers\IBlockHelper::includeIBlockModule();
    }

    public function getCountMaterialsBySection($section_id)
    {
        $list = \Helpers\IBlockHelper::getElements(['IBLOCK_ID' => \Helpers\IBlockHelper::getMaterialsIBlock(), 'IBLOCK_SECTION_ID' => $section_id]);
        return count($list);
    }

    public function setCountToSection($id)
    {
        $material = new \Materials\Materials();
        $section_id = $material->getSection($id);
        if ($section_id > 0) {
            $count = $this->getCountMaterialsBySection($section_id);
            \Helpers\IBlockHelper::updateSection($section_id, ['UF_ELEMENTS_COUNT' => $count]);
        }
    }

    private function getExistsCountMaterials($section_id)
    {
        $list = \Helpers\IBlockHelper::getSections(['IBLOCK_ID' => \Helpers\IBlockHelper::getMaterialsIBlock(), 'ID' => $section_id], ['ID', 'UF_ELEMENTS_COUNT']);
        return (int)$list[0]['UF_ELEMENTS_COUNT'];
    }
}