<?php

namespace Teaching;

use Helpers\HLBlockHelper as HLBlock;

class Recruitment
{
    private string $dataClass;

    public function __construct()
    {
        $this->dataClass = HLBlock::initialize('recruitments');
    }

    public function get($filter = [], $select = ['*'], $order = ["ID" => "DESC"])
    {
        return $this->dataClass::getList(array(
            "select" => $select,
            "order" => $order,
            "filter" => $filter
        ))->fetchAll();
    }
    public function addRecruit($user_id, $dealer_id, $role_id, $date=null){
        $exists_elems = (new Recruitment())->get(['UF_USER' => $user_id, 'UF_DEALER' => $dealer_id]);
        $last_item = check_full_array($exists_elems)?current($exists_elems):[];
        if($last_item['UF_TYPE']!=26) {
            $fields = [
                'UF_USER' => $user_id,
                'UF_DEALER' => $dealer_id,
                'UF_ROLES' => $role_id,
                'UF_TIME' => $date ?? date('d.m.Y H:i:s'),
                'UF_TYPE' => 26,
            ];
            $this->add($fields);
        }
    }
    public function addDismiss($user_id, $dealer_id, $role_id, $date=null){
        $exists_elems = (new Recruitment())->get(['UF_USER' => $user_id, 'UF_DEALER' => $dealer_id]);
        $last_item = check_full_array($exists_elems)?current($exists_elems):[];
        if($last_item['UF_TYPE']!=27) {
            $fields = [
                'UF_USER' => $user_id,
                'UF_DEALER' => $dealer_id,
                'UF_ROLES' => $role_id,
                'UF_TIME' => $date ?? date('d.m.Y H:i:s'),
                'UF_TYPE' => 27,
            ];
            $this->add($fields);
        }
    }
    public function add($fields)
    {
        $this->dataClass::add($fields);
    }

    public function existsRecruitOnNeedPeriod($role_id, $dealer_id, $min_date)
    {
        $list = current($this->get(['UF_TYPE' => 26, 'UF_ROLES' => $role_id, 'UF_DEALER' => $dealer_id, '>UF_TIME' => $min_date]));
        return $list['ID']>0;
    }

    public function existsDismissOnNeedPeriod($role_id, $dealer_id, $min_date)
    {
        $list = current($this->get(['UF_TYPE' => 27, 'UF_ROLES' => $role_id, 'UF_DEALER' => $dealer_id, '>UF_TIME' => $min_date]));
        return $list['ID']>0;
    }

    public function getCountDismissOnNeedPeriod($role_id, $dealer_id, $min_date){
        $list = $this->get(['UF_TYPE' => 27, 'UF_ROLES' => $role_id, 'UF_DEALER' => $dealer_id, '>UF_TIME' => $min_date]);
        return $list;
    }

    public function getCountRecruitOnNeedPeriod($role_id, $dealer_id, $min_date){
        $list = $this->get(['UF_TYPE' => 26, 'UF_ROLES' => $role_id, 'UF_DEALER' => $dealer_id, '>UF_TIME' => $min_date]);
        return count($list);
    }

    public function getCountRecruitPeriod($role_id, $dealer_id, $from='', $to=""){
        $params = ['UF_TYPE' => 26, 'UF_ROLES' => $role_id, 'UF_DEALER' => $dealer_id];
        if($from!="")
            $params['>=UF_TIME'] = $from;
        if($to!="")
            $params['<=UF_TIME'] = $to;
        $list = $this->get($params);
        return count($list);
    }

    public function getRecruitPeriod($role_id, $dealer_id, $from='', $to=""){
        $params = ['!UF_USER' => false, 'UF_TYPE' => 26, 'UF_ROLES' => $role_id, 'UF_DEALER' => $dealer_id];
        if($from!="")
            $params['>=UF_TIME'] = $from;
        if($to!="")
            $params['<=UF_TIME'] = $to;
        return $this->get($params, ['UF_USER', 'UF_DEALER', "UF_TIME"], ["UF_TIME" => 'DESC']);
    }

    public function getDismissPeriod($role_id, $dealer_id, $from='', $to=""){
        $params = ['UF_TYPE' => 27, 'UF_ROLES' => $role_id, 'UF_DEALER' => $dealer_id];
        if($from!="")
            $params['>=UF_TIME'] = $from;
        if($to!="")
            $params['<=UF_TIME'] = $to;
        return $this->get($params, ['UF_USER', 'UF_DEALER', "UF_TIME"], ["UF_TIME" => 'DESC']);
    }

    public function getLastRecruitByUser($user_id)
    {
        return current($this->get(['UF_USER' => $user_id, 'UF_TYPE' => 26], ['*'], ['UF_TIME'=>'DESC']));
    }

    public function delete(mixed $ID)
    {
        $this->dataClass::delete($ID);
    }

    public function setDeleted($ID)
    {
        $this->dataClass::update($ID, ['UF_DELETED' => 1, 'UF_DELETED_AT' => date('d.m.Y H:i:s')]);
    }

    public function unSetDeleted(mixed $ID)
    {
        $this->dataClass::update($ID, ['UF_DELETED' => false]);
    }
}