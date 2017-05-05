<?php
/** 百度链接主动推送(实时)设置 */
if(config('site.baiduSEO')){
	/** 推送链接 */
	add_action('post.add.complete', function($post){
		if($post['post_type'] == 0){
			$data = $post['post_link'] ?: create_url(config('post.staticURI'), $post);
			curl(array(
				'url'=>'http://data.zz.baidu.com/urls?site='.config('site.baiduSEO.site').'&token='.config('site.baiduSEO.token').(@$post['post_original'] ? '&type=original' : ''),
				'method'=>'post',
				'data'=>$data,
				'requestHeaders'=>array(
					'Content-Type'=>'text/plain',
					'Content-Length'=>strlen($data)
					)
				));
		}
	});
	/** 更新链接 */
	add_action('post.update.complete', function($post){
		global $baidu;
		if($post['post_type'] == 0){
			$data = $post['post_link'] ?: create_url(config('post.staticURI'), $post);
			curl(array(
				'url'=>'http://data.zz.baidu.com/update?site='.config('site.baiduSEO.site').'&token='.config('site.baiduSEO.token'),
				'method'=>'post',
				'data'=>$data,
				'requestHeaders'=>array(
					'Content-Type'=>'text/plain',
					'Content-Length'=>strlen($data)
					)
				));
		}
	});
}

/**
 * get_post_desc() 获取文章简介
 * @param  integer $len 简介长度
 * @return string       简介内容
 */
function get_post_desc($len = 100){
	return cms_get_content_desc(post_content(), $len);
}

/** 
 * get_post_link() 获取文章连接
 * @return string  文章连接
 */
function get_post_link(){
	return post_link() ?: create_url(config('post.staticURI'), the_post());
}

/** get_post_date() 获取文章发表日期 */
function get_post_date(){
	return date(config('mod.dateFormat'), post_time());
}

add_action('post.update', function($input){
	if(is_websocket()){
		$input['post_content'] = trim($input['post_content']);
		return $input;
	}
});