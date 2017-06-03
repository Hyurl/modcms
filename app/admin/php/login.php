<?php
$kv = array(
	'user_name' => lang('user.name'),
	'user_email' => lang('user.email'),
	'user_tel' => lang('user.tel')
	);
$login = explode('|', config('user.keys.login'));
if(count($login) == 1){
	$attr = 'name="'.$login[0].'" placeholder="'.$kv[$login[0]].'"';
}else{
	$attr = 'name="user" placeholder="';
	foreach ($login as $k) {
		if(isset($kv[$k])) $attr .= $kv[$k].'/';
	}
	$attr = rtrim($attr, '/').'"';
}

if(config('user.password.recoverEmail') == 'link'){
	$confirmRepass = false;
	$invalidRecoverId = false;
	if(!empty($_GET['RecoverId'])){
		session_write_close();
		session_unset();
		session_id('');
		$recoverId = base64_decode($_GET['RecoverId']);
		$sid = substr($recoverId, 14);
		$recoverId = substr($recoverId, 0, 14);
		if(session_retrieve($sid)){
			$confirmRepass = true;
			if($recoverId == $_SESSION['RecoverId'] && (time() - $_SESSION['time']) <= 60*30) {
				get_user(array('user_id'=>$_SESSION['user_id']));
			}else{
				$invalidRecoverId = true;
			}
		}else{
			$invalidRecoverId = true;
		}
	}
}

/** 检查邮件服务是否可用 */
if(config('mod.mail.host') && config('mod.mail.username')){
	$mailSvr = true;
}else{
	$mailSvr = false;
	$mailWarning = lang('admin.emailServiceWarnig');
}

//使用邮件验证码登录
if(config('user.login.verify') == 'email'){
	$attr = 'name="user_email" placeholder="'.lang('user.email').'"';
}