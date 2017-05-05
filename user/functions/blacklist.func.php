<?php
/** 获取黑名单时根据用户ID获取用户名和昵称 */
add_action('blacklist.get', function($input){
	if($input['blacklist_uid']){
		$user = database::open(0)->select('user', 'user_name, user_nickname', "user_id = {$input['blacklist_uid']}")->fetch(PDO::FETCH_ASSOC);
		$user = array('blacklist_user'=>($user['user_name'] ?: $user['user_nickname']));
	}else{
		$user = array('blacklist_user'=>'');
	}
	return array_merge($input, $user);
});

add_action(array('blacklist.add', 'blacklist.update', 'blacklist.delete'), function($input){
	if(!is_admin() && !is_editor()){
		return error(lang('mod.permissionDenied'));
	}
});

add_action('blacklist.add', function($input){
	if(!empty($input['blacklist_uid']) && get_blacklist(array('blacklist_uid'=>$input['blacklist_uid']))){
		unset($input['blacklist_uid']);
	}
	if(!empty($input['blacklist_email']) && get_blacklist(array('blacklist_email'=>$input['blacklist_email']))){
		unset($input['blacklist_email']);
	}
	if(!empty($input['blacklist_ip']) && get_blacklist(array('blacklist_ip'=>$input['blacklist_ip']))){
		unset($input['blacklist_ip']);
	}
	if(empty($input['blacklist_uid']) && empty($input['blacklist_email']) && empty($input['blacklist_ip'])){
		if(get_blacklist()){
			return error(lang('blacklist.alreadyInBlacklist'));
		}else{
			return error(lang('mod.missingArguments'));
		}
	}
});