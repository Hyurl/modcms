<?php
if(extension_loaded('gd')){
	$ver = gd_info();
	preg_match('/[0-9\.]+/', $ver['GD Version'], $match);
	if(version_compare($match[0], '2.0.0') < 0){
		exit('GD 版本过低，无法使用图形功能。');
	}
}else{
	exit('GD 扩展未开启，无法使用图形功能。');
}
if(session_status() != PHP_SESSION_ACTIVE) session_start();
$_SESSION['vcode'] = rand_str(5);
$_SESSION['time'] = time();
image::set('width', 100)
	 ->set('height', 34)
	 ->set('bgcolor', '#fff')
	 ->set('color', 'rgba(117,175,202,0.8)')
	 ->set('font', admin_dir('fonts/arial.ttf'))
	 ->set('fontsize', 18)
	 ->set('x', 10)
	 ->set('y', 5)
	 ->open('vcode.png')
	 ->text($_SESSION['vcode']);
if(is_browser() && !is_ajax()){
	set_content_type('image/png');
	image::output();
}else{
	set_content_type('application/json');
	echo json_encode(array(session_name()=>session_id(), 'image'=>image::getBase64()));
}