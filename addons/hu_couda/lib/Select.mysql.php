<?php

function prefix($uniacid)
{
    $config = pdo_get('cj_config', ['key' => 'uniacid']);

    if (empty($config)) {
        return '';
    }
    $value = json_decode($config['value'], true);
    if (!in_array($uniacid, $value)) {
        return '';
    }
    return $uniacid;
}

function prefix_table($table)
{
    return PREFIX . $table;
}

function generate_sql($uniacid)
{
    $cj_address = tablename($uniacid . 'cj_address');
    $cj_common_problem = tablename($uniacid . 'cj_common_problem');
    $cj_config = tablename($uniacid . 'cj_config');
    $cj_form_id = tablename($uniacid . 'cj_form_id');
    $cj_goods = tablename($uniacid . 'cj_goods');
    $cj_group_join = tablename($uniacid . 'cj_group_join');
    $cj_jump_program = tablename($uniacid . 'cj_jump_program');
    $cj_member = tablename($uniacid . 'cj_member');
    $cj_member_voucher = tablename($uniacid . 'cj_member_voucher');
    $cj_money_log = tablename($uniacid . 'cj_money_log');
    $cj_order = tablename($uniacid . 'cj_order');
    $cj_pay_order = tablename($uniacid . 'cj_pay_order');
    $cj_pre_prize = tablename($uniacid . 'cj_pre_prize');
    $cj_prize = tablename($uniacid . 'cj_prize');
    $cj_prize_code = tablename($uniacid . 'cj_prize_code');

    $sql = <<<STR
CREATE TABLE IF NOT EXISTS {$cj_address} (
  `address_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL,
  `phone` varchar(30) NOT NULL,
  `address` text NOT NULL,
  `addtime` int(11) NOT NULL,
  PRIMARY KEY (`address_id`),
  KEY `member_id` (`member_id`,`addtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='收货地址';

CREATE TABLE IF NOT EXISTS {$cj_common_problem} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT '问题标题',
  `describe` varchar(1000) NOT NULL COMMENT '问题描述',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:显示 2:不显示',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='常见问题';

CREATE TABLE IF NOT EXISTS {$cj_config} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(50) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$cj_form_id} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL COMMENT '用户id',
  `form_id` varchar(50) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

CREATE TABLE IF NOT EXISTS {$cj_goods} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_name` varchar(255) NOT NULL,
  `goods_img` varchar(255) NOT NULL,
  `shop_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `shop_id` (`shop_id`) USING BTREE
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$cj_group_join} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prize_id` int(11) NOT NULL COMMENT '抽奖id',
  `member_id` int(11) NOT NULL COMMENT '组队创建者id',
  `number` int(11) NOT NULL DEFAULT '3' COMMENT '允许组队人数',
  `apply_num` int(11) NOT NULL DEFAULT '0' COMMENT '已加入该团人数',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`) USING BTREE,
  KEY `prize_id` (`prize_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$cj_jump_program} (
  `jump_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `prize_id` int(10) unsigned NOT NULL,
  `app_name` varchar(30) NOT NULL,
  `appid` varchar(30) NOT NULL,
  `path` varchar(150) NOT NULL,
  `extraData` text COMMENT 'json格式字符串',
  PRIMARY KEY (`jump_id`),
  KEY `prize_id` (`prize_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='抽奖跳转到小程序 信息';

CREATE TABLE IF NOT EXISTS {$cj_member} (
  `id` int(8) unsigned NOT NULL AUTO_INCREMENT,
  `openid` varchar(50) NOT NULL COMMENT '用户唯一标识',
  `form_id` varchar(50) DEFAULT NULL,
  `effective_time` int(11) NOT NULL COMMENT 'form_id有效时间',
  `nickname` varchar(50) NOT NULL COMMENT '用户昵称',
  `user_img` varchar(200) NOT NULL COMMENT '用户头像路径',
  `gender` tinyint(1) NOT NULL COMMENT '用户性别 0：未知  1：男  2：女',
  `province` varchar(50) NOT NULL COMMENT '省份',
  `city` varchar(50) NOT NULL COMMENT '城市',
  `country` varchar(50) NOT NULL COMMENT '国家',
  `money` decimal(10,2) NOT NULL COMMENT '账户余额',
  `frozen_money` decimal(10,2) NOT NULL COMMENT '冻结资金',
  `add_time` int(11) NOT NULL COMMENT '用户注册时间',
  `set_time` int(12) NOT NULL COMMENT '用户登录时间',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `is_robot` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0用户1机器人',
  `shop_id` int(11) NOT NULL DEFAULT '0',
  `is_release` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4995 DEFAULT CHARSET=utf8 COMMENT='用户信息';

CREATE TABLE IF NOT EXISTS {$cj_member_voucher} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `voucher` char(18) NOT NULL,
  `write_off_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未核销1已核销',
  `shop_id` int(11) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `order_id` char(20) NOT NULL,
  `write_off_time` int(11) NOT NULL COMMENT '核销时间',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$cj_money_log} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL COMMENT '用户id',
  `money` decimal(10,2) NOT NULL COMMENT '变动金额',
  `type` tinyint(4) NOT NULL COMMENT '1:充值 2:提现 3:冻结 4:解冻 5:发红包 6:领取红包 7:红包退回 8:手续费',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `member_id` (`member_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

CREATE TABLE IF NOT EXISTS {$cj_order} (
  `order_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(20) NOT NULL COMMENT '订单编号',
  `prize_id` int(11) NOT NULL,
  `group_join_id` int(11) NOT NULL DEFAULT '0',
  `member_id` int(11) NOT NULL,
  `is_prize` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否中奖0:未中奖1:中奖',
  `code_num` int(11) NOT NULL DEFAULT '0',
  `addtime` int(11) NOT NULL,
  PRIMARY KEY (`order_id`),
  KEY `redpackage_id` (`prize_id`),
  KEY `member_id` (`member_id`),
  KEY `addtime` (`addtime`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS {$cj_pay_order} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `trade_no` char(50) NOT NULL,
  `transaction_id` char(32) NOT NULL,
  `pay_time` int(11) NOT NULL COMMENT '支付成功时间',
  `money` decimal(10,2) NOT NULL COMMENT '充值金额',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:发红包',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:未支付 2:已支付',
  `crested` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

CREATE TABLE IF NOT EXISTS {$cj_pre_prize} (
  `prize_id` int(11) NOT NULL AUTO_INCREMENT,
  `uname` varchar(200) NOT NULL DEFAULT '',
  `type` tinyint(4) NOT NULL DEFAULT '1',
  `desc_type` tinyint(4) NOT NULL,
  `title` varchar(50) NOT NULL DEFAULT '',
  `description` text NOT NULL,
  `opentime` int(11) NOT NULL DEFAULT '0',
  `fir_cname` varchar(255) NOT NULL,
  `fir_val` varchar(200) NOT NULL DEFAULT '' COMMENT '根据fir_ptype、ptype 一等奖值 实物 表示奖品名称 奖金代表金额 虚拟商品代表分比例',
  `fir_ptype` tinyint(1) NOT NULL DEFAULT '0' COMMENT '一等奖类型 0实物 1现金',
  `fir_num` int(11) NOT NULL DEFAULT '0' COMMENT '一等奖人数',
  `sec_cname` varchar(255) NOT NULL,
  `sec_val` varchar(200) NOT NULL DEFAULT '',
  `sec_ptype` tinyint(1) NOT NULL DEFAULT '0',
  `sec_num` int(11) NOT NULL DEFAULT '0',
  `trd_cname` varchar(255) NOT NULL,
  `trd_val` varchar(200) NOT NULL DEFAULT '',
  `trd_ptype` tinyint(1) NOT NULL DEFAULT '0',
  `trd_num` int(11) NOT NULL DEFAULT '0',
  `max_group_num` int(11) NOT NULL,
  `attach_id` int(25) NOT NULL DEFAULT '0',
  `path` varchar(150) DEFAULT '',
  `appid` varchar(30) NOT NULL DEFAULT '',
  `app_name` varchar(30) NOT NULL DEFAULT '',
  `wechat_no` varchar(30) NOT NULL DEFAULT '',
  `wechat_title` varchar(255) NOT NULL,
  `extraData` text NOT NULL,
  `appoint_id` varchar(1000) DEFAULT '' COMMENT '指定人id,json格式',
  `is_image` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0不用上传图片1需要上传图片',
  `addtime` int(11) NOT NULL,
  `brief_description` varchar(255) DEFAULT NULL,
  `copy_type` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`prize_id`),
  KEY `fir_ptype` (`fir_ptype`),
  KEY `sec_ptype` (`sec_ptype`),
  KEY `trd_ptype` (`trd_ptype`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS {$cj_prize} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL COMMENT '标题',
  `member_id` int(11) DEFAULT '0',
  `uname` varchar(255) DEFAULT NULL COMMENT '赞助商',
  `wechat_no` varchar(100) DEFAULT NULL COMMENT '付款复制的公众号',
  `typevalue` int(11) DEFAULT '0' COMMENT '开奖方式 people 人数开奖 time时间开奖 manual 手动开奖',
  `max_group_num` tinyint(4) DEFAULT '0' COMMENT '0不组团',
  `desc_type` tinyint(4) DEFAULT '0' COMMENT '详细说明类型0:文字1:图片',
  `desc_text` text COMMENT '详细描述',
  `type` tinyint(1) DEFAULT '1' COMMENT '1:时间,2人数,3手动',
  `attach_id` int(11) DEFAULT NULL,
  `fir_cname` varchar(255) DEFAULT NULL COMMENT '虚拟商品名称',
  `fir_ptype` tinyint(1) DEFAULT '1' COMMENT '0:实物1:红包2:虚拟3:卡券',
  `fir_num` int(11) DEFAULT NULL,
  `fir_val` varchar(2000) DEFAULT NULL COMMENT '根据fir_ptype、ptype 一等奖值 实物 表示奖品名称 奖金代表金额 虚拟商品存入json格式',
  `fir_cardid` int(11) DEFAULT '0',
  `sec_cname` varchar(255) DEFAULT NULL,
  `sec_ptype` tinyint(1) DEFAULT '1' COMMENT '1实物2现金',
  `sec_num` int(11) DEFAULT NULL,
  `sec_val` varchar(2000) DEFAULT NULL,
  `sec_cardid` int(11) DEFAULT '0',
  `trd_cname` varchar(255) DEFAULT NULL,
  `trd_ptype` tinyint(1) DEFAULT NULL,
  `trd_num` int(11) DEFAULT NULL,
  `trd_val` varchar(2000) DEFAULT NULL,
  `trd_cardid` int(11) DEFAULT '0',
  `status` tinyint(4) DEFAULT '0' COMMENT '0进行中,1已结束',
  `is_cancel` tinyint(4) DEFAULT '0' COMMENT '0未取消,1已取消',
  `is_global` tinyint(4) DEFAULT '1' COMMENT '0公共抽奖1用户发布',
  `apply_num` int(11) DEFAULT '0' COMMENT '参与人数',
  `description` text,
  `created` int(11) DEFAULT NULL,
  `open_time` int(11) DEFAULT '0' COMMENT '开奖时间',
  `brief_description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$cj_prize_code} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `prize_id` int(11) NOT NULL,
  `be_invited_id` int(11) NOT NULL,
  `code` char(20) NOT NULL,
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:抽奖或者2:召集好友获得',
  `source` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:普通用户2:机器人',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `prize_id` (`prize_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;



STR;

    pdo_query($sql);

    $cj_prize_result = tablename($uniacid . 'cj_prize_result');
    $cj_program = tablename($uniacid . 'cj_program');
    $cj_red_bag = tablename($uniacid . 'cj_red_bag');
    $cj_resource = tablename($uniacid . 'cj_resource');
    $cj_shop = tablename($uniacid . 'cj_shop');
    $cj_template_message = tablename($uniacid . 'cj_template_message');
    $cj_token = tablename($uniacid . 'cj_token');
    $cj_voucher = tablename($uniacid . 'cj_voucher');
    $cj_withdrawals = tablename($uniacid . 'cj_withdrawals');

    $sql = <<<STR
CREATE TABLE IF NOT EXISTS {$cj_prize_result} (
  `result_id` int(11) NOT NULL AUTO_INCREMENT,
  `prize_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL,
  `type` char(3) NOT NULL COMMENT '中奖类型 fir sec trd',
  `ptype` int(255) NOT NULL COMMENT '0:实物1:红包2:虚拟',
  `cardnum` varchar(100) NOT NULL COMMENT '虚拟中奖卡号',
  `cardpass` varchar(100) NOT NULL COMMENT '虚拟中奖密码',
  `pvalue` varchar(255) NOT NULL COMMENT '实物中奖名称',
  `addtime` int(11) NOT NULL,
  PRIMARY KEY (`result_id`),
  KEY `prize_id` (`prize_id`,`member_id`,`addtime`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COMMENT='开奖结果';
    
CREATE TABLE IF NOT EXISTS {$cj_program} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `appid` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$cj_red_bag} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `prize_id` int(11) NOT NULL COMMENT '奖品id',
  `red_bag_money` decimal(10,2) NOT NULL COMMENT '红包金额',
  `receive_member_id` int(11) NOT NULL COMMENT '领取用户id',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:未领取 2:已领取 3:已退回',
  `receive_time` int(11) NOT NULL COMMENT '领取时间',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `prize_id` (`prize_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

CREATE TABLE IF NOT EXISTS {$cj_resource} (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL DEFAULT '0' COMMENT '上传者',
  `route` varchar(255) NOT NULL COMMENT '路径',
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:图片',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='资源';

CREATE TABLE IF NOT EXISTS {$cj_shop} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `shop_name` varchar(255) NOT NULL,
  `logo` varchar(255) NOT NULL,
  `is_del` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未删除1已经删除',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$cj_template_message} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:开奖通知',
  `relation_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0:未发送,1:已发送',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$cj_token} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `token` char(32) NOT NULL,
  `member_id` int(11) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$cj_voucher} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `voucher` char(18) NOT NULL,
  `goods_id` int(11) NOT NULL,
  `shop_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '0未使用1已使用3已核销',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=78 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS {$cj_withdrawals} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `money` decimal(10,2) NOT NULL COMMENT '提现金额',
  `status` tinyint(4) NOT NULL DEFAULT '1' COMMENT '1:申请成功 2:提现才成功 3:提现失败',
  `success_time` int(11) NOT NULL COMMENT '提现成功时间',
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;


STR;

    pdo_query($sql);

    return true;
}

function update_open_more()
{
    if (!pdo_fieldexists(prefix_table('cj_prize_code'), 'is_prize')) {
        $cj_prize_code = tablename(prefix_table('cj_prize_code'));
        $sql = "ALTER TABLE {$cj_prize_code} ADD COLUMN `is_prize`  tinyint(4) NOT NULL DEFAULT 0 COMMENT '0:未中奖1:已中奖' AFTER `type`;";
        pdo_run($sql);
    }
    if (!pdo_fieldexists(prefix_table('cj_prize'), 'wechat_title')) {
        $cj_prize = tablename(prefix_table('cj_prize'));
        $sql = "ALTER TABLE {$cj_prize} ADD COLUMN `wechat_title`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `wechat_no`;";
        pdo_run($sql);
    }

    if (!pdo_fieldexists(prefix_table('cj_prize_result'), 'code')) {
        $cj_prize_result = tablename(prefix_table('cj_prize_result'));
        $sql = "ALTER TABLE {$cj_prize_result} ADD COLUMN `code`  varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL AFTER `pvalue`;";
        pdo_run($sql);
    }

    if (!pdo_fieldexists(prefix_table('cj_prize_result'), 'is_write_off')) {
        $cj_prize_result = tablename(prefix_table('cj_prize_result'));
        $sql = "ALTER TABLE {$cj_prize_result} ADD COLUMN `is_write_off`  tinyint(4) NOT NULL DEFAULT 0 COMMENT '0未核销.1:已核销' AFTER `code`;";
        pdo_run($sql);
    }

    if (!pdo_fieldexists(prefix_table('cj_prize'), 'is_share')) {
        $cj_prize = tablename(prefix_table('cj_prize'));
        $sql = "ALTER TABLE {$cj_prize} ADD COLUMN `is_share`  tinyint(4) NOT NULL DEFAULT 0 COMMENT '0允许分享,1不允许分享' AFTER `brief_description`;";
        pdo_run($sql);
    }
    $cj_home_recommend = tablename(prefix_table( 'cj_home_recommend'));

    $sql = <<<STR
CREATE TABLE IF NOT EXISTS {$cj_home_recommend} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `contact` varchar(500) NOT NULL,
  `created` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
STR;
    pdo_query($sql);


    if (!pdo_fieldexists(prefix_table('cj_prize'), 'is_command')) {
        $cj_prize = tablename(prefix_table('cj_prize'));
        $sql = "ALTER TABLE {$cj_prize} ADD COLUMN `is_command`  tinyint(4) NULL DEFAULT 0 COMMENT '0不是口令抽奖1是口令抽奖' AFTER `is_share`;";
        pdo_run($sql);
    }

    if (!pdo_fieldexists(prefix_table('cj_prize'), 'command')) {
        $cj_prize = tablename(prefix_table('cj_prize'));
        $sql = "ALTER TABLE {$cj_prize} ADD COLUMN `command`  varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `is_command`;";
        pdo_run($sql);
    }

}


/**
 * GET 请求
 * @param string $url
 */
function _http_get($url, $data) {
    $oCurl = curl_init();
    if (stripos($url, "https://") !== FALSE) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
    }
    $param = array();
    foreach ($data as $key => $val) {
        if (is_array($val)) {
            foreach ($val as $v) {
                $param[] = $key . '[]=' . urlencode($v);
            }
        } else {
            $param[] = $key . "=" . urlencode($val);
        }
    }
    $sparam = join("&", $param);
    $geturl = $url . '&' . $sparam;
    curl_setopt($oCurl, CURLOPT_URL, $url . '&' . $sparam);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if (intval($aStatus["http_code"]) == 200 || !empty($sContent)) {
        return str_replace(array("\n", "\r"), '', $sContent);
    } else {
        $error = intval($aStatus["http_code"]) . 'curl_error' . $geturl;
        return false;
    }
}