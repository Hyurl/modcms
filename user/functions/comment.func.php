<?php
/** 检查评论功能是否开启 */
add_hook('comment.add', 'comment::checkEnable');

/** 匿名评论，原理是评论前创建并登录一个新用户 */
add_action('comment.add', 'comment::anonymousComment');

/** 在回复评论时添加父评论的用户 ID */
add_action('comment.add', 'comment::addParentIdWhenReply');

/** 在添加评论时检测是否在黑名单内 */
add_action('comment.add', 'comment::checkBlacklist');

/** 发送评论提醒邮件 */
add_action('comment.add.complete', 'comment::sendNotifyEmail');

/** 评论加入审核状态 */
add_action('comment.add', 'comment::putInUnreviewed');

/** 仅允许编辑以上权限审核评论 */
add_action('comment.update', 'comment::checkReviewPermission');

/** 对评论者本人或者编辑以上权限用户以外的用户过滤未审核评论 */
add_action('comment.get', 'filterUnreviewedComment');

/** 
 * get_comment_desc() 获取评论摘要
 * @param  integer $len 摘要长度
 * @return [type]       [description]
 */
function get_comment_desc($len = 100){
	return cms_get_content_desc(comment_content(), $len);
}

/** get_comment_date() 获取评论发表日期 */
function get_comment_date(){
	return date(config('mod.dateFormat'), comment_time());
}