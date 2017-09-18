<?php
namespace Tables;

abstract class RecordBase extends BaseTable
{
    public function __construct()
    {
        parent::__construct();
        $this->setPrefix('lz_');
        $this->setModuleInId(97);

    }
}

