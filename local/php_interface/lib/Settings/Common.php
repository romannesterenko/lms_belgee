<?php
namespace Settings;
use \Bitrix\Main\Config\Option;
class Common
{
    public static function get($code){
        return Option::get( "common.settings", $code)??'';
    }
    public static function set($code, $value):bool{
        Option::set( "common.settings", $code, $value);
        return self::get($code)==$value;
    }
    public static function getTestTGChannel(){
        return Option::get( "common.settings", 'channel_for_test');
    }
    public static function isTestTGMode(){
        return Option::get( "common.settings", 'use_test_mode')=='Y';
    }

    public static function getEnrollLife():int
    {
        return (int)self::get('enroll_life');
    }

    public static function getRemindTermin():int
    {
        return (int)self::get('how_long_to_remind');
    }

    public static function getRetestRemindTermin():int
    {
        return (int)self::get('how_long_to_retest_remind')??10;
    }

    public static function getHowLongToRemindToday():int
    {
        return (int)self::get('how_long_to_remind_today');
    }

    public static function getTemplateTopic()
    {
        return self::get('remind_message_email_topic');
    }

    public static function getTemplateMessage()
    {
        return self::get('remind_message_body');
    }

    public static function getTodayTemplateTopic()
    {
        return self::get('remind_message_email_topic_today');
    }

    public static function getTodayTemplateMessage()
    {
        return self::get('remind_message_body_today');
    }

    public static function getNHoursTemplateTopic()
    {
        return self::get('remind_message_email_topic_n_hours');
    }

    public static function getNHoursTemplateMessage()
    {
        return self::get('remind_message_body_n_hours');
    }

    public static function getApproveEventTextMessage()
    {
        return self::get('approve_event_text');
    }

    public static function getDeclineEventTextMessage()
    {
        return self::get('decline_event_text');
    }

    public static function getDeleteExpireEventTextMessage()
    {
        return self::get('delete_expire_event_text');
    }

    public static function getTimeToStartTodaySender()
    {
        return self::get('sender_today_time');
    }

    public static function getTimeToStartNDaysSender()
    {
        return self::get('sender_today_n_days');
    }

    public static function isTestTGChannel()
    {
        return true;
    }

    public static function isTestingMode()
    {
        return $_REQUEST['testing']=="Y"&&$_REQUEST['show_alpha']=="Y";
    }

    public static function isTestingBalanceMode()
    {
        return self::get('balance_testing_mode')=='Y';
    }

    public static function getTestingBalanceDealer()
    {
        return self::get('balance_testing_dealers');
    }

    public static function isAllowToEnrollMinusBalance()
    {
        return self::get('is_allow_to_enroll_minus_balance')=='Y';
    }
}