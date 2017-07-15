<?php
/** 删除默认文件编辑限制 */
remove_hook(array('file.update.check_permission', 'file.delete.check_permission'));

/** 上传并解压模板 */
add_action('file.uploadZip', 'extensions\file::uploadZip', false);

/** 移除目录 */
add_action('file.removeFolder', 'extensions\file::removeFolder', false);

/** 获取文件内容 */
add_action('file.getContent', 'extensions\file::getContent', false);

/** 存储文件内容 */
add_action('file.putContent', 'extensions\file::putContent', false);

/** 删除缓存目录 */
add_action('file.removeTempFolder', 'extensions\file::removeTempFolder', false);

// 分段上传文件时检查用户权限
add_action('file.save', function($file){
	if(!empty($file['file_src']) && get_file(array('file_src'=>$file['file_src'])) && file_user('id') != me_id()){
		return error(lang('mod.permissionDenied'));
	}
});