<?php
/** 百度链接主动推送(实时)设置 */
if(config('site.baiduSEO')){
	/** 推送链接 */
	add_action('post.add.complete', 'hooks\post::pushBaiduLink');
	/** 更新链接 */
	add_action('post.update.complete', 'hooks\post::updateBaiduLink');
}

/** Socket 模式中去掉文章内容两端的空白 */
add_action('post.update', 'hooks\post::trimContent');

// 在添加或更新文章时，用 vidio 标签代替 iframe 标签调用 mp4
add_action(array('post.add', 'post.update'), 'hooks\post::replaceIframeWithVideo');

// 在文件页面将 video 标签转换为 iframe 标签
add_action('post.get', 'hooks\post::replaceVideoWithIframe');

/**
 * get_post_desc() 获取文章简介
 * @param  integer $len 简介长度
 * @return string       简介内容
 */
function get_post_desc($len = 100){
	return the_post('desc') ?: cms_get_content_desc(post_content(), $len);
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