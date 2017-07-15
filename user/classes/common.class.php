<?php
class common{
	static function getAbsolutePath($data){
		if(!empty($data['file_src']) && strpos($data['file_src'], '://') === false){ //文件源地址
			$data['file_src'] = site_url().$data['file_src'];
		}
		if(!empty($data['post_thumbnail']) && strpos($data['post_thumbnail'], '://') === false){ //文章缩略图
			$data['post_thumbnail'] = site_url().$data['post_thumbnail'];
		}
		if(!empty($data['user_avatar']) && strpos($data['user_avatar'], '://') === false){ //用户头像
			$data['user_avatar'] = site_url().$data['user_avatar'];
		}
		if(!empty($data['link_logo']) && strpos($data['link_logo'], '://') === false){ //友情链接 LOGO
			$data['link_logo'] = site_url().$data['link_logo'];
		}
		if(!empty($data['post_content'])){ //替换文章中的文件链接
			$original = array('src="upload/', 'href="upload/');
			$replacement = array('src="'.site_url().'upload/', 'href="'.site_url().'upload/');
			$data['post_content'] = str_replace($original, $replacement, $data['post_content']);
			if(is_single() && preg_match_all('/href="(.*)"/Ui', $data['post_content'], $matches)){
				for ($i=0;$i<count($matches[1]); $i++) {
					if(!stripos($matches[1][$i], '://')){
						$data['post_content'] = str_replace($matches[0][$i], 'href="'.site_url().$matches[1][$i].'"', $data['post_content']);
					}
				}
			}
		}
		return $data;
	}

	static function getRelativePath($arg){
		if(!empty($arg['user_avatar']) && strapos($arg['user_avatar'], site_url()) === 0){
			$arg['user_avatar'] = substr($arg['user_avatar'], strlen(site_url()));
		}
		if(!empty($arg['post_thumbnail']) && strapos($arg['post_thumbnail'], site_url()) === 0){
			$arg['post_thumbnail'] = substr($arg['post_thumbnail'], strlen(site_url()));
		}
		if(!empty($arg['link_logo']) && strapos($arg['link_logo'], site_url()) === 0){
			$arg['link_logo'] = substr($arg['link_logo'], strlen(site_url()));
		}
		if(!empty($arg['post_content'])){
			$original = array('src="'.site_url().'upload/', 'href="'.site_url().'upload/');
			$replacement = array('src="upload/', 'href="upload/');
			$arg['post_content'] = str_replace($original, $replacement, $arg['post_content']);
		}
		return $arg;
	}

	static function emptyCategoryreport404($init){
		if(is_category()){
			$_GET['category_id'] = category_id();
			$_GET['post_type'] = 0;
			if(get_multi_post($_GET)){
				get_multi_post(0);
			}else if(!empty($_GET['page']) && $_GET['page'] != 1 && !is_client_call()){
				$init['__DISPLAY__'] = false;
			}
		}
		return $init;
	}

	static function checkCategoryWhenLoadingPosts($init){
		if(is_single()){
			foreach(post_category() as $key => $value){
				if(isset($_GET[$key]) && $value != $_GET[$key]){
					$init['__DISPLAY__'] = false;
				}
			}
		}
		return $init;
	}

	
}