<?php

namespace Teaching;

use \Helpers\HLBlockHelper as HLBlock;
use Settings\Notifications;

class CourseCategory
{
    private $dataClass;

    public function __construct()
    {
        $this->dataClass = HLBlock::initialize('b_hlbd_courcecategory');
    }

    public function get($filter = [], $select = ['*'], $order = ["ID" => "DESC"])
    {
        return $this->dataClass::getList(array(
            "select" => $select,
            "order" => $order,
            "filter" => $filter
        ))->fetchAll();
    }

    public function getAll()
    {
        return $this->get();
    }
    public function getByName($name){
        return $this->get(['UF_NAME' => $name]);
    }
}