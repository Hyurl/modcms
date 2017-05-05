<?php
if(empty($_GET['folder']) || empty($_GET['type'])){
	redirect(ADMIN_URL.'features.html');
}
$title = $_GET['type'] == 'template' ? lang('admin.editTemplate') : lang('admin.editPlugin');
$folder = $_GET['type'] == 'template' ? 'template/' : 'plugins/';
$dir = __ROOT__.'app/'.$folder.$_GET['folder'];
$template = array($_GET['folder']=>xscandir($dir));
/** admin_get_template_files() 遍历输出目录树 */
function admin_get_template_files($tree, $isRoot = true, $paddingLeft = 0, $filename = ''){
	if($isRoot){
		global $folder;
		$filename = $folder;
		echo '<ul class="list-group">';
	}else{
		$paddingLeft += 20;
	}
	$_filename = $filename;
	foreach ($tree as $key => $value) {
		if($value == '.' || $value == '..') continue;
		if(is_array($value)){
			$filename .= $key.'/';
			echo '<li data-filename="'.$filename.'" class="list-group-item has-children" style="padding-left: '.$paddingLeft.'px;"><i class="glyphicon glyphicon-chevron-down"></i> '.$key.'</li>';
			echo '<li class="list-group-item"><ul class="list-group">';
			admin_get_template_files($value, false, $paddingLeft, $filename);
			echo '</ul></li>';
		}else{
			$filename .= $value;
			echo '<li data-filename="'.$filename.'" class="list-group-item" style="padding-left: '.$paddingLeft.'px;">'.$value.'</li>';
		}
		$filename = $_filename;
	}
	if($isRoot) echo '</ul>';
}