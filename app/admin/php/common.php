<?php
/** 更新链接 */
$VERSION_URLS = config('mod.versionURL') ?: array(
	'modcms' => 'http://modphp.hyurl.com/modcms/version',
	'modphp' => 'http://modphp.hyurl.com/version'
	);

if(empty($_GET['limit'])){
	$_GET['limit'] = config('site.admin.listCount');
}
if(empty($_GET['sequence'])){
	$_GET['sequence'] = config('site.admin.listSequence');
}

/**
 * admin_get_file_icon() 获取并输出文件图标
 * @param  integer $width 宽度
 * @return null
 */
function admin_get_file_icon($width = 64){
	if(is_img(file_src())){
		echo '<img src="'.get_user_avatar($width, file_src()).'" title="'.file_name().'" />';
	}else{
		echo '<span class="glyphicon glyphicon-file media-icon" title="'.file_name().'"></span>';
	}
}

/**
 * admin_load_menu() 加载菜单
 * @param  string $menu [description]
 * @return [type]       [description]
 */
function admin_load_menu($menu = ''){
	$menus = include(ADMIN_DIR.'config/menus.php');
	if(file_exists($file = __ROOT__.'user/config/menus.php')){
		$menus = include($file);
	}else{
		copy(ADMIN_DIR.'config/menus.php', $file);
	}
	return !$menu ? $menus : (isset($menus[$menu]) ? $menus[$menu] : false);
}

function admin_output_menu($menus, $submenu = false){
	if(is_string($menus)) $menus = admin_load_menu($menus);
	foreach($menus as $menu){
		if(($menu['privilege'] == 'admin' && !IS_ADMIN) || ($menu['privilege'] == 'editor' && !IS_AUTH))
			continue;
		echo '<li class="list-group-item'.(!empty($menu['submenu']) ? ' has-submenu' : '').'">
				<a id="'.$menu['id'].'" href="'.@$menu['href'].'" title="'.$menu['title'].'">';
		if(!empty($menu['icon']))
			echo '<span class="glyphicon glyphicon-'.$menu['icon'].'"></span>'.$menu['text'];
		else
			echo $menu['text'];
		echo '</a>';
		if(!empty($menu['submenu']))
			echo '<span class="glyphicon glyphicon-chevron-right"></span>';
		echo '</li>';
		if(!empty($menu['submenu'])){
			echo '<ul class="list-group submenu">';	
			admin_output_menu($menu['submenu'], true);
			echo '</ul>';
		}
		if(!empty($menu['separator']))
			echo '<li role="separator"></li>';
	}
}

/** 
 * admin_count_records() 计算模块数据表的记录数量
 * @param  string $table 表名
 * @param  array  $arg   由字段数据组成的参数
 * @return int           记录数量
 */
function admin_count_records($table, $arg = array()){
	if(empty($arg)){
		$count = database::open(0)->select($table, 'count(*) as count')->fetchObject()->count;
	}else{
		$where = array();
		foreach ($arg as $key => $value) {
			if(in_array($key, database($table))){
				$where[$key] = $value;
			}
		}
		$count = $where ? database::open(0)->select($table, 'count(*) as count', $where)->fetchObject()->count : 0;
	}
	return (int)$count;
}

/**
 * admin_has_compiled_temp() 检查模板或者插件是否存在缓存
 * @param  string $dir 模板或插件目录
 * @return bool
 */
function admin_has_compiled_temp($dir){
	$tmpPath = __ROOT__.config('mod.template.compiler.savePath');
	return is_dir($tmpPath.$dir);
}

/** 检查是否登录 */
if(config('mod.installed')){
	if(!is_logined() && display_file() != 'app/admin/login.html' && display_file() != 'app/admin/install.html'){
		redirect(ADMIN_URL.'login.html');
	}elseif(is_logined() && display_file() == 'app/admin/login.html'){
		redirect(ADMIN_URL);
	}
}

/** 设置访问权限 */
$privileges = include(admin_dir('config/privileges.php'));
if(file_exists($file = __ROOT__.'user/config/privileges.php')){
	$_privileges = include($file);
	$privileges = array_xmerge($privileges, $_privileges);
}
$ADMIN_FILES = array_map(function($v){
	return 'app/admin/'.$v;
}, $privileges['admin']);
$EDITOR_FILES = array_map(function($v){
	return 'app/admin/'.$v;
}, $privileges['editor']);

/** 检查文件访问权限 */
foreach($ADMIN_FILES as $file){
	if(($file == display_file() || ($file[strlen($file)-1] == '/' && stripos(display_file(), $file) === 0)) && !IS_ADMIN){
		report_403();
	}
}
foreach($EDITOR_FILES as $file){
	if(($file == display_file() || ($file[strlen($file)-1] == '/' && stripos(display_file(), $file) === 0)) && !IS_AUTH){
		report_403();
	}
}

$DISPLAY_BASE = substr(display_file(), 0, strrpos(display_file(), '.'));
$DISPLAY_BASE = substr($DISPLAY_BASE, strlen('app/admin/')-1);

/** 自动引入 PHP */
if(file_exists($file = admin_dir('php/'.$DISPLAY_BASE.'.php'))){
	include $file;
}