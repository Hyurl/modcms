<?php
if(!IS_AUTH){
	$_GET['user_id'] = me_id();
}
if(empty($_GET['sequence'])){
	$_GET['sequence'] = 'desc';
}

$tabPerson = lang('file.myFiles');
if(!empty($_GET['file_id'])){
	$title = lang('file.currentFile');
}elseif(empty($_GET['user_id'])){
	$title = lang('file.label');
}else{
	get_user($_GET['user_id']);
	if(user_id() == me_id()){
		$title = lang('file.myFiles');
	}else{
		$title = lang('file.filesOfUser', user_nickname() ?: user_name());
		if(user_gender() == 'female'){
			$tabPerson = lang('file.herFiles');
		}else{
			$tabPerson = lang('file.hisFiles');
		}
	}
}

$arg = $_GET;
unset($arg['file_id']);
if(empty($arg['user_id'])){
	$arg['user_id'] = ME_ID;
}
$fileCount = array(
	'all'=>admin_count_records('file'),
	'person'=>admin_count_records('file', $arg)
	);