<?php

namespace Polls;

use Helpers\UserHelper;

class ProcessFBPOll
{
    public static function init()
    {
        return \Helpers\HLBlockHelper::initialize('fb_process_poll');
    }
    public static function has($completion_id):bool
    {
        return true;
    }
    public static function getByCompletion($completion_id)
    {
        return (self::init())::getList(array(
            "select" => ['*'],
            "order" => ['UF_QUESTION_NUMBER' => 'DESC'],
            "filter" => ['UF_COMPLETION_ID' => $completion_id],
        ))->fetchAll();
    }

    public static function isRunning(mixed $completion_id)
    {
        $user_id = UserHelper::prepareUserId(0);
        $item =  current((self::init())::getList(array(
            "select" => ['*'],
            "order" => ['UF_QUESTION_NUMBER' => 'DESC'],
            "filter" => ['UF_USER_ID' => $user_id, 'UF_COMPLETION_ID' => $completion_id],
            'limit' => 1
        ))->fetchAll());
        return check_full_array($item)&&$item['ID']>0;
    }

    public static function create(mixed $ID)
    {
        (self::init())::add(
            [
                'UF_QUESTION_NUMBER' => 1,
                'UF_COMPLETION_ID' => $ID,
                'UF_USER_ID' => UserHelper::prepareUserId(0),
                'UF_CREATED_AT' => date('d.m.Y H:i:s'),
                'UF_POINTS' => 10,
            ]
        );
    }

    public static function getLastQuestion($completion_id, $comment = false)
    {
        $user_id = UserHelper::prepareUserId(0);
        $filter = ['UF_USER_ID' => $user_id, 'UF_COMPLETION_ID' => $completion_id];
        if(!$comment){
            $filter['UF_IS_COMENT'] = false;
        }
        $item =  current((self::init())::getList(array(
            "select" => ['*'],
            "order" => ['UF_QUESTION_NUMBER' => 'DESC'],
            "filter" => $filter,
            'limit' => 1
        ))->fetchAll());
        return $item??[];
    }

    public static function getLastQuestionNew($completion_id, $comment = false , $iSid = 112)
    {
        if ($comment) {
            $user_id = UserHelper::prepareUserId(0);
            $filter = ['UF_USER_ID' => $user_id, 'UF_COMPLETION_ID' => $completion_id];
            if (!$comment) {
                $filter['UF_IS_COMENT'] = false;
            }
            $item = current((self::init())::getList(array(
                "select" => ['*'],
                "order" => ['UF_QUESTION_NUMBER' => 'DESC'],
                "filter" => $filter,
                'limit' => 1
            ))->fetchAll());
            return $item ?? [];
        }else{
            \CModule::includeModule('iblock');
            $iblock = \Bitrix\Iblock\Iblock::wakeUp(12)->getEntityDataClass();
            $item = $iblock::getList([
                'filter' => ['ACTIVE' => 'Y' , 'IBLOCK_SECTION_ID' => $iSid],
                'select' => ['*'] ,
                'order' => ['SORT' => 'DESC'],
                'limit' => 1
            ])->fetch();
            return $item ?? [];
        }
    }

    public static function getLastQuestionByCompletion($completion_id, $comment = false)
    {
        $user_id = UserHelper::prepareUserId(0);
        $filter = ['UF_COMPLETION_ID' => $completion_id];
        if(!$comment){
            $filter['UF_IS_COMENT'] = false;
        }
        $item =  current((self::init())::getList(array(
            "select" => ['*'],
            "order" => ['UF_QUESTION_NUMBER' => 'DESC'],
            "filter" => $filter,
            'limit' => 1
        ))->fetchAll());
        return $item??[];
    }

    public static function addFromRequest(array $fields)
    {
        (self::init())::add($fields);
    }
}