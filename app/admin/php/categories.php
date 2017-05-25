<?php
$categories = array();
function admin_list_categories($tree, $hasBorder = false){
	global $categories;
	echo '<ul class="list-group">';
	if(!empty($tree)){
		foreach ($tree as $subtree) {
			$categories[$subtree['category_id']] = $subtree['category_alias'] ?: $subtree['category_name'];
			$data = '';
			foreach ($subtree as $k => $v) {
				if(is_array($v)) $v = count($v);
				$data .= ' data-'.$k.'="'.$v.'"';
			}
			echo '<li class="list-group-item'.($hasBorder ? ' has-border' : '').(@$_GET['category_id'] == $subtree['category_id'] ? ' active' : '').'"'.$data.'>';
				$posts = '<span class="badge" title="'.lang('category.contentPostsCount', $subtree['category_posts']).'">'.$subtree['category_posts'].'</span>';
			if(is_array($subtree['category_children'])){
				echo $subtree['category_alias'] ?: $subtree['category_name'];
				echo '<span class="glyphicon glyphicon-chevron-down"></span>'.$posts.'</li>';
				echo '<li class="list-group-item list-children">';
				admin_list_categories($subtree['category_children'], true);
				echo '</li>';
			}else{
				echo $subtree['category_alias'] ?: $subtree['category_name'];
				echo $posts.'</li>';
			}
		}
	}
	echo '</ul>';
}