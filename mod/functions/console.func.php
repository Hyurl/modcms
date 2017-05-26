<?php
/** update() 更新 ModPHP */
function update($ver = null){
	static $version = array();
	if($ver){
		$version = $ver;
		$version['upgrade'] = true;
	}elseif(!empty($version['upgrade'])){
		$result = mod::update($version); //执行升级操作
		if($result['success']){
			echo "Update succeeded, restart ModPHP to get everything done.\n\n";
			update_log('update-log.txt', true); //输出更新日志
		}else{
			echo $result['data'];
		}
	}
}

/** update_db() 更新数据库 */
function update_db(){
	$result = mod::update();
	echo $result['data'];
}

/** install() 安装系统 */
function install($user, $pass){
	$arg = array(
		"mod.database.type"=>"sqlite", //安装到 SQLite 数据库
		"mod.database.host"=>"",
		"mod.database.name"=>"modphp",
		"mod.database.port"=>0,
		"mod.database.username"=>"",
		"mod.database.password"=>"",
		"mod.database.prefix"=>"mod_",
		"site.name"=>"ModPHP",
		"user_name"=>$user,
		"user_password"=>$pass,
		);
	$result = mod::install($arg);
	if($result['success'])
		echo "Install succeeded, restart ModPHP to get everything done.\n\n";
	else
		echo $result['data'];
}

/** update_log() 查看更新日志 */
function update_log($file = 'update-log.txt', $first = false){
	if(php_sapi_name() != 'cli') return;
	if(PHP_OS == 'WINNT' && !$first)
		return exec('start notepad "'.__ROOT__.$file.'"'); //使用记事本打开更新日志
	$logs = str_replace("\r\n", "\n", file_get_contents(__ROOT__.$file));
	$logs = explode("\n\n", $logs);
	if($first){
		echo $logs[0];
		return;
	}
	if($file == 'update-log.txt')
		$logs = array_reverse($logs); //将更新日志按段落反转
	foreach($logs as $log){
		echo $log."\n\n"; //在控制台输出日志
	}
}

/** license() 查看产品协议 */
function license(){
	return update_log('license.txt');
}

/** readme() 查看简介 */
function readme(){
	return update_log('README.md');
}

add_action('console.open.show_tip', function(){
	$tip = 'Type "doc", "update_log", "license" or "readme" for more information.';
	fwrite(STDOUT, $tip.PHP_EOL);
}, false);

/** 控制台检查更新 */
add_action('console.open.check_update', function(){
	$url = 'http://modphp.hyurl.com/version';
	$arg = array('url'=>$url, 'parseJSON'=>true);
	if(ping('hyurl.com')){
		$ver = @json_decode(file_get_contents($url), true) ?: @curl($arg); //访问远程链接并获取响应
		$gt = $ver && !curl_info('error') ? version_compare($ver['version'], MOD_VERSION) : -1;
		if($gt >= 0) update($ver); //保存新版本信息
		if($gt > 0){
			$tip = "ModPHP {$ver['version']} is now availible, use \"update\" to get the new version.";
			fwrite(STDOUT, $tip.PHP_EOL); //输出更新提示
		}
	}
}, false);

/** 检查安装状态 */
add_action('console.open.check_install', function(){
	if(!config('mod.installed')){
		$tip = 'Please use syntax "install <username> <password>" to install ModPHP before you can fully operate it.';
		fwrite(STDOUT, $tip.PHP_EOL); //输出安装提示
	}
}, false);