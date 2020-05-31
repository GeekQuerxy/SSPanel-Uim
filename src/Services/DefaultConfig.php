<?php

namespace App\Services;

use App\Models\GConfig;

class DefaultConfig
{
    /**
     * 创建配置，成功返回 true
     *
     * @param string $key 键名
     */
    public static function create(string $key): bool
    {
        $value = self::default_value($key);
        if ($value != null) {
            $new                 = new GConfig();
            $new->key            = $value['key'];
            $new->type           = $value['type'];
            $new->value          = $value['value'];
            $new->name           = $value['name'];
            $new->comment        = $value['comment'];
            $new->operator_id    = $value['operator_id'];
            $new->operator_name  = $value['operator_name'];
            $new->oldvalue       = '';
            $new->operator_email = '';
            $new->last_update    = time();
            if ($new->save()) {
                return true;
            }
        }
        return false;
    }

    /**
     * 创建并返回配置，如果该键名存在默认配置中
     *
     * @param string $key
     */
    public static function firstOrCreate(string $key): ?GConfig
    {
        return (self::create($key)
            ? GConfig::where('key', '=', $key)->first()
            : null);
    }

    /**
     * 检查新增的配置并创建
     *
     * @return string
     */
    public static function detectConfigs(): string
    {
        $return = '开始检查新增的配置项...' . PHP_EOL;
        $configs = self::configs();
        foreach ($configs as $key => $value) {
            if (GConfig::where('key', '=', $key)->first() == null) {
                if (self::create($key)) {
                    $return .= '新增的配置项：' . $key . '：' . $value['name'] . PHP_EOL;
                } else {
                    $return .= $key . '：配置项创建失败，请检查错误' . PHP_EOL;
                }
            }
        }
        $return .= '以上是新增的配置项...' . PHP_EOL;

        return $return;
    }

    /**
     * 默认配置，新增配置请添加到此处
     *
     * @param string $key 键名
     */
    public static function configs(string $key = null): ?array
    {
        $configs = [
            // Telegram 部分
            'Telegram.bool.show_group_link' => [
                'type'          => 'bool',
                'value'         => 0,
                'name'          => '在 Bot 菜单中显示加入用户群',
                'comment'       => '',
            ],
            'Telegram.string.group_link' => [
                'type'          => 'string',
                'value'         => '',
                'name'          => '用户群的链接',
                'comment'       => '',
            ],
            'Telegram.bool.group_bound_user' => [
                'type'          => 'bool',
                'value'         => 0,
                'name'          => '是否仅允许已绑定 Telegram 账户的用户加入群组',
                'comment'       => '是否仅允许已绑定 Telegram 账户的用户加入 telegram_chatid 设定的群组',
            ],
            'Telegram.bool.unbind_kick_member' => [
                'type'          => 'bool',
                'value'         => 0,
                'name'          => '解绑 Telegram 账户后自动踢出群组',
                'comment'       => '用户解绑 Telegram 账户后自动踢出群组，不含管理员',
            ],

            'Telegram.bool.Diary' => [
                'type'          => 'bool',
                'value'         => 1,
                'name'          => '开启 Telegram 群组推送系统今天的运行状况',
                'comment'       => '',
            ],
            'Telegram.string.Diary' => [
                'type'          => 'string',
                'value'         => ('各位老爷少奶奶，我来为大家报告一下系统今天的运行状况哈~' . PHP_EOL . '今日签到人数：%getTodayCheckinUser%' . PHP_EOL . '今日使用总流量：%lastday_total%' . PHP_EOL . '晚安~'),
                'name'          => '自定义向 Telegram 群组推送系统今天的运行状况的信息',
                'comment'       => '可用变量：' . PHP_EOL . '[今日签到人数] %getTodayCheckinUser%' . PHP_EOL . '[今日使用总流量] %lastday_total%',
            ],

            'Telegram.bool.DailyJob' => [
                'type'          => 'bool',
                'value'         => 1,
                'name'          => '开启 Telegram 群组推送数据库清理的通知',
                'comment'       => '',
            ],
            'Telegram.string.DailyJob' => [
                'type'          => 'string',
                'value'         => '姐姐姐姐，数据库被清理了，感觉身体被掏空了呢~',
                'name'          => '自定义向 Telegram 群组推送数据库清理通知的信息',
                'comment'       => '',
            ],

            'Telegram.bool.NodeOffline' => [
                'type'          => 'bool',
                'value'         => 1,
                'name'          => '开启 Telegram 群组推送节点掉线的通知',
                'comment'       => '',
            ],
            'Telegram.string.NodeOffline' => [
                'type'          => 'string',
                'value'         => '喵喵喵~ %node_name% 节点掉线了喵~',
                'name'          => '自定义向 Telegram 群组推送节点掉线通知的信息',
                'comment'       => '可用变量：' . PHP_EOL . '[节点名称] %node_name%',
            ],

            'Telegram.bool.NodeOnline' => [
                'type'          => 'bool',
                'value'         => 1,
                'name'          => '开启 Telegram 群组推送节点上线的通知',
                'comment'       => '',
            ],
            'Telegram.string.NodeOnline' => [
                'type'          => 'string',
                'value'         => '喵喵喵~ %node_name% 节点恢复了喵~',
                'name'          => '自定义向 Telegram 群组推送节点上线通知的信息',
                'comment'       => '可用变量：' . PHP_EOL . '[节点名称] %node_name%',
            ],

            'Telegram.bool.NodeGFW' => [
                'type'          => 'bool',
                'value'         => 1,
                'name'          => '开启 Telegram 群组推送节点被墙的通知',
                'comment'       => '',
            ],
            'Telegram.string.NodeGFW' => [
                'type'          => 'string',
                'value'         => '喵喵喵~ %node_name% 节点被墙了喵~',
                'name'          => '自定义向 Telegram 群组推送节点被墙通知的信息',
                'comment'       => '可用变量：' . PHP_EOL . '[节点名称] %node_name%',
            ],

            'Telegram.bool.NodeGFW_recover' => [
                'type'          => 'bool',
                'value'         => 1,
                'name'          => '开启 Telegram 群组推送节点被墙恢复的通知',
                'comment'       => '',
            ],
            'Telegram.string.NodeGFW_recover' => [
                'type'          => 'string',
                'value'         => '喵喵喵~ %node_name% 节点恢复了喵~',
                'name'          => '自定义向 Telegram 群组推送节点被墙恢复通知的信息',
                'comment'       => '可用变量：' . PHP_EOL . '[节点名称] %node_name%',
            ],

            'Telegram.bool.AddNode' => [
                'type'          => 'bool',
                'value'         => 1,
                'name'          => '开启 Telegram 群组推送节点新增的通知',
                'comment'       => '',
            ],
            'Telegram.string.AddNode' => [
                'type'          => 'string',
                'value'         => '新节点添加~ %node_name%',
                'name'          => '自定义向 Telegram 群组推送节点新增通知的信息',
                'comment'       => '可用变量：' . PHP_EOL . '[节点名称] %node_name%',
            ],

            'Telegram.bool.UpdateNode' => [
                'type'          => 'bool',
                'value'         => 1,
                'name'          => '开启 Telegram 群组推送节点修改的通知',
                'comment'       => '',
            ],
            'Telegram.string.UpdateNode' => [
                'type'          => 'string',
                'value'         => '节点信息被修改~ %node_name%',
                'name'          => '自定义向 Telegram 群组推送节点修改通知的信息',
                'comment'       => '可用变量：' . PHP_EOL . '[节点名称] %node_name%',
            ],

            'Telegram.bool.DeleteNode' => [
                'type'          => 'bool',
                'value'         => 1,
                'name'          => '开启 Telegram 群组推送节点删除的通知',
                'comment'       => '',
            ],
            'Telegram.string.DeleteNode' => [
                'type'          => 'string',
                'value'         => '节点被删除~ %node_name%',
                'name'          => '自定义向 Telegram 群组推送节点删除通知的信息',
                'comment'       => '可用变量：' . PHP_EOL . '[节点名称] %node_name%',
            ],

            'Telegram.bool.WelcomeMessage' => [
                'type'          => 'bool',
                'value'         => 1,
                'name'          => '开启 TG 机器人新入群发送欢迎消息',
                'comment'       => '',
            ],
            'Telegram.bool.FinancePublic' => [
                'type'          => 'bool',
                'value'         => 1,
                'name'          => '开启财务报告发送至 TG 群',
                'comment'       => '',
            ],
            'Telegram.string.user_not_bind_reply' => [
                'type'          => 'string',
                'value'         => '您未绑定本站账号，您可以进入网站的 **资料编辑**，在右下方绑定您的账号.',
                'name'          => 'Bot 对未绑定账户用户的回复',
                'comment'       => '',
            ],
            'Telegram.string.telegram_general_terms' => [
                'type'          => 'string',
                'value'         => '服务条款.',
                'name'          => 'Bot 面向游客的服务条款',
                'comment'       => '',
            ],
            'Telegram.string.telegram_general_pricing' => [
                'type'          => 'string',
                'value'         => '产品介绍.',
                'name'          => 'Bot 面向游客的产品介绍',
                'comment'       => '',
            ],


            // 注册设置
            'Register.string.Mode' => [
                'type'          => 'string',
                'value'         => 'open',
                'name'          => '注册模式',
                'comment'       => 'close：关闭' . PHP_EOL . 'open：开放' . PHP_EOL . 'invite：仅限邀请码',
            ],
            'Register.bool.Enable_email_verify' => [
                'type'          => 'bool',
                'value'         => 0,
                'name'          => '是否启用注册邮箱验证码',
                'comment'       => '',
            ],
            'Register.bool.send_dailyEmail' => [
                'type'          => 'bool',
                'value'         => 1,
                'name'          => '注册时是否开启每日邮件',
                'comment'       => '',
            ],
            'Register.string.Email_verify_ttl' => [
                'type'          => 'string',
                'value'         => '3600',
                'name'          => '邮箱验证码有效期',
                'comment'       => '',
            ],
            'Register.string.Email_verify_iplimit' => [
                'type'          => 'string',
                'value'         => '10',
                'name'          => '验证码有效期内单IP可请求次数',
                'comment'       => '',
            ],
            'Register.string.defaultTraffic' => [
                'type'          => 'string',
                'value'         => '1',
                'name'          => '用户初始流量',
                'comment'       => '用户初始流量，单位：GB',
            ],
            'Register.string.defaultClass' => [
                'type'          => 'string',
                'value'         => '0',
                'name'          => '用户初始等级',
                'comment'       => '用户初始等级',
            ],
            'Register.string.defaultConn' => [
                'type'          => 'string',
                'value'         => '0',
                'name'          => '初始客户端数量限制',
                'comment'       => '0 为不限制',
            ],
            'Register.string.defaultSpeedlimit' => [
                'type'          => 'string',
                'value'         => '0',
                'name'          => '初始连接速率限制',
                'comment'       => '0 为不限制',
            ],
            'Register.string.defaultExpire_in' => [
                'type'          => 'string',
                'value'         => '3650',
                'name'          => '默认账户过期时间',
                'comment'       => '用户账户过期时间，在注册时设置（天）',
            ],
            'Register.string.defaultClass_expire' => [
                'type'          => 'string',
                'value'         => '24',
                'name'          => '等级过期时间',
                'comment'       => '用户等级过期时间，在注册时设置（小时）',
            ],
            'Register.string.defaultMethod' => [
                'type'          => 'string',
                'value'         => 'chacha20-ietf',
                'name'          => '注册时默认加密方式',
                'comment'       => '',
            ],
            'Register.string.defaultProtocol' => [
                'type'          => 'string',
                'value'         => 'auth_aes128_sha1',
                'name'          => '注册时默认协议',
                'comment'       => '',
            ],
            'Register.string.defaultProtocol_param' => [
                'type'          => 'string',
                'value'         => '',
                'name'          => '注册时默认协议参数',
                'comment'       => '',
            ],
            'Register.string.defaultObfs' => [
                'type'          => 'string',
                'value'         => 'http_simple',
                'name'          => '注册时默认混淆方式',
                'comment'       => '',
            ],
            'Register.string.defaultObfs_param' => [
                'type'          => 'string',
                'value'         => '',
                'name'          => '注册时默认混淆参数',
                'comment'       => '注册时默认混淆参数 设置单端口后 这边必须配置！填写www.jd.hk就行',
            ],
            'Register.string.defaultInviteNum' => [
                'type'          => 'string',
                'value'         => '10',
                'name'          => '邀请链接可用次数',
                'comment'       => '注册后的邀请链接可用次数',
            ],
            'Register.string.defaultInvite_get_money' => [
                'type'          => 'string',
                'value'         => '1',
                'name'          => '通过邀请链接注册获得奖励',
                'comment'       => '新用户通过私人邀请链接注册时，获得奖励金额（作为初始资金）',
            ],

            'Register.string.invite_price' => [
                'type'          => 'string',
                'value'         => '-1',
                'name'          => '用户购买邀请码所需要的价格',
                'comment'       => '价格小于 0 时视为不开放购买',
            ],
            'Register.string.custom_invite_price' => [
                'type'          => 'string',
                'value'         => '-1',
                'name'          => '用户定制邀请码所需要的价格',
                'comment'       => '价格小于 0 时视为不开放购买',
            ],

            'Register.string.reg_auto_reset_day' => [
                'type'          => 'string',
                'value'         => '0',
                'name'          => '注册时的流量重置日',
                'comment'       => '0 为不重置',
            ],
            'Register.string.reg_auto_reset_bandwidth' => [
                'type'          => 'string',
                'value'         => '0',
                'name'          => '注册时的每月重置流量',
                'comment'       => '单位：GB',
            ],

            'Register.string.reg_forbidden_ip' => [
                'type'          => 'string',
                'value'         => '127.0.0.0/8,::1/128',
                'name'          => '注册时默认禁止访问 IP 列表',
                'comment'       => '半角英文逗号分割',
            ],
            'Register.string.reg_forbidden_port' => [
                'type'          => 'string',
                'value'         => '',
                'name'          => '注册时默认禁止访问端口列表',
                'comment'       => '半角英文逗号分割，支持端口段',
            ],

            'Register.string.random_group' => [
                'type'          => 'string',
                'value'         => '0',
                'name'          => '注册时随机分组',
                'comment'       => '注册时随机分配到的分组，多个分组请用英文半角逗号分隔',
            ],
        ];

        $addDefaultFields = [
            'operator_id'   => 0,
            'operator_name' => '系统默认',
        ];
        if ($key === null) {
            $pushConfig = [];
            foreach ($configs as $configKey => $configValue) {
                $pushConfig[$configKey] = array_merge(
                    [
                        'key' => $configKey
                    ],
                    $addDefaultFields,
                    $configValue
                );
            }
            return $pushConfig;
        }
        if (isset($configs[$key])) {
            return array_merge(
                [
                    'key' => $key
                ],
                $addDefaultFields,
                $configs[$key]
            );
        }
        return null;
    }

    /**
     * 配置默认值
     *
     * @param string $key 键名
     */
    public static function default_value(string $key): ?array
    {
        return self::configs($key);
    }
}
