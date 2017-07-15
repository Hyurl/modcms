<?php
/** 在获取上传文件时将路径转换为绝对路径 */
add_action(array(
	'file.get',
	'user.get',
	'post.get',
	'comment.get',
	'link.get'
	), 'common::getAbsolutePath');

/** 在更新/新建用户/文章时将头像/图片/链接更改为相对路径 */
add_action(array(
	'user.add',
	'user.update',
	'post.add',
	'post.update',
	'link.add',
	'link.update'), 'common::getRelativePath');

/** 空分类目录报告 404 错误 */
add_hook('mod.init', 'common::emptyCategoryreport404');

/** 加载文章时检测分类目录是否匹配 */
add_action('mod.init', 'common::checkCategoryWhenLoadingPosts');

/** 检查模板/插件更新 */
add_action('mod.checkUpdate', 'extensions\mod::checkUpdate', false);

/** 更新 CMS 系统 */
add_action('mod.updateCMS', 'extensions\mod::updateCMS', false);

/** 更新模板/插件 */
add_action('mod.updateComponent', 'extensions\mod::updateComponent', false);

/** 检测数据库更新 */
add_action('mod.checkDbUpdate', 'extensions\mod::checkDbUpdate', false);

/** 预备邮件服务 */
if(config('mod.mail.host') && config('mod.mail.username')){
	mail::host(config('mod.mail.host'))
		->type('smtp')
		->port(config('mod.mail.port'))
		->from(config('mod.mail.from'))
		->set('username', config('mod.mail.username'))
		->set('password', config('mod.mail.password'))
		->header('Content-Type', 'text/html; charset=UTF-8');
}

/** 使用邮件发送验证码 */
add_action('mod.mailVcode', 'extensions\mod::mailVcode', false);

function cms_get_content_desc($content, $len = 100){
	return mb_strlen($content, 'utf-8') > $len ? mb_substr(strip_tags($content), 0, $len-3, 'utf-8').'...' : $content;
}

/**
 * admin_dir() 获取后台根目录绝对路径
 * @param  string $filename 目录下的文件名
 * @return string           后台根目录路径[$filename]
 */
function admin_dir($filename = ''){
	return __ROOT__.'app/admin/'.$filename;
}

/**
 * admin_url() 获取后台根目录的 URL 地址
 * @param  string $filename 目录下的文件名
 * @return string           后台根目录路径[$filename]
 */
function admin_url($filename = ''){
	return site_url().(config('mod.pathinfoMode') ? 'index.php/' : '').'admin/'.$filename;
}

/**
 * plugin_dir() 获取插件根目录绝对路径
 * @param  string $filename 目录下的文件名
 * @return string           插件根目录路径[$filename]
 */
function plugin_dir($filename = ''){
	return __ROOT__.'app/plugins/'.$filename;
}

/**
 * plugin_dir_url() 获取插件根目录的 URL 地址
 * @param  string $filename 目录下的文件名
 * @return string           插件根目录路径[$filename]
 */
function plugin_dir_url($filename = ''){
	return SITE_URL.'app/plugins/'.$filename;
}

/** 
 * cms_rewrite_config() 重写 ModPHP 用户配置文件
 * @param  [type] $file [description]
 * @param  [type] $put  [description]
 * @return [type]       [description]
 */
function cms_rewrite_config($file, $put = true){
	$config = array();
	$isIni = strtolower(pathinfo($file, PATHINFO_EXTENSION)) == 'ini';
	if(file_exists($file1 = __ROOT__.'mod/config/'.$file)) {
		$_config = $isIni ? parse_ini_file($file1) : include $file1;
		$config = array_xmerge($config, $_config);
	}
	if(file_exists($file2 = admin_dir('config/').$file)) {
		$_config = $isIni ? parse_ini_file($file2) : include $file2;
		$config = array_xmerge($config, $_config);
	}
	if(file_exists($file3 = __ROOT__.'user/config/'.$file) && config('mod.installed')) {
		$_config = $isIni ? parse_ini_file($file3) : include $file3;
		$config = array_xmerge($config, $_config);
	}
	if($file == 'config.php') config($config);
	if(!$isIni && $put) export($config, $file3);
}

/** 第一次运行 */
if(!config('mod.installed') && __SCRIPT__ != 'mod.php'){
	/** 重写配置文件 */
	cms_rewrite_config('config.php');
	cms_rewrite_config('database.php');
	copy(admin_dir('config/menus.php'), __ROOT__.'user/config/menus.php');
	copy(admin_dir('config/privileges.php'), __ROOT__.'user/config/privileges.php');
	copy(admin_dir('config/mime.ini'), __ROOT__.'user/config/mime.ini');
}

/** 加载常量 */
include_once admin_dir('config/constants.php');

/** 加载插件 */
cms_load_plugins();
function cms_load_plugins(){
	if(!config('mod.installed')) return false;
	$escape = array('.', '..');
	$dirs = array_diff(scandir(plugin_dir()), $escape);
	$plugins = explode('|', config('mod.plugins.enable'));
	foreach($dirs as $dir){
		if(!in_array($dir, $plugins)) continue;
		$dir = plugin_dir().$dir.'/';
		if(is_dir($dir) && file_exists($dir.'/info.json')){
			if(file_exists($file = $dir.'functions.php')) include_once $file;
		}
	}
}

/**
 * pagination() 函数用来生成分页按钮，基于 bootstrap
 * @param  array  $arg  分页参数
 * @param  string $url  伪静态 URL 地址，如果提供，则分页链接地址格式按伪静态规则生成，否则按 url 查询字符串格式，伪静态地址中需要有一个 {page} 参数
 * @return string $html 分页按钮 html
 */
function pagination($arg = array(), $url = '') {
	$default = array(
		'pages'=>10, //总页码数
		'limit'=>4, //最多显示条目，多余部分用 ... 代替
		'prev'=>'&laquo;', //上一页
		'next'=>'&raquo;', //下一页
		'first'=>'', //第一页
		'last'=>'', //最后一页
		);
	$arg = is_array($arg) ? array_merge($default, $arg) : $default;
	if($url){
		$arg = array_merge(analyze_url($url) ?: array(), $arg);
	}else{
		$arg = array_merge($_GET, $arg);
	}
	$arg['page'] = isset($arg['page']) ? $arg['page'] : 1;
	$arr = $arg;
	unset($arr['pages'], $arr['prev'], $arr['next'], $arr['limit']);
	$html = '<ul class="pagination">';
	function create_href($arg, $url = ''){
		$query = explode('?', url());
		if($url){
			return create_url($url, $arg);
		}else{
			$_GET = array_merge($_GET, $arg);
			foreach ($_GET as $key => $value) {
				@$str .= '&'.$key.'='.$value;
			}
			$str = ltrim($str, '&');
			$ahref = $query[0].($str ? '?'.$str : '');
		}
		return $ahref;
	}
	/** 上一页按钮 */
	if($arg['page'] != 1 && $arg['pages'] >= $arg['page']){
		if($arg['first']){
			$arr['page'] = 1;
			$html .= '<li><a data-page="1" href="'.create_href($arr, $url).'" aria-label="First"><span aria-hidden="true">'.$arg['first'].'</span></a></li>';
		}
		$arr['page'] = $arg['page'] - 1;
		$html .= '<li><a href="'.create_href($arr, $url).'" aria-label="Previous"><span aria-hidden="true">'.$arg['prev'].'</span></a></li>';
	}else{
		if($arg['first']){
			$html .= '<li class="disabled"><a aria-label="First"><span aria-hidden="true">'.$arg['first'].'</span></a></li>';
		}
		$html .= '<li class="disabled"><a aria-label="Previous"><span aria-hidden="true">'.$arg['prev'].'</span></a></li>';
	}
	/** 页码按钮 */
	if($arg['page'] <= $arg['limit']){
		for ($i=1; $i <= $arg['limit']; $i++) { 
			if($i > $arg['pages']) continue;
			$arr['page'] = $i;
			$html .= ($i == $arg['page'] || $arg['pages'] == 1) ? '<li class="active">' : '<li>';
			$html .= '<a data-page="'.$arr['page'].'" href="'.create_href($arr, $url).'" class="page-num">'.$i.'</a>';
			$html .= '</li>';
		}
		if($arg['pages'] > $arg['limit']){
			$html .= '<li><a class="page-num" style="pointer-events: none;">...</a></li>';
		}
	}else if($arg['page']+$arg['limit'] <= $arg['pages']){
		if($arg['pages'] > $arg['limit']){
			$html .= '<li><a class="page-num" style="pointer-events: none;">...</a></li>';
		}
		for ($i=$arg['page']; $i < $arg['page']+$arg['limit']-1; $i++) { 
			if($i > $arg['pages']) continue;
			$arr['page'] = $i;
			$html .=  ($i == $arg['page'] || $arg['pages'] == 1) ? '<li class="active">' : '<li>';
			$html .= '<a data-page="'.$arr['page'].'" href="'.create_href($arr, $url).'" class="page-num">'.$i.'</a>';
			$html .= '</li>';
		}
		if($arg['pages'] > $arg['limit'] && $arg['page'] <= $arg['pages'] - $arg['limit']){
			$html .= '<li><a class="page-num" style="pointer-events: none;">...</a></li>';
		}
	}else{
		if($arg['pages'] > $arg['limit']){
			$html .= '<li><a class="page-num" style="pointer-events: none;">...</a></li>';
		}
		for ($i=$arg['pages']-$arg['limit']+1; $i <= $arg['pages']; $i++) { 
			if($i < 1) continue;
			$arr['page'] = $i;
			$html .= ($i == $arg['page'] || $arg['pages'] == 1) ? '<li class="active">' : '<li>';
			$html .= '<a data-page="'.$arr['page'].'" href="'.create_href($arr, $url).'" class="page-num">'.$i.'</a>';
			$html .= '</li>';
		}
		if($arg['pages'] > $arg['limit'] && $arg['page'] <= $arg['pages'] - $arg['limit']){
			$html .= '<li><a class="page-num" style="pointer-events: none;">...</a></li>';
		}
	}
	/** 下一页按钮 */
	if($arg['page'] != $arg['pages'] && $arg['pages'] > 1){
		$arr['page'] = $arg['page'] + 1;
		$html .= '<li><a data-page="'.$arr['page'].'" href="'.create_href($arr, $url).'" aria-label="Next"><span aria-hidden="true">'.$arg['next'].'</span></a></li>';
		if($arg['last']){
			$arr['page'] = $arg['pages'];
			$html .= '<li><a data-page="'.$arr['page'].'" href="'.create_href($arr, $url).'" aria-label="Last"><span aria-hidden="true">'.$arg['last'].'</span></a></li>';
		}
	}else{
		$html .= '<li class="disabled"><a aria-label="Next"><span aria-hidden="true">'.$arg['next'].'</span></a></li>';
		if($arg['last']){
			$html .= '<li class="disabled"><a aria-label="Last"><span aria-hidden="true">'.$arg['last'].'</span></a></li>';
		}
	}
	return $html .= '</ul>';
}

/** 如果系统未安装，跳转至安装页面 */
if(!config('mod.installed') && strapos(url(), admin_url('install.html')) !== 0 && __SCRIPT__ != 'mod.php' && is_browser()){
	redirect(admin_url('install.html'));
}

/** 设置仅允许管理员进行数据库清理 */
add_action('mod.init', function(){
	if(is_client_call('', 'cleanTrash') && !is_admin()){
		return error(lang('mod.permissionDenied'));
	}
});

/** 
 * cms_socket_server_mode() 判断是否开启了 Socket 服务器
 * @return bool
 */
function cms_socket_server_mode(){
	if(!file_exists($file = __ROOT__.'.socket-server')) return false;
	$file = fopen($file, 'r');
	$wsOn = !flock($file, LOCK_EX | LOCK_NB);
	fclose($file);
	return $wsOn;
}
function_alias('cms_socket_server_mode', 'cms_websocket_mode');

/** 
 * cms_get_language() 获取语言
 * @return [type] [description]
 */
function cms_get_language(){
	if((config('mod.language') == 'auto' || !config('mod.installed')) && !empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])){
		$lang = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
		$lang = explode(',', $lang[0]);
		return $lang[0];
	}else{
		return config('mod.language');
	}
}

/** 修改语言并更新配置 */
$lang = strtolower(cms_get_language());
if($lang != strtolower(config('mod.language'))){
	if(file_exists($file = __ROOT__.'user/lang/'.$lang.'.php')){
		lang(include($file));
	}else{
		lang(include(__ROOT__.'user/lang/en-us.php'));
	}
	if(!config('mod.installed')){
		config('mod.language', cms_get_language());
		export(config(), __ROOT__.'user/config/config.php');
	}
}
unset($lang, $file);

/** 设置并更新时区 */
add_action('mod.setTimezone', 'extensions\mod::setTimezone', false);

/** 除了模板目录，其他目录均开启编译器 */
add_action('mod.template.load', function(){
	if(!is_template()){
		config('mod.template.compiler.enable', true);
	}
});

//更新配置前先在内存中重写配置
add_action('mod.config', function($arg){
	if(is_display('app/admin/settings.html')){
		cms_rewrite_config('config.php', false);
	}
});