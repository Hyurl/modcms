<?php
/** 更改密码时验证旧密码 */
add_action('user.update', 'hooks\user::checkOldPassword');

/** 检查是否开启用户注册 */
add_hook('user.add', 'hooks\user::checkRegisterEnable');

/** 验证用户注册验证码 */
add_action('user.add', 'hooks\user::checkRegisterVcode');

// 验证用户登录验证码
add_action('user.login', 'hooks\user::checkLoginVcode');

/** 添加用户时检查邮箱是否可用 */
add_action('user.add', 'hooks\user::addCheckEmail');

/** 更新用户时检查邮箱是否可用 */
add_action('user.update', 'hooks\user::updateCheckEmail');

// 通过邮件检测用户是否存在
add_action('user.checkExistenceByEmail', 'extensions\user::checkExistenceByEmail', false);

// 使用邮箱验证登录用户
add_action('user.loginByEmail', 'extensions\user::loginByEmail', false);

/** 使用邮件发送找回密码链接 */
add_action('user.mailRepass', 'extensions\user::mailRepass', false);

// 重置密码
add_action('user.recoverPassword', 'extensions\user::recoverPassword', false);

// 获取用户头像
add_action('user.getAvatar', 'extensions\user::getAvatar', false);

/** 通过 Email 获取用户 ID */
function cms_get_uid_by_email($email){
	\database::open(0);
	$result = \database::select('user', 'user_id', "`user_email` = ".\database::quote($email));
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