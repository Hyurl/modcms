<?php
/** 为 post 模块添加 like 操作 */
add_action('post.like', function($arg){
	if(empty($arg['post_id'])) return error(lang('mod.missingArguments'));
	if(!isset($_COOKIE['mod_php_liked_posts'])){
		setCookie('mod_php_liked_posts', '0', time()+60*60*24*365);
		$_COOKIE['mod_php_liked_posts'] = '0';
	}
	$liked = explode(',', $_COOKIE['mod_php_liked_posts']);
	if(!in_array($arg['post_id'], $liked)){
		array_push($liked, $arg['post_id']);
		setCookie('mod_php_liked_posts', implode(',', $liked), time()+60*60*24*365);
		if(database::open(0)->update('post', "post_likes = post_likes + 1", "post_id = {$arg['post_id']}")){
			return Post::get(array('post_id'=>$arg['post_id']));
		}else{
			return error('文章点赞失败。');
		}
	}else return error('文章已点赞。');
}, false);

add_action('post.get', function($input){
	$pluginURL = SITE_URL.'app/plugins/'.basename(__DIR__);
	if(is_single()){
		if(isset($_COOKIE['mod_php_liked_posts'])){
			$liked = explode(',', $_COOKIE['mod_php_liked_posts']);
			$likeDisabled = in_array($input['post_id'], $liked) ? ' disabled' : '';
		}else{
			$likeDisabled = '';
		}
		$input['post_content'] .= '
<div style="text-align:center;margin-top:20px;">
	<button class="btn btn-default post-like'.$likeDisabled.'" style="background:#fff url('.$pluginURL.'/like.png) no-repeat 5px 5px;padding-left:35px;" role="button" href="javascript:;" data-id="'.$input['post_id'].'">
		赞('.$input['post_likes'].')
	</button>
</div>
<script>
	SITE_URL = "'.site_url().'";
</script>
<script src="'.$pluginURL.'/javascript.js"></script>
';
	return $input;
	}
});

add_action('mod.init', function(){
	$database = database();
	if(!isset($database['post']['post_likes'])){
		$prefix = config('mod.database.prefix');
		$attr = 'INT(11) UNSIGNED DEFAULT 0';
		$database['post']['post_likes'] = $attr;
		if(database::open(0)->query("ALTER TABLE `{$prefix}post` ADD `post_likes` {$attr}")){
			export($database, __ROOT__.'user/config/database.php');
		}
	}
});