<?php
if(!config('mod.installed')){
	redirect('install.html');
}
$versionURLs = array('template'=>array(), 'plugin'=>array());
foreach(scandir(__ROOT__.'app/template/') as $folder){
	$dir = __ROOT__.'app/template/'.$folder;
	if(is_dir($dir) && $folder != '.' && $folder != '..'){
		$file = $dir.'/info.json';
		if(file_exists($file)){
			$template = json_decode(file_get_contents($file), true);
			if(!empty($template['versionURL'])){
				$versionURLs['template'][] = array('url'=>$template['versionURL'], 'version'=>$template['version']);
			}
		}
	}
}
foreach(scandir(plugin_dir()) as $folder){
	$dir = plugin_dir().$folder;
	if(is_dir($dir) && $folder != '.' && $folder != '..'){
		$file = $dir.'/info.json';
		if(file_exists($file)){
			$plugin = json_decode(file_get_contents($file), true);
			if(!empty($plugin['versionURL'])){
				$versionURLs['plugin'][] = array('url'=>$plugin['versionURL'], 'version'=>$plugin['version']);
			}
		}
	}
}