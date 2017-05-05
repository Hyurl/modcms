<?php
$templates = array();
$default = array(
	'name' => '',
	'thumbnail' => '',
	'author' => '',
	'url' => '',
	'version' => '',
	'desc' => '',
	'versionURL' => ''
	);
foreach(scandir(__ROOT__.'app/template/') as $folder){
	$dir = __ROOT__.'app/template/'.$folder;
	if(is_dir($dir) && $folder != '.' && $folder != '..'){
		$file = $dir.'/info.json';
		if(file_exists($file)){
			$template = json_decode(file_get_contents($file), true);
			$template['desc'] = str_replace(array('{SITE_URL}', '{ADMIN_URL}'), array(SITE_URL, ADMIN_URL), $template['desc']);
			$templates[$folder] = array_merge($default, $template);
			$templates[$folder]['enable'] = config('mod.template.savePath') == 'template/'.$folder.'/';
			$templates[$folder]['hasTemp'] = admin_has_compiled_temp('app/template/'.$folder);
		}
	}
}

if(@$_GET['tablist'] == 'upload'){
	$title = lang('admin.uploadTemplate');
}else{
	$title = lang('admin.selectTemplate');
}

$uploadLimit = (config('file.upload.maxSize')/1024).'MB';