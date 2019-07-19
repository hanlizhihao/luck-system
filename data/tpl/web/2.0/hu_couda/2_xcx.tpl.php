<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('header', TEMPLATE_INCLUDEPATH)) : (include template('header', TEMPLATE_INCLUDEPATH));?>
<link href="./resource/css/app.css" rel="stylesheet">
<ul class="nav nav-tabs">
    <li class="active"><a href="javascript:;">更多小程序</a></li>
    <li><a href="<?php  echo $this->createWeburl('xcx', array('op' => 'addshow'));?>">添加</a></li>
</ul>
<div class="panel panel-default">
    <div class="table-responsive panel-body">
        <table class="table table-hover">
            <thead class="navbar-inner">
            <tr>
                <th style="width:60px;">id</th>
                <th style="width:80px;">小程序名称</th>
                <th style="width:80px;">appid</th>
                <th style="width:80px;">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php  if(is_array($data)) { foreach($data as $item) { ?>
            <tr>
                <td><?php  echo $item['id'];?></td>
                <td><?php  echo $item['name'];?></td>
                <td><?php  echo $item['appid'];?></td>
                <td><a id="<?php  echo $item['id'];?>" class="delete">删除</a></td>
            </tr>
            <?php  } } ?>
            </tbody>
        </table>
    </div>
    <?php  echo $pager;?>
</div>
<script type="application/javascript">
    $(function () {
        $('.delete').click(function () {

            if (!confirm('确认要删除?')) {
                return false;
            }

            id = $(this).attr('id');
            $.post('', {id:id, op:'delete'}, function (data) {
                if (data.status == 1) {
                    location.href = location.href;
                } else {
                    alert('删除失败');
                }

            }, 'json')
        });
    });

</script>