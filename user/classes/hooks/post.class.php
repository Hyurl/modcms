<?php
namespace hooks;

class post{
	static function pushBaiduLink($post){
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
	}

	static function updateBaiduLink($post){
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
	}

	static function trimContent($input){
		if(is_socket() && !empty($input['post_content'])){
			$input['post_content'] = trim($input['post_content']);
			return $input;
		}
	}

	static function replaceIframeWithVideo($arg){
		if(!empty($arg['post_content'])){
			$arg['post_content'] = preg_replace('/<iframe(.*)src="(.*)\.mp4"(.*)>(.*)<\/iframe>/Ui', '<video$1src="$2.mp4"$3>Your browser does not support this video.</video>', $arg['post_content']);
			return $arg;
		}
	}

	static function replaceVideoWithIframe($arg){
		if(is_display('app/admin/posts.html')){
			$arg['post_content'] = preg_replace('/<video(.*)src="(.*)"(.*)>(.*)<\/video>/Ui', '<iframe$1src="$2"$3>$4</iframe>', $arg['post_content']);
			return $arg;
		}
	}
}