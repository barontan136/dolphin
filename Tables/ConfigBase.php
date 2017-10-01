<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2017/2/9
 * Time: 9:49
 */
namespace Tables;
use Utils\Logging;

abstract class ConfigBase extends BaseTable
{
    protected $logger = null;

    public function __construct()
    {
        parent::__construct();
        $this->setPrefix('lz_');
        $this->setModuleInId(94);
        $this->logger = Logging::getLogger();
    }
}