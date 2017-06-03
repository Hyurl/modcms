<?php
/** update_cms() 更新 ModCMS */
function update_cms($ver = null){
	static $version = array();
	if($ver){
		$version = $ver;
	}elseif(!empty($version) && $ver === null){
		$result = mod::updateCMS($version); //执行升级操作
		if($result['success']){
			echo "Update succeeded, restart ModCMS to get everything done.\n\n";
			cms_update_log('modcms-update-log.txt', true); //输出更新日志
		}else{
			echo $result['data'];
		}
	}
}

/** cms_update_log() 查看更新日志 */
function cms_update_log($file = 'modcms-update-log.txt', $first = false){
	if(php_sapi_name() != 'cli') return;
	if(PHP_OS == 'WINNT' && !$first)
		return exec('start notepad "'.__ROOT__.$file.'"'); //使用记事本打开更新日志
	$logs = str_replace("\r\n", "\n", file_get_contents(__ROOT__.$file));
	$logs = explode("\n\n", $logs);
	if($first){
		echo $logs[0];
		return;
	}
	if($file == 'modcms-update-log.txt')
		$logs = array_reverse($logs); //将更新日志按段落反转
	foreach($logs as $log){
		echo $log."\n\n"; //在控制台输出日志
	}
}

/** cms_license() 查看产品协议 */
function cms_license(){
	return cms_update_log('modcms-license.txt');
}

/** 更改交互式控制台标题 */
add_action('console.open', function(){
	global $TITLE, $PROMPT;
	$TITLE = "ModCMS Console";
	$PROMPT = ">>> ";
});

add_action('console.open.show_tip', function(){
	$tip = 'Type "doc", "cms_update_log", "cms_license" for more information.';
	fwrite(STDOUT, $tip.PHP_EOL);
}, false);

/** 交互式控制台中检查 ModCMS 更新 */
add_action('console.open.check_cms_update', function(){
	$url = 'http://modphp.hyurl.com/modcms/version';
	$arg = array('url'=>$url, 'parseJSON'=>true);
	if(ping('hyurl.com')){
		$file = __ROOT__.'modcms.zip';
		$ver = @json_decode(file_get_contents($url), true) ?: @curl($arg); //访问远程链接并获取响应
		$gt = $ver && !curl_info('error') ? version_compare($ver['version'], CMS_VERSION) : -1;
		if($gt > 0 || (!$gt && file_exists($file) && $ver['md5'] != md5_file($file))){
			update_cms($ver); //保存新版本信息
			$tip = "ModCMS {$ver['version']} ".($gt > 0 ? 'is now availible' : 'has updates').", use \"update_cms\" to get the new version.";
			fwrite(STDOUT, $tip.PHP_EOL); //输出更新提示
		}
	}
}, false);