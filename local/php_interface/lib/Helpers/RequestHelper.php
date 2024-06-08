<?php

namespace Helpers;

class RequestHelper {
    public static function isSetAnyDirection():bool
    {
        return $_REQUEST['op_servis_op']=='on'||$_REQUEST['op_servis_servis']=='on';
    }

    public static function isSetOP():bool
    {
        return self::isSetAnyDirection()&&$_REQUEST['op_servis_op']=='on'&&$_REQUEST['op_servis_servis']!='on';
    }

    public static function isSetPPO():bool
    {
        return self::isSetAnyDirection()&&$_REQUEST['op_servis_op']!='on'&&$_REQUEST['op_servis_servis']=='on';
    }
}