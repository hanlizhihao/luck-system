<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<ul class="we7-page-tab"></ul>
<div class="system-check" id="js-system-check" ng-controller="systemCheckCtrl" ng-cloak>
	<div class="system-check-header clearfix we7-margin-bottom">
		<a href="#" onclick="url_reload()" class="btn btn-primary pull-right">检测</a>
	</div>
	<div>
		<table class="tbale we7-table">
			<tr>
				<th>全部检测{{ check_num }}项，错误检测项<span class="color-red">{{ check_wrong_num }}</span>项</th>
				<th>检测结果</th>
				<th>解决建议</th>
				<th>建议操作</th>
				<th>解决方案</th>
			</tr>

			<?php  if(is_array($system_check_items)) { foreach($system_check_items as $check_item_name => $check_item) { ?>
			<tr>
				<td><?php  echo $check_item['description'];?></td>
				<?php  if(!$check_item['check_result']) { ?>
					<td><span class="color-red wi wi-info-sign"><?php  echo $check_item['error_message'];?></span></td>
				<?php  } else { ?>
					<td><span class="color-green wi wi-right-sign">正确</span></td>
				<?php  } ?>
				<td>
					<div>
						<?php  if(!$check_item['check_result']) { ?>
						<a href="#" onclick="url_reload()">重新检测</a>
						<?php  } else { ?> -- <?php  } ?>
					</div>
				</td>
				<td><?php  if(!$check_item['check_result']) { ?> <?php  echo $check_item['solution'];?> <?php  } else { ?> -- <?php  } ?></td>
				<td><?php  if(!$check_item['check_result']) { ?> <a href="<?php  echo $check_item['handle'];?>" target="_balnk">查看</a> <?php  } else { ?> -- <?php  } ?></td>
			</tr>
			<?php  } } ?>


			<!-- fpm 环境 start -->
			<tr>
				<td>php-fpm 运行环境</td>
				<td ng-if="checkFpmStatus == 0"><span class="color-gray wi wi-info-sign">检测中...</span></td>
				<td ng-if="checkFpmStatus == 1"><span class="color-green wi wi-right-sign">正确</span></td>
				<td ng-if="checkFpmStatus == 2"><span class="color-red wi wi-info-sign">错误</span></td>
				<td>
					<span ng-if="checkFpmStatus != 2">--</span>
					<a ng-if="checkFpmStatus == 2" href="javascript:void(0);" ng-click="checkFpm()">重新检测</a>
				</td>
				<td>
					<span ng-if="checkFpmStatus != 2">--</span>
					<span ng-if="checkFpmStatus == 2">安装配置 FPM</span>
				</td>
				<td>
					<span ng-if="checkFpmStatus != 2">--</span>
					<span ng-if="checkFpmStatus == 2">--</span>
				</td>
			</tr>
			<!-- fpm 环境 end -->

			<!-- 授权接入 start -->
			<tr ng-if="checkAccountStatus != 2">
				<td>授权接入账号</td>
				<td ng-if="checkAccountStatus == 0"><span class="color-gray wi wi-info-sign">检测中...</span></td>
				<td ng-if="checkAccountStatus == 1"><span class="color-green wi wi-right-sign">正确</span></td>
				<td><div>--</div></td>
				<td>--</td>
				<td>--</td>
			</tr>
			<tr ng-repeat="account in errorAccounts">
				<td>{{ account.name }}</td>
				<td  style="max-width:250px;"><i class="color-red wi wi-info-sign"></i>{{account.error}}</td>
				<td><a href="javascript:void(0);" ng-click="checkAuthAccount()">重新检测</a></td>
				<td>
					<a href="{{account_post_url}}acid={{account.acid}}&uniacid={{account.uniacid}}&account_type={{account.type}}" target="_blank">
					重新接入 {{ account.name }}
					</a>
				</td>
				<td>--</td>
			</tr>
			<!-- 授权接入 end -->

			<!-- 表损坏 start -->
			<div>
			<tr ng-if="wrong_tables == '' || checkMysqlTableStatus == 1 || checkMysqlTableStatus == 0">
				<td>表损坏</td>
				<td ng-if="checkMysqlTableStatus == 1"><span class="color-green wi wi-right-sign">正确</span></td>
				<td ng-if="checkMysqlTableStatus == 0"><span class="color-gray wi wi-info-sign">检测中...</span></td>
				<td><div>--</div></td>
				<td>--</td>
				<td>--</td>
			</tr>

			<tr ng-repeat="wrong_table in wrong_tables">
				<td ng-bind="wrong_table.Table"></td>
				<td><i class="color-red wi wi-info-sign"></i>错误</td>

				<td>
					<div>
					<a href="javascript:void(0);" ng-click="checkMysqlTable()">重新检测</a>
						<!--<a href="" target="_balnk">解决方案</a>-->
					</div>
				</td>

				<td>修复 {{ wrong_table.Table }} 表</td>
				<td> <a href="https://bbs.w7.cc/thread-33161-1-1.html" target="_balnk">查看</a> </td>
			</tr>
			</div>
			<!-- 表损坏 end -->
		</table>
	</div>
</div>

<script>
	function url_reload() {
		location.reload();
	}
	angular.module('systemApp').value('config', {
		'check_num' : <?php  echo $check_num;?>,
		'check_wrong_num' : <?php  echo $check_wrong_num;?>,
		'links': {
			'account_post_url': "<?php  echo url('account/post')?>",
			'check_table_link': "<?php  echo url('system/check/check_table')?>",
			'check_fpm_link': "<?php  echo url('system/check/check_fpm')?>",
			'check_auth_accounts': "<?php  echo url('system/check/check_auth_accounts')?>",
		},
	});
	angular.bootstrap($('#js-system-check'), ['systemApp']);
</script>

<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>