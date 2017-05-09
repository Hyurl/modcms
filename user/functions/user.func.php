<?php
/** 更改密码时验证旧密码 */
add_action('user.update', function($arg){
	if(!empty($arg['user_password']) && !is_admin()){
		if(!isset($arg['old_password']) || empty($arg['user_id']))
			return error(lang('mod.missingArguments'));
		$pwd = database::open(0)
					   ->select('user', 'user_password', "`user_id` = {$arg['user_id']}")
					   ->fetchObject()
					   ->user_password; //获取数据库中的密码
		if(!password_verify($arg['old_password'], $pwd)) //校验密码是否一致
			return error(lang('user.wrongOldPassword'));
	}
});

/** 检查是否开启用户注册 */
add_hook('user.add', function(){
	if(!config('user.register.enable') && !is_admin()) //管理员可以不受限制地添加用户
		return error(lang('user.regDisabled'));
});

/** 验证用户注册验证码 */
add_action('user.add', function($input){
	if(in_array(config('user.register.verify'), array('vcode', 'email')) && !is_admin()){
		if(session_status() != PHP_SESSION_ACTIVE) session_start();
		if(!isset($input['vcode']) || strtolower($input['vcode']) != strtolower($_SESSION['vcode'])) { //判断验证码是否相等
			return error(lang('admin.wrongVcode'));
		}elseif((time() - $_SESSION['time']) > 60*30){ //判断验证码是否过期
			return error(lang('admin.vcodeTimeout'));
		}
	}
});

/** 添加用户时检查邮箱是否可用 */
add_action('user.add', function($input){
	if(!empty($input['user_email'])){
		if(cms_get_uid_by_email($input['user_email'])){ //尝试通过邮件地址获取用户
			return error(lang('user.emailIsUsed'));
		}
	}
});

/** 更新用户时检查邮箱是否可用 */
add_action('user.update', function($input){
	if(!empty($input['user_email'])){
		$uid = cms_get_uid_by_email($input['user_email']);
		if($uid && $uid != $input['user_id']){ //判断邮件地址是否为当前用户或制定用户使用
			return error(lang('user.emailIsUsed'));
		}
	}
});

/** 使用邮件发送找回密码链接 */
add_action('user.mailRepass', function($input){
	if(error()) return error();
	if(!empty($input['user_email']) && filter_var($input['user_email'], FILTER_VALIDATE_EMAIL)){
		$uid = cms_get_uid_by_email($input['user_email']);
		if($uid && get_user($uid)){
			if(session_status() != PHP_SESSION_ACTIVE) session_start();
			$recoverId = rand_str(14, '0123456789abcdef');
			$_SESSION['RecoverId'] = $recoverId;
			$_SESSION['user_id'] = user_id();
			$_SESSION['time'] = time();
			$user = user_nickname() ?: user_name();
			$body = '<p>'.lang('user.recoverPassEmailTip', $user).'</p>';
			$link = admin_url().'login.html?action=repass&RecoverId='.base64_encode($recoverId.session_id());
			$body .= '<p><a title="'.lang('admin.clickToVisit').'" href="'.$link.'">'.$link.'</a></p>';
			$body .= '<p>'.lang('admin.emailIgnoreTip').'</p>';
			mail::to($input['user_email'])
				->subject('['.lang('user.recoverPassword').']'.config('site.name'))
				->send($body);
			return success(lang('admin.vcodeSendedTip'));
		}else{
			return error(lang('user.emailIsNotUsed'));
		}
	}else{
		return error(lang('user.invalidEmailFormat'));
	}
}, false);

add_action('user.recoverPassword', function($input){
	if(error()) return error();
	$recover = config('user.password.recoverEmail');
	if($recover == 'link' || $recover == 'vcode'){
		if(session_status() != PHP_SESSION_ACTIVE) session_start();
		if(empty($input['user_password']) || ($recover == 'link' && empty($input['RecoverId'])) || ($recover == 'vcode' && empty($input['vcode']))) return error(lang('mod.missingArguments'));
		$recoverId = substr(base64_decode($input['RecoverId']), 0, 14);
		if($recover == 'link' && $recoverId !== $_SESSION['RecoverId']) return error(lang('admin.verifyArgNotMatch'));
		if($recover == 'vcode'){
			if((time() - $_SESSION['time']) > 60*30){
				return error(lang('admin.wrongVcode'));
			}elseif(strtolower($input['vcode']) !== strtolower($_SESSION['vcode'])){
				return error(lang('admin.vcodeTimeout'));
			}
		}
		if(strlen($input['user_password']) < config('user.password.minLength')) return error(lang('user.passwordTooShort'));
		if(strlen($input['user_password']) > config('user.password.maxLength')) return error(lang('user.passwordTooLong'));
		$where = $recover == 'link' ? "`user_id` = {$_SESSION['user_id']}" : "`user_email` = '{$_SESSION['user_email']}'";
		if(database::open(0)->update('user', array('user_password'=>md5_crypt($input['user_password'])), $where)){
			return success(lang('user.recoverPasswordSuccess'));
		}else{
			return error(lang('user.recoverPasswordFail'));
		}
	}else{
		return error(lang('user.recoverPassDisabled'));
	}
}, false);

/** 通过 Email 获取用户 ID */
function cms_get_uid_by_email($email){
	$result = database::open(0)->select('user', 'user_id', "`user_email` = '{$email}'");
	if($result && $user = $result->fetchObject()){
		return (int)$user->user_id;
	}
	return false;
}

/**
 * get_user_avatar() 获取用户头像
 * @param  integer $width 宽度
 * @param  string  $src   头像源地址
 * @return [type]         [description]
 */
function get_user_avatar($width = 64, $src = ''){
	$src = $src ?: user_avatar();
	if(!$src) return '';
	$ext = pathinfo($src, PATHINFO_EXTENSION);
	$_src = $src;
	if(strapos($_src, site_url()) === 0){
		$_src = substr($_src, strlen(site_url()));
	}
	$_src = substr($_src, 0, strripos($_src, '.'));
	if(file_exists(__ROOT__.$_src.'_'.$width.'.'.$ext)){
		return site_url($_src.'_'.$width.'.'.$ext);
	}
	return $src;
}