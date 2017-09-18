<?php
namespace Tables;

abstract class PaymentBase extends BaseTable
{
    public function __construct()
    {
        parent::__construct();
        $this->setPrefix('lz_');
        $this->setModuleInId(96);

    }
}

