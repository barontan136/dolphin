<?php

namespace Tests\Handlers;

use PHPUnit\Framework\TestCase;

use Tests\Protocol;
use Utils\Logging;
use Config\Task as TaskConfig;

class Base extends TestCase 
{
    protected $name = null;
    protected $protocol = null;
    protected $logger = null;
    public function __construct(...$args) {
        parent::__construct(...$args);
        $this->logger = Logging::getLogger('Test');
        $this->protocol = new Protocol($this->name, $this->logger);
    }

    public function getAction($method) {
        return lcfirst(substr($method, 4));
    }

    public function parseResult($ret, $is_valid_code = 1) {
        $this->assertEquals(0, $ret['code'], '请求失败:'.$ret['msg']);
        if (0 == $ret['code']) {
            $this->assertArrayHasKey('resp', $ret, '返回内容为空');
            if (array_key_exists('resp', $ret)) {
                $resp = $ret['resp'];
                $jResp = json_decode($resp, 1);
                $this->assertTrue(is_array($jResp), '错误的返回格式:'.$resp);
                $this->assertArrayHasKey('return_code', $jResp, '错误的返回格式2:'.$resp);
                if (array_key_exists('return_code', $jResp)) {
                    if ($is_valid_code) {
                        $this->assertEquals(
                            '000000',
                            $jResp['return_code'],
                            sprintf(
                                '错误的返回状态码 %s:%s',
                                $jResp['return_code'],
                                $jResp['return_message']
                            )
                        );
                    }
                    return $jResp;
                }
            }
        }
        return false;
    }
}
