<?php
namespace Teaching;
use CIBlockElement;
use Helpers\IBlockHelper;

class Coupons
{
    public static function getIblockID():int{
        return IBlockHelper::getCertIBlock();
    }

    private static function getList($filter, $select = [])
    {
        IBlockHelper::includeIBlockModule();
        $res = CIBlockElement::GetList(array(), array_merge(["IBLOCK_ID" => self::getIblockID()], $filter), false, array(), $select);
        $list = [];
        while ($ob = $res->GetNextElement())
            $list[] = $ob->GetFields();
        return $list;
    }
    private static function updateCouponField($id, $code, $value){
        IBlockHelper::includeIBlockModule();
        CIBlockElement::SetPropertyValuesEx($id, false, array($code => $value));
    }
    public static function getForCourse($course_id):string {
        $list = self::getList(['ACTIVE' => 'Y', 'PROPERTY_ACTIVATION_DATE' => false, 'PROPERTY_WHO_ACTIVATE' => false, 'PROPERTY_COURSE'=>$course_id], ['ID', 'CODE']);
        return $list[0]['CODE']??'';
    }
    public static function processCoupon($course_id, $coupon){
        global $USER;
        $coupon = current(self::getList(['CODE' => $coupon, 'PROPERTY_COURSE'=>$course_id], ['ID']));
        if((int)$coupon['ID']>0) {
            self::updateCouponField($coupon['ID'], 'WHO_ACTIVATE', $USER->GetID());
            self::updateCouponField($coupon['ID'], 'ACTIVATION_DATE', date('d.m.Y H:i:s'));
            return true;
        }else{
            return false;
        }
    }
}