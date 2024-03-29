<?php defined('IN_IA') or exit('Access Denied');?><!DOCTYPE html>
<html lang="zh-cn">
<head>
    <?php  global $_GPC, $_W;?>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php  if(!empty($_W['page']['title'])) { ?><?php  echo $_W['page']['title'];?><?php  } ?><?php  if(empty($_W['page']['copyright']['sitename'])) { ?><?php  if(IMS_FAMILY != 'x') { ?><?php  if(!empty($_W['page']['title'])) { ?> - <?php  } ?>微擎活动运维系统 -  Powered by WE7<?php  } ?><?php  } else { ?><?php  if(!empty($_W['page']['title'])) { ?> - <?php  } ?><?php  echo $_W['page']['copyright']['sitename'];?><?php  } ?></title>
    <meta name="keywords" content="<?php  if(empty($_W['page']['copyright']['keywords'])) { ?><?php  if(IMS_FAMILY != 'x') { ?>微擎活动运维系统<?php  } ?><?php  } else { ?><?php  echo $_W['page']['copyright']['keywords'];?><?php  } ?>" />
    <meta name="description" content="<?php  if(empty($_W['page']['copyright']['description'])) { ?><?php  if(IMS_FAMILY != 'x') { ?>微擎活动运维系统，简称微擎，微擎是一款基于微信营销免费开源的活动运维系统，为现代智慧活动场景提供最为高效、完善、靠谱的saas互联网技术解决方案。<?php  } ?><?php  } else { ?><?php  echo $_W['page']['copyright']['description'];?><?php  } ?>" />
    <link rel="shortcut icon" href="<?php  if(!empty($_W['setting']['copyright']['icon'])) { ?><?php  echo $_W['attachurl'];?><?php  echo $_W['setting']['copyright']['icon'];?><?php  } else { ?>./resource/images/favicon.ico<?php  } ?>" />
    <link href="../../../../addons/<?php  echo $_GPC['m']?>/template/common/bootstrap.min.css?v=20170802" rel="stylesheet">
    <link href="../../../../addons/<?php  echo $_GPC['m']?>/template/common/common.css?v=20170802" rel="stylesheet">
    <script type="text/javascript">
        if(navigator.appName == 'Microsoft Internet Explorer'){
            if(navigator.userAgent.indexOf("MSIE 5.0")>0 || navigator.userAgent.indexOf("MSIE 6.0")>0 || navigator.userAgent.indexOf("MSIE 7.0")>0) {
                alert('您使用的 IE 浏览器版本过低, 推荐使用 Chrome 浏览器或 IE8 及以上版本浏览器.');
            }
        }
        window.sysinfo = {
        <?php  if(!empty($_W['uniacid'])) { ?>'uniacid': '<?php  echo $_W['uniacid'];?>',<?php  } ?>
        <?php  if(!empty($_W['acid'])) { ?>'acid': '<?php  echo $_W['acid'];?>',<?php  } ?>
        <?php  if(!empty($_W['openid'])) { ?>'openid': '<?php  echo $_W['openid'];?>',<?php  } ?>
        <?php  if(!empty($_W['uid'])) { ?>'uid': '<?php  echo $_W['uid'];?>',<?php  } ?>
        'isfounder': <?php  if(!empty($_W['isfounder'])) { ?>1<?php  } else { ?>0<?php  } ?>,
        'siteroot': '<?php  echo $_W['siteroot'];?>',
            'siteurl': '<?php  echo $_W['siteurl'];?>',
            'attachurl': '<?php  echo $_W['attachurl'];?>',
            'attachurl_local': '<?php  echo $_W['attachurl_local'];?>',
            'attachurl_remote': '<?php  echo $_W['attachurl_remote'];?>',
            'module' : {'url' : '<?php  if(defined('MODULE_URL')) { ?><?php echo MODULE_URL;?><?php  } ?>', 'name' : '<?php  if(defined('IN_MODULE')) { ?><?php echo IN_MODULE;?><?php  } ?>'},
        'cookie' : {'pre': '<?php  echo $_W['config']['cookie']['pre'];?>'},
        'account' : <?php  echo json_encode($_W['account'])?>,
        };
    </script>
    <script>var require = { urlArgs: 'v=20170802' };</script>
    <script type="text/javascript" src="../../../../addons/<?php  echo $_GPC['m']?>/template/common/jquery.min.js"></script>
    <script type="text/javascript" src="../../../../addons/<?php  echo $_GPC['m']?>/template/common/bootstrap.min.js"></script>
    <script type="text/javascript" src="../../../../addons/<?php  echo $_GPC['m']?>/template/common/util.js?v=20170802"></script>

</head>
<body>
<div class="loader" style="display:none">
    <div class="la-ball-clip-rotate">
        <div></div>
    </div>
</div>


<div class="skin-black" data-skin="black">
    <?php  $frames = buildframes(FRAME);_calc_current_frames($frames);?>
    <div class="head">
        <style>
            .skin-black .head .navbar-nav>li>a:focus, .skin-black .head .navbar-nav>li>a:visited, .skin-black .head .navbar-default .navbar-nav>li>a{color:#fff!important}
            .skin-black .left-menu .left-menu-top{color:#fff!important}
            .skin-black .left-menu .list-group a{color:#fff!important}
        </style>
        <!--<nav class="navbar navbar-default" role="navigation">
            <div class="container <?php  if(!empty($frames['section']['platform_module_menu']['plugin_menu'])) { ?>plugin-head<?php  } ?>">
                <div class="navbar-header">
                    <a class="navbar-brand" href="<?php  echo $_W['siteroot'];?>">
                        <img src="<?php  if(!empty($_W['setting']['copyright']['blogo'])) { ?><?php  echo tomedia($_W['setting']['copyright']['blogo'])?><?php  } else { ?>./resource/images/logo/logo.png<?php  } ?>" class="pull-left" width="110px" height="35px">
                        <span class="version"><?php echo IMS_VERSION;?></span>
                    </a>
                </div>
                <?php  if(!empty($_W['uid'])) { ?>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav navbar-left">
                        <?php  global $top_nav?>
                        <?php  if(is_array($top_nav)) { foreach($top_nav as $nav) { ?>
                        <li<?php  if(FRAME == $nav['name']) { ?> class="active"<?php  } ?>><a href="<?php  if(empty($nav['url'])) { ?><?php  echo url('home/welcome/' . $nav['name']);?><?php  } else { ?><?php  echo $nav['url'];?><?php  } ?>" <?php  if(!empty($nav['blank'])) { ?>target="_blank"<?php  } ?>><?php  echo $nav['title'];?></a></li>
                        <?php  } } ?>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown"><i class="wi wi-user color-gray"></i><?php  echo $_W['user']['username'];?> <span class="caret"></span></a>
                            <ul class="dropdown-menu color-gray" role="menu">
                                <li>
                                    <a href="<?php  echo url('user/profile');?>" target="_blank"><i class="wi wi-account color-gray"></i> 我的账号</a>
                                </li>
                                <?php  if($_W['isfounder']) { ?>
                                <li class="divider"></li>
                                <li><a href="<?php  echo url('cloud/upgrade');?>" target="_blank"><i class="wi wi-update color-gray"></i> 自动更新</a></li>
                                <li><a href="<?php  echo url('system/updatecache');?>" target="_blank"><i class="wi wi-cache color-gray"></i> 更新缓存</a></li>
                                <li class="divider"></li>
                                <?php  } ?>
                                <li>
                                    <a href="<?php  echo url('user/logout');?>"><i class="fa fa-sign-out color-gray"></i> 退出系统</a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <?php  } else { ?>
                <div class="collapse navbar-collapse">
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown"><a href="<?php  echo url('user/register');?>">注册</a></li>
                        <li class="dropdown"><a href="<?php  echo url('user/login');?>">登陆</a></li>
                    </ul>
                </div>
                <?php  } ?>
            </div>
        </nav>-->
    </div>
    <div class="main" style="margin-left: 260px">
        <?php  if(!defined('IN_MESSAGE')) { ?>
        <div class="container">
            <?php  if(in_array(FRAME, array('account', 'system', 'adviertisement', 'wxapp', 'site')) && !in_array($_GPC['a'], array('news-show', 'notice-show'))) { ?>
            <div class="panel panel-content main-panel-content <?php  if(!empty($frames['section']['platform_module_menu']['plugin_menu'])) { ?>panel-content-plugin<?php  } ?>">
                <div class="panel-body clearfix main-panel-body <?php  if(!empty($_W['setting']['copyright']['leftmenufixed'])) { ?>menu-fixed<?php  } ?>">
                    <div class="left-menu" style="position: fixed;height: 100%">
                        <?php  if(empty($frames['section']['platform_module_menu']['plugin_menu'])) { ?>
                        <div class="left-menu-content">
                            <div class="left-menu-top skin-black">
                                <?php  if(!empty($_GPC['m']) && !in_array($_GPC['m'], array('keyword', 'special', 'welcome', 'default', 'userapi', 'service')) || defined('IN_MODULE')) { ?>
                                <div class="account-info-name">
                                    <a href="<?php  echo url('home/welcome/account')?>"><i class="wi wi-back-circle"></i></a>
                                    <span class="account-name"><a href="<?php  echo url('home/welcome/account')?>"><?php  echo $_W['account']['name'];?></a></span>
                                </div>
                                <div class="module-info-name">
                                    <?php  if(file_exists(IA_ROOT. "/addons/". $_W['current_module']['name']. "/icon-custom.jpg")) { ?>
                                    <img src="<?php  echo tomedia("addons/".$_W['current_module']['name']."/icon-custom.jpg")?>" class="head-app-logo" onerror="this.src='./resource/images/gw-wx.gif'">
                                    <?php  } else { ?>
                                    <img src="<?php  echo tomedia("addons/".$_W['current_module']['name']."/icon.jpg")?>" class="head-app-logo" onerror="this.src='./resource/images/gw-wx.gif'">
                                    <?php  } ?>
                                    <span class="name"><?php  echo $_W['current_module']['title'];?></span>
                                </div>
                                <!-- 兼容历史性问题：模块内获取不到模块信息$module的问题-start -->
                                <?php  if(CRUMBS_NAV == 1) { ?>
                                <?php  global $module;?>
                                <?php  } ?>
                                <!-- end -->
                                <?php  } else if(FRAME == 'account') { ?>
                                <div class="text-center"><img src="<?php  echo tomedia('headimg_'.$_W['account']['acid'].'.jpg')?>?time=<?php  echo time()?>" class="head-logo"></div>
                                <div class="text-center account-name"><?php  echo $_W['account']['name'];?></div>
                                <div class="text-center">
                                    <?php  if($_W['account']['level'] == 1 || $_W['account']['level'] == 3) { ?>
                                    <span class="label label-primary">订阅号</span><?php  if($_W['account']['level'] == 3) { ?><span class="label label-primary">已认证</span><?php  } ?>
                                    <?php  } ?>
                                    <?php  if($_W['account']['level'] == 2 || $_W['account']['level'] == 4) { ?>
                                    <span class="label label-primary">服务号</span> <?php  if($_W['account']['level'] == 4) { ?><span class="label label-primary">已认证</span><?php  } ?>
                                    <?php  } ?>
                                    <?php  if($_W['uniaccount']['isconnect'] == 0) { ?>
                                    <span class="label label-danger" data-toggle="popover">未接入</span>
                                    <script>
                                        $(function(){
                                            var url = "<?php  echo url('account/post', array('uniacid' => $_W['account']['uniacid'], 'acid' => $_W['acid']));?>";
                                            $('[data-toggle="popover"]').popover({
                                                trigger: 'manual',
                                                html: true,
                                                placement: 'bottom',
                                                content: '<i class="wi wi-warning-sign"></i>未接入微信公众号' +
                                                '<a href="' +
                                                url +
                                                '">立即接入</a>'
                                            }).on("mouseenter", function() {
                                                var _this = this;
                                                $(this).popover("show");
                                                $(this).siblings(".popover").on("mouseleave", function() {
                                                    $(_this).popover('hide');
                                                });
                                            }).on("mouseleave", function() {
                                                var _this = this;
                                                setTimeout(function() {
                                                    if(!$(".popover:hover").length) {
                                                        $(_this).popover("hide")
                                                    }
                                                }, 100);
                                            });
                                        });
                                    </script>
                                    <?php  } ?>
                                </div>
                                <div class="text-center operate">
                                    <a href="<?php  echo url('utility/emulator');?>" target="_blank"><i class="wi wi-iphone" data-toggle="tooltip" data-placement="bottom" title="模拟测试"></i></a>
                                    <?php  if(uni_permission($_W['uid'], $_W['uniacid']) != ACCOUNT_MANAGE_NAME_OPERATOR) { ?>
                                    <a href="<?php  echo url('account/post', array('uniacid' => $_W['account']['uniacid'], 'acid' => $_W['acid']))?>" data-toggle="tooltip" data-placement="bottom" title="公众号设置"><i class="wi wi-appsetting"></i></a>
                                    <?php  } ?>
                                    <a href="<?php  echo url('account/display')?>"  data-toggle="tooltip" data-placement="bottom" title="切换公众号"><i class="wi wi-changing-over"></i></a>
                                </div>
                                <?php  } ?>

                                <?php  if(FRAME == 'system') { ?>
                                <div class="font-lg title-setting"><i class="wi wi-setting"></i> 系统管理</div>
                                <?php  } ?>
                                <?php  if(FRAME == 'site') { ?>
                                <div class="font-lg title-site"><i class="wi wi-system-site"></i> 站点管理</div>
                                <?php  } ?>
                                <!-- 					<?php  if(FRAME == 'adviertisement') { ?>
                                                        <div class="font-lg title-ad"><i class="wi wi-ad"></i>广告联盟</div>
                                                    <?php  } ?> -->
                                <?php  if(FRAME == 'wxapp') { ?>
                                <div class="text-center"><img src="<?php  echo tomedia('headimg_'.$_W['account']['acid'].'.jpg')?>?time=<?php  echo time()?>" class="head-logo"></div>
                                <div class="text-center wxapp-name font-lg"><?php  echo $wxapp_info['name'];?></div>
                                <div class="text-center wxapp-version"><?php  echo $version_info['version'];?></div>
                                <div class="text-center operate">
                                    <a href="<?php  echo url('wxapp/version/display', array('uniacid' => $version_info['uniacid']))?>"><i class="wi wi-cut" data-toggle="tooltip" data-placement="bottom" title="切换版本"></i></a>
                                    <?php  if(in_array($role, array(ACCOUNT_MANAGE_NAME_OWNER, ACCOUNT_MANAGE_NAME_MANAGER)) || $_W['isfounder']) { ?>
                                    <a href="<?php  echo url('wxapp/manage/display', array('uniacid' => $version_info['uniacid']))?>"><i class="wi wi-text" data-toggle="tooltip" data-placement="bottom" title="管理"></i></a>
                                    <?php  } ?>
                                    <a href="<?php  echo url('wxapp/display')?>"><i class="wi wi-small-routine" data-toggle="tooltip" data-placement="bottom" title="切换小程序"></i></a>
                                </div>
                                <?php  } ?>
                            </div>
                            <?php  if(is_array($frames['section'])) { foreach($frames['section'] as $frame_section_id => $frame_section) { ?>
                            <?php  if(!isset($frame_section['is_display']) || !empty($frame_section['is_display'])) { ?>
                            <div class="panel panel-menu">
                                <?php  if($frame_section['title']) { ?>
                                <div class="panel-heading">
                                    <span class="" data-toggle="collapse" data-target="#frame-<?php  echo $frame_section_id;?>" onclick="util.cookie.set('menu_fold_tag:<?php  echo $frame_section_id;?>', util.cookie.get('menu_fold_tag:<?php  echo $frame_section_id;?>') == 1 ? 0 : 1)"><?php  echo $frame_section['title'];?><i class="wi wi-down-sign-s pull-right"></i></span>
                                </div>
                                <?php  } ?>
                                <ul class="list-group collapse <?php  if($_GPC['menu_fold_tag:'.$frame_section_id] == 0) { ?>in<?php  } ?>" id="frame-<?php  echo $frame_section_id;?>">

                                    <?php  if(is_array($frame_section['menu'])) { foreach($frame_section['menu'] as $menu_id => $menu) { ?>
                                    <?php  if(!empty($menu['is_display'])) { ?>
                                    <?php  if($menu_id == 'platform_module_more') { ?>
                                    <li class="list-group-item list-group-more">
                                        <a href="<?php  echo url('profile/module');?>" target="_blank"><span class="label label-more">更多应用</span></a>
                                    </li>
                                    <?php  } else if(($menu_id != 'platform_module_permissions' && (strpos($menu['title'], '开通多开') === false || $_W['user']['type'] == 0))) { ?>

                                    <li class="list-group-item <?php  if($menu['active']) { ?>active<?php  } ?>">
                                        <a href="<?php  echo $menu['url'];?>" class="text-over" <?php  if($frame_section_id == 'platform_module') { ?>target="_blank"<?php  } ?>>
                                        <?php  if($menu['icon']) { ?>
                                        <?php  if($frame_section_id == 'platform_module') { ?>
                                        <img src="<?php  echo $menu['icon'];?>"/>
                                        <?php  } else { ?>
                                        <i class="<?php  echo $menu['icon'];?>"></i>
                                        <?php  } ?>
                                        <?php  } ?>
                                        <?php  echo $menu['title'];?>
                                        </a>
                                    </li>
                                    <?php  } ?>
                                    <?php  } ?>
                                    <?php  } } ?>
                                </ul>
                            </div>
                            <?php  } ?>
                            <?php  } } ?>
                        </div>
                        <?php  } else { ?>
                        <div class="plugin-menu clearfix">
                            <div class="plugin-menu-main pull-left">
                                <ul class="list-group">
                                    <li class="list-group-item<?php  if($_W['current_module']['name'] == $frames['section']['platform_module_menu']['plugin_menu']['main_module']) { ?> active<?php  } ?>">
                                        <a href="<?php  echo url('home/welcome/ext', array('m' => $frames['section']['platform_module_menu']['plugin_menu']['main_module']))?>">
                                            <i class="wi wi-main-apply"></i>
                                            <div>主应用</div>
                                        </a>
                                    </li>
                                    <li class="list-group-item">
                                        <div>插件</div>
                                    </li>
                                    <?php  if(is_array($frames['section']['platform_module_menu']['plugin_menu']['menu'])) { foreach($frames['section']['platform_module_menu']['plugin_menu']['menu'] as $plugin_name => $plugin) { ?>
                                    <li class="list-group-item<?php  if($_W['current_module']['name'] == $plugin_name) { ?> active<?php  } ?>">
                                        <a href="<?php  echo url('home/welcome/ext', array('m' => $plugin_name))?>">
                                            <img src="<?php  echo $plugin['icon'];?>" alt="" class="img-icon" />
                                            <div><?php  echo $plugin['title'];?></div>
                                        </a>
                                    </li>
                                    <?php  } } ?>
                                </ul>
                                <?php  unset($plugin_name);?>
                                <?php  unset($plugin);?>
                            </div>
                            <div class="plugin-menu-sub pull-left">
                                <?php  if(is_array($frames['section'])) { foreach($frames['section'] as $frame_section_id => $frame_section) { ?>
                                <?php  if(!isset($frame_section['is_display']) || !empty($frame_section['is_display'])) { ?>
                                <div class="panel panel-menu">
                                    <?php  if($frame_section['title']) { ?>
                                    <div class="panel-heading">
                                        <span class="collapsed" data-toggle="collapse" data-target="#frame0-<?php  echo $frame_section_id;?>" aria-expanded="true" aria-controls="frame0-<?php  echo $frame_section_id;?>"><?php  echo $frame_section['title'];?><i class="wi wi-down-sign-s pull-right"></i></span>
                                    </div>
                                    <?php  } ?>
                                    <ul class="list-group panel-collapse collapse in" id="frame0-<?php  echo $frame_section_id;?>">
                                        <?php  if(is_array($frame_section['menu'])) { foreach($frame_section['menu'] as $menu_id => $menu) { ?>
                                        <?php  if(!empty($menu['is_display'])) { ?>
                                        <?php  if($menu_id == 'platform_module_more') { ?>
                                        <li class="list-group-item list-group-more">
                                            <a href="<?php  echo url('profile/module');?>" target="_blank"><span class="label label-more">更多应用</span></a>
                                        </li>
                                        <?php  } else { ?>
                                        <li class="list-group-item <?php  if($menu['active']) { ?>active<?php  } ?>">
                                            <a href="<?php  echo $menu['url'];?>" class="text-over" <?php  if($frame_section_id == 'platform_module') { ?>target="_blank"<?php  } ?>>
                                            <?php  if($menu['icon']) { ?>
                                            <?php  if($frame_section_id == 'platform_module') { ?>
                                            <img src="<?php  echo $menu['icon'];?>"/>
                                            <?php  } else { ?>
                                            <i class="<?php  echo $menu['icon'];?>"></i>
                                            <?php  } ?>
                                            <?php  } ?>
                                            <?php  echo $menu['title'];?>
                                            </a>
                                        </li>
                                        <?php  } ?>
                                        <?php  } ?>
                                        <?php  } } ?>
                                    </ul>
                                </div>
                                <?php  } ?>
                                <?php  } } ?>
                            </div>
                        </div>
                        <?php  } ?>
                    </div>
                    <div class="right-content">
                        <div class="content" style="margin-left: 230px;">
                            <!--系统提示-->
                            <?php  if($_COOKIE['private_app_notice']) { ?>
                            <div class="system-tips we7-body-alert">
                                <div class="container text-right">
						<span class="alert-info">
							<a href="javascript:;">
								<?php  echo $_COOKIE['private_app_notice'];?>
							</a>
							<span class="tips-close" onclick="check_setmeal_hide();" ><i class="wi wi-error-sign"></i></span>
						</span>
                                </div>
                            </div>
                            <?php  setcookie('private_app_notice', '', -1);?>
                            <?php  } ?>
                            <!--end  系统提示-->
                            <?php  if(empty($_COOKIE['check_setmeal']) && !empty($_W['account']['endtime']) && ($_W['account']['endtime'] - TIMESTAMP < (6*86400))) { ?>
                            <div class="system-tips we7-body-alert" id="setmeal-tips">
                                <div class="container text-right">
                                    <div class="alert-info">
                                        <a href="<?php  if($_W['isfounder']) { ?><?php  echo url('user/edit', array('uid' => $_W['account']['uid']));?><?php  } else { ?>javascript:void(0);<?php  } ?>">
                                            您的服务有效期限：<?php  echo date('Y-m-d', $_W['account']['starttime']);?> ~ <?php  echo date('Y-m-d', $_W['account']['endtime']);?>.
                                            <?php  if($_W['account']['endtime'] < TIMESTAMP) { ?>
                                            目前已到期，请联系管理员续费
                                            <?php  } else { ?>
                                            将在<?php  echo floor(($_W['account']['endtime'] - strtotime(date('Y-m-d')))/86400);?>天后到期，请及时付费
                                            <?php  } ?>
                                        </a>
                                        <span class="tips-close" onclick="check_setmeal_hide();"><i class="wi wi-error-sign"></i></span>
                                    </div>
                                </div>
                            </div>
                            <script>
                                function check_setmeal_hide() {
                                    util.cookie.set('check_setmeal', 1, 1800);
                                    $('#setmeal-tips').hide();
                                    return false;
                                }
                            </script>
                            <?php  } ?>
                            <?php  } ?>
                            <?php  } ?>
