<?php
/**
 * 设置其他模块列表中
 */
return array(
	/** 侧栏导航菜单 */
	'sidebar'=>array(
		array(
			'id'=>'home', //元素 id
			'privilege'=>'editor', //最低访问权限，无权限则不会显示该菜单，可选值 admin, editor, all
			'icon'=>'th-large', //glyphicon 图标名称
			'href'=>'home.html', //链接地址
			'text'=>lang('admin.home'), //显示名称
			'title'=>'', //提示语
			'separator'=>false //与后面的菜单进行分隔
			),
		array(
			'id'=>'site-home',
			'privilege'=>'all',
			'icon'=>'home',
			'href'=>SITE_URL.(config('mod.pathinfoMode') ? 'index.php' : ''),
			'text'=>lang('admin.siteHome'),
			'title'=>'',
			'separator'=>true
			),
		array(
			'id'=>'users',
			'privilege'=>'all',
			'icon'=>'user',
			'href'=>'users.html',
			'text'=>lang('user.label'),
			'title'=>'',
			'separator'=>false
			),
		array(
			'id'=>'categories',
			'privilege'=>'editor',
			'icon'=>'list-alt',
			'href'=>'categories.html',
			'text'=>lang('category.label'),
			'title'=>'',
			'separator'=>false
			),
		array(
			'id'=>'posts',
			'privilege'=>'all',
			'icon'=>'book',
			'href'=>'posts.html',
			'text'=>lang('post.label'),
			'title'=>'',
			'separator'=>false
			),
		array(
			'id'=>'comments',
			'privilege'=>'all',
			'icon'=>'comment',
			'href'=>'comments.html',
			'text'=>lang('comment.label'),
			'title'=>'',
			'separator'=>false
			),
		array(
			'id'=>'files',
			'privilege'=>'all',
			'icon'=>'file',
			'href'=>'files.html',
			'text'=>lang('file.label'),
			'title'=>'',
			'separator'=>true
			),
		array(
			'id'=>'modules',
			'privilege'=>'editor',
			'icon'=>'th',
			'href'=>'modules.html',
			'text'=>lang('admin.modules'),
			'title'=>'',
			'separator'=>true
			),
		array(
			'id'=>'features',
			'privilege'=>'admin',
			'icon'=>'eye-open',
			'href'=>'features.html',
			'text'=>lang('admin.feature'),
			'title'=>'',
			'separator'=>false
			),
		array(
			'id'=>'plugins',
			'privilege'=>'admin',
			'icon'=>'leaf',
			'href'=>'plugins.html',
			'text'=>lang('admin.plugin'),
			'title'=>'',
			'separator'=>true
			),
		array(
			'id'=>'settings',
			'privilege'=>'admin',
			'icon'=>'cog',
			'href'=>'settings.html',
			'text'=>lang('admin.settings'),
			'title'=>'',
			'separator'=>true
			),
		),

	/** 更多模块列表菜单 */
	'modules'=>array(
		array(
			'id'=>'links',
			'privilege'=>'editor',
			'icon'=>'link',
			'href'=>'modules/links.html',
			'text'=>lang('link.friendlyLinks'),
			'title'=>'',
			'separator'=>false
			),
		array(
			'id'=>'blacklists',
			'privilege'=>'editor',
			'icon'=>'warning-sign',
			'href'=>'modules/blacklists.html',
			'text'=>lang('blacklist.label'),
			'title'=>'',
			'separator'=>false
			)
		)
	);