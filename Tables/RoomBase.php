<?php
namespace Tables;

abstract class RoomBase extends BaseTable
{
    public function __construct()
    {
        parent::__construct();
        $this->setPrefix('lz_');
        $this->setModuleInId(98);

    }
}

