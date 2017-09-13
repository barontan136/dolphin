<?php

namespace Tests;

use Exception;
use Config\Task as TaskConfig;
use Config\GatewayConstants;
use Utils\Token;
use Utils\Logging;
use Utils\DES;

class Protocol {
    protected $logger = null;
    protected $name = null;
    protected $des = null;
    protected $version = null;
    protected $platform = null;

    public function __construct($name, $logger) {
        $this->logger = $logger;
        $this->name = $name;
        if (!array_key_exists($this->name, TaskConfig::$config)) {
            $msg = 'config not found Module:'.$this->name;
            $this->logger->error($msg);
            throw new Exception($msg);
        }
        $this->uri = GatewayConstants::LISTEN_URI;
        //$this->uri = 'http://112.74.134.29:60000';
        //$this->uri = 'https://api.avicks.com';
        $this->des = new DES(
            GatewayConstants::DES_SCRET_KEY,
            GatewayConstants::DES_SCRET_KEY
        );
        $this->setVersion('1.0.0');
        $this->setPlatform('3');
    }

    public function getVersion() {
        return $this->version;
    }

    public function setVersion($version) {
        $this->version = $version;
    }

    public function getPlatform() {
        return $this->platform;
    }

    public function setPlatform($platform) {
        $this->platform = $platform;
    }

    public function isEncrypt($module, $action)
    {
        return false;
        if (array_key_exists($module, GatewayConstants::UN_ENCRYPT_API)
            && in_array($action, GatewayConstants::UN_ENCRYPT_API[$module])) {
            return false;
        }
        return true;
    }

    public function request($action, $param, $need_des = 1) {
        $module = $this->name;
        $result = array('code'=>'0', 'msg'=>'succ');
        $param = is_array($param) ? $param : array();
        $param['source'] = '1';
        $param['device_id'] = '000000000000';
        $sParam = json_encode($param);

        $this->logger->error(sprintf(
            '[%s][%s][url][%s]',
            $module,
            $action,
            $this->uri
        ));

        $this->logger->error(sprintf(
            '[%s][%s][raw-params][%s]',
            $this->name,
            $action,
            Logging::json_pretty($param)
        ));

        if ($this->isEncrypt($module, $action)) {
            $sParam = $this->des->encrypt($sParam);
        }

        $data = array(
            'm' => $this->name,
            'f' => $action,
            't' => Token::getToken(),
            'p' => $this->getPlatform(),
            'v' => $this->getVersion(),
            'r' => $sParam,
        );

        $this->logger->error(sprintf(
            '[%s][%s][full-params][%s]',
            $this->name,
            $action,
            Logging::json_pretty($data)
        ));

        do {
            $ch = curl_init();
            if (false === $ch) {
                $this->logger->error(sprintf(
                    '[%s][create curl handler error]',
                    $action
                ));
                $result['code'] = -1;
                $result['msg'] = sprintf('create curl handler error');
                break;
            }

            curl_setopt_array($ch, array(
                CURLOPT_URL => $this->uri,
                CURLOPT_HEADER => false,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => http_build_query($data),
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 5,
                CURLOPT_RETURNTRANSFER => 1
            ));
            $respRaw  = curl_exec($ch);
            $errno = curl_errno($ch);
            $error = curl_error($ch);
            curl_close($ch);
            if ($errno || $error) {
                $this->logger->error(sprintf(
                    '[%s][recv][%s:%s]',
                    $action,
                    $errno,
                    $error
                )
                );
                $result['code'] = -3;
                $result['msg'] = sprintf(
                    'recv msg fail %s:%s',
                    $errno,
                    $error
                );
                break;
            }

            $this->logger->info(sprintf(
                '[%s][%s][outputRaw][%s]',
                $this->name,
                $action,
                $respRaw
            ));

            $resp = $respRaw;
            if ($this->isEncrypt($module, $action)) {
                $this->logger->info(sprintf(
                    '[%s][%s][raw-output][%s]',
                    $this->name,
                    $action,
                    $respRaw
                ));
                $resp = $this->des->decrypt($respRaw);
            }

            $this->logger->info(sprintf(
                '[%s][%s][output][%s]',
                $this->name,
                $action,
                $resp
            ));
            $result['resp'] = $resp;
        } while(false);
        return $result;
    }

    public function __call($method, array $args) {
        return $this->request($method, $args);
    }
}
