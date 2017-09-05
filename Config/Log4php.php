<?php

namespace Config;

class Log4php {
    public static function get() {
        return array (
            'rootLogger' => array(
                'appenders' => array('info', 'error'),
            ),
            'loggers' => array(
                'Payment' => array(
                    'additivity' => 'false',
                    'appenders' => array('payment_info', 'payment_error'),
                ),
                'Admin' => array(
                    'additivity' => 'false',
                    'appenders' => array('admin_info', 'admin_error'),
                ),
            ),
            'appenders' => array (
                'default' => array(
                    'class' => 'LoggerAppenderDailyFile',
                    'layout' => array (
                        'class' => 'LoggerLayoutPattern',
                        'params' => array (
                            'conversionPattern' => '%d{ISO8601} [%p] DEFAULT %c[%pid]: %m (at %F line %L)%n'
                        )
                    ),
                    'params' => array (
                        'datePattern' => 'Y-m-d-H',
                        'file' => APP_LOG_PATH.'/%s.log',
                        'append' => true
                    ),
                ),
                'stdout' => array(
                    'class' => 'LoggerAppenderEcho',
                    'layout' => array (
                        'class' => 'LoggerLayoutPattern',
                        'params' => array (
                            'conversionPattern' => '%d{ISO8601} [%p] STDOUT %c[%pid]: %m%n'
                        )
                    ),
                ),
                'info' => array(
                    'class' => 'LoggerAppenderDailyFile',
                    'layout' => array (
                        'class' => 'LoggerLayoutPattern',
                        'params' => array (
                            'conversionPattern' => '%d{ISO8601} [%p] '.MODULE_TYPE.' %c[%pid]: %m%n'
                        )
                    ),
                    'params' => array (
                        'datePattern' => 'Y-m-d-H',
                        'file' => APP_LOG_PATH.'/%s.log',
                        'append' => true
                    ),
                ),
                'error' => array ( // 只打印error 以上级别的日志信息
                    'class' => 'LoggerAppenderDailyFile',
                    'layout' => array (
                        'class' => 'LoggerLayoutPattern',
                        'params' => array (
                            'conversionPattern' => '%d{ISO8601} [%p] '.MODULE_TYPE.' %c[%pid]: %m%n%ex'
                        )
                    ),
                    'params' => array (
                        'datePattern' => 'Y-m-d-H',
                        'file' => APP_LOG_PATH.'/error-%s.log',
                        'append' => true
                    ),
                    'filters' => array ( // 过滤器
                        array (
                            'class' => 'LoggerFilterLevelRange',
                            'params' => array (
                                'levelMin' => 'error',
                                'levelMax' => 'fatal'
                            )
                        )
                    )
                ),
                'payment_info' => array(
                    'class' => 'LoggerAppenderDailyFile',
                    'layout' => array (
                        'class' => 'LoggerLayoutPattern',
                        'params' => array (
                            'conversionPattern' => '%d{ISO8601} [%p] PAYMENT %c[%pid]: %m%n'
                        )
                    ),
                    'params' => array (
                        'datePattern' => 'Y-m-d-H',
                        'file' => APP_LOG_PATH.'/payment-%s.log',
                        'append' => true
                    ),
                ),
                'payment_error' => array ( // 只打印error 以上级别的日志信息
                    'class' => 'LoggerAppenderDailyFile',
                    'layout' => array (
                        'class' => 'LoggerLayoutPattern',
                        'params' => array (
                            'conversionPattern' => '%d{ISO8601} [%p] PAYMENT %c[%pid]: %m%n%ex'
                        )
                    ),
                    'params' => array (
                        'datePattern' => 'Y-m-d-H',
                        'file' => APP_LOG_PATH.'/payment-error-%s.log',
                        'append' => true
                    ),
                    'filters' => array ( // 过滤器
                        array (
                            'class' => 'LoggerFilterLevelRange',
                            'params' => array (
                                'levelMin' => 'error',
                                'levelMax' => 'fatal'
                            )
                        )
                    )
                ),
                'admin_info' => array(
                    'class' => 'LoggerAppenderDailyFile',
                    'layout' => array (
                        'class' => 'LoggerLayoutPattern',
                        'params' => array (
                            'conversionPattern' => '%d{ISO8601} [%p] ADMIN %c[%pid]: %m%n'
                        )
                    ),
                    'params' => array (
                        'datePattern' => 'Y-m-d-H',
                        'file' => APP_LOG_PATH.'/admin-%s.log',
                        'append' => true
                    ),
                ),
                'admin_error' => array ( // 只打印error 以上级别的日志信息
                    'class' => 'LoggerAppenderDailyFile',
                    'layout' => array (
                        'class' => 'LoggerLayoutPattern',
                        'params' => array (
                            'conversionPattern' => '%d{ISO8601} [%p] ADMIN %c[%pid]: %m%n%ex'
                        )
                    ),
                    'params' => array (
                        'datePattern' => 'Y-m-d-H',
                        'file' => APP_LOG_PATH.'/admin-error-%s.log',
                        'append' => true
                    ),
                    'filters' => array ( // 过滤器
                        array (
                            'class' => 'LoggerFilterLevelRange',
                            'params' => array (
                                'levelMin' => 'error',
                                'levelMax' => 'fatal'
                            )
                        )
                    )
                ),
            )
        );

    }
}