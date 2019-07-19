<?php
defined('IN_IA') or exit('Access Denied');

class redbag{

    public $error = '';

    /**
     * 生成红包
     * @param $bag_id
     * @return int
     */
    public function generate($bag_id)
    {
        $bag = pdo_get('bq_bag', ['id' => $bag_id]);
        $procedures = ceil($bag['red_bag_money'] * 2)/ 100;
        $member = pdo_get('bq_member', ['id' => $bag['member_id']]);

        if ($member['money'] < $bag['red_bag_money'] + $procedures) {
            $this->error = '余额不足';
            return 1;
        }
        //金额变更
        $flag = $this->change($bag['member_id'], $bag['red_bag_money'], 5);
        if (!$flag) {
            return 1;
        }
        //手续费
        $flag = $this->change($bag['member_id'], $procedures, 8);
        if (!$flag) {
            return 1;
        }

        $red = $this->getRedPackage($bag['red_bag_money'], $bag['red_bag_number']);

        if (empty($red)) {
            $this->error = '每个红包金额最少为0.01元';
            return 2;
        }

        $sql = 'INSERT INTO ims_bq_red_bag (member_id,bag_id,red_bag_money,created) VALUES';
        $t = time();
        foreach ($red as $v) {
            $sql .= "({$bag['member_id']}, {$bag['id']}, {$v}, {$t}),";
        }
        $sql = rtrim($sql, ',');
        if (!pdo_query($sql)) {
            $this->error = '设置失败';
            return 2;
        }

        if (pdo_update('bq_bag', ['status' => 2], ['id' => $bag_id])) {
            return 3;
        }
        $this->error = '设置失败';
        return 2;

    }


    /**
     * 资金变动
     *
     * @param $member_id
     * @param $money
     * @param int $type
     * @return bool
     */
    public function change($member_id, $money, $type = 1)
    {
        $member = pdo_get('bq_member', ['id' => $member_id]);

        if (in_array($type, [1, 6, 7])) {
            $after_money = $member['money'] + $money;
        } else {

            if ($member['money'] < $money) {
                $this->error = '余额不足';
                return false;
            }

            $after_money = $member['money'] - $money;
        }

        $update = [ 'money' => $after_money];
        if (!pdo_update('bq_member', $update, ['id' => $member_id])) {
            $this->error = '余额变动失败';
            return false;
        }

        $money_log = [
            'member_id' => $member_id,
            'money' => $money,
            'type' => $type,
            'created' => time()
        ];
        if (pdo_insert('bq_money_log', $money_log)) {
            return true;
        }

        $this->error = '日志更新出错';
        return false;

    }


    /**
     * @param $member_id
     * @param $money
     * @param $relation_id
     * @param int $type
     */
    public function unifiedOrder($member_id, $money, $relation_id, $type = 1)
    {
        global $_W,$_GPC;
        $member = pdo_get('bq_member', ['id' => $member_id]);
        $trade_no = date("YmdHis", time()) . $member['id'] . mt_rand(1000, 9999);
        $pay = [
            'member_id' => $member_id,
            'relation_id' => $relation_id,
            'trade_no' => $trade_no,
            'money' => $money,
            'type' => $type,
            'crested' => time()
        ];
        if (!pdo_insert('bq_pay_order', $pay)) {
            $this->error = '支付记录生成失败';
            return false;
        }
        $tools = new JsApiPay();
        $input = new \WxPayUnifiedOrder();
        $input->SetBody("充值");
        $input->SetOut_trade_no($trade_no);
        $input->SetTotal_fee($money * 100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetGoods_tag("test tag");
        //$input->SetNotify_url('https://' . $_SERVER['HTTP_HOST'] . '/Pay/dorech/trade_no/' . $trade_no);
        $input->SetNotify_url('https://' . $_SERVER['HTTP_HOST'] . '/addons/'.$_GPC['m'].'/native1.php');
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($member['openid']);
        $order = \WxPayApi::unifiedOrder($input);

        if ($order['return_code'] != 'SUCCESS') {
            $this->error=$order['return_msg'];
            return false;
        }
        $jsApiParameters = $tools->GetJsApiParameters($order);
        return json_decode($jsApiParameters, true);
    }


    /**
     * 提现
     * @param $id
     * @return bool
     */
    public function cash($id)
    {
        $info = pdo_get('bq_withdrawals', ['id' => $id]);
        $member = pdo_get('bq_member', ['id' => $info['member_id']]);

        $MerchPay = new MerchPay();
        $trade_no = date("YmdHis", NOW_TIME) . $member['id'] . rand(100, 999);
        $res = $MerchPay->pay($member['openid'], $trade_no, $info['money'], '提现', '');//本机ip

        if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {

            //$weObj = new Wxadoc();
            //if ($member['form_id'] && $member['effective_time'] - );
            //推送消息
            if ($member['effective_time'] > time()) {
                //todo 推送
                $update = [
                    'effective_time' => time() - 100
                ];
                //Db::name('member')->where('id', $info['member_id'])->update($update);
            }

            return true;
        }
        $this->error = '提款失败';
        return false;
    }


    public function faceplusplus($id, $jsonpost = false, $post_file = false)
    {
        $image = pdo_get('bq_resource', ['id' => $id]);
        if (empty($image)) {
            return false;
        }

        $image_base64 = base64EncodeImage(ATTACHMENT_ROOT . $image['route']);
        $url = 'https://api-cn.faceplusplus.com/facepp/v3/detect';

        $param = [
            'api_key' => 'ym02DiJ6ouJb-Vk8qZ9AU2z4pGI_iwhn',
            'api_secret' => 'bExeYFBK8APW0JvRvKfWh2kzbofzVuoU',
            'image_base64' => $image_base64,
            'return_attributes' => 'emotion'
        ];
        $oCurl = curl_init();
        if (stripos($url, "https://") !== FALSE) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1
        }
        if ($jsonpost) {
            $strPOST = str_replace('\/', '/', json_encode($param));
        } elseif (is_string($param) || $post_file) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach ($param as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = join("&", $aPOST);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($oCurl, CURLOPT_POST, true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if (intval($aStatus["http_code"]) == 200) {
            return $sContent;
        } else {
            return false;
        }
    }


    public function getRedPackage($money, $num, $min = 0.01)
    {
        //将最大金额  设为红包总数
        $max = $money;
        $data = array();
        //最小金额*数量  不能大于  总金额
        if ($min * $num > $money) {
            return $data;
        }

        //最大金额*数量  不能大于  总金额
        if ($max * $num < $money) {
            return $data;
        }
        //如果红包数量为1  直接返回总数
        if ($num == 1){
            $data[] = $money;
            return $data;
        }
        while ($num >= 1) {
            $num--;
            $kmin = max($min, $money - $num * $max);
            $kmax = min($max, $money - $num * $min);
            $kAvg = $money / ($num + 1);
            //获取最大值和最小值的距离之间的最小值
            $kDis = min($kAvg - $kmin, $kmax - $kAvg);

            //获取0到1之间的随机数与距离最小值相乘得出浮动区间，这使得浮动区间不会超出范围
            $r = ((float)(rand(1, 10000) / 10000) - 0.5) * $kDis * 2;
            $k = round($kAvg + $r, 2);
            $money -= $k;
            $data[] = sprintf("%.2f",$k);
        }
        return $data;
    }

}

function base64EncodeImage($image_file) {
    $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
    $base64_image = base64_encode($image_data);
    return $base64_image;
}





function we7_coupon_activity_get_member($type, $param = array()) {
	activity_coupon_type_init();
	global $_W;
	$types =  array('new_member', 'old_member', 'quiet_member', 'activity_member', 'group_member', 'cash_time', 'openids');
	if (!in_array($type, $types)) {
		return error('1', '没有匹配的用户类型');
	}
	$propertys = activity_member_propertys();
	$openids = pdo_getall('mc_mapping_fans', array('openid <>' => '', 'uniacid' => $_W['uniacid'], 'uid <>' => ''), array('uid', 'openid'), 'uid');
	$uids = array_keys($openids);
	if ($type == 'new_member') {
		$property_time = strtotime('-' . $propertys['newmember'] . ' month', time());
		$members_sql = "SELECT c.openid FROM ( SELECT a.uid FROM ". tablename('mc_members')." as a LEFT JOIN ".tablename('mc_cash_record')." as b ON a.uid = b.uid WHERE a.uniacid = :uniacid AND a.createtime > :time AND (b.createtime > :time or b.id is null) GROUP BY a.uid HAVING COUNT(*) < 2) as d  LEFT JOIN ". tablename('mc_mapping_fans')." as c ON d.uid = c.uid WHERE c.openid <> ''";
		$members = pdo_fetchall($members_sql, array(':uniacid' => $_W['uniacid'], ':time' => $property_time), 'openid');
	}
	if ($type == 'old_member') {
		$property_time = strtotime('-' . $propertys['oldmember'] . ' month', time());
		$members = pdo_fetchall("SELECT b.openid FROM ".tablename('mc_members')." as a LEFT JOIN ". tablename('mc_mapping_fans')." as b ON a.uid = b.uid WHERE a.createtime < :time AND a.uniacid = :uniacid AND b.openid <> ''", array(':time' => $property_time, ':uniacid' => $_W['uniacid']), 'openid');
	}
	if ($type == 'activity_member') {
		$property_time = strtotime('-' . $propertys['activitymember'] . ' month', time());
		$cash_records = pdo_fetchall("SELECT COUNT(*) AS total, uid, createtime FROM " . tablename('mc_cash_record') . " WHERE uid IN (" . implode(',', $uids) . ") GROUP BY uid", array(), 'uid');
		foreach ($cash_records as $uid => $record) {
			if ($record['total'] >= 2 && $record['createtime'] > $property_time) {
				$activity_member[$uid] = $record;
			}
		}
		foreach ($activity_member as $uid => $member) {
			$members[$openids[$uid]['openid']] = $openids[$uid]['openid'];
		}
		unset($member);
	}
	if ($type == 'quiet_member') {
		$property_time = strtotime('-' . $propertys['quietmember'] . ' month', time());
		$cash_records = pdo_fetchall("SELECT COUNT(*) AS total, uid, createtime FROM " . tablename('mc_cash_record') . " WHERE uid IN (" . implode(',', $uids) . ") GROUP BY uid", array(), 'uid');
		foreach ($uids as $uid) {
			if (empty($cash_records[$uid])) {
				$quiet_member[$uid]['uid'] = $uid;
			}
		}
		foreach ($quiet_member as $uid => $member) {
			$members[$openids[$uid]['openid']] = $openids[$uid]['openid'];
		}
	}

	if ($type == 'group_member') {
		if (empty($param)) {
			return error(1, '请选择会员组');
		}
		if (COUPON_TYPE == WECHAT_COUPON) {
			$members =  pdo_getall('mc_mapping_fans', array('uniacid' => $_W['uniacid']), array(), 'openid');
			foreach ($members as $key => &$fan) {
				$fan['groupid'] = explode(',', $fan['groupid']);
				if (!is_array($fan['groupid']) || !in_array($param['groupid'], $fan['groupid'])) {
					unset($members[$key]);
				}
			}
		} else {
			$members = pdo_fetchall('SELECT b.openid FROM '.tablename('mc_members')." as a LEFT JOIN ". tablename('mc_mapping_fans')." as b ON a.uid  = b.uid WHERE a.groupid  = :groupid AND a.uniacid = :uniacid AND b.openid <> ''", array(':groupid' => $param['groupid'], ':uniacid' => $_W['uniacid']), 'openid');
		}
	}
	if ($type == 'cash_time') {
		$members = pdo_fetchall("SELECT a.openid FROM ". tablename('mc_mapping_fans')." as a LEFT JOIN ".tablename('mc_cash_record')." as b ON a.uid = b.uid WHERE a.uniacid = :uniacid AND b.createtime >= :start AND b.createtime <= :end GROUP BY a.openid", array(':uniacid' => $_W['uniacid'], ':start' => $param['start'], ':end' => $param['end']), 'openid');
	}
	if ($type == 'openids') {
		$members = json_decode($_COOKIE['fans_openids'.$_W['uniacid']]);
	}

	if (is_array($members)) {
		$member = $type == 'openids' ? $members : array_keys($members);
		$members = array();
		$members['members'] = $member;
		$members['total'] = count($members['members']);
	} else {
		$members = array();
	}
	return $members;
}

function we7_coupon_activity_coupon_give() {
	global $_W;
	$openid = $_W['openid'];
	if (!empty($openid)) {
		$member = array();
		$new_members = we7_coupon_activity_get_member('new_member');
		if (!empty($new_members['members'])) {
			$member['is_newmember'] = in_array($openid, $new_members['members'])? 'new_member' : '';
		} else {
			$member['is_newmember'] = '';
		}
		$old_members = we7_coupon_activity_get_member('old_member');
		if (!empty($old_members['members'])) {
			$member['is_oldmember'] = in_array($openid, $old_members['members'])? 'old_member' : '';
		} else {
			$member['is_oldmember'] = '';
		}
		$quiet_members = we7_coupon_activity_get_member('quiet_member');
		if (!empty($quiet_members['members'])) {
			$member['is_quietmember'] = in_array($openid, $quiet_members['members'])? 'quiet_member' : '';
		} else {
			$member['is_quietmember'] = '';
		}
		$activity_members = we7_coupon_activity_get_member('activity_member');
		if (!empty($activity_members['members'])) {
			$member['is_activitymember'] = in_array($openid, $activity_members['members'])? 'activity_member' : '';
		} else {
			$member['is_activitymember'] = '';
		}
	} else {
		$member = array();
	}
	$coupon_activitys = pdo_getall('coupon_activity', array('uniacid' => $_W['uniacid'], 'type' => 1, 'status' => 1));
	foreach ($coupon_activitys as $activity) {
		$is_give = pdo_get('coupon_record', array('grantmodule' => $activity['id'], 'remark' => '系统赠送'));
		if (!empty($is_give)) {
			continue;
		}
		$activity['members'] = empty($activity['members']) ? array() : iunserializer($activity['members']);
				if (in_array('group_member', $activity['members'])) {
			$groupid = pdo_fetchcolumn("SELECT groupid FROM ". tablename('mc_members')." WHERE uniacid = :uniacid AND uid = :uid", array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid']));
			if ($groupid == $activity['members']['groupid']) {
				$member['is_groupmember'] = 'group_member';
			}
		}
		if (in_array('cash_time', $activity['members'])) {
			$cash_member = pdo_fetch("SELECT * FROM " . tablename('mc_cash_record') . " WHERE uniacid = :uniacid AND uid = :uid AND createtime > :start AND createtime < :end", array(':uniacid' => $_W['uniacid'], ':uid' => $_W['member']['uid'], ':start' => strtotime($activity['members']['start']), ':end' => strtotime($activity['members']['start'])));
			if (!empty($cash_member)) {
				$member['is_cashtime'] = 'cash_time';
			}
		}
		if (in_array('openids', $activity['members'])) {
			$fan = pdo_get('mc_mapping_fans', array('uniacid' => $_W['uniacid'], 'uid' => $_W['member']['uid']));
			$openid = $_W['openid'];
			if (in_array($openid, $activity['members']['openids'])) {
				$member['is_openids'] = 'openids';
			}
		}
		if (array_intersect($activity['members'], $member)) {
			$activity['coupons'] = empty($activity['coupons']) ? array() : iunserializer($activity['coupons']);
			foreach ($activity['coupons'] as $id){
				$coupon = activity_coupon_info($id);
				if(is_error($coupon)){
					continue;
				}
				$_W['activity_coupon_id'] = $activity['id'];
				$ret = we7_coupon_activity_coupon_grant($id, $_W['member']['uid']);
				unset($_W['activity_coupon_id']);
				if(is_error($ret)) {
					continue;
				}
			}
			unset($id);
		}
	}
	unset($activity);
}

function we7_coupon_activity_coupon_owned() {
	global $_W, $_GPC;
	$uid = $_W['member']['uid'];
	$param = array('uniacid' => $_W['uniacid'], 'openid' => $_W['openid'], 'status' => 1);
	$data = pdo_getall('coupon_record', $param);
	foreach ($data as $key => $record) {
		$coupon = activity_coupon_info($record['couponid']);
		if ($coupon['source'] != COUPON_TYPE) {
			unset($data[$key]);
			continue;
		}
		if ($coupon['status'] != '3') {
			pdo_delete('coupon_record', array('id' => $record['id']));
			unset($data[$key]);
			continue;
		}
		if (is_error($coupon)) {
			unset($data[$key]);
			continue;
		}
		$modules = array();
		if (!empty($coupon['modules'])) {
			foreach ($coupon['modules'] as $module) {
				$modules[] = $module['name'];
			}
		}
		if (!empty($modules) && !in_array($_W['current_module']['name'], $modules) && !empty($_W['current_module']['name']) && $_W['current_module']['name'] != 'we7_coupon') {
			unset($data[$key]);
			continue;
		}
		if (is_array($coupon['date_info']) && $coupon['date_info']['time_type'] == '2') {
			$starttime = $record['addtime'] + $coupon['date_info']['deadline'] * 86400;
			$endtime = $starttime + ($coupon['date_info']['limit'] - 1) * 86400;
			if ($endtime < time()) {
				unset($data[$key]);
				pdo_delete('coupon_record', array('id' => $record['id']));
				continue;
			} else {
				$coupon['extra_date_info'] = '有效期:' . date('Y.m.d', $starttime) . '-' . date('Y.m.d', $endtime);
			}
		}
		if (is_array($coupon['date_info']) && $coupon['date_info']['time_type'] == '1') {
			$endtime = str_replace('.', '-', $coupon['date_info']['time_limit_end']);
			$endtime = strtotime($endtime);
			if ($endtime < time()) {
				pdo_delete('coupon_record', array('id' => $record['id']));
				unset($data[$key]);
				continue;
			}

		}
		if ($coupon['type'] == COUPON_TYPE_DISCOUNT) {
			$coupon['icon'] = '<div class="price">' . $coupon['extra']['discount'] * 0.1 . '<span>折</span></div>';
		}
		elseif($coupon['type'] == COUPON_TYPE_CASH) {
			$coupon['icon'] = '<div class="price">' . $coupon['extra']['reduce_cost'] * 0.01 . '<span>元</span></div><div class="condition">满' . $coupon['extra']['least_cost'] * 0.01 . '元可用</div>';
		}
		elseif($coupon['type'] == COUPON_TYPE_GIFT) {
			$coupon['icon'] = '<img src="resource/images/wx_gift.png" alt="" />';
		}
		elseif($coupon['type'] == COUPON_TYPE_GROUPON) {
			$coupon['icon'] = '<img src="resource/images/groupon.png" alt="" />';
		}
		elseif($coupon['type'] == COUPON_TYPE_GENERAL) {
			$coupon['icon'] = '<img src="resource/images/general_coupon.png" alt="" />';
		}
		$data[$key] = $coupon;
		$data[$key]['recid'] = $record['id'];
		$data[$key]['code'] = $record['code'];
		if ($coupon['source'] == '2') {
			if (empty($data[$key]['code'])) {
				$data[$key]['extra_ajax'] = url('entry', array('m' => 'we7_coupon', 'do' => 'activity', 'type' => 'coupon', 'op' => 'addcard'));
			} else {
				$data[$key]['extra_ajax'] = url('entry', array('m' => 'we7_coupon', 'do' => 'activity', 'type' => 'coupon', 'op' => 'opencard'));
			}
		}
	}
	return $data;
}

function we7_coupon_activity_paycenter_coupon_available() {
	$coupon_owned = we7_coupon_activity_coupon_owned();
	foreach ($coupon_owned as $key => &$val) {
		if (empty($val['code'])) {
			unset($val);
		}
		if ($val['type'] == '1' || $val['type'] == '2') {
			$coupon_available[$val['id']] = $val;
		}
	}
	return $coupon_available;
}

function we7_coupon_activity_store_sync() {
	global $_W;
	load()->classs('coupon');
	$cachekey = "storesync:{$_W['uniacid']}";
	$cache = cache_load($cachekey);
	if (!empty($cache) && $cache['expire'] > time()) {
		return false;
	}
	$stores = pdo_getall('activity_stores', array('uniacid' => $_W['uniacid'], 'source' => 2));
	foreach ($stores as $val) {
		if ($val['status'] == 3) {
			continue;
		}
		$coupon_api = new coupon($_W['acid']);
		$location = $coupon_api->LocationGet($val['location_id']);
		if(is_error($location)) {
			return error(-1, $location['message']);
		}
		$location = $location['business']['base_info'];
		$status2local = array('', 3, 2, 1, 3);
		$location['status'] = $status2local[$location['available_state']];
		$location['location_id'] = $location['poi_id'];
		$category_temp = explode(',', $location['categories'][0]);
		$location['category'] = iserializer(array('cate' => $category_temp[0], 'sub' => $category_temp[1], 'clas' => $category_temp[2]));
		$location['photo_list'] = iserializer($location['photo_list']);
		unset($location['categories'], $location['poi_id'], $location['update_status'], $location['available_state'], $location['offset_type'], $location['sid'], $location['type'], $location['qualification_list'], $location['upgrade_comment'], $location['upgrade_status'], $location['mapid']);
		pdo_update('activity_stores', $location, array('uniacid' => $_W['uniacid'], 'id' => $val['id']));
	}
	cache_write($cachekey, array('expire' => time() + 1800));
	return true;
}

function we7_coupon_paycenter_order_status() {
	/*		SUCCESS—支付成功
		REFUND—转入退款
		NOTPAY—未支付
		CLOSED—已关闭
		REVOKED—已撤销（刷卡支付）
		USERPAYING--用户支付中
		PAYERROR--支付失败(其他原因，如银行返回失败)*/
	return array(
		'0' => array(
			'text' => '未支付',
			'class' => 'text-danger',
		),
		'1' => array(
			'text' => '已支付',
			'class' => 'text-success',
		),
		'2' => array(
			'text' => '已支付,退款中...',
			'class' => 'text-default',
		),
	);
}

function we7_coupon_paycenter_order_types() {
	return array(
		'wechat' => '微信支付',
		'alipay' => '支付宝支付',
		'credit' => '余额支付',
		'baifubao' => '百付宝'
	);
}

function we7_coupon_paycenter_order_trade_types() {
	return array(
		'native' => '扫码支付',
		'jsapi' => '公众号支付',
		'micropay' => '刷卡支付'
	);
}

function we7_coupon_paycenter_check_login() {
	global $_W, $_GPC;
	if(empty($_W['uid']) && $_GPC['do'] != 'login') {
		message('抱歉，您无权进行该操作，请先登录', murl('entry', array('m' => 'we7_coupon', 'do' => 'clerk', 'op' => 'login'), true, true), 'error');
	}
	if($_W['user']['type'] == ACCOUNT_OPERATE_CLERK) {
		isetcookie('__uniacid', $_W['user']['uniacid'], 7 * 86400);
		isetcookie('__uid', $_W['uid'], 7 * 86400);
	} else {
		message('非法访问', '', 'error');
	}
}
