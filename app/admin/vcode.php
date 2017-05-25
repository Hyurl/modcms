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
$generateColor = function(){
	$red = rand(0, 255);
	$green = rand(0, 255);
	$blue = rand(0, 255);
	return 'rgb('.$red.','.$green.','.$blue.')';
};
image::set('width', 100)
	 ->set('height', 34)
	 ->set('bgcolor', 'rgba(0,0,0,0)')
	 ->set('color', $generateColor())
	 ->set('font', admin_dir('fonts/arial.ttf'))
	 ->set('fontsize', 18)
	 ->set('x', 10)
	 ->set('y', 5)
	 ->open('vcode.png')
	 ->text($_SESSION['vcode'])
	 ->set('thickness', 4);
/** 随机画点 */
for($i=0; $i<20; $i++){
	image::set('color', $generateColor())->dot(rand(5, 95), rand(5, 30));
}
if(is_browser() && !is_ajax()){
	set_content_type('image/png');
	image::output();
}else{
	set_content_type('application/json');
	echo json_encode(array(session_name()=>session_id(), 'image'=>image::getBase64()));
}