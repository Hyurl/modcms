<?php
/** 更改密码时验证旧密码 */
add_action('user.update', function($arg){
	if(!empty($arg['user_password']) && !is_admin()){
		if(!isset($arg['old_password']) || empty($arg['user_id']))
			return error(lang('mod.missingArguments'));
		$pwd = database::open(0)
					   ->select('user', 'user_password', "`user_id` = ".(int)$arg['user_id'])
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
		if(empty($input['vcode']) || strtolower($input['vcode']) != strtolower(@$_SESSION['vcode'])) { //判断验证码是否相等
			return error(lang('admin.wrongVcode'));
		}elseif((time() - @$_SESSION['time']) > 60*30){ //判断验证码是否过期
			return error(lang('admin.vcodeTimeout'));
		}
	}
});

// 验证用户登录验证码
add_action('user.login', function($input){
	if(in_array(config('user.login.verify'), array('vcode', 'email'))){
		if(session_status() != PHP_SESSION_ACTIVE) session_start();
		if(empty($input['vcode']) || strtolower($input['vcode']) != strtolower(@$_SESSION['vcode'])) { //判断验证码是否相等
			return error(lang('admin.wrongVcode'));
		}elseif((time() - @$_SESSION['time']) > 60*30){ //判断验证码是否过期
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

// 通过邮件检测用户是否存在
add_action('user.checkExistenceByEmail', function($arg){
	if(empty($arg['user_email'])) return error(lang('mod.missingArguments'));
	database::open(0);
	$result = database::select('user', 'user_id', "`user_email` = ".database::quote($arg['user_email']), 1);
	if($result->fetch()){
		return success('User exists.');
	}else{
		return error(lang('mod.notExists', lang('user.label')));
	}
}, false);

// 使用邮箱验证登录用户
add_action('user.loginByEmail', function($arg){
	do_hooks('user.login', $arg); //执行登录前挂钩函数
	if(error()) return error();
	if(!session_id()) session_id(strtolower(rand_str(26))); //生成随机 Session ID
	if(session_status() != PHP_SESSION_ACTIVE) @session_start();
	if(empty($arg['user_email'])) return error(lang('mod.missingArguments'));
	if(empty($_SESSION['user_email']) || $_SESSION['user_email'] != $arg['user_email'])
		return error(lang('user.emailLoginForbidden'));
	database::open(0);
	$result = database::select('user', '*', "`user_email` = ".database::quote($arg['user_email']), 1); //获取符合条件的用户
	if($user = $result->fetch()){
		$_SESSION['ME_ID'] = (int)$user['user_id']; //保存用户 ID 到 Session 中
		_user('me_id', (int)$user['user_id']);
		_user('me_level', (int)$user['user_level']);
		$expires = !empty($arg['remember_me']) ? time()+ini_get('session.gc_maxlifetime') : null; //Cookie 生存期
		$params = session_get_cookie_params();
		setcookie(session_name(), session_id(),  $expires, $params['path']); //重写客户端 Cookie
		$user = user::getMe();
		do_hooks('user.login.complete', $user['data']);
		return $user;
	}
	return error($hasUser ? lang('user.wrongPassword') : lang('mod.notExists', lang('user.label')));
}, false);

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

// 重置密码
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
	database::open(0);
	$result = database::select('user', 'user_id', "`user_email` = ".database::quote($email));
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

// 获取用户头像
add_action('user.getAvatar', function($arg){
	$login = explode('|', config('user.keys.login'));
	database::open(0);
	$where = '';
	foreach($login as $k) { //根据登录字段组合用户信息获取条件
		if(!empty($arg[$k])){
			$where = "`{$k}` = ".database::quote($arg[$k]);
			break;
		}elseif(count($login) > 1 && !empty($arg['user'])){
			$where .= " OR `{$k}` = ".database::quote($arg['user']);
		}
	}
	if(!$where) return error(lang('mod.missingArguments'));
	$where = ltrim($where, ' OR ');
	$result = database::select('user', '*', $where); //获取符合条件的用户
	$hasUser = false;
	if($result && $user = $result->fetch()){
		$avatar = $user['user_avatar'] ? SITE_URL.$user['user_avatar'] : '';
		return success(array('user_avatar'=> $avatar));
	}
	return error('User not exists.');
}, false);