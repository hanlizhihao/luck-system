<?php defined('IN_IA') or exit('Access Denied');?><div class="clearfix user-browser">
	<div class="">
		<div class="search-box ">
			<div class="search-form">
				<div class="input-group">
					<input id="keyword" type="text" class="form-control" value="<?php  echo $_GPC['keyword'];?>" placeholder="请输入用户名"/>
					<div class="input-group-btn">
						<button class="btn btn-default" onclick="<?php  echo $callback;?>.pIndex=1;<?php  echo $callback;?>.query();"><i class="fa fa-search"></i></button>
					</div>
				</div>
			</div>
		</div>
		<table class="table we7-table" style="min-width:568px;">
			<thead>
				<tr>
					<th style="width: 100px;">用户名</th>
					<th style="width: 100px;">用户组</th>
					<th style="width: 100px;">说明</th>
					<th style="width: 50px;">操作</th>
				</tr>
			</thead>
			<tbody>
				<?php  if(is_array($list)) { foreach($list as $row) { ?>
				<tr>
					<td><label for="chk_user_<?php  echo $row['uid'];?>" style="font-weight:normal;"><?php  echo $row['username'];?></label></td>
					<td>
						<label class="label label-info"><?php echo empty($usergroups[$row['groupid']]) ? '未分组' : $usergroups[$row['groupid']]['name']?></label>
					</td>
					<td title="<?php  echo $row['remark'];?>"><?php echo empty($row['remark']) ? '无' : cutstr($row['remark'], 13, '.')?></td>
					<td><a href="javascript:;" class="color-default <?php  if(in_array($row['uid'], $uidArr)) { ?>btn-primary<?php  } else { ?>js-btn-select<?php  } ?>" js-uid="<?php  echo $row['uid'];?>" onclick="$(this).toggleClass('btn-primary')">选取</a></td>
				</tr>
				<?php  } } ?>
			</tbody>
		</table>
		<?php  echo $pager;?>
	</div>
</div>