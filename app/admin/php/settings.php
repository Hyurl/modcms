<?php
$settings = array(
	'common' => lang('admin.common'),
	'user' => lang('user.label'),
	'post' => lang('post.label'),
	'comment' => lang('comment.label'),
	'file' => lang('file.label'),
	'advanced' => lang('admin.advanced'),
	);
if(empty($_GET['content'])){
	$title = lang('admin.settings');
}elseif($_GET['content'] == 'update'){
	$title = lang('admin.settings').': '.lang('admin.update');
}else{
	$title = lang('admin.settings').': '.$settings[$_GET['content']];
}
$getLang = function($path){
	$lang = array();
	$dir = array_diff(scandir($path), array('.', '..'));
	foreach ($dir as $file) {
		if($file == 'auto.php' || !strpos($file, '-')) continue;
		$array = include $path.$file;
		if(!empty($array['name'])){
			$key = preg_replace_callback('/-(.+)/', function($match){
				return strtoupper("{$match[0]}");
			}, substr($file, 0, strrpos($file, '.')));
			$lang[$key] = $array['name'];
		}
	}
	return $lang;
};
$Lang = array_merge($getLang(__ROOT__.'mod/lang/'), $getLang(__ROOT__.'user/lang/'));
$lang = config('mod.language');

$login = array(
	"user_name"=>lang('user.name'),
	"user_email"=>lang('user.email'),
	"user_tel"=>lang('user.tel'),
	"user_name|user_email"=>lang('user.name').'/'.lang('user.email'),
	"user_name|user_tel"=>lang('user.name').'/'.lang('user.tel'),
	"user_email|user_tel"=>lang('user.email').'/'.lang('user.tel'),
	"user_name|user_email|user_tel"=>lang('user.name').'/'.lang('user.email').'/'.lang('user.tel')
	);

/** 检查 GD 版本 */
if(extension_loaded('gd')){
	$ver = gd_info();
	preg_match('/[0-9\.]+/', $ver['GD Version'], $match);
	if(version_compare($match[0], '2.0.0') < 0){
		$gdSvr = false;
		$gdWarning = lang('admin.gdWarning1');
	}else{
		$gdSvr = true;
	}
}else{
	$gdSvr = false;
	$gdWarning = lang('admin.gdWarning2');
}

/** 检查邮件服务是否可用 */
if(config('mod.mail.host') && config('mod.mail.username')){
	$mailSvr = true;
}else{
	$mailSvr = false;
	$mailWarning = lang('admin.emailServiceWarnig');
}

/** 检查 ZIP 扩展是否可用 */
if(extension_loaded('zip')){
	$zipSvr = true;
}else{
	$zipSvr = false;
	$zipWarning = lang('admin.zipWarning');
}

/** 最大上传限制 */
$uploadLimit = ini_get('post_max_size');
if(strripos($uploadLimit, 'M')){
	$uploadLimit = intval($uploadLimit) * 1024;
}

/** 判断 Socket 服务器是否已经开启 */
$wsOn = cms_socket_server_mode();

/** 判断是否能够启动 Socket 服务器 */
$wsRunable = false;
$wsUnrunableWarning = '';
if(!function_exists('exec')){
	$wsUnrunableWarning = lang('admin.unabilityToRunShell');
}elseif(php_sapi_name() == 'cgi-fcgi'){
	$wsUnrunableWarning = lang('mod.socketFastCGIWarning');
}else{
	$wsRunable = true;
}