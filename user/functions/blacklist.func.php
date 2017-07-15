<?php
/** 获取黑名单时根据用户ID获取用户名和昵称 */
add_action('blacklist.get', 'blacklist::getUserFromUid');

//管理黑名单时检查用户权限
add_action(array(
	'blacklist.add',
	'blacklist.update',
	'blacklist.delete'
	), 'blacklist::checkPermission');

//添加黑名单时忽略重复的记录
add_action('blacklist.add', 'blacklist::ignoreExistingRecord');