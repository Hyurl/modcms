<?php
/** 检查评论功能是否开启 */
add_hook('comment.add', function($arg){
	if(!config('comment.enable')) //判断是否开启评论
		return error(lang('comment.commentDisabled'));
	if(!empty($arg['post_id'])){
		if(get_post(array('post_id'=>$arg['post_id']))){ //查询文章是否存在
			if(!post_commentable()) //判断当前文章是否允许评论
				return error(lang('post.uncommentable'));
		}else{
			return error(lang('mod.notExists', lang('post.label')));
		}
	}
});

/** 匿名评论，原理是评论前创建并登录一个新用户 */
add_action('comment.add', function($arg){
	$anonymous = config('comment.anonymous');
	if($anonymous['enable'] && !is_logined()){
		if(session_status() != PHP_SESSION_ACTIVE) session_start();
		$login = explode('|', config('user.keys.login'));
		$require = explode('|', $anonymous['require']);
		$where = '';
		foreach ($require as $key) {
			if(empty($arg[$key])){
				return error(lang('mod.missingArguments'));
			}elseif(in_array($key, $login)){
				$where = "`{$key}` = '{$arg[$key]}'"; //拼合 where 查询语句
				break;
			}
		}
		if(!$where) return error(lang('mod.missingArguments'));
		if($anonymous['verify']){ //判断是否使用验证码
			if(empty($arg['vcode']) || strtolower($arg['vcode']) != strtolower($_SESSION['vcode'])){ //判断验证码是否正确
				return error(lang('admin.wrongVcode'));
			}elseif((time() - $_SESSION['time']) > 60*30){ //判断验证码是否过期
				return error(lang('admin.vcodeTimeout'));
			}
		}
		$result = database::open(0)->select('user', '*', $where); //尝试获取用户
		if($result && $user = $result->fetch()){ //用户存在
			if(!password_verify('', $user['user_password'])){ //校验密码是否为空
				return error(lang('user.infoProtected'));
			}
			$user['user_password'] = '';
			user::login($user); //登录这个用户
			if(error()){
				return error(lang('mod.addFailed', lang('comment.label')));
			}
		}else{ //用户不存在，则创建一个新用户
			$userSerialize = explode('|', config('user.keys.serialize'));
			$userKeys = database('user');
			$userInfo = array();
			foreach($arg as $key => $value){
				if(in_array($key, $userKeys) && $key != 'user_id' && $key != 'user_password'){
					if(in_array($key, $userSerialize)) //对数据进行序列化存储
						$value = config('mod.jsonSerialize') ? json_encode($value) : serialize($value);
					$userInfo[$key] = escape_tags($value, config('mod.escapeTags'));
				}
			}
			$userInfo['user_password'] = md5_crypt(''); //加密用户密码
			if(database::insert('user', $userInfo)){ //讲用户信息写入数据库
				$userInfo['user_password'] = '';
				user::login($userInfo); //登录新创建的用户
				if(error()){
					return error(lang('mod.addFailed', lang('comment.label')));
				}
			}else{
				return error(lang('mod.addFailed', lang('comment.label')));
			}
		}
	}
});

/** 在回复评论时添加父评论的用户 ID */
add_action('comment.add', function($arg){
	if(!empty($arg['comment_parent']) && get_comment($arg['comment_parent'])){
		$arg['comment_parent_uid'] = comment_user('id');
		$arg['post_id'] = comment_post('id');
		return $arg;
	}
});

/** 在添加评论时检测是否在黑名单内 */
add_action('comment.add', function($arg){
	if((me_id() && get_blacklist(array('blacklist_uid' => me_id()))) || (me_email() && get_blacklist(array('blacklist_email'=>me_email())))) {
		return error(lang('comment.youAreBlacked'));
	}
	$arg['client_ip'] = get_client_ip();
	if(!empty($arg['client_ip']) && get_blacklist(array('blacklist_ip'=>$arg['client_ip']))){
		return error(lang('comment.youAreBlacked'));
	}
});

/** 发送评论邮件 */
add_action('comment.add.complete', function($comment){
	if(config('comment.notify')){
		if(get_post($comment['post_id'])){
			$title = config('site.name').': '.post_title();
			$author = '<div><h4><a href="mailto:'.$comment['user_email'].'" style="text-decoration: none;"><strong>'.($comment['user_nickname'] ?: $comment['user_name']).'</strong></a> <small style="color: gray;">'.date('Y-m-d H:i', $comment['comment_time']).'</small></h4>';
			$hasParent = $comment['comment_parent'] && get_comment($comment['comment_parent']);
			$at = $hasParent ? '<span style="color: gray">@'.(comment_user('nickname') ?: comment_user('name')).' </span>' : '';
			$content = str_replace("\n", '<br>', $comment['comment_content']);
			$quote = $hasParent ? '<div style="border: 1px solid #ddd;padding: 10px;border-left-width: 4px;background-color: rgba(238, 238, 238, 0.55);color: gray;">'.(comment_user('nickname') ?: comment_user('name')).': '.str_replace("\n", '<br>', comment_content()).'</div>' : '';
			$view = '<p><a style="text-decoration: none;" href="'.(post_link() ?: create_url(config('post.staticURI'), the_post())).'#comment_id-'.$comment['comment_id'].'"  target="_blank">'.lang('admin.viewDetails').'</a></p>';
			$body = '<!DOCTYPE html><html><head><meta http-equiv="content-type" content="text/html;charset=utf-8"></head><body>'.$author.'<p>'.$at.$content.'</p>'.$quote.$view.'</div></body></html>';
			if($comment['user_id'] != post_user('id') && post_user('email')){
				mail::to((post_user('nickname') ?: post_user('name')).'<'.post_user('email').'>')
					->subject('['.lang('comment.label').']'.$title)
					->send($body);
			}
			if($hasParent && comment_user('email')){
				mail::to((comment_user('nickname') ?: comment_user('name')).'<'.comment_user('email').'>')
					->subject('['.lang('comment.reply').']'.$title)
					->send($body);
			}
		}
	}
});

/** 评论加入审核状态 */
add_action('comment.add', function($arg){
	if(config('comment.review') && !is_admin() && !is_editor()){
		$arg['comment_status'] = 0;
	}else{
		$arg['comment_status'] = 1;
	}
	return $arg;
});

/** 仅允许编辑以上劝降审核评论 */
add_action('comment.update', function($arg){
	if(!empty($arg['comment_status']) && !is_admin() && !is_editor()){
		unset($arg['comment_status']);
	}
	return $arg;
});

/** 对评论者本人或者编辑以上权限用户以外的用户过滤未审核评论 */
add_action('comment.get', function($arg){
	if(config('comment.review') && $arg['comment_status'] == 0 && !is_admin() && !is_editor() && $arg['user_id'] != me_id()){
		return false;
	}
});

/** 获取未审核评论数 */
add_action('comment.getUnreviewedCount', function($arg){
	if(!is_editor() && !is_admin()) return error(lang('mod.permissionDenied'));
	$arg['comment_status'] = 0;
	$result = comment::getMulti($arg);
	if($result['success']){
		return success($result['total']);
	}else{
		return error($result['data']);
	}
}, false);

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