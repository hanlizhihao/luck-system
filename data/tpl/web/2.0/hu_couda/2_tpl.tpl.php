<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('header', TEMPLATE_INCLUDEPATH)) : (include template('header', TEMPLATE_INCLUDEPATH));?>
<link href="./resource/css/app.css" rel="stylesheet">


<div class="main">
    <div class="panel panel-info">
        <div class="panel-heading">设置</div>
        <div class="panel-body">
            <form action="" method="post" class="form-horizontal" id="myform" role="form" enctype="multipart/form-data">


                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">选择模板:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">

                        <select class="select valid" name="template_id" id="template" size="1" aria-invalid="false" style="width: 450px;">
                            <?php  if(is_array($list)) { foreach($list as $key => $item) { ?>
                            <option key="<?php  echo $key;?>" value="<?php  echo $item['template_id'];?>"><?php  echo $item['title'];?></option>
                            <?php  } } ?>
                        </select>

                    </div>
                </div>
                <span id="content">

                </span>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">小程序跳转路径:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <input class="form-control" name="path" placeholder="" value="pages/news/news" type="text">
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label">发送的用户:</label>
                    <div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">
                        <select class="select valid" name="member_id" id="member" size="1" aria-invalid="false" style="width: 450px;">
                            <option value="0">所有人</option>
                            <?php  if(is_array($member)) { foreach($member as $item) { ?>
                            <option value="<?php  echo $item['id'];?>"><?php  echo $item['nickname'];?></option>
                            <?php  } } ?>
                        </select>
                    </div>
                </div>


                <div class="form-group">
                    <label class="col-xs-12 col-sm-3 col-md-2 control-label"></label>
                    <div class="" >
                        <input type="submit" name="submit" id="submit" value="保存" class="btn btn-primary">
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>


<script type="application/javascript">
    var list_json = <?php  echo $list_json;?>;
    $(function () {
        $('#template').change(function () {
            var key = $('#template option:selected').attr('key');
            var html = '';
            $.each(list_json[key]['keyword'], function (k, v) {
                html += '<div class="form-group">' +
                    '<label class="col-xs-12 col-sm-3 col-md-2 control-label">'+v+':</label>' +
                    '<div class="col-sm-7 col-lg-8 col-md-8 col-xs-12">' +
                    '<input class="form-control" name="keyword[]" placeholder="" value="" type="text">' +
                    '</div>' +
                    '</div>';
            });
            $('#content').html(html);
        });
        $('#template').change();
    });
</script>
