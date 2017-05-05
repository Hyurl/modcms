<?php
/** 获得磁盘空间信息 */
$dir = strpos(__ROOT__, ':') === 1 ? substr(__ROOT__, 0, 2) : '/';
$free = round(disk_free_space($dir)/1024/1024);
if($free >= 1024){
	$freeSpace = round($free/1024).'GB('.lang('admin.free').')';
}else{
	$freeSpace = $free.'MB('.lang('admin.free').')';
}
$total = round(disk_total_space($dir)/1024/1024);
if($total >= 1024){
	$totalSpace = round($total/1024).'GB('.lang('admin.total').')';
}else{
	$totalSpace = $total.'MB('.lang('admin.total').')';
}

/** 上传限制 */
$uploadLimit = floor(config('file.upload.maxSize')/1024).'MB('.lang('admin.current').') / '.ini_get('post_max_size').'B('.lang('admin.maximum').')';

/** 数据库信息 */
$type = database::open(0)->set('type');
$dbVersion = ($type == 'mysql' ? 'MySQL' : 'sqlite').' '.database::info('serverVersion');
if($type == 'mysql'){
	$dbUsedSize = database::dbname('information_schema')->query("select round(sum(data_length)/1024, 2) as size from tables where table_schema='".config('mod.database.name')."';")->fetchObject()->size;
	database::dbname(config('mod.database.name'));
}else{
	$dbUsedSize = round(filesize(database::set('dbname').'.db')/1024, 2);
}
if($dbUsedSize >= 1024){
	$dbUsedSize = round($dbUsedSize/1024, 2);
	if($dbUsedSize >= 1024){
		$dbUsedSize = round($dbUsedSize/1024, 2).'GB';
	}else{
		$dbUsedSize = $dbUsedSize.'MB';
	}
}else{
	$dbUsedSize = $dbUsedSize.'KB';
}