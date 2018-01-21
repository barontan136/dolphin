<?php
namespace Config;

class GlobalConfig
{


    /* 绑卡状态 0: 未绑定 1:已绑定 2:已解绑 3:逻辑删除 */
    const NOBUNDING = 0;
    const BUNDING = 1;
    const UNBUNDING = 2;

    /* 用户类型 0未知 1普通用户 2 主播 3其他用户 */
    const USR_UNKNOW = 0;
    const USER_NORMAL = 1;
    const USER_MODER = 2;
    const USER_OTHER = 3;

    /* 操作类型 */
    const OT_RECHARGE = 1;
    const OT_WITHDRAW = 2;
    const OT_SEND_GIFT = 3; //送礼物
    const OT_RECEIVE_GIFT = 4; //收礼物


    /* 资产增减 */
    const CAPITAL_INC = 1;
    const CAPITAL_DEC = 2;

    /* 禁言状态*/
    const GAG_ING = 1; //禁言中
    const GAG_AUTO_CANCEL = 2; //自动解禁
    const GAG_ADMIN_CANCEL = 3; // 管理员解禁

}
