<?php
/** 定义常量 */
define('CMS_VERSION', '1.3.5'); //ModCMS 版本
define('SITE_URL', site_url()); //网站根目录 URL 地址
define('ADMIN_URL', admin_url()); //后台根目录 URL 地址
define('ADMIN_DIR', admin_dir()); //后台根目录路径
define('__LANG__', cms_get_language(), true); //语言
if(config('mod.installed')){
	define('IS_LOGINED', is_logined()); //是否已登录
	define('IS_ADMIN', is_admin()); //是否为管理员
	define('IS_EDITOR', is_editor()); //是否为编辑
	define('IS_AUTH', IS_ADMIN || IS_EDITOR); //是否为管理员或编辑
	define('ME_ID', me_id()); //当前用户 ID
}