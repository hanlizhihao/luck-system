<?php

/**
 * [WeEngine System] Copyright (c) 2013 WE7.CC
 * User: fanyk
 * Date: 2017/12/10
 * Time: 14:46.
 */
include "model/prize.mod.php";
include "lib/Wxadoc.php";
include "lib/WxpayAPI_php_v3/WxpayAPI_php_v3.php";
include "lib/Select.mysql.php";
define('LOCAL_IP',$_SERVER['SERVER_ADDR']);

class hu_coudaModuleWxapp extends WeModuleWxapp {


    private $gpc;
    private $w;
    private $member;
    public function __construct() {
//		parent::__construct();
        global $_W;
        global $_GPC;
        $this->gpc = $_GPC;
        $this->w = $_W;

        define('PREFIX', prefix($_W['uniacid']));
        update_open_more();
        //$this->uniacid = $_W['uniacid'];
        $noLogin = ['login', 'dorech', 'upload', 'image', 'open', 'share', 'config', 'advertisement', 'index', 'total', 'qrcode'];
        if (isset($_GPC['debug'])) {
            error_reporting(E_ALL);
        }
        if ($_GET['ceshi']) {
            var_dump($_GPC['do']);
            var_dump($noLogin);
            var_dump(in_array($_GPC['do'], $noLogin));
        }

        if (empty($_GPC['do']) || in_array($_GPC['do'], $noLogin)) {
            return true;
        }

        $trd_session = $this->get('trd_session');
        $res = pdo_get(prefix_table('cj_token'), ['token' => $trd_session]);
        if (empty($res)) {
            json('unlogin', 0);
        }

        $member_id = $res['member_id'];
        //$member_id = 7;
        if ($member_id >= 1) {
            $member = pdo_get(prefix_table('cj_member'), ['id' => $member_id]);

            if ($member) {
                $this->member = $member;

                $form_id = $this->get('formid');
                if ($form_id && $form_id != 'the formId is a mock one') {
                    pdo_insert(prefix_table('cj_form_id'), ['member_id' => $member_id, 'form_id' => $form_id, 'created' => time()]);
                }
                return true;
            }
        }
    }


    public function doPageQrcode()
    {
        $code = $this->get('code');
        if (empty($code)) {
            exit;
        }

        $path = ATTACHMENT_ROOT . 'images/qrcode' . $this->w['uniacid']. '/';
        if (!file_exists($path)) {
            @mkdir($path,0777,true);
        }

        $qrcode = $path . $code . '.png';
        if (!is_file($qrcode)) {
            include "lib/phpqrcode/phpqrcode.php";

            $value = $code;         //二维码内容
            $errorCorrectionLevel = 'L';  //容错级别
            $matrixPointSize = 8;      //生成图片大小
            //生成二维码图片
            QRcode::png($value,$qrcode , $errorCorrectionLevel, $matrixPointSize, 2);
            $QR = $qrcode;        //已经生成的原始二维码图片文件
            $QR = imagecreatefromstring(file_get_contents($QR));
            //输出图片
            imagepng($QR, $qrcode);
            imagedestroy($QR);
        }
        header("content-type:image/png");
        echo file_get_contents($qrcode);
        exit;
    }



    public function doPageAuthor()
    {
        if ($this->member['user_img'] || $this->member['nickname']) {
            json('');
        }
        json('', 2);
    }



    public function doPageRelease()
    {
        $is_release = $this->_release();
        $msg = '';
        if (!$is_release) {
            $msg = pdo_get(prefix_table('cj_config'), ['key' => 'release_msg']);
            $msg = $msg ? $msg['value'] : '无发布权限';
        }
        $data = [
            'is_release' => $is_release,
            'msg' => $msg
        ];
        json($data);
    }


    private function _release()
    {
        $config = pdo_get(prefix_table('cj_config'), ['key' => 'is_release']);
        $is_release = $config ? $config['value'] : 0;
        $is_release = $is_release == 1 ? $this->member['is_release'] : $is_release;
        return $is_release;
    }


    /**
     * 店铺优惠券
     */
    public function doPageVoucher()
    {
        $priae = new prize();
        $goods = $priae->is_voucher($this->member['id']);
        if ($goods) {
            json($goods);
        }
        json('', 2);
    }


    /**
     * 图片上传
     */
    public function doPageUpload()
    {
        $jietu = $this->get('jietu');
        if ($jietu) {
            $_FILES['file']['name'] = $_FILES['file']['name'] . '.png';
        }
        if (!isset($_FILES['file'])) {
            json('请上传图片', 0);
        }
        load()->func('file');
        $reslut = file_upload($_FILES['file']);
        if (isset($reslut['errno'])) {
            json($reslut['message'], 0);
        }
        $pic =  '/' . $reslut['path'];

        if ($this->_is_oss()) {
            $remotestatus = file_remote_upload($reslut['path']);
            if (is_error($remotestatus)) {
                json('远程附件上传失败', 0);
            }
        }

        $data = [
            'member_id' => $this->member['id'],
            'route' => $pic,
            'created' => time()
        ];
        if (!pdo_insert(prefix_table('cj_resource'), $data)) {
            json('领取失败', 0);
        }
        json(pdo_insertid());
    }


    public function doPageShare()
    {
        $id = $this->get('id');
        if (empty($id)) {
            exit;
        }
        $m = $this->gpc['m'];
        include "lib/Gd.class.php";
        include "lib/Image.class.php";
        $Image = new Image();
        $path = ATTACHMENT_ROOT . 'images/share' . $this->w['uniacid'];
        if (!file_exists($path)) {
            @mkdir($path,0777,true);
        }
        $sharepath = $path . '/share_sub_21' . $id . '.png';
        if (!is_file($sharepath)) {
            $bg = IA_ROOT . '/addons/'.$m.'/template/sharebg.png';

            $info = pdo_get(prefix_table('cj_prize'), ['id' => $id]);
            if (empty($info)) {
                exit;
            }

            $apath = IA_ROOT . '/addons/'.$m.'/template/attach2.jpg';

            if (!empty($info['attach_id'])) {
                $img = pdo_get(prefix_table('cj_resource'), ['id' => $info['attach_id']]);
                if ($img) {
                    $apath = ATTACHMENT_ROOT . $img['route'];
                    if ($this->_is_oss()) { // 判断系统是否开启了远程附件
                        file_put_contents($apath, file_get_contents($this->w['attachurl'] . $img['route']));
                    }
                }
            }
            $aimg = $Image->open($apath);
            $apath_temp = ATTACHMENT_ROOT . $id . '_sub_temp.png';
            $aimg->thumb(396, 196, 3)->save($apath_temp);
            $img = $Image->open($bg);
            $arr=array('fir','sec','trd');
            foreach($arr as $v)
            {
                $$v='';
                if(!empty($info[$v.'_num']))
                {
                    switch ($info[$v.'_ptype'])
                    {
                        case '0':
                            $$v=$info[$v.'_val'];
                            break;
                        case '1':
                            $$v=$info[$v.'_val'].'￥';
                            break;
                        case '2':
                            $$v=$info[$v.'_cname'];
                            break;
                    }
                }
            }
            $handle = $img->water($apath_temp, array(12, 12), 100);
            $addheight = 0;
            if ($trd) {

                $handle = $handle->text($trd, IA_ROOT . '/addons/'.$m.'/template/font/msyh.ttf', 16, '#000000', array(12, 279));
                $handle = $handle->text('*'.$info['trd_num'], IA_ROOT . '/addons/'.$m.'/template/font/msyh.ttf', 12, '#999999', array(370, 279));
            }

            if ($sec) {
                $addheight = - 30;
                $handle = $handle->text($sec, IA_ROOT . '/addons/'.$m.'/template/font/msyh.ttf', 16, '#000000', array(12, 285 + $addheight));
                $handle = $handle->text('*'.$info['sec_num'], IA_ROOT . '/addons/'.$m.'/template/font/msyh.ttf', 12, '#999999', array(370, 285 + $addheight));
            }
            if ($fir) {
                if(!$sec)
                {
                    $addheight =24;
                }

                $handle = $handle->text($fir, IA_ROOT . '/addons/'.$m.'/template/font/msyh.ttf', 16, '#000000', array(12, 255+$addheight));
                $handle = $handle->text('*'.$info['fir_num'], IA_ROOT . '/addons/'.$m.'/template/font/msyh.ttf', 12, '#999999', array(370, 255+$addheight));
            }
            $open_type = 1 == $info['type'] ? date('定时开奖时间Y-m-d H:i:s', $info['typevalue']) : (2 == $info['type'] ? '达到' . $info['typevalue'] . '人数开奖' : '发起者手动开奖');
            $handle = $handle->text($open_type, IA_ROOT . '/addons/'.$m.'/template/font/msyh.ttf', 12, '#9F9494', array(12, 302));
            $handle->save($sharepath);

            unlink($apath_temp);
        }

        header("content-type:image/png");
        echo file_get_contents($sharepath);
        exit;
    }




    public function doPageShareImg()
    {
        $id = $this->get('id');
        if (empty($id)) {
            exit;
        }

        include "lib/Gd.class.php";
        include "lib/Image.class.php";
        $Image = new Image();

        $path = ATTACHMENT_ROOT . 'images/shareImg' . $this->w['uniacid']. '/';
        if (!file_exists($path)) {
            @mkdir($path,0777,true);
        }

        $sharepath = $path . $this->member['id'] . '_share_201911' . $id  .'.png';
        if (!is_file($sharepath)) {

            if (!file_exists(ATTACHMENT_ROOT . 'qr' . $this->w['uniacid'])) {
                @mkdir(ATTACHMENT_ROOT . 'qr' . $this->w['uniacid'],0777,true);
            }

            $qrpath = ATTACHMENT_ROOT . 'qr' . $this->w['uniacid'] . '/qr217' . $id . '.png';
            if (!is_file($qrpath)) {
                $qr_temp = ATTACHMENT_ROOT . 'qr_temp' . $id . '.jpg';
                //$cfg = C('WXADOC_INFO.' . MY_PROJECT);
                $option = array(
                    'appid' => $this->w['uniaccount']['key'],
                    'secret' => $this->w['uniaccount']['secret'],
                );


                $wxObj = new Wxadoc($option);
                $res = $wxObj->createwxaqrcode($id, 'pages/partake/partake', 430, false, array('r' => 72, 'g' => 145, 'b' => 92));
                file_put_contents($qr_temp, $res);
                //file_put_contents($qr_temp, file_get_contents(IA_ROOT . '/addons/c_jiang/template/attach2.jpg'));
                $qrimg = $Image->open($qr_temp);
                $qrimg->thumb(217, 217)->save($qrpath);
//                $imgg = yuan_img($qr_temp);
//                imagepng($imgg, $qrpath);
//                imagedestroy($imgg);
                unlink($qr_temp);
            }
            //bg https://z.9xy.cn/Public/images/bag_bg.png
            $bg = IA_ROOT . '/addons/'.$this->gpc['m'].'/template/bg2.png';


            $prize = pdo_get(prefix_table('cj_prize'), ['id' => $id]);
            if (empty($prize)) {
                exit;
            }

            if (empty($prize['attach_id'])) {
                $apath = IA_ROOT . '/addons/'.$this->gpc['m'].'/template/attach2.jpg';
            } else {
                $apath =  ATTACHMENT_ROOT . $this->getImage($prize['attach_id'], true);
                if ($this->_is_oss()) { // 判断系统是否开启了远程附件
                    file_put_contents($apath, file_get_contents($this->w['attachurl'] . $this->getImage($prize['attach_id'], true)));
                }

            }


            //头像
            $headpath_temp = ATTACHMENT_ROOT . 'head' . $this->member['id'] . '.jpg';
            $headpath = ATTACHMENT_ROOT . 'head' . $this->member['id'] . '.png';
            file_put_contents($headpath_temp, file_get_contents($this->member['user_img']));
            $himg = $Image->open($headpath_temp);
            $himg->thumb(100, 100, 3)->save($headpath_temp);
            $imgg = yuan_img($headpath_temp);
            imagepng($imgg, $headpath);
            imagedestroy($imgg);
            unlink($headpath_temp);

            $aimg = $Image->open($apath);
            //$apath_temp 630 * 320
            $apath_temp = ATTACHMENT_ROOT . $id . '_temp.png';
            $aimg->thumb(654, 326, 3)->save($apath_temp);
            $img = $Image->open($bg);
            $nickname = $this->member['nickname'];
            $title = $prize['title'];
            $fir = !empty($prize['fir_num']) ? $prize['fir_ptype'] == '0' ? $prize['fir_val'] : $prize['fir_val'] . '元现金红包' : '';

            if ($prize['fir_ptype'] == '0') {
                $fir = $prize['fir_val'];
            } elseif ($prize['fir_ptype'] == 1) {
                $fir = $prize['fir_val'] . '元现金红包';
            } else {
                $fir = $prize['fir_cname'];
            }

            $sec = !empty($prize['sec_num']) ? $prize['sec_ptype'] == '0' ? $prize['sec_val'] : $prize['sec_val'] . '元现金红包' : '';
            $trd = !empty($prize['trd_num']) ? $prize['trd_ptype'] == '0' ? $prize['trd_val'] : $prize['trd_val'] . '元现金红包' : '';

            if ($prize['sec_ptype'] == '0') {
                $sec = $prize['sec_val'];
            } elseif ($prize['sec_ptype'] == 1) {
                $sec = $prize['sec_val'] . '元现金红包';
            } else {
                $sec = $prize['sec_cname'];
            }

            if ($prize['trd_ptype'] == '0') {
                $trd = $prize['trd_val'];
            } elseif ($prize['trd_ptype'] == 1) {
                $trd = $prize['trd_val'] . '元现金红包';
            } else {
                $trd = $prize['trd_cname'];
            }

//            $fir=$sec=$trd='';//暂时去掉文字 图片上写死
            $textWidth = imagefontwidth(24) * mb_strlen($fir);
//            exit;
            $handle = $img
                ->water($apath_temp, array(49, 266), 100)
                ->water($qrpath, array(265, 796), 100)
                ->water($headpath, array(326, 32), 100)//头像
            ;
            if ($title) {
                //$textWidth = 33 * mb_strlen($title);
                //$handle = $handle->text($title, IA_ROOT . '/addons/'.$this->gpc['m'].'/template/font/msyh.ttf', 24, '#ffffff', array(60, 520));
            }

            if ($fir) {
                $handle = $handle->text($fir.'*'.$prize['fir_num'], IA_ROOT . '/addons/'.$this->gpc['m'].'/template/font/msyh.ttf', 24, '#000000', array(60, 613));
            }
            if ($sec) {
                $handle=$handle->text($sec.'*'.$prize['sec_num'], IA_ROOT . '/addons/'.$this->gpc['m'].'/template/font/msyh.ttf', 24, '#000000', array(60, 650));
            }
            if ($trd) {
                $handle=$handle->text($trd.'*'.$prize['trd_num'], IA_ROOT . '/addons/'.$this->gpc['m'].'/template/font/msyh.ttf', 24, '#000000', array(60, 690));
            }
            $open_type = 1 == $prize['type'] ? date('Y-m-d H:s', $prize['typevalue']) : (2 == $prize['type'] ? '达到' . $prize['typevalue'] . '人自动开启' : '由发起者手动开启');
            $handle = $handle->text($open_type, IA_ROOT . '/addons/'.$this->gpc['m'].'/template/font/msyh.ttf', 18, '#9F9494', array(60, 741));
            $handle->save($sharepath);

            unlink($apath_temp);
        }
        header("content-type:image/png");
        echo file_get_contents($sharepath);
        exit;

    }

    public function doPageImage()
    {
        $id = $this->get('id');
        if (!is_numeric($id)) {
            json('参数错误', 0);
        }
        $image = pdo_get(prefix_table('cj_resource'), ['id' => $id]);
        if (empty($image)) {
            json('图片不存在', 0);
        }
        header("content-type:image/png");

        if ($this->_is_oss()) { // 判断系统是否开启了远程附件
            echo file_get_contents($this->w['attachurl'] . $image['route']);
        } else {
            echo file_get_contents(ATTACHMENT_ROOT . $image['route']);
        }
        die;
    }


    public function doPageLogin()
    {
        $code = $this->get('code');
        if (empty($code)) {
            json('参数错误');
        }

        $option = [
            'appid' => $this->w['uniaccount']['key'],
            'secret' => $this->w['uniaccount']['secret'],
        ];

        $wxObj = new Wxadoc($option);

        if (false === $res = $wxObj->jscode2session($code)) {
            json($wxObj->getError(), 0);
        }
        $member = pdo_get(prefix_table('cj_member'), ['openid' => $res['openid']]);
        if (empty($member)) {
            pdo_insert(prefix_table('cj_member'), ['openid' => $res['openid']]);
            $member['id'] = pdo_insertid();
        }

        $_SESSION['member_id'] = $member['id'];
        $_SESSION['openid'] = $res['openid'];
        $_SESSION['session_key'] = $res['session_key'];

        $token = md5($member['id'] . time() . '9xy');

        $insert = [
            'token' => $token,
            'member_id' => $member['id'],
            'created' => time()
        ];

        pdo_insert(prefix_table('cj_token'), $insert);

        json($token);
    }


    public function doPageRegister()
    {
        $member_id = $this->member['id'];
        if (!$this->get('nickname') || !$this->get('headimgurl')) {
            json('非法请求', 0);
        }

        $update = [
            'nickname' => $this->get('nickname'),
            'user_img' => $this->get('headimgurl'),
            'gender' => $this->get('gender'),
            'province' => $this->get('province'),
            'city' => $this->get('city'),
            'country' => $this->get('country'),
            'add_time' => time(),
            'set_time' => time()
        ];
        pdo_update(prefix_table('cj_member'), $update, ['id' => $member_id]);
        json('ok');
    }


    /**
     * 个人信息
     */
    public function doPageMy()
    {
        json($this->member);
    }


    public function doPageWithdrawals()
    {
        $money = $this->get('money', 0);

        if ($money < 1 or $money > 200) {
            json('提现金额为1-200元之间', 0);
        }

        $start = strtotime(date('Y-m-d', time()));
        $sql = 'SELECT COUNT(1) as count FROM ' . tablename(prefix_table('cj_withdrawals')) . ' WHERE created>' . $start;
        $res = pdo_fetch($sql);
        if (!empty($res) && $res['count'] > 2) {
            //json('每天提现不得超过3次', 0);
        }

        pdo_begin();
        try {
            $insert = [
                'member_id' => $this->member['id'],
                'money' => $money,
                'created' => time()
            ];
            $this->setPayment();

            $p = new prize();
            if (!$p->change($this->member['id'], $money, 2)) {
                throw new Exception($p->error);
            }
            if (!pdo_insert(prefix_table('cj_withdrawals'), $insert)) {
                throw new Exception('提现失败');
            }
            $insert_id = pdo_insertid();

            if (!$p->cash($insert_id)) {
                throw new Exception('提现失败');
            }
            pdo_commit();

        } catch (Exception $e) {
            pdo_rollback();
            json($e->getMessage(), 0);
        }

        json('提现成功', 1);
    }


    private function setPayment()
    {
        WxPayConfig::$APPID = $this->w['uniaccount']['key'];
        WxPayConfig::$MCHID = $this->w['account']['setting']['payment']['wechat']['mchid'];
        WxPayConfig::$KEY = $this->w['account']['setting']['payment']['wechat']['signkey'];
        WxPayConfig::$APPSECRET = $this->w['uniaccount']['secret'];

        $cert = pdo_get(prefix_table('cj_config'), ['key' => 'apiclient_cert']);
        if (!empty($cert)) {
            WxPayConfig::$SSLCERT_PATH = $cert['value'];
        }
        $key = pdo_get(prefix_table('cj_config'), ['key' => 'apiclient_key']);
        if (!empty($key)) {
            WxPayConfig::$SSLKEY_PATH = $key['value'];
        }
    }


    /**
     * 常见问题
     */
    public function doPageProblem()
    {
        $data = pdo_getall(prefix_table('cj_common_problem'), ['status' => 1]);
        json($data);
    }


    /**
     * 我的参与
     */
    public function doPageHistory()
    {
        $p = $this->get('p', 1 );
        $page_size = 10;
        $limit = ($p - 1) * $page_size;

        $status = $this->get('status', 1) == 1 ? 1 : 0;
        $cj_order = tablename(prefix_table('cj_order'));
        $cj_prize = tablename(prefix_table('cj_prize'));

        $sql = "SELECT A.* FROM {$cj_order} A LEFT JOIN $cj_prize B ON A.prize_id=B.id WHERE A.member_id = {$this->member['id']} AND B.status={$status} ORDER BY order_id DESC LIMIT {$limit}, {$page_size}";

        //$order = pdo_getall('cj_order', ['member_id' => $this->member['id']], [], '', ['order_id desc'], "{$limit}, {$page_size}");
        $order = pdo_fetchall($sql);

        foreach ($order as & $value) {
            $value['is_win'] = pdo_get(prefix_table('cj_prize_result'), ['prize_id' => $value['prize_id'], 'member_id' => $this->member['id']]);
            $value['pinfo'] = pdo_get(prefix_table('cj_prize'), ['id' => $value['prize_id']]);
            $value['pinfo']['minfo'] = pdo_get(prefix_table('cj_member'), ['id' => $value['pinfo']['member_id']]);
        }

        json($order);
    }


    public function doPageMywinn()
    {
        $p = $this->get('p', 1 );
        $page_size = 10;
        $limit = ($p - 1) * $page_size;

        $cj_prize_result = tablename(prefix_table('cj_prize_result'));
        $cj_prize = tablename(prefix_table('cj_prize'));

        $sql = "SELECT A.* FROM {$cj_prize_result} A LEFT JOIN $cj_prize B ON A.prize_id=B.id WHERE A.member_id = {$this->member['id']}  ORDER BY result_id DESC LIMIT {$limit}, {$page_size}";

        //$order = pdo_getall('cj_order', ['member_id' => $this->member['id']], [], '', ['order_id desc'], "{$limit}, {$page_size}");
        //echo $sql;
        $order = pdo_fetchall($sql);

        foreach ($order as & $value) {
            $value['is_win'] = pdo_get(prefix_table('cj_prize_result'), ['prize_id' => $value['prize_id'], 'member_id' => $this->member['id']]);
            $value['pinfo'] = pdo_get(prefix_table('cj_prize'), ['id' => $value['prize_id']]);
            $value['pinfo']['minfo'] = pdo_get(prefix_table('cj_member'), ['id' => $value['pinfo']['member_id']]);
        }

        json($order);
    }


    /**
     * 我发起
     */
    public function doPageLaunch()
    {
        $p = $this->get('p', 1 );
        $page_size = 10;
        $limit = ($p - 1) * $page_size;
        $status = $this->get('status', 1) == 1 ? 1 : 0;

        $prize = pdo_getall(prefix_table('cj_prize'), ['member_id' => $this->member['id'], 'status' => $status], [], '', ['id desc'], "{$limit}, {$page_size}");
        foreach ($prize as &$value) {
            $value['minfo'] = $this->member;
        }

        json($prize);
    }



    /**
     * 首页
     */
    public function doPageIndex()
    {
        $condition = [
            'is_cancel' => 0,
            'is_global' => 0,
            'status' => 0
        ];
        $prize = pdo_getall(prefix_table('cj_prize'), $condition);
        $trd_session = $this->get('trd_session');
        if ($trd_session) {
            $res = pdo_get(prefix_table('cj_token'), ['token' => $trd_session]);
            $this->member['id'] = $res['member_id'];
        }

        if ($prize) {
            foreach ($prize as & $value) {
                $image = pdo_get(prefix_table('cj_resource'), ['id' => $value['attach_id']]);
                $value['imgurl'] = $this->getImage($image['route']);
                $value['joined'] = $this->member['id'] ? pdo_get(prefix_table('cj_order'), ['prize_id' => $value['id'], 'member_id' => $this->member['id']]) : '';
            }
        }

        json($prize);
    }

    public function doPageTotal()
    {
        $jiezhi = date("Y-m-d",strtotime("-1 day"));
        $total = pdo_get(prefix_table('cj_prize_result'), [], ['COUNT(result_id)']);
        if ($total) {
            $total = $total[0];
        } else {
            $total = 0;
        }
        $apply = pdo_get(prefix_table('cj_config'), ['key' => 'apply_number']);

        if (!empty($apply)) {
            $total += $apply['value'];
        }

        json(compact('jiezhi', 'total'));
    }


    public function doPageRecommend()
    {
        $contact = $this->get('contact');
        if (empty($contact)) {
            json('请填写联系方式', 0);
        }

        //pdo_get('')

        $recommend = pdo_get(prefix_table('cj_config'), ['key' => 'home_recommendation']);
        if (empty($recommend)) {
            $money = 1500;
        } else {
            $money = $recommend['value'];
        }
        sleep(2);

        $prize = new prize();
        $member = pdo_get(prefix_table('cj_member'), ['id' => $this->member['id']]);
        if ($member['money'] < $money) {
            $pay_money = $money - $member['money'];
            $orderid = $prize->unifiedOrder($this->member['id'], $pay_money);

            $xcx = pdo_get(prefix_table('cj_config'), ['key' => 'title']);
            $xcx = $xcx ? $xcx['value'] : '';

            $order = array(
                'tid' => $orderid, //订单号
                'fee' => floatval($pay_money), //支付参数
                'title' => $xcx . '的订单', //标题
            );
            global $_W;
            $_W['openid'] = $this->member['openid'];
            $_W['member']['uid'] = $this->member['id'];
            $paydata = $this->pay($order);
            if (is_error($paydata)) {
                //$this->result($paydata['errno'], $paydata['message']);
                json($paydata['message'], 0);
            }
            json($paydata, 2);
        }
        pdo_begin();
        try{
            if (!$res = $prize->change($this->member['id'], $money, 9)) {
                throw new Exception('更新余额失败');
            }
            $data = [
                'member_id' => $this->member['id'],
                'contact' => $contact,
                'created' => time()
            ];

            if (!pdo_insert(prefix_table('cj_home_recommend'), $data)) {
                throw new Exception('推荐失败');
            }
            pdo_commit();
        } catch (Exception $e) {
            pdo_rollback();
            json($e->getMessage(), 0);
        }
        json('提交申请成功,稍后管理员联系你');
    }

    /**
     * 添加
     */
    public function doPageAdd()
    {
        if ($this->_release() != 1) {
            json('无发布权限', 0);
        }

        if (!$this->member['user_img'] && !$this->member['nickname']) {
            json('', -1);
        }

        $attach_id = $this->get('attach_id');
        $title = $this->get('title');
        $desc_type = $this->get('desc_type');
        $description = $this->get('description');
        $uname = $this->get('uname');
        $wechat_no = $this->get('wechat_no');
        $wechat_title = $this->get('wechat_title');

        $fir_cname = $this->get('fir_cname');
        $sec_cname = $this->get('sec_cname');
        $trd_cname = $this->get('trd_cname');

        $fir_ptype = $this->get('fir_ptype');
        $is_share = $this->get('is_share');
        $is_command = $this->get('is_command', 0);

        $prize = new prize();
        $is_voucher = $prize->is_voucher($this->member['id']);

        if ($fir_ptype == 2) {
            $fir_val = htmlspecialchars_decode($this->get('fir_cardmsg'));
            $fir_num = $is_voucher ? $this->get('fir_num') : count(json_decode($fir_val, true));
        } else {
            $fir_val = $this->get('fir_val');
            $fir_num = $this->get('fir_num', 0);
        }

        $sec_ptype = $this->get('sec_ptype');
        if ($sec_ptype == 2) {
            $sec_val = htmlspecialchars_decode($this->get('sec_cardmsg'));
            $sec_num = $is_voucher ? $this->get('sec_num') :  count(json_decode($sec_val, true));
        } else {
            $sec_val = $this->get('sec_val');
            $sec_num = $this->get('sec_num', 0);
        }

        $trd_ptype = $this->get('trd_ptype');
        if ($trd_ptype == 2) {
            $trd_val = htmlspecialchars_decode($this->get('trd_cardmsg'));
            $trd_num = $is_voucher ? $this->get('trd_num') :  count(json_decode($trd_val, true));
        } else {
            $trd_val = $this->get('trd_val');
            $trd_num = $this->get('trd_num', 0);
        }

        $max_group_num = 0;//$this->get('max_group_num', 0);
        $type = $this->get('type', 'people');
        $types = [
            'people' => 2,
            'time' => 1,
            'manual' => 3
        ];
        $type = $types[$type];

        $typevalue = $this->get('typevalue');

        if ($type == 1) {
            $typevalue = strtotime($typevalue);
            if ($typevalue <= time()) {
                json('开奖时间已过，请重新选择时间', 0);
            }
        }
        else if ($type == 2) {
            $num = $fir_num + $sec_num + $trd_num;
            if ($num > $typevalue) {
                json('开奖人数小于奖品数', 0);
            }
        }
        $money = 0;

        if ($fir_ptype == 1 || $trd_ptype == 1 || $sec_ptype == 1) {

            if ($fir_ptype == 1) {
                $money += ($fir_val * $fir_num);
            }
            if ($sec_ptype == 1) {
                $money += ($sec_val * $sec_num);
            }
            if ($trd_ptype == 1) {
                $money += ($trd_val * $trd_num);
            }

            $red_package_fee = pdo_get(prefix_table('cj_config'), ['key' => 'red_package_fee']);
            if ($red_package_fee && $red_package_fee['value'] > 0) {
                $fee = ceil($money * $red_package_fee['value']) / 100;
                $money += $fee;
            }

        }

        $isjump = $this->get('isjump');
        if ($isjump == 1) {
            $pay_function = pdo_get(prefix_table('cj_config'), ['key' => 'pay_function']);
            $pay_function = $pay_function ? $pay_function['value'] : 5;
            $money += $pay_function;
        }

        if ($money > 0) {
            sleep(2);
            $member = pdo_get(prefix_table('cj_member'), ['id' => $this->member['id']]);
            if ($member['money'] < $money) {

                //构造订单数据
                $orderid = $prize->unifiedOrder($this->member['id'], $money);
                $xcx = pdo_get(prefix_table('cj_config'), ['key' => 'title']);
                $xcx = $xcx ? $xcx['value'] : '';

                $order = array(
                    'tid' => $orderid, //订单号
                    'fee' => floatval($money), //支付参数
                    'title' => $xcx . '的订单', //标题
                );
                global $_W;
                $_W['openid'] = $this->member['openid'];
                $_W['member']['uid'] = $this->member['id'];
                $paydata = $this->pay($order);
                if (is_error($paydata)) {
                    //$this->result($paydata['errno'], $paydata['message']);
                    json($paydata['message'], 0);
                }
                json($paydata, 2);
            }
            $res = $prize->change($this->member['id'], $money, 5);
            if ($res == false) {
                json('奖品发布失败', 0);
            }
        }

        $data = [
            'brief_description' => $title,
            'member_id' => $this->member['id'],
            'uname' => $uname,
            'wechat_no' => $wechat_no,
            'wechat_title' => $wechat_title,
            'typevalue' => $typevalue,
            'max_group_num' => $max_group_num,
            'desc_type' => $desc_type,
            'desc_text' => $desc_type,
            'type' => $type,
            'attach_id' => $attach_id,
            'fir_ptype' => $fir_ptype,
            'fir_num' => $fir_num,
            'fir_val' => $fir_val,
            'sec_ptype' => $sec_ptype,
            'sec_num' => $sec_num,
            'sec_val' => $sec_val,
            'trd_ptype' => $trd_ptype,
            'trd_num' => $trd_num,
            'trd_val' => $trd_val,
            'description' => $description,
            'fir_cname' => $fir_cname,
            'sec_cname' => $sec_cname,
            'trd_cname' => $trd_cname,
            'created' => time(),
            'is_share' => $is_share,
            'is_command' => $is_command
        ];
        if ($is_command == 1) {
            $data['command'] = $this->get('command');
            if (empty($data['command'])) {
                json('请输入口令', 0);
            }
        }

        pdo_begin();
        try {
            if ($data['fir_ptype'] == 2 && $is_voucher) {
                $data['fir_ptype'] = 3;
                $data['fir_cardid'] = $this->get('fir_cardid');
                if (!is_numeric($data['fir_cardid'])) {
                    throw new Exception('请选择优惠券');
                }
                $res = $prize->deduction($this->member['shop_id'], $data['fir_cardid'], $fir_num);
                if (!$res) {
                    throw new Exception($prize->error);
                }
                $data['fir_val'] = json_encode($res);
            }

            if ($data['sec_ptype'] == 2 && $is_voucher) {
                $data['sec_ptype'] = 3;
                $data['sec_cardid'] = $this->get('sec_cardid');
                if (!is_numeric($data['sec_cardid'])) {
                    throw new Exception('请选择优惠券');
                }
                $res = $prize->deduction($this->member['shop_id'], $data['sec_cardid'], $sec_num);
                if (!$res) {
                    throw new Exception($prize->error);
                }
                $data['sec_val'] = json_encode($res);
            }
            if ($data['trd_ptype'] == 2 && $is_voucher) {
                $data['trd_ptype'] = 3;
                $data['trd_cardid'] = $this->get('trd_cardid');
                if (!is_numeric(!$data['trd_cardid'])) {
                    throw new Exception('请选择优惠券');
                }
                $res = $prize->deduction($this->member['shop_id'], $data['trd_cardid'], $trd_num);
                if (!$res) {
                    throw new Exception($prize->error);
                }
                $data['trd_val'] = json_encode($res);
            }
            file_put_contents(ATTACHMENT_ROOT . '/1.txt', var_export($data, true));

            if (!pdo_insert(prefix_table('cj_prize'), $data)) {
                throw new Exception('发布失败1');
            }
            $insert_id = pdo_insertid();
            if ($data['fir_ptype'] == 1) {
                $prize->bag($this->member['id'], $insert_id, $data['fir_val'], $data['fir_num']);
            }
            if ($data['sec_ptype'] == 1) {
                $prize->bag($this->member['id'], $insert_id, $data['sec_val'], $data['sec_num']);
            }
            if ($data['trd_ptype'] == 1) {
                $prize->bag($this->member['id'], $insert_id, $data['trd_val'], $data['trd_num']);
            }

            if ($isjump == 1 && $this->get('copyorjump') == 2) {
                $data = [
                    'prize_id' => $insert_id,
                    'appid' => $this->get('appid'),
                    'path' => $this->get('path'),
                    'extraData' =>  $this->get('extraData'),
                    'app_name' =>  $this->get('appname')
                ];
                pdo_insert(prefix_table('cj_jump_program'), $data);
            }
            pdo_commit();
            json($insert_id);
        } catch (Exception $e) {
            pdo_rollback();
            json($e->getMessage(), 0);
        }

    }


    /**
     * 核销卡券
     */
    public function doPageWriteoff()
    {
        $voucher = $this->get('voucher');
        if (empty($voucher)) {
            json('卡券不存在', 0);
        }
        if (!$this->member['shop_id']) {
            json('没有核销该卡券权限', 0);
        }
        if(!$voucher = pdo_get(prefix_table('cj_member_voucher'), ['voucher' => $voucher, 'status' => 0])) {
            json('卡券不存在或者已经被使用', 0);
        }
        $shop = pdo_get(prefix_table('cj_shop'), ['id' => $this->member['shop_id'], 'is_del' => 0]);
        if (empty($shop) || $shop['id'] != $voucher['shop_id']) {
            json('没有核销该卡券权限', 0);
        }

        $update = [
            'write_off_id' => $this->member['id'],
            'status' => 1,
            'write_off_time' => time()
        ];
        if (pdo_update(prefix_table('cj_member_voucher'), $update, ['id' => $voucher['id']])) {
            pdo_update(prefix_table('cj_voucher'), ['status' => 3], ['voucher' => $voucher]);

            json('核销成功');
        }
        json('核销失败', 0);
    }


    /**
     * 补充订单消息
     */
    public function doPageWriteOrder()
    {
        $order_id = $this->get('order_id');
        $voucher = $this->get('voucher');

        $voucher = pdo_get(prefix_table('cj_member_voucher'), ['voucher' => $voucher, 'write_off_id' => $this->member['id']]);
        if (empty($voucher)) {
            json('无权限', 0);
        }
        if (pdo_update(prefix_table('cj_member_voucher'), ['order_id' => $order_id], ['id' => $voucher['id']])) {
            json('');
        }
        json('填写失败', 0);
    }


    /**
     * 我的优惠券
     */
    public function doPageVoucherList()
    {
        $p = $this->get('p', 1 );
        $page_size = 10;
        $limit = ($p - 1) * $page_size;
        $voucher = pdo_getall(prefix_table('cj_member_voucher'), ['member_id' => $this->member['id']], [], '', ['id desc'], "{$limit}, {$page_size}");

        foreach ($voucher as & $value) {
            $value['goods'] = pdo_get(prefix_table('cj_goods'), ['id' => $value['goods_id']]);
        }
        json($voucher);
    }


    /**
     * 优惠券详情
     */
    public function doPageVoucherDetails()
    {
        $id = $this->get('id');
        $voucher = pdo_get(prefix_table('cj_member_voucher'), ['id' => $id, 'member_id' => $this->member['id']]);

        if (empty($voucher) || $voucher['status'] == 1) {
            json('该优惠券不存在或者已经被使用', 0);
        }
        $voucher['goods'] = pdo_get(prefix_table('cj_goods'), ['id' => $voucher['goods_id']]);

        json($voucher);
    }





    /**
     * 获取支付结果.
     */
    public function doPagePayResult($data = [])
    {
        $this->_payResult($data);
    }

    public function payResult($data = [])
    {
        $this->_payResult($data);
    }

    private function _payResult($data)
    {
        global $_GPC;
        global $_W;
        //file_put_contents(ATTACHMENT_ROOT . '1.txt', var_export($data, true), FILE_APPEND);
        $orderid = $data['tid'] ? $data['tid'] : $_GPC['orderid'];
        $order_type = trim($_GPC['order_type']);
        //订单id
        //file_put_contents(ATTACHMENT_ROOT . '1.txt', var_export($_W, true), FILE_APPEND);
        $paylog = pdo_get('core_paylog', array('uniacid' => $_W['uniacid'], 'module' => 'hu_couda', 'tid' => $orderid));
        $status = intval($paylog['status']) === 1;
        if ($status) {
            pdo_begin();
            try {
                $order = pdo_get(prefix_table('cj_pay_order'), ['trade_no' => $orderid, 'status' => 1]);
                if (empty($order)) {
                    throw new Exception('订单不存在或者已经处理');
                }

                if (pdo_update(prefix_table('cj_pay_order'), ['pay_time' => time(), 'status' => 2])) {
                    $prize = new prize();
                    $prize->change($order['member_id'], $order['money'], 1);
                }
                pdo_commit();
            } catch (Exception $e) {
                pdo_rollback();
            }
        }
        $this->result($status, $status ? '支付成功' : '支付失败');
    }


    /**
     * 抽奖详情
     */
    public function doPageDetails()
    {
        $id = $this->get('id');
        if (!is_numeric($id)) {
            json('参数错误', 0);
        }
        $prize = pdo_get(prefix_table('cj_prize'), ['id' => $id]);
        if (empty($prize)) {
            json('该抽奖不存在', 0);
        }


        $prize['imgurl'] = $this->getImage($prize['attach_id']);

        if ($prize['desc_type'] == 1 && $prize['description']) {
            $description = explode(',', $prize['description']);
            $prize['description'] = [];
            foreach ($description as $item) {
                $prize['description'][] = $this->getImage($item);
            }

        }


        $t_order = tablename(prefix_table('cj_order'));
        $t_member = tablename(prefix_table('cj_member'));


        $robot = pdo_get(prefix_table('cj_config'), ['key' => 'robot']);
        $is_robot = $robot ? $robot['value'] : 0;
        if ($is_robot && $prize['is_global'] == 0) {
            if ($prize['type'] == 1) {
                $time = $prize['open_time'] > 0 ? $prize['open_time'] : time();
            } else {
                $time = time();
            }
            $people = floor((($time - $prize['created']) / 60) * 10);
            $robot_num = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(prefix_table('cj_member')) . ' WHERE is_robot=1');
            //var_dump($robot_num);
            $people = $people < $robot_num ? $people : $robot_num;
            $prize['apply_num'] += $people;
        }


        $apply = [];

        if ($prize['apply_num'] > 0) {
            $l = $prize['apply_num'] > 6 ? 6 : $prize['apply_num'];
            $where = "B.prize_id={$id}";
            if ($is_robot && $prize['is_global'] == 0) {
                $where .= ' OR is_robot=1';
            }
            $sql = "SELECT A.user_img,B.code_num FROM {$t_member} AS A LEFT JOIN {$t_order} AS B ON A.id=B.member_id WHERE {$where} ORDER BY B.code_num DESC,A.is_robot ASC LIMIT {$l}";
            $apply = pdo_fetchall($sql);
        }




        $prize['jump_info'] = pdo_get(prefix_table('cj_jump_program'), ['prize_id' => $id]);

        $my_apply = pdo_get(prefix_table('cj_order'), ['member_id' => $this->member['id'], 'prize_id' => $id]);
        $my_prize = pdo_get(prefix_table('cj_prize_result'), ['prize_id' => $id, 'member_id' => $this->member['id']]);

        $my_in_prize = [];
        $in_prize = [];

        $getname = function ($type) use (& $prize) {
            if ($prize[$type . '_num'] < 1) {
                return '';
            }
            if ($prize[$type . '_ptype'] > 1) {
                return $prize[$type . '_cname'];
            }
            return $prize[$type . '_ptype'] == 0 ? $prize[$type . '_val'] : ('红包' . $prize['fir_val'] . '￥');
        };

        $in_prize_sort = [
            'fir' => [
                'name' => $getname('fir'),
                'list' => []
            ],
            'sec' => [
                'name' => $getname('sec'),
                'list' => []
            ],
            'trd' => [
                'name' => $getname('trd'),
                'list' => []
            ]
        ];

        if ($prize['status'] == 1 && $prize['is_cancel'] == 0) {
            $in_prize = pdo_getall(prefix_table('cj_prize_result'), ['prize_id' => $id], [], '', ['type ASC']);
            foreach ($in_prize as & $value) {
                $value['minfo'] = pdo_get(prefix_table('cj_member'), ['id' => $value['member_id']]);
                $value['minfo']['address'] = pdo_get(prefix_table('cj_address'), ['member_id' => $value['member_id']]);
                //$value['prize_name'] = $getname($value['ptype']);

                if ($value['member_id'] == $this->member['id']) {
                    $my_in_prize[] = $value;
                }
                $in_prize_sort[$value['type']]['list'][] = $value;
            }
        }
        $prize['max_group_num'] = 0;
        $group = [];
        if ($my_apply && $prize['max_group_num'] > 0) {
            $group = pdo_get(prefix_table('cj_group_join'), ['member_id' => $this->member['id'], 'prize_id' => $id]);
            $sql = "SELECT A.user_img,A.nickname FROM {$t_member} AS A LEFT JOIN {$t_order} AS B ON A.id=B.member_id WHERE B.group_join_id={$group['id']}";
            $group['apply'] = pdo_fetchall($sql);
        }
        $group_code = $this->get('group_code');
        if (empty($my_apply) && $group_code && $prize['max_group_num'] > 0) {
            $group = pdo_get(prefix_table('cj_group_join'), ['id' => $group_code, 'prize_id' => $id]);

            if (!empty($group)) {
                $sql = "SELECT A.user_img,A.nickname FROM {$t_member} AS A LEFT JOIN {$t_order} AS B ON A.id=B.member_id WHERE B.group_join_id={$group['id']}";
                $group['apply'] = pdo_fetchall($sql);
            }

        }

        if ($prize['is_global'] == 1) {
            $prize['minfo'] = pdo_get(prefix_table('cj_member'), ['id' => $prize['member_id']]);
        } else {
            $prize['minfo'] = ['nickname' => '系统'];
        }

        $sql = "SELECT * FROM " . tablename(prefix_table('cj_prize_code')) . " WHERE prize_id={$id} AND is_prize=1 LIMIT 9";
        //$prize['code'] = pdo_getall(prefix_table('cj_prize_code'), ['prize_id' => $id, 'is_prize' => 1], [], '', [], []);
        //var_dump($sql);
        $prize['code'] = pdo_fetchall($sql);


        $prize['apply'] = $apply;
        $prize['my_apply'] = $my_apply;
        $prize['my_prize'] = $my_prize;
        $prize['group'] = $group;
        $prize['in_prize'] = $in_prize;
        $prize['my_in_prize'] = $my_in_prize;
        $prize['is_winning'] = $my_in_prize ? 1 : 0;
        $prize['is_mine'] = $this->member['id'] == $prize['member_id'] ? 1 : 0;
        $prize['is_buy'] = $my_apply ? 1 : 0;
        $prize['share_sub_url'] = $prize['imgurl'];
        $prize['member'] = $this->member;
        $prize['in_prize_sort'] = $in_prize_sort;


        json($prize);
    }


    public function doPageAllcode()
    {
        $id = $this->get('id');
        if (!is_numeric($id)) {
            json('参数错误', 0);
        }
        $prize = pdo_get(prefix_table('cj_prize'), ['id' => $id]);
        if (empty($prize)) {
            json('该抽奖不存在', 0);
        }

        $code = pdo_getall(prefix_table('cj_prize_code'), ['prize_id' => $id]);
        json($code);
    }


    public function doPagePrizeWins()
    {
        $id = $this->get('id');
        if (!is_numeric($id)) {
            json('参数错误', 0);
        }
        $prize = pdo_get(prefix_table('cj_prize'), ['id' => $id]);
        if (empty($prize) || $prize['status'] != 1 || $this->member['id'] != $prize['member_id']) {
            json('该抽奖不存在', 0);
        }

        $in_prize = pdo_getall(prefix_table('cj_prize_result'), ['prize_id' => $id], [], '', ['type ASC']);
        foreach ($in_prize as & $value) {
            $value['minfo'] = pdo_get(prefix_table('cj_member'), ['id' => $value['member_id']]);
            $value['minfo']['address'] = pdo_get(prefix_table('cj_address'), ['member_id' => $value['member_id']]);
        }
        json(['prize' => $prize, 'list' => $in_prize]);
    }




    public function doPageAddress()
    {
        $name = $this->get('name');
        $phone = $this->get('phone');
        $address = $this->get('address');
        $data = [
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
        ];
        pdo_update(prefix_table('cj_address'), $data, ['member_id' => $this->member['id']]);
        json('ok');
    }


    public function doPageGetAddress()
    {
        $data = pdo_get(prefix_table('cj_address'), ['member_id' => $this->member['id']]);
        if (empty($data)) {
            pdo_insert(prefix_table('cj_address'), ['member_id' => $this->member['id'], 'addtime' => time()]);
            $data = [
                'name' => '',
                'phone' => '',
                'address' => ''
            ];
        }
        json($data);
    }


    /**
     * 所有参与人
     */
    public function doPageAll()
    {
        $id = $this->get('id');
        $p = $this->get('p', 1);
        $page_size = 10;
        $limit = ($p - 1) * $page_size;

        $robot = pdo_get(prefix_table('cj_config'), ['key' => 'robot']);
        $is_robot = $robot ? $robot['value'] : 0;

        $prize = pdo_get(prefix_table('cj_prize'), ['id' => $id]);

        if ($is_robot && $prize['is_global'] == 0) {
            //$time = $prize['open_time'] > 0 ? $prize['open_time'] : time();

            if ($prize['type'] == 1) {
                $time = $prize['open_time'] > 0 ? $prize['open_time'] : time();
            } else {
                $time = time();
            }

            $people = floor((($time - $prize['created']) / 60) * 10);
            $robot_num = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename(prefix_table('cj_member')) . ' WHERE is_robot=1');

            $people = $people < $robot_num ? $people : $robot_num;
            $people +=  $prize['apply_num'];
            if ($limit > $people) {
                json([]);
            }

            $page_size = ($limit + $page_size) > $people ? ($people % $page_size) : $page_size;
        }


        $where = "B.prize_id={$id}";
        if ($is_robot && $prize['is_global'] == 0) {
            $where .= ' OR A.is_robot=1';
        }

        $t_order = tablename(prefix_table('cj_order'));
        $t_member = tablename(prefix_table('cj_member'));
        $sql = "SELECT A.user_img,A.id,A.nickname,B.code_num FROM {$t_member} AS A LEFT JOIN {$t_order} AS B ON A.id=B.member_id WHERE {$where} ORDER BY B.code_num DESC,A.is_robot ASC,B.order_id DESC LIMIT {$limit}, {$page_size}";

        $apply = pdo_fetchall($sql);
        if ($apply) {
            $cj_prize_result = tablename(prefix_table('cj_prize_result'));
            foreach ($apply as & $value) {
                $value['win_num'] = pdo_fetchcolumn("SELECT COUNT(*) FROM {$cj_prize_result} WHERE member_id={$value['id']}");
                $value['code_num'] = $value['code_num'] ? : 1;
            }
        }

        json($apply);
    }


    /**
     * 参加抽奖
     */
    public function doPageApply()
    {
        if (!$this->member['user_img'] && !$this->member['nickname']) {
            json('', -1);
        }

        $id = $this->get('id');
        $invitation_id = $this->get('invitation_id', 0);
        if (!$invitation_id || $invitation_id == $this->member['id']) {
            $invitation_id = 0;
        }

        $prize = pdo_get(prefix_table('cj_prize'), ['id' => $id]);
        if (empty($prize)) {
            json('该抽奖不存在', 0);
        }
        $apply = pdo_get(prefix_table('cj_order'), ['prize_id' => $id, 'member_id' => $this->member['id']]);

        if (!empty($apply)) {
            json('您已经参与过', 0);
        }

        pdo_begin();
        try {


            $group_id = 0;
            if ($prize['max_group_num'] > 0 && false) {
                $group_join_id = $this->get('group_code');
                if (!empty($group_join_id)) {
                    $group = pdo_get(prefix_table('cj_group_join'), ['id' => $group_join_id]);

                    if (!empty($group)
                        && $group['prize_id'] == $id
                        && $group['apply_num'] < $group['number']
                    ) {

                        $cj_group_join = tablename(prefix_table('cj_group_join'));
                        $sql = "UPDATE {$cj_group_join} SET apply_num=apply_num+1 WHERE id={$group_join_id} AND apply_num<{$group['number']}";

                        if (pdo_query($sql)) {
                            $group_id = $group_join_id;
                        }
                    }
                }
                if ($group_id == 0) {
                    $group = [
                        'prize_id' => $id,
                        'member_id' => $this->member['id'],
                        'number' => $prize['max_group_num'],
                        'apply_num' => 1,
                        'created' => time()
                    ];
                    pdo_insert(prefix_table('cj_group_join'), $group);
                    $group_id = pdo_insertid();
                }
            }

            if ($prize['is_command'] == 1) {
                if (!$command = $this->get('command')) {
                    json('请输入口令', 0);
                }
                if ($command != $prize['command']) {
                    json('口令错误', 0);
                }
            }


            $order = [
                'order_sn' => date("YmdHis", time()) . $this->member['id'] . mt_rand(1000, 9999),
                'prize_id' => $id,
                'group_join_id' => $group_id,
                'member_id' => $this->member['id'],
                'addtime' => time()
            ];

            if (!pdo_insert(prefix_table('cj_order'), $order)) {
                throw new Exception('参加失败');
            }

            $cj_prize = tablename(prefix_table('cj_prize'));
            $sql = "UPDATE {$cj_prize} SET apply_num=apply_num+1 WHERE id={$id}";

            if (!pdo_query($sql)) {
                throw new Exception('参加失败');
            }
            $p = new prize();
            $code = $p->create_code($id, $this->member['id']);
            if (!$code) {
                throw new Exception($p->error);
            }
            if ($invitation_id) {
                $p->create_code($id, $invitation_id, $this->member['id'], 2);
                /*if (!$p->create_code($id, $invitation_id, $this->member['id'], 2)) {
                    throw new Exception($p->error);
                }*/
            }
            pdo_commit();

            if ($prize['type'] == 2 && $prize['typevalue'] - 1 <= $prize['apply_num']) {
                $p->open($id);
            }

            json($code);

        } catch (Exception $e) {
            $error = $e->getMessage();
            pdo_rollback();
        }
        json($error, 0);
    }


    public function doPageCode()
    {
        $p = $this->get('p', 1);
        $page_size = 10;
        $limit = ($p - 1) * $page_size;

        $member_id = $this->get('member_id') ? : $this->member['id'];
        $prize_id = $this->get('id');

        if (empty($member_id) || empty($prize_id)) {
            json('参数错误', 0);
        }
        $member = pdo_get(prefix_table('cj_member'), ['id' => $member_id]);
        if ($member['is_robot'] == 1) {
            $code = pdo_get(prefix_table('cj_prize_code'), ['member_id' => $member_id, 'prize_id' => $prize_id]);
            if (empty($code)) {
                $p = new prize();
                $p->create_code($prize_id, $member_id, 0, 1, 2);
            }
        }

        //$code = pdo_getall('cj_prize_code', ['member_id' => $member_id, 'prize_id' => $prize_id]);
        $cj_prize_code = tablename(prefix_table('cj_prize_code'));
        if ($this->get('ispage') == 1) {
            $sql = "SELECT * FROM {$cj_prize_code} WHERE member_id={$member_id} AND prize_id={$prize_id} ORDER BY id ASC";
        } else {
            $sql = "SELECT * FROM {$cj_prize_code} WHERE member_id={$member_id} AND prize_id={$prize_id} ORDER BY id ASC LIMIT $limit,$page_size";
        }


        $code = pdo_fetchall($sql);
        if (!empty($code)) {
            foreach ($code as & $value) {
                $id = $value['type'] == 1 ? $value['member_id'] : $value['be_invited_id'];
                $value['minfo'] = pdo_get(prefix_table('cj_member'), ['id' => $id]);
            }
        }
        json($code);
    }


    /**
     * 手动开奖
     */
    public function doPageOpenPrize()
    {
        $id = $this->get('id');
        if (!is_numeric($id)) {
            json('参数错误');
        }
        $prize = pdo_get(prefix_table('cj_prize'), ['id' => $id]);
        if (empty($prize)
            || $prize['member_id'] != $this->member['id']
            || $prize['apply_num'] < 1
            || $prize['type'] != 3
        ) {
            json('还没人参与,无法开奖');
        }
        $p = new prize();
        if ($p->open($id)) {
            json('开奖成功');
        }
        json($p->error, 0);
    }


    /**
     * 开奖
     */
    public function doPageOpen()
    {
        $cj_prize = tablename(prefix_table('cj_prize'));
        $t = time();
        $m_prize = new  prize();
        $sql = "SELECT* FROM {$cj_prize} WHERE status=0 AND is_cancel=0 AND (type=1 AND typevalue<{$t})";
        $data = pdo_fetchall($sql);

        if (!empty($data)) {
            foreach ($data as $value) {
                $m_prize->open($value['id']);
            }
        }
        $data = pdo_getall(prefix_table('cj_pre_prize'));
        if ($data) {
            foreach ($data as $line) {
                $m_prize->add($line);
            }
        }

        $this->sendTpl();

        echo 'ok';
    }


    public function sendTpl()
    {
        $config = pdo_get(prefix_table('cj_config'), ['key' => 'open_prize_notice']);
        if (empty($config)) {
            return true;
        }
        $template = pdo_getall(prefix_table('cj_template_message'), ['type' => 1, 'status' => 0]);
        if (empty($template)) {
            return true;
        }
        $option = [
            'appid' => $this->w['uniaccount']['key'],
            'secret' => $this->w['uniaccount']['secret'],
        ];

        $wxObj = new Wxadoc($option);

        foreach ($template as $value) {

            $prize = pdo_get(prefix_table('cj_prize'), ['id' => $value['relation_id']]);
            $kv2 = '';
            if ($prize['member_id'] > 0) {
                $member = pdo_get(prefix_table('cj_member'), ['id' => $prize['member_id']]);
                $kv2 = $member['nickname'] . '发起的';
            }
            if ($prize['fir_ptype'] == 0) {
                $title = $prize['fir_val'];
            } elseif ($prize['fir_ptype'] == 1) {
                $title = '现金红包';
            } else {
                $title = $prize['fir_cname'];
            }
            $data = [
                /*'first' => [
                    'value' => '抽奖结果通知',
                    'color' => '#ff510'
                ],*/
                'keyword1' => [
                    'value' => '抽奖已经开启,快去看看你是不是幸运儿!'
                ],
                'keyword2' => [
                    'value' => $title
                ]
            ];
            pdo_update(prefix_table('cj_template_message'), ['status' => 1], ['id' => $value['id']]);

            $order = pdo_getall(prefix_table('cj_order'), ['prize_id' => $value['relation_id']]);
            foreach ($order as $line) {
                $member = pdo_get(prefix_table('cj_member'), ['id' => $line['member_id']]);
                $time = time() - (7*86400);
                $form_id = pdo_fetch("SELECT * FROM " . tablename(prefix_table('cj_form_id')) . " WHERE member_id={$line['member_id']} AND created > {$time}");

                if (empty($member) || empty($form_id)) {
                    continue;
                }
                $wxObj->sendTemplateMessage($member['openid'], $config['value'], $data, $form_id['form_id'], 'pages/partake/partake?id=' . $value['relation_id']);
                pdo_delete(prefix_table('cj_form_id'), ['id' => $form_id['id']]);
            }
        }

        return true;
    }


    public function doPageConfig()
    {
        $key = $this->get('key');
        if (empty($key)) {
            json('参数错误', 0);
        }
        $config = pdo_get(prefix_table('cj_config'), ['key' => $key]);

        if ($config) {
            $config = $config['value'];
        }
        elseif ($key == 'pay_function') {
            pdo_insert(prefix_table('cj_config'), ['key' => 'pay_function', 'value' => 5]);
            $config = 5;
        } elseif ($key == 'home_recommendation') {
            pdo_insert(prefix_table('cj_config'), ['key' => 'home_recommendation', 'value' => 1500]);
            $config = 1500;
        }

        json($config);
    }


    public function doPageAdvertisement()
    {
        $type = pdo_get(prefix_table('cj_config'), ['key' => 'advertisement_type']);
        if (empty($type)) {
            json('', 2);
        }
        $advertisement = pdo_get(prefix_table('cj_config'), ['key' => 'advertisement']);
        if (empty($advertisement)) {
            json('', 2);
        }
        if ($type['value'] == 1) {
            $advertisement['value'] = json_decode($advertisement['value'], true);
            $advertisement['value']['image'] = $this->getImage($advertisement['value']['image']);
        }
        $data = [
            'type' => $type['value'],
            'advertisement' => $advertisement['value']
        ];
        json($data);
    }

    public function doPagePopupadv()
    {
        $adv = pdo_get(prefix_table('cj_config'), ['key' => 'popup_adv']);
        if (empty($adv)) {
            json('', 2);
        }
        $adv = json_decode($adv['value'], true);
        if (empty($adv['image']) || empty($adv['appId'])) {
            json('', 2);
        }
        $adv['image'] = $this->getImage($adv['image']);
        json($adv);
    }


    public function doPageJump()
    {
        $data = pdo_getall(prefix_table('cj_program'));
        json($data);
    }


    public function get($key, $default = '') {
        return isset($this->gpc[$key]) ? $this->gpc[$key] : $default;
    }


    public function getImage($route, $path = false)
    {
        if (empty($route)) {
            return 'https://z.9xy.cn/Public/images/attach2.jpg';
        }

        if (is_numeric($route)) {
            $image = pdo_get(prefix_table('cj_resource'), ['id' => $route]);
            $route = $image['route'];
        }
        if ($path == true) {
            return $route;
        }
        if ($this->_is_oss()) {
            return $this->w['attachurl'] . $route;
        }

        return $this->w['siteroot'] . '/attachment/' . $route;
    }



    /**
     *  执行支付.
     */
    public function doPagePay() {
        //模拟模块数据 支付需要 正式版本无需这行代码
//		$this->module = array('name' => 'we7_wxappsample');
        //构造订单数据
        $orderid = $this->get('orderid', null);
        // 判断权限
        if (!$this->hasOrder($orderid)) {
            $this->result(1, '非用户订单');
        }
//		$this->result(1, '非用户订单');
        $order = array(
            'tid' => $orderid, //订单号
            'fee' => floatval(0.01), //支付参数
            'title' => '测试订单', //标题
        );
        $paydata = $this->pay($order);
        if (is_error($paydata)) {
            $this->result($paydata['errno'], $paydata['message']);
        }
        $this->result(0, '', $paydata);
    }

    // 判断当前用户有没这个订单
    private function hasOrder($orderid) {
        return true;
    }

    private function _is_oss()
    {
        $is_oss = pdo_get(prefix_table('cj_config'), ['key' => 'is_oss']);
        if ($is_oss) {
            $is_oss = $is_oss['value'];
        } else {
            $is_oss = 0;
        }

        return !empty($this->w['setting']['remote']['type']) && $is_oss == 0;
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


function yuan_img($imgpath = './tx.jpg') {
    $ext = pathinfo($imgpath);
    $src_img = null;
    switch ($ext['extension']) {
        case 'jpg':
            $src_img = imagecreatefromjpeg($imgpath);
            break;
        case 'png':
            $src_img = imagecreatefrompng($imgpath);
            break;
    }
    $wh = getimagesize($imgpath);
    $w = $wh[0];
    $h = $wh[1];
    $w = min($w, $h);
    $h = $w;
    $img = imagecreatetruecolor($w, $h);
    //这一句一定要有
    imagesavealpha($img, true);
    //拾取一个完全透明的颜色,最后一个参数127为全透明
    $bg = imagecolorallocatealpha($img, 255, 255, 255, 127);
    imagefill($img, 0, 0, $bg);
    $r = ceil($w / 2); //圆半径
    $y_x = $r; //圆心X坐标
    $y_y = $r; //圆心Y坐标
    for ($x = 0; $x < $w; $x++) {
        for ($y = 0; $y < $h; $y++) {
            $rgbColor = imagecolorat($src_img, $x, $y);
            if (((($x - $r) * ($x - $r) + ($y - $r) * ($y - $r)) < ($r * $r))) {
                imagesetpixel($img, $x, $y, $rgbColor);
            }
        }
    }
    return $img;
}
