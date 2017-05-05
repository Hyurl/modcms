<?php
if(!IS_AUTH){
	$_GET['user_id'] = me_id();
}

/** 设置人称和标题 */
$tabPerson = lang('post.myPosts');
if(empty($_GET['action'])){
	if(!empty($_GET['post_id'])){
		$title = lang('post.currentPost');
	}elseif(!empty($_GET['category_id']) && get_category($_GET['category_id'])){
		$title = lang('post.postsOfCategory', category_alias() ?: category_name());
	}elseif(empty($_GET['user_id']) || !get_user($_GET['user_id'])){
		$title = lang('post.label');
	}else if(user_id() == me_id()){
		$title = lang('post.myPosts');
	}else{
		$title = lang('post.postsOfUser', user_nickname() ?: user_name());
		if(user_gender() == 'female'){
			$tabPerson = lang('post.herPosts');
		}else{
			$tabPerson = lang('post.hisPosts');
		}
	}
}else{
	if($_GET['action'] == 'search'){
		$title = lang('admin.searchResult');
	}else{
		$title = $_GET['action'] == 'update' ? lang('post.editPost') : lang('post.addPost');
	}
}

/** admin_fetch_multi_post() 获取多条文章记录 */
function admin_fetch_multi_post($arg){
	$get_multi = @$_GET['action'] == 'search' ? 'get_search_post' : 'get_multi_post';
	return $get_multi($arg);
}

/** @var string 分类目录 ID */
$categoryId = !empty($_GET['category_id']) ? '&category_id='.$_GET['category_id'] : '';

/** @var array 文章类型 */
$postType = lang('post.postTypes');

if(empty($_GET['action']) || $_GET['action'] == 'search'){
	$arg = $_GET;
	if(empty($arg['user_id']) && !IS_AUTH) $arg['user_id'] = ME_ID;
	$_arg = $arg;
	unset($_arg['category_id']);
	unset($_arg['post_id']);
	$postCount = array(
		'all'=>admin_count_records('post'),
		'person'=>admin_count_records('post', $_arg),
		'category'=>get_category() ? admin_count_records('post', $arg) : 0
		);
	if(!empty($_GET['keyword'])){
		get_search_post($_GET);
		$postCount['search'] = _post('total');
		get_search_post(0);
	}
}