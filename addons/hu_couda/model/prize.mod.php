<?php
defined('IN_IA') or exit('Access Denied');

class prize{

    public $error = '';


    public function moreOpen($token, $uniacid)
    {
        //http_get();
        if (prefix($uniacid)) {
            return true;
        }
        $data = [
            'token' => $token,
            'uniacid' => $uniacid,
            'server' => $_SERVER['HTTP_HOST']
        ];

        $result = _http_get('http://validate.9xy.cn/index.php?s=index/Index/check.html', $data);
        if (!$result) {
            $this->error = '开通失败,稍后再试';
            return false;
        }
        $json = json_decode($result, true);

        if ($json['status'] != 1) {
            $this->error = $json['info'];
            return false;
        }
        generate_sql($uniacid);

        $config = pdo_get('cj_config', ['key' => 'uniacid']);
        $value = [];
        if ($config) {
            $value = json_decode($config['value'], true);
        } else {
            pdo_insert('cj_config', ['key' => 'uniacid']);
        }
        $value[] = $uniacid;
        if (pdo_update('cj_config', ['value' => json_encode($value)], ['key' => 'uniacid'])) {
            return true;
        }
        $this->error = '数据库保存失败';
        return false;
    }



    public function create_code($prize_id, $member_id, $be_invited_id = 0, $type = 1, $source = 1)
    {
        if ($source == 1) {
            $order = pdo_get(prefix_table('cj_order'), ['prize_id' => $prize_id, 'member_id' => $member_id]);
            if (empty($order)) {
                $this->error = '还未参加此次抽奖';
                return false;
            }
        }

        $data = [
            'member_id' => $member_id,
            'prize_id' => $prize_id,
            'be_invited_id' => $be_invited_id,
            'type' => $type,
            'source' => $source,
            'created' => time()
        ];
        if (!pdo_insert(prefix_table('cj_prize_code'), $data)) {
            $this->error = '生成抽奖码失败';
            return false;
        }
        $id = pdo_insertid() % 1000000;
        $code = str_pad($id, 7, '0', STR_PAD_LEFT);
        $code = str_pad($code, 8, mt_rand(0, 9), STR_PAD_LEFT);
        if(!pdo_update(prefix_table('cj_prize_code'), ['code' => $code], ['id' => $id])) {
            $this->error = '生成抽奖码失败';
            return false;
        }
        if ($source == 2) {
            return $code;
        }
        $tble = tablename(prefix_table('cj_order'));
        $sql = "UPDATE {$tble} SET code_num=code_num+1 WHERE prize_id={$prize_id} AND member_id={$member_id}";
        if (!pdo_query($sql)) {
            $this->error = '生成抽奖码失败';
            return false;
        }

        return $code;
    }



    public function create_voucher($goods_id, $shop_id, $num)
    {
        $data = [
            'goods_id' => $goods_id,
            'shop_id' => $shop_id,
            'created' => time()
        ];
        for ($i = 0; $i < $num; $i++) {
            if (!pdo_insert(prefix_table('cj_voucher'), $data)) {
                continue;
            }
            $id = pdo_insertid();
            $no = $id . time();
            $voucher = str_pad($no, 18, '0', STR_PAD_LEFT);
            pdo_update(prefix_table('cj_voucher'), ['voucher' => $voucher], ['id' => $id]);
        }
        return true;
    }
    public function get_code($prize_id, $prize_num)
    {
        $cj_prize_code = tablename(prefix_table('cj_prize_code'));
        $sql = "SELECT * FROM {$cj_prize_code} WHERE prize_id={$prize_id} AND source=1 ORDER BY rand() LIMIT {$prize_num}";

        $code =  pdo_fetchall($sql);
        foreach ($code as $value) {
            pdo_update(prefix_table('cj_prize_code'), ['is_prize' => 1], ['id' => $value['id']]);
        }
        return $code;
    }





    public function deduction($shop_id, $goods_id, $num)
    {
        $goods = pdo_get(prefix_table('cj_goods'), ['id' => $goods_id, 'shop_id'  => $shop_id]);
        if (empty($goods)) {
            $this->error = '该优惠券不存在';
            return false;
        }
        $voucher = pdo_getall(prefix_table('cj_voucher'), ['goods_id' => $goods_id, 'status' => 0], [], '', [], $num);
        if (count($voucher) < $num) {
            $this->error = $goods['goods_name'] . '数量不足';
            return false;
        }
        $vou = [];
        foreach ($voucher as $value) {
            $vou[] = $value['voucher'];
            if (!pdo_update(prefix_table('cj_voucher'), ['status' => 1], ['id' => $value['id']])) {
                $this->error = '发起失败';
                return false;
            }
        }
        return $vou;
    }


    public function is_voucher($member_id)
    {
        $member = pdo_get(prefix_table('cj_member'), ['id' => $member_id]);
        if ($member['shop_id'] < 1) {
            return false;
        }
        $shop = pdo_get(prefix_table('cj_shop'), ['id' => $member['shop_id'], 'is_del' => 0]);
        if (empty($shop)) {
            return false;
        }
        $goods = pdo_getall(prefix_table('cj_goods'), ['shop_id' => $member['shop_id']]);
        if (empty($goods)) {
            return false;
        }
        foreach ($goods as $k => $value) {
            $not_used = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_voucher')) . " WHERE goods_id={$value['id']} AND status=0");//var_dump($not_used);
            if ($not_used < 1) {
                unset($goods[$k]);
                continue;
            }
            $goods[$k]['not_used'] = $not_used;
        }
        if (empty($goods)) {
            return false;
        }
        return $goods;
    }


    /**
     * 开奖
     */
    public function open($prize_id)
    {
        $condition = [
            'id' => $prize_id,
            'status' => 0,
            'is_cancel' => 0
        ];
        $prize = pdo_get(prefix_table('cj_prize'), $condition);
        if (empty($prize)
            || $prize['is_cancel'] == 1
            || $prize['status'] == 1
        ) {
            $this->error = '该抽奖不符合开奖条件';
            return false;
        }
        $prize_num = $prize['fir_num'] + $prize['sec_num'] + $prize['trd_num'];
        //$apply_count = pdo_get('cj_order', ['prize_id' => $prize_id], ['COUNT(1) AS count']);

        $appoint = pdo_getall(prefix_table('cj_appoint'), ['prize_id' => $prize_id]);


        if ($prize_num > $prize['apply_num'] || (!empty($appoint) && count($appoint) < $prize_num)) {
            $data = [
                'status' => 1,
                'is_cancel' => 1
            ];
            if (pdo_update(prefix_table('cj_prize'), $data, ['id' => $prize_id])) {

                $refund = 0;
                if ($prize['fir_ptype'] == 1) {
                    $refund += ($prize['fir_num'] * $prize['fir_val']);
                }
                if ($prize['sec_ptype'] == 1) {
                    $refund += ($prize['sec_num'] * $prize['sec_val']);
                }
                if ($prize['trd_ptype'] == 1) {
                    $refund += ($prize['trd_num'] * $prize['trd_val']);
                }
                if ($refund > 0) {
                    $this->change($prize['member_id'], $refund, 7);
                }

                return true;
            }
            $this->error = '取消开奖失败';
            return false;
        }
        pdo_begin();
        try{
            $order = [];
            if (!empty($appoint)) {
                foreach ($appoint as & $value) {
                    $where = [
                        'member_id' => $value['member_id'],
                        'prize_id' => $value['prize_id'],
                        'is_prize' => 0
                    ];
                    $code = pdo_get(prefix_table('cj_prize_code'), $where);
                    //$value['code_id'] = $code['id'];
                    $value['code'] = $code['code'];
                    pdo_update(prefix_table('cj_prize_code'), ['is_prize' => 1], ['id' => $code['id']]);
                }
                unset($value);

                $order = $appoint;
            }
            if ($prize['max_group_num'] > 0 && empty($order) && false) {
                $group = pdo_getall(prefix_table('cj_group_join'), ['prize_id' => $prize], [], '', ['apply_num desc']);
                $in_group = [];
                foreach ($group as $value) {
                    if ($value['apply_num'] <= $prize_num) {
                        $in_group[] = $value['id'];
                        $prize_num = $prize_num - $value['apply_num'];
                    }
                    if ($prize_num == 0) {
                        break;
                    }
                }
                $cj_order = tablename(prefix_table('cj_order'));
                if (!empty($in_group)) {
                    $group_ids = implode(',', $in_group);
                    $sql = "SELECT * FROM {$cj_order} WHERE prize_id={$prize_id} AND group_join_id in ({$group_ids})";
                    $order = pdo_fetchall($sql);
                }
            }
            if (empty($order)) {
                $order = $this->get_code($prize_id, $prize_num);
                /*$cj_order = tablename('cj_order');
                $sql = "SELECT * FROM {$cj_order} WHERE prize_id={$prize_id} ORDER BY rand() LIMIT {$prize_num}";
                $order = pdo_fetchall($sql);*/
                //$order = pdo_getall('cj_order', ['prize_id' => $prize_id], [], '', [], "0, {$prize_num}");
            }
            $fir = [];

            if ($prize['fir_num'] > 0) {
                $fir = [
                    'type' => $prize['fir_ptype'],
                    'val' => ($prize['fir_ptype'] == 2 || $prize['fir_ptype'] == 3) ? json_decode($prize['fir_val'], true) : $prize['fir_val'],
                    'num' => $prize['fir_num']
                ];
            }

            $sec = [];

            if ($prize['sec_num'] > 0) {
                $sec = [
                    'type' => $prize['sec_ptype'],
                    'val' => ($prize['sec_ptype'] == 2 || $prize['sec_ptype'] == 3) ? json_decode($prize['sec_val'], true) : $prize['sec_val'],
                    'num' => $prize['sec_num']
                ];
            }

            $trd = [];

            if ($prize['trd_num'] > 0) {
                $trd = [
                    'type' => $prize['trd_ptype'],
                    'val' => ($prize['trd_ptype'] == 2 || $prize['trd_ptype'] == 3) ? json_decode($prize['trd_val'], true) : $prize['trd_val'],
                    'num' => $prize['trd_num']
                ];
            }


            $apply_member = function ($member_id, $type, & $p, $code) {
                $info = [];

                if ($p['type'] == 2) {
                    $val = array_shift($p['val']);
                    $info['cardnum'] = $val['cardnum'];
                    $info['cardpass'] = $val['cardpass'];
                } elseif ($p['type'] == 3) {
                    $val = array_shift($p['val']);
                    $info['pvalue'] = $val;
                } else {
                    $info['pvalue'] = $p['val'];
                }
                $info['code'] = $code;

                $info = [
                    'member_id' => $member_id,
                    'type' => $type,
                    'ptype' => $p['type'],
                    'info' => $info
                ];
                $p['num']--;

                return $info;
            };


            $wininfo = [];
            foreach ($order as $value) {
                if ($fir['num'] > 0) {
                    $wininfo[] = $apply_member($value['member_id'], 'fir', $fir, $value['code']);
                    continue;
                }
                if ($sec['num'] > 0) {
                    $wininfo[] = $apply_member($value['member_id'], 'sec', $sec, $value['code']);
                    continue;
                }

                if ($trd['num'] > 0) {
                    $wininfo[] = $apply_member($value['member_id'], 'trd', $trd, $value['code']);
                    continue;
                }
            }

            foreach ($wininfo as $value) {

                if ($value['ptype'] == 1) {
                    $bag = pdo_get(prefix_table('cj_red_bag'), ['prize_id' => $prize_id, 'status' => 1]);
                    if (empty($bag)) {
                        continue;
                    }
                    if (!$this->change($value['member_id'], $bag['red_bag_money'], 6)) {
                        throw new Exception($this->error);
                    }
                    if (!pdo_update(prefix_table('cj_red_bag'), ['receive_member_id' => $value['member_id'], 'status' => 2], ['id' => $bag['id']])) {
                        throw new Exception('领取红包失败');
                    }
                }

                if ($value['ptype'] == 3) {
                    $result = pdo_get(prefix_table('cj_voucher'), ['voucher' => $value['info']['pvalue']]);
                    if ($result) {
                        $voucher = [
                            'member_id' => $value['member_id'],
                            'voucher' => $value['info']['pvalue'],
                            'shop_id' => $result['shop_id'],
                            'goods_id' => $result['goods_id'],
                            'created' => time()
                        ];
                        if (!pdo_insert(prefix_table('cj_member_voucher'), $voucher)) {
                            throw new Exception('插入优惠券失败');
                        }
                    }

                }

                $result = [
                    'prize_id' => $prize_id,
                    'member_id' => $value['member_id'],
                    'type' => $value['type'],
                    'ptype' => $value['ptype'],
                    'addtime' => time()
                ];
                $result = array_merge($result, $value['info']);
                if (!pdo_insert(prefix_table('cj_prize_result'), $result)) {
                    throw new Exception('插入失败中奖信息');
                }

            }

            $update = [
                'status' => 1,
                'open_time' => time()
            ];
            if (!pdo_update(prefix_table('cj_prize'), $update, ['id' => $prize_id])) {
                throw new Exception('更改开奖活动状态失败');
            }
            $t = [
                'relation_id' => $prize_id,
                'created' => time()
            ];
            pdo_insert(prefix_table('cj_template_message'), $t);
            pdo_commit();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            pdo_rollback();
            return false;
        }
        return true;
    }


    public function add($data)
    {
        $insert = [
            'title' => $data['title'],
            'uname' => $data['uname'],
            //'wechat_no' => $data['wechat_no'],
            'typevalue' => $data['opentime'],
            'max_group_num' => $data['max_group_num'],
            'fir_ptype' => $data['fir_ptype'],
            'fir_num' => $data['fir_num'],
            'fir_val' => $data['fir_val'],
            'sec_ptype' => $data['sec_ptype'],
            'sec_num' => $data['sec_num'],
            'sec_val' => $data['sec_val'],
            'trd_ptype' => $data['trd_ptype'],
            'trd_num' => $data['trd_num'],
            'trd_val' => $data['trd_val'],
            'is_global' => $data['is_global'],
            'description' => $data['description'],
            'created' => $data['created'],
            'attach_id' => $data['attach_id'],
            'brief_description' => $data['brief_description'],
            'type' => $data['type'],
            'desc_type' => $data['desc_type'],
            'fir_cname' => $data['fir_cname'],
            'sec_cname' => $data['sec_cname'],
            'trd_cname' => $data['trd_cname'],
            'created' => time()
        ];
        if ($data['copy_type'] == 2) {
            $insert['wechat_no'] = $data['wechat_no'];
            $insert['wechat_title'] = $data['wechat_title'];
        }

        if (pdo_insert(prefix_table('cj_prize'), $insert)) {
            if ($data['appid'] && $data['app_name'] && $data['copy_type'] == 1) {
                $program = [
                    'prize_id' => pdo_insertid(),
                    'app_name' => $data['app_name'],
                    'appid' => $data['appid'],
                    'path' => $data['path'],
                    'extraData' => $data['extraData'],
                ];
                pdo_insert(prefix_table('cj_jump_program'), $program);
            }
        }

        pdo_delete(prefix_table('cj_pre_prize'), ['prize_id' => $data['prize_id']]);

        return true;
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
        if ($money <= 0) {
            return true;
        }

        $member = pdo_get(prefix_table('cj_member'), ['id' => $member_id]);

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
        if (!pdo_update(prefix_table('cj_member'), $update, ['id' => $member_id])) {
            $this->error = '余额变动失败';
            return false;
        }

        $money_log = [
            'member_id' => $member_id,
            'money' => $money,
            'type' => $type,
            'created' => time()
        ];
        if (pdo_insert(prefix_table('cj_money_log'), $money_log)) {
            return true;
        }

        $this->error = '日志更新出错';
        return false;

    }


    public function unifiedOrder($member_id, $money)
    {
        $trade_no = date("YmdHis", time()) . $member_id . mt_rand(1000, 9999);
        $pay = [
            'member_id' => $member_id,
            'trade_no' => $trade_no,
            'money' => $money,
            'crested' => time()
        ];

        if (pdo_insert(prefix_table('cj_pay_order'), $pay)) {
            return $trade_no;
        }
        return false;
    }


    public function bag($member_id, $prize_id, $money, $number)
    {
        $sql = 'INSERT INTO ' . tablename(prefix_table('cj_red_bag')) . ' (member_id,prize_id,red_bag_money,created) VALUES';
        $t = time();

        for ($i = 0; $i < $number; $i++) {
            $sql .= "({$member_id}, {$prize_id}, {$money}, {$t}),";
        }

        $sql = rtrim($sql, ',');
        if (!pdo_query($sql)) {
            $this->error = '设置失败';
            return false;
        }

        return true;



        $red = $this->getRedPackage($money, $number);

        if (empty($red)) {
            $this->error = '每个红包金额最少为0.01元';
            return false;
        }
        $sql = 'INSERT INTO ' . tablename('cj_red_bag') . ' (member_id,prize_id,red_bag_money,created) VALUES';
        $t = time();
        foreach ($red as $v) {
            $sql .= "({$member_id}, {$prize_id}, {$v}, {$t}),";
        }
        $sql = rtrim($sql, ',');
        if (!pdo_query($sql)) {
            $this->error = '设置失败';
            return false;
        }

        return true;
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


    /**
     * @param $member_id
     * @param $money
     * @param $relation_id
     * @param int $type
     */
    public function unifiedOrder1($member_id, $money, $relation_id, $type = 1)
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
        $info = pdo_get(prefix_table('cj_withdrawals'), ['id' => $id]);
        $member = pdo_get(prefix_table('cj_member'), ['id' => $info['member_id']]);

        $MerchPay = new MerchPay();
        $trade_no = date("YmdHis", time()) . $member['id'] . rand(100, 999);
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


}
