<?php
$plugins = array();
$default = array(
	'name' => '',
	'logo' => '',
	'author' => '',
	'url' => '',
	'version' => '',
	'desc' => '',
	'versionURL' => '',
	'enable' => false,
	);
$escape = array('.', '..');
$folders = array_diff(scandir(plugin_dir()), $escape);
$enable = explode('|', str_replace(' ', '', config('mod.plugins.enable')));
foreach($folders as $folder){
	$dir = plugin_dir().$folder;
	if(is_dir($dir)){
		$file = $dir.'/info.json';
		if(file_exists($file)){
			$plugin = json_decode(file_get_contents($file), true);
			$plugin['desc'] = str_replace(array('{SITE_URL}', '{ADMIN_URL}', '{PLUGIN_URL}'), array(SITE_URL, ADMIN_URL, SITE_URL.'app/plugins/'.$folder), $plugin['desc']);
			$plugins[$folder] = array_merge($default, $plugin);
			$plugins[$folder]['enable'] = in_array($folder, $enable);
			$plugins[$folder]['hasTemp'] = admin_has_compiled_temp('plugins/'.$folder);
		}
	}
}

if(@$_GET['tablist'] == 'upload'){
	$title = lang('admin.uploadPlugin');
}else{
	$title = lang('admin.selectPlugins');
}

$uploadLimit = (config('file.upload.maxSize')/1024).'MB';

function admin_get_plugin_icon($foldername, $logo){
	$file = config('mod.plugins.savePath').$foldername.'/'.$logo;
	if($logo && file_exists(__ROOT__.$file)){
		import(site_url($file));
	}else{
		echo '<span class="glyphicon glyphicon-leaf media-icon"></span>';
	}
}