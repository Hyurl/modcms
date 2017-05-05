<?php
/** 安装环境检测 */
$env = true;
$envWarning = lang('admin.envWritabilityWarning');
$envTip = lang('admin.envWritabilityTip');
$soft = $_SERVER['SERVER_SOFTWARE'];
$dbDrivers = PDO::getAvailableDrivers();
if(preg_match('/Apache\/([0-9.])\s/i', $_SERVER['SERVER_SOFTWARE'], $match) && version_compare($match[1], '2.0.0') < 1){
	$env = false;
	$envWarning = lang('admin.apacheWarning');
	$envTip = lang('admin.apacheTip');
}elseif(!($b = extension_loaded('session')) || !($c = extension_loaded('mbstring'))){
	$env = false;
	$envWarning = lang('admin.extensionWarning', !$b ? 'Session' : 'MBString');
	$envTip = lang('admin.extensionTip', !$b ? 'Session': 'MBString');
}elseif(!file_exists($file = __ROOT__.'user/config/config.php') || !is_writable($file)){
	$env = false;
}elseif(!is_dir(__ROOT__.config('file.upload.savePath'))){
	$env = false;
}elseif(!in_array('mysql', $dbDrivers) && !in_array('sqlite', $dbDrivers)){
	$env = false;
	$envWarning = lang('admin.dbDriverWarning');
	$envTip = lang('admin.dbDriverTip');
}

/** 协议内容 */
$license = str_replace("\n", '<br>', file_get_contents(ADMIN_DIR.'license.txt'));

/** 检测新版本 */
$version = @json_decode(file_get_contents($VERSION_URLS['modcms']), true) ?: @curl(array('url'=>$VERSION_URLS['modcms'], 'parseJSON'=>true));
$newVersion = $version ? !config('mod.installed') && version_compare($version['version'], CMS_VERSION) > 0 : false;