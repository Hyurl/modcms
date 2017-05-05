<?php
if(@$_GET['profile'] == 'me' || !is_admin()){
	$_GET['user_id'] = me_id();
}
if(empty($_GET['action']) && empty($_GET['user_id']) && !is_admin()){
	report_403();
}elseif(get_multi_user($_GET)){
	get_multi_user(0);
}

/** 设置人称和标题 */
if(((@$_GET['action'] == 'update' && $_GET['user_id'] != me_id()) || @$_GET['action'] == 'add') && !is_admin()){
	report_403();
}else{
	get_user($_GET);
}
if(@$_GET['action'] == 'add'){
	$title = lang('user.addUser');
}else if(@$_GET['action'] == 'update'){
	if($_GET['user_id'] == me_id()){
		$title = lang('user.editMyInfo');
	}else{
		$title = lang('user.editUserInfo', user_nickname() ?: user_name());
	}
}else if(!empty($_GET['user_id'])){
	if($_GET['user_id'] == me_id()){
		$title = lang('user.myUserCenter');
	}else{
		$title = lang('user.userCenterOfUser', user_nickname() ?: user_name());
	}
}else{
	$title = lang('user.label');
}

/** @var array 性别 */
$userGender = lang('user.userGenders');

/** @var array 用户组 */
$userLevel = lang('user.userLevels');

/** @var array 私密信息字段 */
$userProtect = array(
	'user_realname' => lang('user.realname'),
	'user_identity' => lang('user.identity'),
	'user_company' => lang('user.company'),
	'user_number' => lang('user.number'),
	'user_email' => lang('user.email'),
	'user_tel' => lang('user.tel')
	);

if(empty($_GET['user_id']) && empty($_GET['action'])){
	/** @var array 用户数目 */
	$userCount = array(
		'all' => admin_count_records('user'),
		'admin' => admin_count_records('user', array('user_level'=>5)),
		'editor' => admin_count_records('user', array('user_level'=>4))
		);
}

/** 提示 */
$isMe = user_id() == ME_ID;
$female = user_gender() == 'female';
$lang = array(
	'viewUserCenter'=>$isMe ? lang('user.viewMyUserCenter') : ($female ? lang('user.viewHerUserCenter') : lang('user.viewHisUserCenter')),
	'realnameTip'=>$isMe ? lang('user.myRealnameTip') : ($female ? lang('user.herRealnameTip') : lang('user.hisRealnameTip')),
	'identityTip'=>$isMe ? lang('user.myIdentityTip') : ($female ? lang('user.herIdentityTip') : lang('user.hisIdentityTip')),
	'companyTip'=>$isMe ? lang('user.myCompanyTip') : ($female ? lang('user.herCompanyTip') : lang('user.hisCompanyTip')),
	'descTip'=>$isMe ? lang('user.myDescTip') : ($female ? lang('user.herDescTip') : lang('user.hisDescTip')),
	'newPasswordTip'=>@$_GET['action'] == 'add' ? lang('user.newPasswordTip2') : lang('user.newPasswordTip3'),
	'protectInfo'=>$isMe ? lang('user.myProtectInfo') : ($female ? lang('user.herProtectInfo') : lang('user.hisProtectInfo')),
	'postTitle'=>$isMe ? lang('post.myPosts') : ($female ? lang('post.herPosts') : lang('post.hisPosts')),
	'commentTitle'=>$isMe ? lang('comment.myComments') : ($female ? lang('comment.herComments') : lang('comment.hisComments')),
	);