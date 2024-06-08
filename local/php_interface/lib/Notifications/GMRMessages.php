<?php

namespace Settings;
class GMRMessages
{
    private string $HlDataClass;

    public function __construct()
    {
        $this->HlDataClass = \Helpers\HLBlockHelper::initialize('gmr_messages');
    }

    public function get()
    {

    }
}