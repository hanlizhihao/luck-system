<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('header', TEMPLATE_INCLUDEPATH)) : (include template('header', TEMPLATE_INCLUDEPATH));?>
<link href="./resource/css/app.css" rel="stylesheet">
<link href="../../../../addons/<?php  echo $_GPC['m'];?>/template/css/webuploader.css" rel="stylesheet">
<script type="application/javascript" src="../../../../addons/<?php  echo $_GPC['m'];?>/template/js/webuploader.min.js"></script>
<div class="main">
    <div class="panel panel-info">
        <div class="panel-heading">设置</div>
        <div class="panel-body">
            <form action="" method="post" class="form-horizontal" id="myform" role="form" enctype="multipart/form-data">
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">定时任务地址</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        https://<?php  echo $_SERVER['HTTP_HOST'];?>/app/index.php?i=<?php  echo $_W['uniacid'];?>&c=entry&op=receive_card&do=open&m=hu_couda&a=wxapp
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">小程序标题</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="title" placeholder="标题" value="<?php  echo $title['value'];?>" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">是否允许前台发布抽奖</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <select class="select valid" name="is_release" size="1" aria-invalid="false" style="width: 150px;">
                            <option <?php  if($is_release['value'] == 0) { ?>selected = "selected"<?php  } ?> value="0">不允许</option>
                            <option <?php  if($is_release['value'] == 1) { ?>selected = "selected"<?php  } ?> value="1">允许</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">无发布抽奖权限提醒:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="release_msg" placeholder="无发布抽奖提醒" value="<?php  echo $release_msg['value'];?>" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">机器人:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <select class="select valid" name="robot" size="1" aria-invalid="false" style="width: 150px;">
                            <option <?php  if($robot['value'] == 0) { ?>selected = "selected"<?php  } ?> value="0">不开启</option>
                            <option <?php  if($robot['value'] == 1) { ?>selected = "selected"<?php  } ?> value="1">开启</option>
                        </select>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">是否使用oss(修改后先保存):</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <select class="select valid" name="is_oss" size="1" aria-invalid="false" style="width: 150px;">
                            <option <?php  if($is_oss['value'] == 0) { ?>selected = "selected"<?php  } ?> value="0">使用</option>
                            <option <?php  if($is_oss['value'] == 1) { ?>selected = "selected"<?php  } ?> value="1">不使用</option>
                        </select>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">红包:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <select class="select valid" name="red_bag" size="1" aria-invalid="false" style="width: 150px;">
                            <option <?php  if($red_bag['value'] == 0) { ?>selected = "selected"<?php  } ?> value="0">不开启</option>
                            <option <?php  if($red_bag['value'] == 1) { ?>selected = "selected"<?php  } ?> value="1">开启</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">参与人初始人数:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="apply_number" placeholder="标题" value="<?php  echo $apply_number['value'];?>" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">审核开关:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <select class="select valid" name="switch_examine" size="1" aria-invalid="false" style="width: 150px;">
                            <option <?php  if($switch_examine['value'] == 0) { ?>selected = "selected"<?php  } ?> value="0">不开启</option>
                            <option <?php  if($switch_examine['value'] == 1) { ?>selected = "selected"<?php  } ?> value="1">开启</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">开奖通知模板消息id:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="open_prize_notice" placeholder="开奖通知模板消息id" value="<?php  echo $open_prize_notice['value'];?>" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">红包手续费(%):</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="red_package_fee" placeholder="红包手续费(%)" value="<?php  echo $red_package_fee['value'];?>" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">付费功能价格:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="pay_function" placeholder="付费功能价格" value="<?php  echo $pay_function['value'];?>" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">首页推荐功能价格:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="home_recommendation" placeholder="首页推荐功能价格" value="<?php  echo $home_recommendation['value'];?>" type="text">
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">广告方式:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <select class="select valid" name="advertisement_type" id="advertisement_type" size="1" aria-invalid="false" style="width: 150px;">
                            <option <?php  if($advertisement_type['value'] == 0) { ?>selected = "selected"<?php  } ?> value="0">流量组id</option>
                            <option <?php  if($advertisement_type['value'] == 1) { ?>selected = "selected"<?php  } ?> value="1">自定义</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="advertisement-0" <?php  if($advertisement_type['value'] == 1) { ?>style="display: none;"<?php  } ?>>
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">流量组id:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="advertisement" placeholder="unit-id" value="<?php echo $advertisement_type['value'] == 0 ? $advertisement['value']: ''?>" type="text">
                    </div>
                </div>

                <!--<div class="form-group" id="advertisement-1" <?php  if($advertisement_type['value'] == 0) { ?>style="display: none;"<?php  } ?>>
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">自定义广告:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <?php echo tpl_form_field_image('single-image', $advertisement_type['value'] == 1 ? $advertisement['value']['image'] : '');?>
                        <input class="form-control" name="appId" placeholder="appId必填" value="<?php echo $advertisement_type['value'] == 1 ? $advertisement['value']['appId'] : ''?>" type="text">
                        <input class="form-control" name="xcx_path" placeholder="路径" value="<?php echo $advertisement_type['value'] == 1 ? $advertisement['value']['xcx_path'] : ''?>" type="text">
                        <input class="form-control" name="extradata" placeholder="参数json格式" value="<?php echo $advertisement_type['value'] == 1 ? $advertisement['value']['extradata'] : ''?>" type="text">
                    </div>
                </div>-->

                <div class="form-group" id="advertisement-1" <?php  if($advertisement_type['value'] == 0) { ?>style="display: none;"<?php  } ?>>
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">自定义广告:</label>
                <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                    <div id="filePicker">选择图片</div>
                    <div class="input-group " style="margin-top:.5em;">
                        <img id="adv-img" src="<?php echo $advertisement_type['value'] == 1 ? $advertisement['value']['image_url'] : ''?>" onerror="this.src='./resource/images/nopic.jpg'; this.title='图片未找到.'" class="img-responsive img-thumbnail" width="150">
                        <em class="close" style="position:absolute; top: 0px; right: -14px;" title="删除这张图片" onclick="$('#adv-img').attr('src', './resource/images/nopic.jpg');$('#single-image').val('')">×</em>
                    </div>
                    <input type="hidden" value="<?php echo $advertisement_type['value'] == 1 ? $advertisement['value']['image'] : ''?>" name="single-image" id="single-image">
                    <input class="form-control" name="appId" placeholder="appId必填" value="<?php echo $advertisement_type['value'] == 1 ? $advertisement['value']['appId'] : ''?>" type="text">
                    <input class="form-control" name="xcx_path" placeholder="路径" value="<?php echo $advertisement_type['value'] == 1 ? $advertisement['value']['xcx_path'] : ''?>" type="text">
                    <input class="form-control" name="extradata" placeholder="参数json格式" value="<?php echo $advertisement_type['value'] == 1 ? $advertisement['value']['extradata'] : ''?>" type="text">
                </div>
                </div>


                <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">抽奖弹出层广告:</label>
                <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                    <div id="filePicker1">选择图片</div>
                    <div class="input-group " style="margin-top:.5em;">
                        <img id="adv-img1" src="<?php echo $popup_adv ? $popup_adv['value']['image_url'] : ''?>" onerror="this.src='./resource/images/nopic.jpg'; this.title='图片未找到.'" class="img-responsive img-thumbnail" width="150">
                        <em class="close" style="position:absolute; top: 0px; right: -14px;" title="删除这张图片" onclick="$('#adv-img1').attr('src', './resource/images/nopic.jpg');$('#single-image1').val('')">×</em>
                    </div>
                    <input type="hidden" value="<?php echo $popup_adv ? $popup_adv['value']['image'] : ''?>" name="single-image1" id="single-image1">
                    <input class="form-control" name="popup_appId" placeholder="appId必填" value="<?php echo $popup_adv ? $popup_adv['value']['appId'] : ''?>" type="text">
                    <input class="form-control" name="popup_xcx_path" placeholder="路径" value="<?php echo $popup_adv ? $popup_adv['value']['xcx_path'] : ''?>" type="text">
                    <input class="form-control" name="popup_extradata" placeholder="参数json格式" value="<?php echo $popup_adv ? $popup_adv['value']['extradata'] : ''?>" type="text">
                </div>
                </div>


                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">apiclient_cert.pem:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="apiclient_cert" placeholder="标题" value="<?php  echo $apiclient_cert['value'];?>" type="file">
                        <?php  if($apiclient_cert['value'] != '') { ?>
                        <input class="form-control" name="apiclient_cert" placeholder="标题" value="<?php  echo $apiclient_cert['value'];?>" type="text"  readonly>
                        <?php  } ?>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">apiclient_key.pem:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="apiclient_key" placeholder="标题" value="<?php  echo $apiclient_key['value'];?>" type="file">
                        <?php  if($apiclient_key['value'] != '') { ?>
                        <input class="form-control" name="apiclient_key" placeholder="标题" value="<?php  echo $apiclient_key['value'];?>" type="text" readonly>
                        <?php  } ?>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="" >
                        <input type="hidden" name="op" value="add">
                        <input type="hidden" id="attach_id" name="attach_id" value="">
                        <input type="submit" name="submit" id="submit" value="保存" class="btn btn-primary">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="application/javascript">
    $(function () {
        var adv = "<?php  echo $advertisement_type['value'];?>";
        if (adv == 1) {
            var uploader = new WebUploader.Uploader({
                auto : true,
                server : '<?php  echo $upload;?>',
                pick : '#filePicker',
                extensions: 'gif,jpg,jpeg,bmp,png'
                // 其他配置项
            });
            adv == 2;
            uploader.on('uploadSuccess', function(file, data) {
                $('#single-image').val(data.info);
                console.log(1);
                //console.log(file);
                //console.log(data);
                $('#adv-img').attr('src', '<?php  echo $image;?>&id=' + data.info);
                //$('#' + file.id).addClass('upload-state-done');
            });
        }

        var uploader1 = new WebUploader.Uploader({
            auto : true,
            server : '<?php  echo $upload;?>',
            pick : '#filePicker1',
            extensions: 'gif,jpg,jpeg,bmp,png'
            // 其他配置项
        });
        uploader1.on('uploadSuccess', function(file, data) {
            $('#single-image1').val(data.info);
            console.log(file);
            console.log(data);
            $('#adv-img1').attr('src', '<?php  echo $image;?>&id=' + data.info);
        });

        $('#advertisement_type').change(function () {
            if (adv == 0) {
                var uploader = new WebUploader.Uploader({
                    auto : true,
                    server : '<?php  echo $upload;?>',
                    pick : '#filePicker',
                    extensions: 'gif,jpg,jpeg,bmp,png'
                    // 其他配置项
                });
                adv == 2;
                uploader.on('uploadSuccess', function(file, data) {
                    $('#single-image').val(data.info);
                    console.log(2);
                    //console.log(file);
                    //console.log(data);
                    $('#adv-img').attr('src', '<?php  echo $image;?>&id=' + data.info);
                    //$('#' + file.id).addClass('upload-state-done');
                });

            }
            val = $(this).val();
            if (val == 0) {
                $('#advertisement-1').hide();
            } else {
                $('#advertisement-0').hide();
            }
            $('#advertisement-' + val).show();
        });

    });
</script>


