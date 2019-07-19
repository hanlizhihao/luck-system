<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('header', TEMPLATE_INCLUDEPATH)) : (include template('header', TEMPLATE_INCLUDEPATH));?>
<link href="./resource/css/app.css" rel="stylesheet">
<ul class="nav nav-tabs">
    <li class="active"><a href="javascript:;">用户列表</a></li>
</ul>

<div class="panel panel-info">
    <div class="panel-heading">搜索</div>
    <div class="panel-body">
        <form action="./index.php" method="get" class="form-horizontal">
            <input type="hidden" name="c" value="site">
            <input type="hidden" name="a" value="entry">
            <input type="hidden" name="m" value="hu_couda">
            <input type="hidden" name="do" value="member">

            <div class="form-group">
                <label class="col-xs-12 col-sm-3 col-md-2 control-label">昵称</label>
                <div class="col-sm-8 col-xs-12">
                    <input type="text" class="form-control" name="keyword" style="width: 500px;" value="<?php  echo $keyword;?>" />
                </div>
                <div class="pull-right col-xs-12 col-sm-3 col-md-2 col-lg-2">
                    <button type="submit" class="btn btn-default"><i class="fa fa-search"></i> 搜索</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="panel panel-default">
    <div class="table-responsive panel-body">
        <table class="table table-hover">
            <thead class="navbar-inner">
            <tr>
                <th style="width:60px;">id</th>
                <th style="width:80px;">昵称</th>
                <th style="width:100px;">性别</th>
                <th style="width:100px;">省份</th>
                <th style="width:100px;">城市</th>
                <th style="width:100px;">能否发布抽奖</th>
                <th style="width:100px;">关联店铺</th>
                <th style="width:100px;">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php  if(is_array($list)) { foreach($list as $item) { ?>
            <tr>
                <td><?php  echo $item['id'];?></td>
                <td><?php  echo $item['nickname'];?></td>
                <td>
                    <?php  if($item['gender'] == 1) { ?>
                    男
                    <?php  } else if($item['gender'] == 2) { ?>
                    女
                    <?php  } else { ?>
                    未知
                    <?php  } ?>
                </td>
                <td><?php  echo $item['province'];?></td>
                <td><?php  echo $item['city'];?></td>
                <td>
                    <select name="is_release" class="is_release" id="<?php  echo $item['id'];?>" size="1" aria-invalid="false" style="width: 150px;">
                        <option <?php  if($item['is_release'] == 0) { ?>selected = "selected"<?php  } ?> value="0">不允许</option>
                        <option <?php  if($item['is_release'] == 1) { ?>selected = "selected"<?php  } ?> value="1">允许</option>
                    </select>
                </td>
                <td>
                    <?php  if($item['shop_id'] == 0) { ?>
                    未关联
                    <?php  } else { ?>
                    <?php  echo $item['shop']['shop_name'];?>
                    <?php  } ?>
                </td>
                <td>
                    <?php  if($item['shop_id'] == 0) { ?>
                    <a href="<?php  echo $this->createWeburl('member', array('op' => 'gl', 'id' => $item['id']));?>">关联店铺</a>
                    <?php  } else { ?>
                    <a href="javascript:;" class="cancel" data-id="<?php  echo $item['id'];?>">取消关联</a>
                    <?php  } ?>
                </td>
            </tr>
            <?php  } } ?>
            </tbody>
        </table>
    </div>
    <?php  echo $pager;?>
</div>
<script type="application/javascript">
    $(function () {
        $('.is_release').change(function () {
            $.post('', {id:$(this).attr('id'), is_release: $(this).val(), 'op' : 'is_release'}, function (data) {
                if (data.status == 1) {
                    alert('设置成功');
                } else {
                    alert('设置失败');
                }
            }, 'json');
        });
        $('.cancel').click(function () {
            if (!confirm('确认要取消关联的店铺')) {
                return ;
            }
            var id = $(this).data('id');
            $.post('', {id:id, op:'cancel'}, function (data) {
                location.href = location.href;
            }, 'json')
        });
    })
</script>