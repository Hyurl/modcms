<?php
/**
 * 设置仅允许管理员访问的文件或文件夹
 */
return array(
	/** 仅允许管理员访问的文件（夹） */
	'admin'=>array(
		'features.html',
		'plugins.html',
		'settings.html',
		'settings/',
		'advanced/'
		),
	
	/** 仅允许编辑以上用户访问的文件（夹） */
	'editor'=>array(
		'home.html',
		'categories.html',
		'modules.html',
		'modules/'
		)
);