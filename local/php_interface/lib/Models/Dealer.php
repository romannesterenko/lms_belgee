<?php

namespace Models;

use BaconQrCode\Exception\InvalidArgumentException;
use CIBlockElement;
use Helpers\HLBlockHelper as HLBlock;
use Helpers\IBlockHelper;
use Helpers\StringHelpers;

class Dealer
{

    public static function find($dealer_id, $select = [])
    {
        $list = current(\Helpers\DealerHelper::getList(['ID' => $dealer_id], $select));
        return check_full_array($list)?$list:[];
    }

    public static function getBalance($dealer_id) {

        $result_array = [];
        $invoice_filter = ['UF_DEALER_ID' => $dealer_id];

        $directions = User::getDirectionsByDealerAdmin();

        if(check_full_array($directions))
            $invoice_filter["UF_ID_TRENING"] = $directions;

        $list = Invoice::get($invoice_filter);

        foreach ($list as $item) {
            if($item["UF_STATUS"]==51)
                $result_array[$item['UF_ID_TRENING']]['incoming'] += $item['UF_TOTAL'];
        }

        foreach ($directions as $direction){
            if(!check_full_array($result_array[$direction])){
                $result_array[$direction]['incoming'] = 0;
            }
        }

        $reserves = Reserve::get(['UF_DEALER_ID' => $dealer_id, "UF_IS_CLOSED" => false, "UF_DIRECTION" => User::getDirectionsByDealerAdmin(), 'UF_ID_DEBIT' => false]);
        foreach ($reserves as $reserve) {
            if($reserve["UF_IS_COMPLETE"]) {
                if($reserve['UF_PRICE']!=$reserve['UF_CLOSED_SUM'])
                    $reserve['UF_PRICE'] = $reserve['UF_PRICE']-$reserve['UF_CLOSED_SUM'];
                $result_array[$reserve['UF_DIRECTION']]['expenses'] += $reserve['UF_PRICE'];
            } else {
                $result_array[$reserve['UF_DIRECTION']]['reserves'] += $reserve['UF_PRICE'];
            }
        }
        foreach ($result_array as &$one_category) {
            $one_category['incoming'] -= (int)$one_category['expenses'];
            $one_category['free'] = $one_category['incoming'] - (int)$one_category['reserves'];
        }
        return $result_array;
    }
    public static function getAllBalance($dealer_id): array
    {
        $directions = Direction::all();

        $invoice_filter["UF_ID_TRENING"] = array_values($directions);
        $result_array = [];
        $invoice_filter = ['UF_DEALER_ID' => $dealer_id];
        $list = Invoice::get($invoice_filter);
        foreach ($list as $item) {
            if($item["UF_STATUS"]==51)
                $result_array[$item['UF_ID_TRENING']]['incoming'] += $item['UF_TOTAL'];
        }
        foreach ($directions as $direction){
            if(!check_full_array($result_array[$direction])){
                $result_array[$direction]['incoming'] = 0;
            }
        }
        $reserves = Reserve::get(['UF_DEALER_ID' => $dealer_id, "UF_IS_CLOSED" => false, "UF_DIRECTION" => array_values($directions), 'UF_ID_DEBIT' => false]);
        foreach ($reserves as $reserve) {
            if($reserve["UF_IS_COMPLETE"]) {
                if($reserve['UF_PRICE']!=$reserve['UF_CLOSED_SUM'])
                    $reserve['UF_PRICE'] = $reserve['UF_PRICE']-$reserve['UF_CLOSED_SUM'];
                $result_array[$reserve['UF_DIRECTION']]['expenses'] += $reserve['UF_PRICE'];
            } else {
                $result_array[$reserve['UF_DIRECTION']]['reserves'] += $reserve['UF_PRICE'];
            }
        }
        foreach ($result_array as &$one_category) {
            $one_category['incoming'] -= (int)$one_category['expenses'];
            $one_category['free'] = $one_category['incoming'] - (int)$one_category['reserves'];
        }
        return $result_array;
    }

    public static function getRegional($dealer_id): string
    {
        $dealer = self::find($dealer_id, ['ID', 'NAME', 'PROPERTY_REGIONAL']);
        if($dealer['PROPERTY_REGIONAL_VALUE']>0){
            return \Models\User::getFullName($dealer['PROPERTY_REGIONAL_VALUE']);
        }
        return '';
    }

    public static function getByCity($r_city, $select = [])
    {
        return \Helpers\DealerHelper::getList(['PROPERTY_CITY' => $r_city], $select);
    }

    public static function getIdByCode($code)
    {
        if(trim($code)=="")
            return 0;
        $dealer = current(\Helpers\DealerHelper::getList(['CODE' => $code], ['ID', 'NAME']));
        return (int)$dealer['ID']>0?(int)$dealer['ID']:0;
    }

    public static function getCodeById($id)
    {
        if(!$id>0)
            return 0;
        $dealer = current(\Helpers\DealerHelper::getList(['ID' => $id], ['ID', 'NAME', 'CODE']));
        return (string)$dealer['CODE'];
    }

    public static function getIdByName($name)
    {
        $dealer = current(\Helpers\DealerHelper::getList(['NAME' => $name], ['ID', 'NAME']));
        return $dealer['ID'];
    }

    public static function getRegionalOP($dealer_id)
    {
        $dealer = self::find($dealer_id, ['ID', 'NAME', 'PROPERTY_REGIONAL']);
        if($dealer['PROPERTY_REGIONAL_VALUE']>0){
            return \Models\User::getFullName($dealer['PROPERTY_REGIONAL_VALUE']);
        }
        return '';
    }

    public static function getRegionalPPO($dealer_id)
    {
        $dealer = self::find($dealer_id, ['ID', 'NAME', 'PROPERTY_REGIONAL_PPO']);
        if((int)$dealer['PROPERTY_REGIONAL_PPO_VALUE']>0){
            return \Models\User::getFullName((int)$dealer['PROPERTY_REGIONAL_PPO_VALUE']);
        }
        return '';
    }

    public static function getRegionalMarketing($dealer_id)
    {
        $dealer = self::find($dealer_id, ['ID', 'NAME', 'PROPERTY_REGIONAL_MARKETING']);
        if((int)$dealer['PROPERTY_REGIONAL_MARKETING_VALUE']>0){
            return \Models\User::getFullName((int)$dealer['PROPERTY_REGIONAL_MARKETING_VALUE']);
        }
        return '';
    }

    public static function getAll($select = ['ID', 'NAME', 'CODE', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_MARKETING'])
    {
        return \Helpers\DealerHelper::getList(['ACTIVE' => 'Y'], $select);
    }

    public static function getAllWithoutIds($ids = [], $select = ['ID', 'NAME', 'CODE', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_MARKETING'])
    {
        return \Helpers\DealerHelper::getList(['!ID' => $ids, 'ACTIVE' => 'Y'], $select);
    }

    public static function getAllWithoutActiveFilter($select = ['ID', 'NAME', 'CODE', 'PROPERTY_REGIONAL_PPO', 'PROPERTY_REGIONAL', 'PROPERTY_REGIONAL_MARKETING'])
    {
        return \Helpers\DealerHelper::getList([], $select);
    }

    public static function getByRegionalPPO($regional_ppo, $without_dealers = [])
    {
        $filter = ['PROPERTY_REGIONAL_PPO' => $regional_ppo];
        if(count($without_dealers) > 0)
            $filter['!ID'] = $without_dealers;
        return \Helpers\DealerHelper::getList($filter, ['ID', 'NAME', 'CODE']);
    }

    public static function getByRegionalOP($regional_op, $select = ['ID', 'NAME', 'CODE'], $without_dealers = [])
    {
        $filter = ['ACTIVE' => 'Y', 'PROPERTY_REGIONAL' => $regional_op];
        if(count($without_dealers) > 0)
            $filter['!ID'] = $without_dealers;
        return \Helpers\DealerHelper::getList($filter, $select);
    }

    public static function getByRegionalMarketing($regional_m, $select = ['ID', 'NAME', 'CODE'], $without_dealers = [])
    {
        $filter = ['ACTIVE' => 'Y', 'PROPERTY_REGIONAL_MARKETING' => $regional_m];
        if(count($without_dealers) > 0)
            $filter['!ID'] = $without_dealers;
        return \Helpers\DealerHelper::getList($filter, $select);
    }

    public static function getList($filter, $select = ['ID', 'NAME', 'CODE'])
    {
        return \Helpers\DealerHelper::getList($filter, $select);
    }

    public static function getByEmployee($user_id = 0)
    {
        $user_id = \Helpers\UserHelper::prepareUserId($user_id);
        return \Helpers\UserHelper::getDealerId($user_id);
    }

    public static function getNameByUser($employee_id)
    {
        $user = \Models\User::find($employee_id, ['ID', 'UF_DEALER']);
        if((int)$user['UF_DEALER']>0){
            $dealer = self::find((int)$user['UF_DEALER'], ['ID', 'NAME']);
            return $dealer['NAME'];
        }
        return '';
    }

    public static function createFromNameAndCode($name, $code)
    {
        IBlockHelper::includeIBlockModule();
        $el = new \CIBlockElement;
        $fields = [
            'IBLOCK_ID' => DEALER_IBLOCK,
            'NAME' => $name,
            'ACTIVE' => 'N',
            'CODE' => $code,
        ];
        return $el->Add($fields);
    }

    public static function setRegional($dealer, $regional)
    {
        IBlockHelper::includeIBlockModule();
        \CIBlockElement::SetPropertyValuesEx($dealer, DEALER_IBLOCK, ['REGIONAL' => $regional]);
    }

    public static function getCountriesList()
    {
        IBlockHelper::includeIBlockModule();
        $property_enums = \CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID"=>DEALER_IBLOCK, "CODE"=>"COUNTRY"));
        $return_array = [];
        while($enum_fields = $property_enums->GetNext()) {
            $return_array[$enum_fields["ID"]] = $enum_fields["VALUE"];
        }
        return $return_array;
    }

    public static function setCity($dealer, $city)
    {
        IBlockHelper::includeIBlockModule();
        \CIBlockElement::SetPropertyValuesEx($dealer, DEALER_IBLOCK, ['CITY_ID' => $city]);
    }

    public static function setPPORegional($dealer, $regional)
    {
        IBlockHelper::includeIBlockModule();
        \CIBlockElement::SetPropertyValuesEx($dealer, DEALER_IBLOCK, ['REGIONAL_PPO' => $regional]);
    }

    public static function setMarketingRegional($dealer, $regional)
    {
        IBlockHelper::includeIBlockModule();
        \CIBlockElement::SetPropertyValuesEx($dealer, DEALER_IBLOCK, ['REGIONAL_MARKETING' => $regional]);
    }

    public static function findOrCreate($dealer_name, $dealer_code)
    {
        IBlockHelper::includeIBlockModule();
        $dealer = (int)self::getIdByCode($dealer_code);
        if($dealer>0)
            return $dealer;
        $el = new \CIBlockElement;
        $fields = [
            'IBLOCK_ID' => DEALER_IBLOCK,
            'NAME' => $dealer_name,
            'ACTIVE' => 'N',
            'CODE' => $dealer_code,
        ];
        return $el->Add($fields);
    }

    public static function isHidePrice($dealer_id):bool
    {
        $dealer = current(\Helpers\DealerHelper::getList(['ID' => $dealer_id], ['ID', 'PROPERTY_HIDE_PRICE']));
        return $dealer['PROPERTY_HIDE_PRICE_ENUM_ID'] == 126;
    }

    public static function deactivate($dealer_id)
    {
        IBlockHelper::includeIBlockModule();

        $el = new \CIBlockElement;
        return $el->Update($dealer_id, ['ACTIVE' => 'N']);
    }

    public static function getActiveIdByCode($code)
    {
        if(trim($code)=="")
            return 0;
        $dealer = current(\Helpers\DealerHelper::getList(['CODE' => $code, 'ACTIVE' => 'Y'], ['ID', 'NAME']));
        return (int)$dealer['ID']>0?(int)$dealer['ID']:0;
    }

    public static function getIblockId()
    {
        return DEALER_IBLOCK;
    }

    public static function getCurrent($select = [])
    {
        return self::find(self::getByEmployee(), $select);
    }

    public static function getAppsCount($app_id, $dealer_id = 0)
    {
        if($dealer_id == 0){
            $dealer_id = Dealer::getByEmployee();
        }
        $arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM","PROPERTY_*");
        $arFilter = Array("ID" => $dealer_id, "IBLOCK_ID"=>Dealer::getIblockId(), "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);
        if($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $arProps = $ob->GetProperties();
            $arFields['PROPERTIES'] = $arProps;
            if(check_full_array($arFields['PROPERTIES']['APP_COUNT']['VALUE'])) {
                foreach ($arFields['PROPERTIES']['APP_COUNT']['VALUE'] as $prop){
                    $prop = StringHelpers::unserialize($prop);
                    if($prop['APP'] == $app_id)
                        return (int)$prop['COUNT'];
                }
            }
        }
        return 0;
    }
}