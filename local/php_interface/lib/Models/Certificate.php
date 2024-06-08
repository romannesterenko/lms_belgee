<?php

namespace Models;

use CIBlockElement;
use Helpers\IBlockHelper;
use Teaching\Coupons;

class Certificate
{

    public static function updateOrCreate(array $array)
    {
        $exists = self::getByCodeAndCourse($array['CODE'], $array['PROPERTY_VALUES']['COURSE']);
        if($exists['ID']>0){
            if($array['PROPERTY_VALUES']['WHO_ACTIVATE']>0)
                self::activate($exists['ID']);
            else
                self::reset_activate($exists['ID']);
        }else{
            self::create($array);
        }
    }

    public static function activate($id, $user_id = 0)
    {
        global $USER;
        self::updateCouponField($id, 'WHO_ACTIVATE', $USER->GetID());
        if( $user_id > 0 )
            self::updateCouponField($id, 'TEACHABLE', $user_id);
        self::updateCouponField($id, 'ACTIVATION_DATE', date('d.m.Y H:i:s'));
    }

    public static function reset_activate($id)
    {
        self::updateCouponField($id, 'WHO_ACTIVATE', false);
        self::updateCouponField($id, 'ACTIVATION_DATE', false);
        self::updateCouponField($id, 'TEACHABLE', false);
    }

    private static function updateCouponField($id, $code, $value)
    {
        IBlockHelper::includeIBlockModule();
        CIBlockElement::SetPropertyValuesEx($id, false, array($code => $value));
    }

    public static function getByCodeAndCourse($code, $course_id)
    {
        return current(self::getList(['CODE' => $code, 'PROPERTY_COURSE' => $course_id], ['ID']));
    }

    public static function getByCodeAndCourseNotActivated($code, $course_id)
    {
        return current(
            self::getList(
                [
                    'CODE' => $code,
                    'PROPERTY_COURSE' => $course_id,
                    'PROPERTY_ACTIVATION_DATE' => false,
                    'PROPERTY_WHO_ACTIVATE' => false,
                    'PROPERTY_TEACHABLE' => false,
                ],
                [
                    'ID'
                ]
            )
        );
    }

    public static function create($fields)
    {
        return self::add($fields);
    }

    public static function getList($filter, $select = [])
    {
        IBlockHelper::includeIBlockModule();
        $arFilter = array_merge(["IBLOCK_ID" => IBlockHelper::getCertIBlock(), 'ACTIVE' => 'Y'], $filter);
        $res = CIBlockElement::GetList(array(), $arFilter, false, array(), $select);
        $list = [];
        while ($ob = $res->GetNextElement()) {
            $list[] = $ob->GetFields();
        }
        return $list;
    }

    private static function add($fields)
    {
        global $USER;
        IBlockHelper::includeIBlockModule();
        $el = new CIBlockElement;
        $coupon_array = [
            "MODIFIED_BY"    => $USER->GetID(),
            "IBLOCK_ID"      => Coupons::getIblockID(),
            "ACTIVE"         => "Y"
        ];
        $arLoadProductArray = array_merge($coupon_array, $fields);
        $PRODUCT_ID = $el->Add($arLoadProductArray);
        return $PRODUCT_ID>0;
    }

    public static function getActivatedByUserAndCourse(mixed $UF_USER_ID, mixed $UF_COURSE_ID)
    {
        return current(
            self::getList(
                [
                    '!CODE' => false,
                    'PROPERTY_COURSE' => $UF_COURSE_ID,
                    '!PROPERTY_ACTIVATION_DATE' => false,
                    'PROPERTY_TEACHABLE' => $UF_USER_ID,
                ],
                [
                    'ID'
                ]
            )
        );
    }

    public static function resetByUserAndCourse($user_id, $course_id)
    {
        $activated = self::getActivatedByUserAndCourse($user_id, $course_id);
        if(check_full_array($activated)&&$activated['ID']>0)
            self::reset_activate($activated['ID']);
    }

    public static function getFreeByCourse($course)
    {
        return self::getList(
            [
                '!CODE' => false,
                'PROPERTY_COURSE' => $course,
                'PROPERTY_ACTIVATION_DATE' => false,
                'PROPERTY_TEACHABLE' => false,
            ],
            [
                'ID', 'CODE'
            ]
        );
    }

    public static function setUser($id, $to_user_id)
    {
        self::updateCouponField($id, 'TEACHABLE', $to_user_id);
    }

    public static function delete(mixed $ID)
    {
        CIBlockElement::Delete($ID);
    }
}