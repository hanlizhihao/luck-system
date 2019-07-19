<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('header', TEMPLATE_INCLUDEPATH)) : (include template('header', TEMPLATE_INCLUDEPATH));?>
<link href="./resource/css/app.css" rel="stylesheet">
<ul class="nav nav-tabs">
    <li class="active"><a href="javascript:;">商品管理</a></li>
    <li class=""><a href="<?php  echo $this->createWeburl('goods', array('op' => 'addshow'));?>">添加卡券</a></li>
</ul>
<div class="panel panel-default">
    <div class="table-responsive panel-body">
        <table class="table table-hover">
            <thead class="navbar-inner">
            <tr>
                <th style="width:60px;">序号</th>
                <th style="width:80px;">商品名称</th>
                <th style="width:80px;">所属店铺</th>
                <th style="width:80px;">未使用</th>
                <th style="width:80px;">已使用</th>
                <th style="width:80px;">已核销</th>
                <th style="width:100px;">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php  if(is_array($list)) { foreach($list as $item) { ?>
            <tr>
                <td><?php  echo $item['id'];?></td>
                <td><?php  echo $item['goods_name'];?></td>
                <td><?php  echo $item['shop']['shop_name'];?></td>
                <td><?php  echo $item['not_used'];?></td>
                <td><?php  echo $item['used'];?></td>
                <td><?php  echo $item['write_off'];?></td>
                <td><a id="<?php  echo $item['id'];?>" class="delete">删除</a>|<a href="<?php  echo $this->createWeburl('goods', array('op' => 'createShow', 'id' => $item['id']));?>">生成</a>|<a href="<?php  echo $this->createWeburl('goods', array('op' => 'details', 'id' => $item['id']));?>">详情</a></td>
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