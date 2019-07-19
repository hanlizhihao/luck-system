<?php defined('IN_IA') or exit('Access Denied');?>

<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('header', TEMPLATE_INCLUDEPATH)) : (include template('header', TEMPLATE_INCLUDEPATH));?>

<script type="text/javascript" src="../../../../addons/<?php  echo $name;?>/template/lib/My97DatePicker/4.8/WdatePicker.js"></script>
<link rel="stylesheet" href="https://fengyuanchen.github.io/cropper/css/cropper.css"></link>
<link rel="stylesheet" href="../../../../addons/<?php  echo $name;?>/template/css/cropper.min.css">
<link rel="stylesheet" href="../../../../addons/<?php  echo $name;?>/template/css/ImgCropping.css">
<style>
    .webuploader-pick{
        border: 1px solid;
        background-color:#428bca;
        color: #fff;
        border-radius: 4px;
        width: 82px !important;
        height: 32px !important;
        height:32px;
        line-height:32px;
        overflow:hidden;
        text-align:center
    }
</style>
<div class="main">
    <div class="panel panel-info">
        <div class="panel-heading">添加</div>
        <div class="panel-body">
            <form action="" method="get" class="form-horizontal" id="myform" role="form">

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">*标题</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="title" placeholder="标题" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">*赞助商：</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="uname" placeholder="赞助商" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">开奖方式(人数抽奖需要关闭机器人):</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <select class="select valid" name="type" id="type-select" size="1" style="width: 150px;" aria-invalid="false">
                            <option value="1">时间</option>
                            <option value="2">人数</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="type-content">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">*开奖时间：</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control"  onfocus="WdatePicker({ dateFmt: 'yyyy-MM-dd HH:mm:ss' })" id="logmax"  name="opentime" placeholder="开奖时间" type="text" style="width:180px;">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">组团人数:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <select class="select valid" name="max_group_num" size="1" aria-invalid="false" style="width: 150px;">
                            <option value="0">单人抽奖</option>
                            <!--<option value="3">3人团</option>
                            <option value="6">6人团</option>
                            <option value="9">9人团</option>-->
                        </select>
                    </div>
                </div>

                <!--<div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">path：</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="path" placeholder="path" type="text">
                    </div>
                </div>-->


                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">复制/跳转小程序公众号:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <select class="select valid" name="copy_type" id="copy_type" size="1" aria-invalid="false" style="width: 150px;">
                            <option value="1">小程序</option>
                            <option value="2">公众号</option>
                        </select>
                    </div>
                </div>

                <span id="copy-1">
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">关联程序appid：</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="appid" placeholder="appid" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">小程序名：</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="app_name" placeholder="app_name" type="text">
                    </div>
                </div>
                </span>

                <span id="copy-2" style="display: none;">
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">公众号：</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="wechat_no" placeholder="公众号" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">公众号描述：</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="wechat_title" placeholder="公众号描述" type="text">
                    </div>
                </div>
                </span>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">一句话广告：</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="brief_description" placeholder="一句话广告" type="text">
                    </div>
                </div>



                <!--<div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">wechat_no：</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="wechat_no" placeholder="wechat_no" type="text">
                    </div>
                </div>-->

                <!--<span id="extraData_add">
                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">extraData：</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="extraData" placeholder="extraData" type="text" w>
                    </div>
                </div>
                </span>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="" >
                        <input type="button" name="submit" value="添加" id="add-extraData" class="btn btn-primary">
                    </div>
                </div>-->


                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">*一等奖奖品:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="fir_val" id="fir_val" placeholder="一等奖奖品" type="text">
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">一等奖类型:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <select class="select valid select-voucher" data-id="fir" name="fir_ptype" size="1" style="width: 150px;" aria-invalid="false">
                            <option value="0">实物</option>
                            <?php  if($goods) { ?><option value="3">卡券<?php  } ?>
                        </select>
                        <select id="fir_ptype_voucher" class="select valid voucher-list" data-id="fir" name="fir_voucher" size="1" style="width: 150px;display: none;" aria-invalid="false">
                            <?php  if(is_array($goods)) { foreach($goods as $item) { ?>
                            <option value="<?php  echo $item['id'];?>"><?php  echo $item['goods_name'];?></option>
                            <?php  } } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">*一等奖数量:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="fir_num" placeholder="一等奖数量" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">二等奖奖品:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="sec_val" id="sec_val" placeholder="" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">二等奖奖品:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <select class="select select-voucher" data-id="sec" name="sec_ptype" size="1" style="width: 150px;">
                            <option value="0">实物</option>
                            <?php  if($goods) { ?><option value="3">卡券<?php  } ?>
                        </select>
                        <select id="sec_ptype_voucher" class="select valid voucher-list" data-id="sec" name="sec_voucher" size="1" style="width: 150px;display: none;" aria-invalid="false">
                            <?php  if(is_array($goods)) { foreach($goods as $item) { ?>
                            <option value="<?php  echo $item['id'];?>"><?php  echo $item['goods_name'];?></option>
                            <?php  } } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">二等奖数量:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="sec_num" placeholder="" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">三等奖奖品:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control " name="trd_val" id="trd_val" placeholder="" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">三等奖类型:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <select class="select select-voucher" data-id="trd" name="trd_ptype" size="1" style="width: 150px;">
                            <option value="0">实物</option>
                            <?php  if($goods) { ?><option value="3">卡券<?php  } ?>
                        </select>
                        <select id="trd_ptype_voucher" class="select valid voucher-list" data-id="trd" name="trd_voucher" size="1" style="width: 150px;display: none;" aria-invalid="false">
                            <?php  if(is_array($goods)) { foreach($goods as $item) { ?>
                            <option value="<?php  echo $item['id'];?>"><?php  echo $item['goods_name'];?></option>
                            <?php  } } ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">三等奖数量:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="trd_num" placeholder="" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">详情类型:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <select class="select valid" name="desc_type" id="desc_type" size="1" style="width: 150px;" aria-invalid="false">
                            <option value="0">文字</option>
                            <option value="1">图片</option>
                        </select>
                    </div>
                </div>
                <div class="form-group" style="display: none;" id="image-type">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">详细图片:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12" style="padding-top:8px;">
                        <div id="imgPicker" style="float:left">添加图片</div>
                    </div>
                </div>
                <div class="form-group" id="image-type-list" style="margin-top: 75px;display: none">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <span id="fileList">

                    </span>

                </div>



                <div class="form-group" id="text-type">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">详细说明:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <textarea name="description" cols="" rows="" class="textarea" placeholder="" dragonfly="true" style="margin: 0px; height: 107px; width: 1051px;"></textarea>
                    </div>

                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">封面图片:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12" >
                        <input type="button" name="submit" value="更换图片" id="replaceImg" class="btn btn-primary">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="" >
                        <img style="display: none" id="finalImg" src="" width="50%" height="50%">
                    </div>
                </div>



                <div style="display: none" class="tailoring-container">
                    <div class="black-cloth" onClick="closeTailor(this)"></div>
                    <div class="tailoring-content">
                        <div class="tailoring-content-one">
                            <label title="上传图片" for="chooseImg" class="l-btn choose-btn">
                                <input type="file" accept="image/jpg,image/jpeg,image/png" name="file" id="chooseImg" class="hidden"
                                       onChange="selectImg(this)">
                                选择图片
                            </label>
                            <div class="close-tailoring" onclick="closeTailor(this)">×</div>
                        </div>
                        <div class="tailoring-content-two">
                            <div class="tailoring-box-parcel">
                                <img id="tailoringImg">
                            </div>
                            <div class="preview-box-parcel">
                                <p>图片预览：</p>
                                <div class="square previewImg"></div>
                                <!--<div class="circular previewImg"></div>-->
                            </div>
                        </div>
                        <div class="tailoring-content-three">
                            <input type="button" class="l-btn sureCut" id="sureCut" value="确定">
                        </div>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="" >
                        <input type="hidden" name="op" value="add">
                        <input type="hidden" id="attach_id" name="attach_id" value="">
                        <input type="button" name="submit" id="submit" value="提交" class="btn btn-primary">
                    </div>
                </div>


            </form>
        </div>
    </div>
</div>


<script type="text/javascript" src="../../../../addons/<?php  echo $name;?>/template/js/cropper.min.js"></script>

<link rel="stylesheet" type="text/css" href="../../../../addons/<?php  echo $name;?>/template/js/webuploader/css/globle.css?1" />

<script type="text/javascript" src="../../../../addons/<?php  echo $name;?>/template/js/webuploader/js/webuploader.min.js"></script>
<script type="application/javascript">
    var href = "<?php  echo $this->createWeburl('index');?>";
    function selectImg(file) {
        if (!file.files || !file.files[0]) {
            return;
        }
        var reader = new FileReader();
        reader.onload = function (evt) {
            var replaceSrc = evt.target.result;
            //更换cropper的图片
            $('#tailoringImg').cropper('replace', replaceSrc, false);//默认false，适应高度，不失真
        }
        reader.readAsDataURL(file.files[0]);
    }
    //关闭裁剪框
    function closeTailor() {
        $(".tailoring-container").toggle();
    }

    function remove(file){
        $('#image-'+file).remove();
    }


    $(function () {
        $('#desc_type').change(function () {
            if ($(this).val() == 0) {
                $('#image-type,#image-type-list').hide();
                $('#text-type').show();
            } else {
                $('#image-type,#image-type-list').show();
                $('#text-type').hide();
            }
        });

        $('#copy_type').change(function () {
            if ($(this).val() == 1) {
                $('#copy-1').show();
                $('#copy-2').hide();
            } else {
                $('#copy-2').show();
                $('#copy-1').hide();
            }
        });

        $('.select-voucher').click(function () {
            var id = $(this).data('id');
            if ($(this).val() == 0) {
                $('#' + id + '_ptype_voucher').hide();
            } else {
                $('#' + id + '_ptype_voucher').show();console.log('#' + id + '_ptype_voucher');
                $('#' + id + '_ptype_voucher').change();
            }
        });
        $('#fir_ptype_voucher,#sec_ptype_voucher,#trd_ptype_voucher').change(function () {

            id = $(this).data('id');console.log(id);
            $val = $("#"+id+"_ptype_voucher option:selected").text();
            $('#' + id + '_val').val($val);
        });
        

        var $list = $('#fileList');

        var uploader = WebUploader.create({
            auto: true,// 选完文件后，是否自动上传。
            swf: '/static/admin/js/webupload/Uploader.swf',// swf文件路径 换成你的接收路径
            server: "/app/index.php?i=<?php  echo $uniacid?>&c=entry&op=receive_card&do=upload&m=hu_couda&a=wxapp",// 文件接收服务端 换成你的接收路径
            duplicate :true,// 重复上传图片，true为可重复false为不可重复
            pick: '#imgPicker',// 选择文件的按钮
            accept: {
                title: 'Images',
                extensions: 'gif,jpg,jpeg,bmp,png',
                mimeTypes: 'image/jpg,image/jpeg,image/png'
            },

            'onUploadSuccess': function(file, data, response) {
                var up_src = data._raw.replace("\\", "\/");
                console.log(file);
                console.log(data);

                img = '<span id="image-'+data.info+'">' +
                      '<img>' +
                      '<label style="margin-top: -90px;"><a class="file-panel" href="javascript:;" onclick="remove('+data.info+')">' +
                    '<span class="fa fa-close"></span></a></label><input type="hidden" name="images[]" value="'+data.info+'"></span>';

                //上传成功后显示图片
                var $li = $(img),
                    $img = $li.find('img');

                // $list为容器jQuery实例
                $list.append( $li );

                // 创建缩略图 如果为非图片文件，可以不用调用此方法 100（宽） x 100（高）
                uploader.makeThumb( file, function( error, src ) {
                    if ( error ) {
                        $img.replaceWith('<span>不能预览</span>');
                        return;
                    }
                    $img.attr( 'src', src );
                }, 100, 100 );
            }
        });

        // 当有文件添加进来的时候
        uploader.on( 'fileQueued', function( file ) {

        });

        // 文件上传成功
        uploader.on( 'uploadSuccess', function( file ) {
            $( '#'+file.id ).find('p.state').text('上传成功！');
        });

        // 文件上传失败，显示上传出错。
        uploader.on( 'uploadError', function( file ) {
            $( '#'+file.id ).find('p.state').text('上传出错!');
        });





        $("#type-select").change(function () {
            console.log($(this).val());

            if ($(this).val() == 1) {
                html = '<label class="col-xs-12 col-sm-3 col-md-2 control-label">*开奖时间：</label>' +
                    '<div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">' +
                    '<input class="form-control"  onfocus="WdatePicker({ dateFmt: \'yyyy-MM-dd HH:mm:ss\' })" id="logmax"  name="opentime" placeholder="开奖时间" type="text" style="width:180px;">' +
                    '</div>';
            } else {
                html = '<label class="col-xs-12 col-sm-3 col-md-2 control-label">*开奖人数：</label>' +
                    '<div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">' +
                    '<input class="form-control"  name="opentime" placeholder="开奖人数" type="text" style="width:180px;">' +
                    '</div>';
            }
            $('#type-content').html(html);
        });

        $('#submit').click(function () {
            $.post('', $('#myform').serialize(), function (data) {
                if (data.status == 1) {
                    location.href = href;
                } else {
                    alert(data.info);
                }
            }, 'json');
        });

        $('#add-extraData').click(function () {
            html = '<div class="form-group">' +
                '<label class="col-xs-12 col-sm-3 col-md-2 control-label">extraData:</label>' +
                '<div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">' +
                '<input class="form-control" name="keys[]" placeholder="" type="text">' +
                '</div>' +
                '</div>';

            html += '<div class="form-group">' +
                '<label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>' +
                '<div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">' +
                '<input class="form-control" name="vals[]" placeholder="" type="text">' +
                '</div>' +
                '</div>';

            $('#extraData_add').append(html);

        });

        $("#replaceImg").on("click", function () {
            console.log(11512);
            $(".tailoring-container").toggle();
        });

        //cropper图片裁剪 本/\模\/块\/来/\自\/阿/\莫\/之/\家！
        $('#tailoringImg').cropper({
            aspectRatio: 2 / 1,//默认比例
            preview: '.previewImg',//预览视图
            guides: false,  //裁剪框的虚线(九宫格)
            autoCropArea: 0.5,  //0-1之间的数值，定义自动剪裁区域的大小，默认0.8
            movable: false, //是否允许移动图片
            dragCrop: true,  //是否允许移除当前的剪裁框，并通过拖动来新建一个剪裁框区域
            movable: true,  //是否允许移动剪裁框
            resizable: true,  //是否允许改变裁剪框的大小
            zoomable: false,  //是否允许缩放图片大小
            mouseWheelZoom: false,  //是否允许通过鼠标滚轮来缩放图片
            touchDragZoom: true,  //是否允许通过触摸移动来缩放图片
            rotatable: true,  //是否允许旋转图片
            crop: function (e) {
                // 输出结果数据裁剪图像。
            }
        });


        $("#sureCut").on("click", function () {
            var urlConfig = {upload: '/app/index.php?i=<?php  echo $uniacid?>&c=entry&op=receive_card&do=upload&m=hu_couda&a=wxapp&jietu=1'}
            if ($("#tailoringImg").attr("src") == null) {
                return false;
            } else {
                $('#tailoringImg').cropper("getCroppedCanvas").toBlob(function (blob) {
                    var formData = new FormData();
                    formData.append('file', blob, 'file')
                    $.ajax({
                        method: "post",
                        url: urlConfig.upload, //用于文件上传的服务器端请求地址
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function (result) {
                            console.log(result);
                            if (typeof result == "string") {
                                result = $.parseJSON(result);
                            }
                            if (result.data && result.data.length) {
                                currentUploadDom.parent().next().next().show();
                                currentUploadDom.attr("src", result.data[0]);
                                //close
                                cutView.hide();
                                stopCropper();
                            }
                            if (result.status == 1) {
                                $('#attach_id').val(result.info);
                                alert('裁剪成功')
                            } else {
                                alert('请上传图片')
                            }

                        }
                    });

                });
                //裁剪完成预览
                var cas = $('#tailoringImg').cropper('getCroppedCanvas');//获取被裁剪后的canvas
                var base64url = cas.toDataURL('image/png'); //转换为base64地址形式
                $("#finalImg").prop("src", base64url).show();//显示为图片的形式
                //关闭裁剪框
                closeTailor();
            }
        });
    });
</script>

