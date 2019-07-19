<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('header', TEMPLATE_INCLUDEPATH)) : (include template('header', TEMPLATE_INCLUDEPATH));?>
<link href="./resource/css/app.css" rel="stylesheet">
<ul class="nav nav-tabs">
    <li class="active"><a href="javascript:;">常见问题</a></li>
    <li><a href="<?php  echo $_SERVER['REQUEST_URI'];?>&op=addshow">添加</a></li>
</ul>
<div class="panel panel-default">
    <div class="table-responsive panel-body">
        <table class="table table-hover">
            <thead class="navbar-inner">
            <tr>
                <th style="width:60px;">id</th>
                <th style="width:80px;">标题</th>
                <th style="width:80px;">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php  if(is_array($question)) { foreach($question as $item) { ?>
            <tr>
                <td><?php  echo $item['id'];?></td>
                <td><?php  echo $item['describe'];?></td>
                <td><a id="<?php  echo $item['id'];?>" class="delete">删除</a>/<a href="<?php  echo $this->createWeburl('question', array('op' => 'edit_show', 'id' => $item['id']));?>" class="">编辑</a></td>
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
