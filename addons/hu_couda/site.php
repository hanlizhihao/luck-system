<?php
/**
 * xcx_stcp模块微站定义
 *
 * @author 阿莫之家社区
 * @url http://www.0766city.com/
 */
defined('IN_IA') or exit('Access Denied');
include "model/prize.mod.php";
include "lib/Select.mysql.php";

class hu_coudaModuleSite extends WeModuleSite {


    public function __construct()
    {
        global $_GPC, $_W;
//var_dump($_W['user']['type']);exit;
        define('PREFIX', prefix($_W['uniacid']));
        update_open_more();

        if (isset($_GPC['debug']) && $_GPC['debug'] == 'debug') {
            var_export($_GPC);
            var_export($_W);
            exit;
        }

    }


    public function doWebOpenMore()
    {
        global $_GPC, $_W;
        $op = $this->get('op');
        if ($op == 'open_up') {
            $token = $this->get('token');
            if (empty($token)) {
                message('请填写开通码', $this->createWeburl('openMore'), 'error');
            }
            $p = new prize();
            if ($p->moreOpen($token, $_W['uniacid'])) {
                message('开通成功', $this->createWeburl('openMore'));
            }
            message($p->error, $this->createWeburl('openMore'), 'error');
        }
        $is_more = PREFIX;
        include $this->template('open_more');
    }

    public function doWebRecommend()
    {
        global $_GPC, $_W;
        $pindex = max(1, intval($this->get('page')));
        $psize = 20;
        $list = pdo_fetchall('SELECT * FROM '.tablename(prefix_table('cj_home_recommend'))." ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);

        $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_home_recommend')));

        if ($list) {
            foreach ($list as & $value) {
                $value['member'] = pdo_get(prefix_table('cj_member'), ['id' => $value['member_id']]);
                $value['created'] = date('Y-m-d H:i:s', $value['created']);
            }
        }

        $pager = pagination($total, $pindex, $psize);

        include $this->template('recommend');
    }


    public function doWebShop()
    {
        global $_GPC, $_W;
        $op = $this->get('op');
        if ($op == 'add') {
            $shop_name = $this->get('shop_name');
            if (empty($shop_name)) {
                message('请填写商家名');
            }
            pdo_insert(prefix_table('cj_shop'), ['shop_name' => $shop_name, 'created' => time()]);
            message('添加成功', $this->createWeburl('shop'));
        }
        elseif ($op == 'delete') {
            $id = $this->get('id');
            pdo_update(prefix_table('cj_shop'), ['is_del' => 1], ['id' => $id]);
            json('删除成功');
        }
        elseif ($op == 'addshow') {
            include $this->template('add_shop');
        } else {
            $pindex = max(1, intval($this->get('page')));
            $psize = 10;
            $list = pdo_fetchall('SELECT * FROM '.tablename(prefix_table('cj_shop'))." WHERE is_del=0 ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);

            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_shop')) . ' WHERE is_del=0');

            $pager = pagination($total, $pindex, $psize);

            include $this->template('shop');
        }
    }


    public function doWebGoods()
    {
        global $_GPC, $_W;
        $op = $this->get('op');
        if ($op == 'add') {
            $shop_id = $this->get('shop_id');
            $goods_name = $this->get('goods_name');
            if (empty($shop_id) || empty($goods_name)) {
                message('缺少必要参数');
            }
            $insert = [
                'goods_name' => $goods_name,
                'shop_id' => $shop_id,
                'created' => time()
            ];

            if (pdo_insert(prefix_table('cj_goods'), $insert)) {
                message('添加成功', $this->createWeburl('goods'));
            }
            message('添加失败');
        }
        elseif ($op == 'addshow') {
            $shop = pdo_getall(prefix_table('cj_shop'), ['is_del' => 0]);
            if (empty($shop)) {
                message('请先添加商家', $this->createWeburl('shop'));
            }
            include $this->template('add_goods');
        }
        elseif ($op == 'createShow') {
            $id = $this->get('id');
            $goods = pdo_get(prefix_table('cj_goods'), ['id' => $id]);
            if (empty($goods)) {
                message('卡券不存在', $this->createWeburl('goods'));
            }
            include $this->template('voucher');
        }
        elseif ($op == 'create') {
            $id = $this->get('goods_id');
            $goods = pdo_get(prefix_table('cj_goods'), ['id' => $id]);
            if (empty($goods)) {
                message('卡券不存在', $this->createWeburl('goods'));
            }
            $voucher_num = $this->get('voucher_num');
            if ($voucher_num < 1) {
                message('参数错误');
            }
            $prize = new prize();
            $prize->create_voucher($id, $goods['shop_id'], $voucher_num);
            message('生成成功', $this->createWeburl('goods'));
        }
        elseif ($op == 'details') {
            $id = $this->get('id');
            $goods = pdo_get(prefix_table('cj_goods'), ['id' => $id]);
            if (empty($goods)) {
                message('卡券不存在', $this->createWeburl('goods'));
            }
            $pindex = max(1, intval($this->get('page')));
            $psize = 10;
            $list = pdo_fetchall('SELECT * FROM '.tablename(prefix_table('cj_voucher'))." WHERE goods_id={$id} ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);

            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_voucher')) . " WHERE goods_id={$id}");

            $status_msg = [
                0 => '未使用',
                1 => '已发出',
                2 => '已核销',
                3 => '已核销',
            ];
            $pager = pagination($total, $pindex, $psize);
            include $this->template('voucher_details');
        }
        else if ($op == 'delete') {
            $id = $this->get('id');
            pdo_delete(prefix_table('cj_goods'), ['id' => $id]);
            json(1);
        }
        else {

            $pindex = max(1, intval($this->get('page')));
            $psize = 10;
            $list = pdo_fetchall('SELECT * FROM '.tablename(prefix_table('cj_goods'))." ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);

            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_goods')));
            if ($list) {
                foreach ($list as & $value) {
                    $value['shop'] = pdo_get('cj_shop', ['id' => $value['shop_id']]);
                    $value['not_used'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_voucher')) . " WHERE goods_id={$value['id']} AND status=0");
                    $value['used'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_voucher')) . " WHERE goods_id={$value['id']} AND status=1");
                    $value['write_off'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_voucher')) . " WHERE goods_id={$value['id']} AND status=2");
                }
            }

            $pager = pagination($total, $pindex, $psize);

            include $this->template('goods');
        }
    }




    public function doWebTpl()
    {
        global $_GPC, $_W;
        include "lib/Wxadoc.php";
        $option = [
            'appid' => $_W['uniaccount']['key'],
            'secret' => $_W['uniaccount']['secret'],
        ];
        $wxObj = new Wxadoc($option);
        $cj_member = tablename(prefix_table('cj_member'));
        global $_GPC, $_W;
        if ($_W['ispost']) {
            $template_id = $this->get('template_id');
            $keyword = $this->get('keyword');
            $member_id = $this->get('member_id');
            $path = $this->get('path');

            $data = [];
            foreach ($keyword as $k => $value) {
                $key = 'keyword' . ($k + 1);
                $data[$key] = ['value' => $value];
            }

            if ($member_id == 0) {
                $sql = "SELECT * FROM {$cj_member} WHERE is_robot=0";
            } else {
                $sql = "SELECT * FROM {$cj_member} WHERE id={$member_id} AND is_robot=0";
            }
            $member = pdo_fetchall($sql);
            if ($member) {
                $time = time() - (7*86400);
                foreach ($member as $value) {
                    $form_id = pdo_fetch("SELECT * FROM " . tablename(prefix_table('cj_form_id')) . " WHERE member_id={$value['id']} AND created > {$time}");
                    if (empty($form_id)) {
                        continue;
                    }
                    $wxObj->sendTemplateMessage($value['openid'], $template_id, $data, $form_id['form_id'], $path);
                    pdo_delete(prefix_table('cj_form_id'), ['id' => $form_id['id']]);
                    //pdo_update('cj_member', ['form_id' => ''], ['id' => $value['id']]);
                }

            }
            message('发送成功');
        }

        $list = $wxObj->getTplList();

        if ($list) {
            foreach ($list as & $value) {
                $content = str_replace('\n', '', $value['content']);
                $t = '{{keyword1.DATA}}';
                $c = explode($t, $content);
                $str = $c[1];
                $keyword = [$c[0]];
                $i = 2;
                while (strpos($str, '{{keyword' . $i .'.DATA}}') !== false) {
                    $c = explode('{{keyword' . $i .'.DATA}}', $str);
                    $keyword[] = $c[0];
                    $str = $c[1];
                    $i++;
                }
                $value['keyword'] = $keyword;
            }
        }

        $sql = "SELECT * FROM {$cj_member} WHERE is_robot=0";
        $member = pdo_fetchall($sql);
        $list_json = json_encode($list);


        include $this->template('tpl');
    }



    public function doWebConfig() {
        global $_GPC, $_W;
        $cj_config = prefix_table('cj_config');
        if ($_W['ispost']) {
            $title = $this->get('title');
            pdo_update($cj_config, ['value' => $title], ['key' => 'title']);
            pdo_update($cj_config, ['value' => $this->get('robot')], ['key' => 'robot']);
            pdo_update($cj_config, ['value' => $this->get('red_bag')], ['key' => 'red_bag']);
            pdo_update($cj_config, ['value' => $this->get('apply_number')], ['key' => 'apply_number']);
            pdo_update($cj_config, ['value' => $this->get('open_prize_notice')], ['key' => 'open_prize_notice']);
            pdo_update($cj_config, ['value' => $this->get('switch_examine')], ['key' => 'switch_examine']);

            pdo_update($cj_config, ['value' => $this->get('red_package_fee')], ['key' => 'red_package_fee']);
            pdo_update($cj_config, ['value' => $this->get('pay_function')], ['key' => 'pay_function']);
            pdo_update($cj_config, ['value' => $this->get('is_release')], ['key' => 'is_release']);
            pdo_update($cj_config, ['value' => $this->get('release_msg')], ['key' => 'release_msg']);
            pdo_update($cj_config, ['value' => $this->get('advertisement_type')], ['key' => 'advertisement_type']);
            pdo_update($cj_config, ['value' => $this->get('home_recommendation')], ['key' => 'home_recommendation']);
            pdo_update($cj_config, ['value' => $this->get('is_oss')], ['key' => 'is_oss']);
            //json('修改成功');

            $path1 = ATTACHMENT_ROOT . $_W['uniacid'] . 'cert/';
            if (!file_exists($path1)) {
                @mkdir($path1,0777,true);
            }

            if (isset($_FILES['apiclient_cert']['tmp_name']) && file_exists($_FILES['apiclient_cert']['tmp_name'])) {
                $path = $path1 . 'apiclient_cert.pem';
                if (move_uploaded_file($_FILES['apiclient_cert']['tmp_name'], $path)) {
                    pdo_update($cj_config, ['value' => $path], ['key' => 'apiclient_cert']);
                }
            }
            if (isset($_FILES['apiclient_key']['tmp_name']) && file_exists($_FILES['apiclient_key']['tmp_name'])) {
                $path = $path1 . 'apiclient_key.pem';
                if (move_uploaded_file($_FILES['apiclient_key']['tmp_name'], $path)) {
                    pdo_update($cj_config, ['value' => $path], ['key' => 'apiclient_key']);
                }
            }

            if ($this->get('advertisement_type') == 0) {
                pdo_update($cj_config, ['value' => $this->get('advertisement')], ['key' => 'advertisement']);
            } else {
                $data = [
                    'image' => $this->get('single-image'),
                    'appId' => $this->get('appId'),
                    'xcx_path' => $this->get('xcx_path'),
                    'extradata' => $this->get('extradata'),
                ];
                pdo_update($cj_config, ['value' => json_encode($data)], ['key' => 'advertisement']);
            }
            $data = [
                'image' => $this->get('single-image1'),
                'appId' => $this->get('popup_appId'),
                'xcx_path' => $this->get('popup_xcx_path'),
                'extradata' => $this->get('popup_extradata')
            ];
            pdo_update($cj_config, ['value' => json_encode($data)], ['key' => 'popup_adv']);

        }

        $initial = function ($key, $default = 0) use (& $cj_config) {
            $config = pdo_get(prefix_table('cj_config'), ['key' => $key]);
            if (empty($config)) {
                $config = [
                    'key' => $key,
                    'value' => $default
                ];
                pdo_insert($cj_config, $config);
            }
            return $config;
        };
        $apply_number = $initial('apply_number', 0);
        $red_bag = $initial('red_bag', 0);
        $robot = $initial('robot', 0);
        $title = $initial('title', '');
        $apiclient_cert = $initial('apiclient_cert', '');
        $apiclient_key = $initial('apiclient_key', '');
        $open_prize_notice = $initial('open_prize_notice', '');
        $switch_examine = $initial('switch_examine', 0);
        $advertisement = $initial('advertisement', '');
        $red_package_fee = $initial('red_package_fee', 0);
        $red_package_fee = $initial('red_package_fee', 0);
        $pay_function = $initial('pay_function', 5);
        $is_release = $initial('is_release', 0);
        $release_msg = $initial('release_msg', '无发布权限');
        $advertisement_type = $initial('advertisement_type', 0);
        $home_recommendation = $initial('home_recommendation', 1500);
        $popup_adv = $initial('popup_adv', '');
        $is_oss = $initial('is_oss', 0);

        if ($popup_adv) {
            $popup_adv['value'] = json_decode($popup_adv['value'], true);
            $popup_adv['value']['image_url'] = $this->getImage($popup_adv['value']['image']);
        }

        if ($advertisement_type['value'] == 1) {
            $advertisement['value'] = json_decode($advertisement['value'], true);
            $advertisement['value']['image_url'] = $this->getImage($advertisement['value']['image']);
        }

        $upload = "/app/index.php?i={$_W['uniacid']}&c=entry&op=receive_card&do=upload&m={$_GPC['m']}&a=wxapp";
        $image = "/app/index.php?i={$_W['uniacid']}&c=entry&op=receive_card&do=image&m={$_GPC['m']}&a=wxapp";


        $web_title=$_W['current_module']['title'];
        //include $this->template('header');
        include $this->template('config');
    }

    public function getImage($route, $path = false)
    {
        if (empty($route)) {
            return '';
        }
        global $_GPC, $_W;
        if (is_numeric($route)) {
            $image = pdo_get(prefix_table('cj_resource'), ['id' => $route]);
            $route = $image['route'];
        }
        if ($path == true) {
            return $route;
        }
        if ($this->_is_oss()) {
            return $_W['attachurl'] . $route;
        }

        return $_W['siteroot'] . '/attachment/' . $route;
    }
    private function _is_oss()
    {
        global $_GPC, $_W;
        $is_oss = pdo_get(prefix_table('cj_config'), ['key' => 'is_oss']);
        if ($is_oss) {
            $is_oss = $is_oss['value'];
        } else {
            $is_oss = 0;
        }
        return !empty($_W['setting']['remote']['type']) && $is_oss == 0;
    }


    public function doWebQuestion()
    {
        global $_GPC, $_W;
        $op = $this->get('op');
        if ($op == 'addshow') {
            include $this->template('add_common_problem');
        }
        elseif ($op == 'delete') {
            $id = $this->get('id');
            pdo_delete(prefix_table('cj_common_problem'), ['id' => $id]);
            json('');
        }
        elseif ($op == 'edit_show') {
            $id = $this->get('id');
            $question = pdo_get(prefix_table('cj_common_problem'), ['id' => $id]);
            if (empty($question)) {
                message('问题不存在', $this->createWeburl('question'));
            }
            include $this->template('question_edit_show');
        }
        elseif ($op == 'edit') {
            $id = $this->get('id');
            $title = $this->get('title');
            $describe = $this->get('describe');

            pdo_update(prefix_table('cj_common_problem'), ['title' => $title, 'describe' => $describe], ['id' => $id]);
            message('修改成功', $this->createWeburl('question'));
        }

        else {
            if ($op == 'add') {
                $title = $this->get('title');
                $describe = $this->get('describe');
                if (!empty($title) && !empty($describe)) {
                    pdo_insert(prefix_table('cj_common_problem'), ['title' => $title, 'describe' => $describe]);
                }
            }

            $question = pdo_getall(prefix_table('cj_common_problem'));
            include $this->template('common_problem');
        }
    }



    public function doWebXcx()
    {
        global $_GPC, $_W;
        $op = $this->get('op');
        if ($op == 'addshow') {
            include $this->template('add_xcx');
        }
        elseif ($op == 'delete') {
            $id = $this->get('id');
            pdo_delete(prefix_table('cj_program'), ['id' => $id]);
            json('');
        } else {

            if ($op == 'add') {
                $name = $this->get('name');
                $appid = $this->get('appid');
                if (!empty($name) && !empty($appid)) {
                    pdo_insert(prefix_table('cj_program'), ['name' => $name, 'appid' => $appid]);
                }
            }

            $data = pdo_getall(prefix_table('cj_program'));
            include $this->template('xcx');
        }

    }


    public function doWebMember()
    {
        global $_GPC, $_W;
        $op = $this->get('op');
        if ($op == 'gl') {
            $id = $this->get('id');
            $member = pdo_get(prefix_table('cj_member'), ['id' => $id]);
            if (empty($member)) {
                message('用户不存在', $this->createWeburl('member'));
            }
            $shop = pdo_getall(prefix_table('cj_shop'), ['is_del' => 0]);
            if (empty($shop)) {
                message('还没有店铺,请先添加', $this->createWeburl('member'));
            }

            include $this->template('member_gl');
        }
        elseif ($op == 'addgl') {
            $shop_id = $this->get('shop_id');
            $shop = pdo_get(prefix_table('cj_shop'), ['id' => $shop_id, 'is_del' => 0]);
            if (empty($shop)) {
                message('该店铺不存在', $this->createWeburl('member'));
            }
            pdo_update(prefix_table('cj_member'), ['shop_id' => $shop_id], ['id' => $this->get('id')]);
            message('关联成功', $this->createWeburl('member'));
        }
        elseif ($op == 'is_release') {
            $id = $this->get('id');
            $is_release = $this->get('is_release');
            if (pdo_update(prefix_table('cj_member'), ['is_release' => $is_release], ['id' => $id])) {
                json(1);
            }
            json('', 0);
        }
        elseif ($op == 'cancel') {
            if (!$id = $this->get('id')) {
                json('参数错误', 0);
            }
            pdo_update(prefix_table('cj_member'), ['shop_id' => 0], ['id' => $id]);
            json(1);
        }
        else {

            $keyword = $this->get('keyword');
            $where = "is_robot = 0";
            if (!empty($keyword)) {
                $where .= " AND nickname LIKE '%{$keyword}%'";
            }

            $pindex = max(1, intval($this->get('page')));
            $psize = 20;
            $list = pdo_fetchall('SELECT * FROM '.tablename(prefix_table('cj_member'))." WHERE {$where} ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);

            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_member')) . " WHERE {$where}");

            if ($list) {
                foreach ($list as & $value) {
                    $value['shop'] = $value['shop_id'] ? pdo_get(prefix_table('cj_shop'), ['id' => $value['shop_id']]) : [];
                }
            }

            $pager = pagination($total, $pindex, $psize);

            include $this->template('member');
        }
    }


    public function doWebIndex()
    {
        global $_GPC, $_W;
        $op = $this->get('op');

        if ($op == 'delete') {
            $id = $this->get('id');
            if (pdo_delete(prefix_table('cj_pre_prize'), ['prize_id' => $id])) {
                json('删除成功');
            }
            json('删除失败', 0);
        }

        $data = pdo_getall(prefix_table('cj_pre_prize'));
        if ($data) {
            foreach ($data as & $value) {
                $value['opentime'] = $value['type'] == 1 ? date('Y-m-d H:i:s', $value['opentime']) : $value['opentime'];
            }
        }

        include $this->template('index');

    }

    public function doWebAdd()
    {
        global $_GPC, $_W;
        $op = $this->get('op');
        $priae = new prize();
        if ($op == 'add') {
            $data = [];
            $data['uname'] = $this->get('uname');
            $data['title'] = $this->get('title');
            $data['description'] = $this->get('description');

            $data['fir_val'] = $this->get('fir_val');

            $data['fir_ptype'] = $this->get('fir_ptype');
            $data['fir_num'] = $this->get('fir_num');
            $data['sec_val'] = $this->get('sec_val');
            $data['sec_ptype'] = $this->get('sec_ptype');

            $data['sec_num'] = $this->get('sec_num');
            $data['trd_val'] = $this->get('trd_val');
            $data['trd_ptype'] = $this->get('trd_ptype');
            $data['trd_num'] = $this->get('trd_num');
            $data['max_group_num'] = 0;//$this->get('max_group_num');
            $data['attach_id'] = $this->get('attach_id');

            $data['path'] = $this->get('path');
            $data['appid'] = $this->get('appid');
            $data['app_name'] = $this->get('app_name');
            $data['wechat_no'] = $this->get('wechat_no');
            $data['brief_description'] = $this->get('brief_description');
            $data['type'] = $this->get('type');
            $data['copy_type'] = $this->get('copy_type');

            $data['desc_type'] = $this->get('desc_type');
            if ($data['desc_type'] == 1) {
                $data['description'] = implode(',', $this->get('images', []));
            }

            if ($data['type'] == 1) {
                $data['opentime'] = strtotime($this->get('opentime'));
            } else {
                $data['opentime'] = $this->get('opentime');
                if ($data['trd_num'] + $data['fir_num'] + $data['sec_num'] > $data['opentime']) {
                    json('开奖人数小于奖品数', 0);
                }
            }

            if ($data['copy_type'] == 2) {
                $data['wechat_no'] = $this->get('wechat_no');
                $data['wechat_title'] = $this->get('wechat_title');
            }

            try{
                pdo_begin();
                if ($data['fir_ptype'] == 3) {
                    $data['fir_cname'] = $data['fir_val'];
                    $voucher_id = $this->get('fir_voucher');
                    $goods = pdo_get(prefix_table('cj_goods'), ['id' => $voucher_id]);
                    $vou = $priae->deduction($goods['shop_id'], $voucher_id, $data['fir_num']);
                    if (!$vou) {
                        throw new Exception('优惠券' . $goods['goods_name'] . '数量不足');
                    }
                    $data['fir_val'] = json_encode($vou);
                }
                if ($data['sec_ptype'] == 3) {
                    $data['sec_cname'] = $data['sec_val'];
                    $voucher_id = $this->get('sec_voucher');
                    $goods = pdo_get(prefix_table('cj_goods'), ['id' => $voucher_id]);
                    $vou = $priae->deduction($goods['shop_id'], $voucher_id, $data['sec_num']);
                    if (!$vou) {
                        throw new Exception('优惠券' . $goods['goods_name'] . '数量不足');
                    }
                    $data['sec_val'] = json_encode($vou);
                }
                if ($data['trd_ptype'] == 3) {
                    $data['trd_cname'] = $data['trd_val'];
                    $voucher_id = $this->get('trd_voucher');
                    $goods = pdo_get(prefix_table('cj_goods'), ['id' => $voucher_id]);
                    $vou = $priae->deduction($goods['shop_id'], $voucher_id, $data['trd_num']);
                    if (!$vou) {
                        throw new Exception('优惠券' . $goods['goods_name'] . '数量不足');
                    }
                    $data['trd_val'] = json_encode($vou);
                }

                $keys= $_GET['keys'];
                $vals= $_GET['vals'];

                if(!empty($keys)) {
                    foreach ($keys as $k => $key) {
                        $extraData[$key] = $vals[$k];
                    }
                    $data['extraData'] = json_encode($extraData);
                }  else {
                    $data['extraData'] = '';
                }

                if (empty($data['title'])
                    || empty($data['fir_val'])
                    || empty($data['fir_num'])
                ) {
                    throw new Exception('请填写完整');
                }
                if ($data['opentime'] <= time() && $data['type'] == 1) {
                    throw new Exception('开奖时间已过');
                }
                if (!pdo_insert(prefix_table('cj_pre_prize'), $data)) {
                    throw new Exception('发布失败');
                }
                pdo_commit();
                json('发布成功');
            } catch (Exception $e) {
                json($e->getMessage(), 0);
                pdo_rollback();
            }
        }


        $goods = pdo_getall(prefix_table('cj_goods'));
        if ($goods) {
            foreach ($goods as $k => $value) {
                $not_used = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_voucher')) . " WHERE goods_id={$value['id']} AND status=0");//var_dump($not_used);
                if ($not_used < 1) {
                    unset($goods[$k]);
                    continue;
                }
                $goods[$k]['not_used'] = $not_used;
            }
        }

        $uniacid = $_W['uniacid'];
        $name = $this->get('m');
        include $this->template('add');
    }



    public function doWebStatistics()
    {
        global $_GPC, $_W;
        $op = $this->get('op');
        if ($op == 'in_prize') {
            $id = $this->get('id');
            $prize = pdo_get(prefix_table('cj_prize'), ['id' => $id]);
            if (!empty($prize)) {
                $result = pdo_getall(prefix_table('cj_prize_result'), ['prize_id' => $id]);
                if ($result) {
                    foreach ($result as &$value) {
                        $value['address'] = pdo_get(prefix_table('cj_address'), ['member_id' => $value['member_id']]);
                        $value['minfo'] = pdo_get(prefix_table('cj_member'), ['id' => $value['member_id']]);
                    }
                }
                $type = [
                    'fir' => '一等奖',
                    'sec' => '二等奖',
                    'trd' => '三等奖',
                ];
                include $this->template('prize');

            }
        } elseif ($op == 'apply'){
            $id = $this->get('id');
            $prize = pdo_get(prefix_table('cj_prize'), ['id' => $id]);
            if (empty($prize)) {
                message('抽奖不存在', $this->createWeburl('statistics'));
            }
            $keyword = $this->get('keyword');
            $where = '1=1';
            if (!empty($keyword)) {
                $cj_member = tablename(prefix_table('cj_member'));
                $member = pdo_fetchall("SELECT * FROM {$cj_member} WHERE nickname LIKE '%{$keyword}%'");

                if ($member) {
                    $ids = [];
                    foreach ($member as $value) {
                        $ids[] = $value['id'];
                    }
                    $where = 'member_id in(' . implode(',', $ids) . ')';
                } else {
                    include $this->template('apply');
                    exit;
                }
            }

            $psize = 10;
            $pindex = max(1, intval($this->get('page')));
            $list = pdo_fetchall('SELECT * FROM '.tablename(prefix_table('cj_order'))." WHERE prize_id={$id} AND {$where} ORDER BY order_id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);

            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_order')) . "  WHERE prize_id={$id}");

            $pager = pagination($total, $pindex, $psize);

            if ($list) {
                foreach ($list as & $value) {
                    $value['member'] = pdo_get(prefix_table('cj_member'), ['id' => $value['member_id']]);
                    $value['appoint'] = pdo_get(prefix_table('cj_appoint'), ['order_id' => $value['order_id'], 'prize_id' => $value['prize_id']]);
                }
            }

            //var_dump($list);exit;


            include $this->template('apply');
        }
        elseif ($op == 'appoint') {
            $ranking = $this->get('ranking');
            $order_id = $this->get('order_id');
            $prize_id = $this->get('prize_id');

            if (is_numeric($ranking)) {
                pdo_delete(prefix_table('cj_appoint'), ['order_id' => $order_id, 'prize_id' => $prize_id]);
                json('');
            }
            $order = pdo_get(prefix_table('cj_order'), ['order_id' => $order_id]);
            $prize = pdo_get(prefix_table('cj_prize'), ['id' => $prize_id]);

            if (empty($order) || empty($prize)) {
                json('参数错误', 0);
            }
            if ($prize['status'] == 1 || $prize['is_cancel'] == 1) {
                json('该抽奖已经结束', 0);
            }
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_appoint')) . "  WHERE prize_id={$prize_id} AND ranking='{$ranking}'");


            if ($total >= $prize[$ranking . '_num']) {
                json('设置失败,该奖数量不足', 0);
            }

            pdo_delete(prefix_table('cj_appoint'), ['order_id' => $order_id]);

            $data = [
                'order_id' => $order_id,
                'prize_id' => $prize_id,
                'member_id' => $order['member_id'],
                'ranking' => $ranking,
                'created' => time()
            ];
            pdo_insert(prefix_table('cj_appoint'), $data);
            json('设置成功');
        }

        elseif ($op == 'delete') {
            $id = $this->get('id');
            pdo_delete(prefix_table('cj_prize_result'), ['prize_id' => $id]);
            pdo_delete(prefix_table('cj_order'), ['prize_id' => $id]);
            pdo_delete(prefix_table('cj_prize'), ['id' => $id]);

            json('删除成功');
        }

        elseif ($op == 'details') {
            $id = $this->get('id');
            $prize = pdo_get(prefix_table('cj_prize'), ['id' => $id]);
            if ($prize) {
                $prize['jump_program'] = pdo_get(prefix_table('cj_jump_program'), ['prize_id' => $prize['id']]);
                $image = pdo_get(prefix_table('cj_resource'), ['id' => $prize['attach_id']]);
                global $_W;
                $prize['attach_img'] = $_W['attachurl'] . $image['route'];
            }
            //$name = $this->get('m');
            include $this->template('details');
        }
        elseif ($op == 'write_off') {
            $id = $this->get('id');
            pdo_update(prefix_table('cj_prize_result'), ['is_write_off' => 1], ['result_id' => $id]);
            json('');
        }

        else {

            $is_global = $this->get('is_global', 0);
            $pindex = max(1, intval($this->get('page')));
            $psize = 10;
            $list = pdo_fetchall('SELECT * FROM '.tablename(prefix_table('cj_prize'))." WHERE is_global={$is_global} ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);

            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_prize')) . "  WHERE is_global={$is_global} ");

            $pager = pagination($total, $pindex, $psize);
            include "lib/Wxadoc.php";
            include "lib/Gd.class.php";
            include "lib/Image.class.php";
            $Image = new Image();
            $option = array(
                'appid' => $_W['uniaccount']['key'],
                'secret' => $_W['uniaccount']['secret'],
            );
            $wxObj = new Wxadoc($option);

            foreach ($list as &$value) {
                if ($value['type'] == 1) {
                    $value['typevalue'] = date('Y-m-d H:i:s', $value['typevalue']);
                    $value['types'] = '时间开奖';
                } else if ($value['type'] == 2) {
                    $value['types'] = '人数开奖';
                } else {
                    $value['types'] = '手动开奖';
                }
                if ($value['member_id'] > 0) {
                    $value['member'] = pdo_get(prefix_table('cj_member'), ['id' => $value['member_id']]);
                }

                if ($value['is_cancel'] == 0 && $value['status'] == 1) {
                    $value['in_prize'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_prize_result')) . " WHERE prize_id={$value['id']}");

                } else {
                    $value['in_prize'] = 0;
                }
                $value['prize_name'] =  $value['fir_ptype'] > 1 ? $value['fir_cname'] : $value['fir_val'];

                if (!file_exists(ATTACHMENT_ROOT . 'qr' . $_W['uniacid'])) {
                    @mkdir(ATTACHMENT_ROOT . 'qr' . $_W['uniacid'],0777,true);
                }

                $qrpath = ATTACHMENT_ROOT . 'qr' . $_W['uniacid'] . '/qr217' . $value['id'] . '.png';
                //echo $qrpath;
                //$value['qr_code'] = '';
                if (!is_file($qrpath)) {
                    $qr_temp = ATTACHMENT_ROOT . 'qr_temp' . $value['id'] . '.jpg';
                    $res = $wxObj->createwxaqrcode($value['id'], 'pages/partake/partake', 430, false, array('r' => 72, 'g' => 145, 'b' => 92));

                    if ($res) {
                        file_put_contents($qr_temp, $res);
                        $qrimg = $Image->open($qr_temp);
                        $qrimg->thumb(217, 217)->save($qrpath);
                    }

                    unlink($qr_temp);
                }
                $value['qr_code'] = $_W['siteroot'] . '/attachment/qr' . $_W['uniacid'] . '/qr217' . $value['id'] . '.png';
            }//exit;

            include $this->template($is_global ? 'statistics1' : 'statistics');
        }



    }


    public function doWebStatistics1()
    {
        global $_GPC, $_W;
        $op = $this->get('op');
        if ($op == 'in_prize') {
            $id = $this->get('id');
            $prize = pdo_get(prefix_table('cj_prize'), ['id' => $id]);
            if (!empty($prize)) {
                $result = pdo_getall(prefix_table('cj_prize_result'), ['prize_id' => $id]);
                if ($result) {
                    foreach ($result as &$value) {
                        $value['address'] = pdo_get(prefix_table('cj_address'), ['member_id' => $value['member_id']]);
                        $value['minfo'] = pdo_get(prefix_table('cj_member'), ['id' => $value['member_id']]);
                    }
                }
                $type = [
                    'fir' => '一等奖',
                    'sec' => '二等奖',
                    'trd' => '三等奖',
                ];
                include $this->template('prize');

            }
        } elseif ($op == 'apply'){
            $id = $this->get('id');
            $prize = pdo_get(prefix_table('cj_prize'), ['id' => $id]);
            if (empty($prize)) {
                message('抽奖不存在', $this->createWeburl('statistics1'));
            }
            $keyword = $this->get('keyword');
            $where = '1=1';
            if (!empty($keyword)) {
                $cj_member = tablename(prefix_table('cj_member'));
                $member = pdo_fetchall("SELECT * FROM {$cj_member} WHERE nickname LIKE '%{$keyword}%'");

                if ($member) {
                    $ids = [];
                    foreach ($member as $value) {
                        $ids[] = $value['id'];
                    }
                    $where = 'member_id in(' . implode(',', $ids) . ')';
                } else {
                    include $this->template('apply');
                    exit;
                }
            }

            $psize = 10;
            $pindex = max(1, intval($this->get('page')));
            $list = pdo_fetchall('SELECT * FROM '.tablename(prefix_table('cj_order'))." WHERE prize_id={$id} AND {$where} ORDER BY order_id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);

            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_order')) . "  WHERE prize_id={$id}");

            $pager = pagination($total, $pindex, $psize);

            if ($list) {
                foreach ($list as & $value) {
                    $value['member'] = pdo_get(prefix_table('cj_member'), ['id' => $value['member_id']]);
                    $value['appoint'] = pdo_get(prefix_table('cj_appoint'), ['order_id' => $value['order_id'], 'prize_id' => $value['prize_id']]);
                }
            }

            //var_dump($list);exit;


            include $this->template('apply');
        }
        elseif ($op == 'appoint') {
            $ranking = $this->get('ranking');
            $order_id = $this->get('order_id');
            $prize_id = $this->get('prize_id');

            if (is_numeric($ranking)) {
                pdo_delete(prefix_table('cj_appoint'), ['order_id' => $order_id, 'prize_id' => $prize_id]);
                json('');
            }
            $order = pdo_get(prefix_table('cj_order'), ['order_id' => $order_id]);
            $prize = pdo_get(prefix_table('cj_prize'), ['id' => $prize_id]);

            if (empty($order) || empty($prize)) {
                json('参数错误', 0);
            }
            if ($prize['status'] == 1 || $prize['is_cancel'] == 1) {
                json('该抽奖已经结束', 0);
            }
            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_appoint')) . "  WHERE prize_id={$prize_id} AND ranking='{$ranking}'");


            if ($total >= $prize[$ranking . '_num']) {
                json('设置失败,该奖数量不足', 0);
            }

            pdo_delete(prefix_table('cj_appoint'), ['order_id' => $order_id]);

            $data = [
                'order_id' => $order_id,
                'prize_id' => $prize_id,
                'member_id' => $order['member_id'],
                'ranking' => $ranking,
                'created' => time()
            ];
            pdo_insert(prefix_table('cj_appoint'), $data);
            json('设置成功');
        }

        elseif ($op == 'delete') {
            $id = $this->get('id');
            pdo_delete(prefix_table('cj_prize_result'), ['prize_id' => $id]);
            pdo_delete(prefix_table('cj_order'), ['prize_id' => $id]);
            pdo_delete(prefix_table('cj_prize'), ['id' => $id]);

            json('删除成功');
        }

        elseif ($op == 'details') {
            $id = $this->get('id');
            $prize = pdo_get(prefix_table('cj_prize'), ['id' => $id]);
            if ($prize) {
                $prize['jump_program'] = pdo_get(prefix_table('cj_jump_program'), ['prize_id' => $prize['id']]);
                $image = pdo_get(prefix_table('cj_resource'), ['id' => $prize['attach_id']]);
                global $_W;
                $prize['attach_img'] = $_W['attachurl'] . $image['route'];
            }
            //$name = $this->get('m');
            include $this->template('details');
        }
        elseif ($op == 'write_off') {
            $id = $this->get('id');
            pdo_update(prefix_table('cj_prize_result'), ['is_write_off' => 1], ['result_id' => $id]);
            json('');
        }

        else {

            $nickname = $this->get('nickname');

            $is_global = $this->get('is_global', 0);
            $pindex = max(1, intval($this->get('page')));
            $psize = 10;

            $where = "is_global={$is_global}";
            if (!empty($nickname)) {
                $users = pdo_fetchall("SELECT * FROM " . tablename(prefix_table('cj_member')) . " WHERE nickname LIKE '%{$nickname}%'");
                if ($users) {
                    $where .= ' AND member_id IN (';
                    foreach ($users as $value) {
                        $where .= "{$value['id']},";
                    }
                    $where = rtrim($where, ',') . ')';
                } else {
                    $where = '1>1';
                }
            }

            $list = pdo_fetchall('SELECT * FROM '.tablename(prefix_table('cj_prize'))." WHERE {$where} ORDER BY id DESC LIMIT ".($pindex - 1) * $psize.','.$psize);

            $total = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_prize')) . "  WHERE {$where} ");

            $pager = pagination($total, $pindex, $psize);
            include "lib/Wxadoc.php";
            include "lib/Gd.class.php";
            include "lib/Image.class.php";
            $Image = new Image();
            $option = array(
                'appid' => $_W['uniaccount']['key'],
                'secret' => $_W['uniaccount']['secret'],
            );
            $wxObj = new Wxadoc($option);

            foreach ($list as &$value) {
                if ($value['type'] == 1) {
                    $value['typevalue'] = date('Y-m-d H:i:s', $value['typevalue']);
                    $value['types'] = '时间开奖';
                } else if ($value['type'] == 2) {
                    $value['types'] = '人数开奖';
                } else {
                    $value['types'] = '手动开奖';
                }
                if ($value['member_id'] > 0) {
                    $value['member'] = pdo_get(prefix_table('cj_member'), ['id' => $value['member_id']]);
                }

                if ($value['is_cancel'] == 0 && $value['status'] == 1) {
                    $value['in_prize'] = pdo_fetchcolumn("SELECT COUNT(*) FROM ".tablename(prefix_table('cj_prize_result')) . " WHERE prize_id={$value['id']}");

                } else {
                    $value['in_prize'] = 0;
                }
                $value['prize_name'] =  $value['fir_ptype'] > 1 ? $value['fir_cname'] : $value['fir_val'];

                if (!file_exists(ATTACHMENT_ROOT . 'qr' . $_W['uniacid'])) {
                    @mkdir(ATTACHMENT_ROOT . 'qr' . $_W['uniacid'],0777,true);
                }

                $qrpath = ATTACHMENT_ROOT . 'qr' . $_W['uniacid'] . '/qr217' . $value['id'] . '.png';
                //echo $qrpath;
                //$value['qr_code'] = '';
                if (!is_file($qrpath)) {
                    $qr_temp = ATTACHMENT_ROOT . 'qr_temp' . $value['id'] . '.jpg';
                    $res = $wxObj->createwxaqrcode($value['id'], 'pages/partake/partake', 430, false, array('r' => 72, 'g' => 145, 'b' => 92));

                    if ($res) {
                        file_put_contents($qr_temp, $res);
                        $qrimg = $Image->open($qr_temp);
                        $qrimg->thumb(217, 217)->save($qrpath);
                    }

                    unlink($qr_temp);
                }
                $value['qr_code'] = $_W['siteroot'] . '/attachment/qr' . $_W['uniacid'] . '/qr217' . $value['id'] . '.png';
            }//exit;

            include $this->template($is_global ? 'statistics1' : 'statistics');
        }



    }

    private function get($key, $default = '')
    {
        global $_GPC;
        return isset($_GPC[$key]) ? $_GPC[$key] : $default;
    }




}

function json($info, $status = 1)
{
    $info = [
        'info' => $info,
        'status' => $status
    ];
    $info = json_encode($info, JSON_UNESCAPED_UNICODE);
    header('Content-Type: application/json;charset=utf-8');
    die($info);
}
