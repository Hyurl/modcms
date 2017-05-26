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
			update_log('modcms-update-log.txt', true); //输出更新日志
		}else{
			echo $result['data'];
		}
	}
}

/** 更改交互式控制台标题 */
add_action('console.open', function(){
	global $TITLE, $PROMPT;
	$TITLE = "ModCMS Console";
	$PROMPT = ">>> ";
});

/** 交互式控制台中检查 ModCMS 更新 */
add_action('console.open.check_update', function(){
	$url = 'http://modphp.hyurl.com/modcms/version';
	$arg = array('url'=>$url, 'parseJSON'=>true);
	if(ping('hyurl.com')){
		$ver = @json_decode(file_get_contents($url), true) ?: @curl($arg); //访问远程链接并获取响应
		$gt = $ver && !curl_info('error') ? version_compare($ver['version'], CMS_VERSION) : -1;
		if($gt >= 0) update_cms($ver); //保存新版本信息
		if($gt > 0){
			$tip = "ModCMS {$ver['version']} is now availible, use \"update_cms\" to get the new version.";
			fwrite(STDOUT, $tip.PHP_EOL); //输出更新提示
		}
	}
}, false);