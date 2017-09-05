<?php
namespace Config;

class GlobalConfig
{
    const NEW_GOLD_STATUS_ENABLE ='1';
    const NEW_GOLD_STATUS_DISABLE ='0';
    const API_VERSION ='v0200';
    const TG_CAT_TYPE = 99; //托管金条


    /* 绑卡状态 0: 未绑定 1:已绑定 2:已解绑 3:逻辑删除 */
    const NOBUNDING = 0;
    const BUNDING = 1;
    const UNBUNDING = 2;
}
