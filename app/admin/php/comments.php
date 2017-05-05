<?php
if(!IS_AUTH && empty($_GET['post_id'])){
	$_GET['user_id'] = me_id();
}

/** 设置人称和标题 */
$tabPerson = lang('comment.myComments');
$tabReply = lang('comment.replyToMe');
if(isset($_GET['comment_status']) && !$_GET['comment_status']){
	$title = lang('comment.unreviewedComments');
}elseif(!empty($_GET['comment_id'])){
	$title = lang('comment.currentComment');
}elseif(!empty($_GET['post_id']) && get_post($_GET['post_id'])){
	$title = lang('comment.commentsOfPostTitle', post_title());
}else if(empty($_GET['user_id']) && empty($_GET['commentsTo'])){
	$title = lang('comment.label');
}else{
	get_user($_GET['user_id']);
	if(user_id() == me_id()){
		if(empty($_GET['commentsTo'])){
			$title = lang('comment.myComments');
		}else{
			$title = lang('comment.replyToMe');
		}
	}else{
		if(empty($_GET['commentsTo'])){
			$title = lang('comment.commentsOfUser', user_nickname() ?: user_name());
		}else{
			$title = lang('comment.replyToUser', user_nickname() ?: user_name());
		}
		if(user_gender() == 'female'){
			$tabPerson = lang('comment.herComments');
			$tabReply = lang('comment.replyToHer');
		}else{
			$tabPerson = lang('comment.hisComments');
			$tabReply = lang('comment.replyToHim');
		}
	}
}

/** @var array 请求参数 */
$arg = $_GET;
if(!empty($_GET['commentsTo'])){
	$arg['comment_parent_uid'] = $arg['user_id'];
	unset($arg['user_id']);
	if(config('comment.review') && !IS_AUTH){
		$arg['comment_status'] = 1;
	}
}
if(empty($_GET['sequence'])){
	$arg['sequence'] = 'desc';
	$arg['orderby'] = 'comment_time';
}
if(isset($arg['comment_status']) && IS_AUTH){
	unset($arg['user_id']);
}

$_arg = $_GET;
unset($_arg['commentsTo']);
unset($_arg['comment_id']);
unset($_arg['post_id']);
unset($_arg['comment_status']);
if(empty($_arg['user_id'])){
	$_arg['user_id'] = ME_ID;
}
$commentCount = array(
	'all'=>admin_count_records('comment'),
	'person'=>admin_count_records('comment', $_arg),
	);
if(IS_AUTH){
	unset($_arg['user_id']);
}
$commentCount['unreviewed'] = admin_count_records('comment', array_merge($_arg, array('comment_status'=>0)));
if(!empty($_GET['commentsTo']) || !empty($_GET['user_id'])){
	unset($_arg['user_id']);
	$commentCount['to'] = admin_count_records('comment', array_merge($_arg, array('comment_parent_uid'=>$_GET['user_id'])));
}
if(!empty($_GET['post_id'])){
	$commentCount['post'] = admin_count_records('comment', array('post_id'=>$_GET['post_id']));
}