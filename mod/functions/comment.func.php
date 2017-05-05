<?php
/** 自动设置评论回复数量 */
add_hook('comment.get.set_reply_counts', function($input){
	$count = database::open(0)
		   ->select('comment', 'COUNT(*) AS count', "`comment_parent` = {$input['comment_id']}")
		   ->fetchObject()
		   ->count; //实际回复数量
	if($count != $input['comment_replies']){
		$input['comment_replies'] = $count;
		//更新数据库记录
		database::update('comment', "`comment_replies` = $count", "`comment_id` = {$input['comment_id']}");
	}
	return $input;
}, false);