<?php

namespace Helpers;


class DateHelper
{
    public static function getHumanDate($date, $format = 'd F Y')
    {
        return strtolower(FormatDate($format, MakeTimeStamp($date)));
    }

    public static function getIntervalArray(string $begin, string $end, string $format = 'j.m.Y')
    {
        $return_array = [];
        $begin_timestamp = strtotime($begin);
        $end_timestamp = strtotime($end.' 23:59:59');
        for ($i = $begin_timestamp; $i <= $end_timestamp; $i += 86400) {
            $return_array[] = date($format, $i);
        }
        return $return_array;
    }

    public static function getMonthArray(int $months_count)
    {
        $array = [];
        $timestamp_n = strtotime('01.' . date('m.Y'));
        for ($i = 1; $i <= $months_count; $i++) {
            $timestamp = strtotime("+".$i." month", strtotime(date('d.m.Y', $timestamp_n)));
            $array[date('m.Y', $timestamp)] = GetMessage(strtoupper(date('F', $timestamp))) . ' ' . date('Y', $timestamp);
        }
        return $array;
    }

    public static function getCurDateTime()
    {
        return date("d.m.Y H:i:s");
    }

    public static function getFormatted($date, $format)
    {
        return date($format, strtotime($date));
    }

    public static function printDates($BEGIN_DATE='', $END_DATE='')
    {
        $dates = [];
        $dates[] = date('d.m.Y', strtotime($BEGIN_DATE));
        $dates[] = date('d.m.Y', strtotime($END_DATE));
        $dates = array_unique($dates);
        return count($dates)>1? self::getHumanDate($dates[0])." - ".self::getHumanDate($dates[1]):self::getHumanDate($dates[0]);
    }

    public static function printDatesFormatting($BEGIN_DATE='', $END_DATE='', $format='d F Y')
    {
        $dates = [];
        if($BEGIN_DATE=='')
            return '';
        if($END_DATE=='')
            return '';
        $dates[] = date('d.m.Y', strtotime($BEGIN_DATE));
        $dates[] = date('d.m.Y', strtotime($END_DATE));
        $dates = array_unique($dates);
        if(count($dates)==1) {
            self::getHumanDate($dates[0], $format);
        } else {
            if(self::getHumanDate($dates[0], 'm.Y')==self::getHumanDate($dates[1], 'm.Y')){
                return self::getHumanDate($dates[0], 'd')." - ".self::getHumanDate($dates[1], $format);
            } else {
                return self::getHumanDate($dates[0], $format)." - ".self::getHumanDate($dates[1], $format);
            }
        }
    }
}