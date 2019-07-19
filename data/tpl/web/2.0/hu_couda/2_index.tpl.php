<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('header', TEMPLATE_INCLUDEPATH)) : (include template('header', TEMPLATE_INCLUDEPATH));?>

<link href="./resource/css/app.css" rel="stylesheet">
<ul class="nav nav-tabs">
    <li class="active"><a href="javascript:;">奖品管理</a></li>
</ul>
<div class="panel panel-default">
    <div class="table-responsive panel-body">
        <table class="table table-hover">
            <thead class="navbar-inner">
            <tr>
                <th style="width:60px;">标题</th>
                <th style="width:80px;">赞助商</th>
                <th style="width:80px;">开奖方式</th>
                <th style="width:100px;">开奖时间/人数</th>
                <th style="width:100px;">奖品1</th>
                <th style="width:100px;">奖品1类型</th>
                <th style="width:100px;">奖品1数量</th>
                <th style="width:150px;">奖品2奖品</th>
                <th style="width:80px;">奖品2类型</th>
                <th style="width:80px;">奖品2数量</th>
                <th style="width:80px;">奖品3奖品</th>
                <th style="width:80px;">奖品3类型</th>
                <th style="width:80px;">奖品3数量</th>
                <th style="text-align:center;width:80px;">操作</th>
            </tr>
            </thead>
            <tbody>
            <?php  if(is_array($data)) { foreach($data as $item) { ?>
            <tr>
                <td><?php  echo $item['title'];?></td>
                <td><?php  echo $item['uname'];?></td>
                <td><?php echo $item['type'] == 1 ? '时间开奖' : '人数开奖';?></td>
                <td><?php  echo $item['opentime'];?></td>
                <td><?php  echo $item['fir_val'];?></td>

                <td>
                    <?php  if($item['fir_ptype'] == 3) { ?>
                    卡券
                    <?php  } else { ?>
                    实物
                    <?php  } ?>
                </td>
                <td><?php  echo $item['fir_num'];?></td>
                <td><?php  echo $item['sec_val'];?>

                <td>
                    <?php  if($item['sec_ptype'] == 3) { ?>
                    卡券
                    <?php  } else { ?>
                    实物
                    <?php  } ?>
                </td>

                <td><?php  echo $item['sec_num'];?></td>
                <td><?php  echo $item['trd_val'];?></td>

                <td>
                    <?php  if($item['trd_ptype'] == 3) { ?>
                    卡券
                    <?php  } else { ?>
                    实物
                    <?php  } ?>
                </td>

                <td><?php  echo $item['trd_num'];?></td>
                <td class="delete" id="<?php  echo $item['prize_id'];?>">删除</td>

            </tr>
            <?php  } } ?>
            </tbody>
        </table>
    </div>
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