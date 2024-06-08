<?php

namespace Models;

class Direction
{
    private static array $code_array = [
        "S01" => "ОП",
        "A01" => "ППО",
        "M01" => "Маркетинг"
    ];
    private static array $user_array = [
        15 => "S01",
        16 => "A01",
        53 => 'M01'
    ];

    public static function all(): array
    {
        return self::$user_array;
    }

    public static function getDirectionByCode($code): array
    {
        return [
            'title' => self::$code_array[$code],
        ];

    }

    public static function getByUser($id)
    {
        return self::$user_array[$id];
    }

    public static function getByCourse($course_id): string
    {
        if(Course::isOP($course_id))
            return "S01";
        if(Course::isMarketing($course_id))
            return "M01";
        return "A01";
    }
}