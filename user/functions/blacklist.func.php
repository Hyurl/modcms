<?php
/** 获取黑名单时根据用户ID获取用户名和昵称 */
add_action('blacklist.get', function($arg){
	if($arg['blacklist_uid']){
		$user = database::open(0)
						->select('user', 'user_name, user_nickname', "user_id = ".(int)$arg['blacklist_uid'])
						->fetch();
		$user = array(
			'blacklist_user' => $user['user_name'] ?: $user['user_nickname']
			);
	}else{
		$user = array('blacklist_user'=>'');
	}
	return array_merge($arg, $user);
});

//管理黑名单时检查用户权限
add_action(array(
	'blacklist.add',
	'blacklist.update',
	'blacklist.delete'), function($arg){
	if(!is_admin() && !is_editor()){
		return error(lang('mod.permissionDenied'));
	}
});

//添加黑名单时忽略重复的记录
add_action('blacklist.add', function($arg){
	if(!empty($arg['blacklist_uid']) && get_blacklist(array('blacklist_uid'=>$arg['blacklist_uid']))){
		unset($arg['blacklist_uid']);
	}
	if(!empty($arg['blacklist_email']) && get_blacklist(array('blacklist_email'=>$arg['blacklist_email']))){
		unset($arg['blacklist_email']);
	}
	if(!empty($arg['blacklist_ip']) && get_blacklist(array('blacklist_ip'=>$arg['blacklist_ip']))){
		unset($arg['blacklist_ip']);
	}
	if(empty($arg['blacklist_uid']) && empty($arg['blacklist_email']) && empty($arg['blacklist_ip'])){
		if(get_blacklist()){
			return error(lang('blacklist.alreadyInBlacklist'));
		}else{
			return error(lang('mod.missingArguments'));
		}
	}
});