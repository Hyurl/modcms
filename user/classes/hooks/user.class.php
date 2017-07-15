<?php
namespace hooks;

class user{
	static function checkOldPassword($arg){
		if(!empty($arg['user_password']) && !is_admin()){
			if(!isset($arg['old_password']) || empty($arg['user_id']))
				return error(lang('mod.missingArguments'));
			$pwd = \database::open(0)
						   ->select('user', 'user_password', "`user_id` = ".(int)$arg['user_id'])
						   ->fetchObject()
						   ->user_password; //获取数据库中的密码
			if(!password_verify($arg['old_password'], $pwd)) //校验密码是否一致
				return error(lang('user.wrongOldPassword'));
		}
	}

	static function checkRegisterEnable(){
		if(!config('user.register.enable') && !is_admin()) //管理员可以不受限制地添加用户
			return error(lang('user.regDisabled'));
	}

	static function checkRegisterVcode($input){
		if(in_array(config('user.register.verify'), array('vcode', 'email')) && !is_admin()){
			if(session_status() != PHP_SESSION_ACTIVE) session_start();
			if(empty($input['vcode']) || strtolower($input['vcode']) != strtolower(@$_SESSION['vcode'])) { //判断验证码是否相等
				return error(lang('admin.wrongVcode'));
			}elseif((time() - @$_SESSION['time']) > 60*30){ //判断验证码是否过期
				return error(lang('admin.vcodeTimeout'));
			}
		}
	}

	static function checkLoginVcode($input){
		if(in_array(config('user.login.verify'), array('vcode', 'email'))){
			if(session_status() != PHP_SESSION_ACTIVE) session_start();
			if(empty($input['vcode']) || strtolower($input['vcode']) != strtolower(@$_SESSION['vcode'])) { //判断验证码是否相等
				return error(lang('admin.wrongVcode'));
			}elseif((time() - @$_SESSION['time']) > 60*30){ //判断验证码是否过期
				return error(lang('admin.vcodeTimeout'));
			}
		}
	}

	static function addCheckEmail($input){
		if(!empty($input['user_email'])){
			if(cms_get_uid_by_email($input['user_email'])){ //尝试通过邮件地址获取用户
				return error(lang('user.emailIsUsed'));
			}
		}
	}

	static function updateCheckEmail($input){
		if(!empty($input['user_email'])){
			$uid = cms_get_uid_by_email($input['user_email']);
			if($uid && $uid != $input['user_id']){ //判断邮件地址是否为当前用户或制定用户使用
				return error(lang('user.emailIsUsed'));
			}
		}
	}
}