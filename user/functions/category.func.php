<?php
//添加或者更新分类目录名称时判断其可用性
add_action(array('category.add', 'category.update'), function($input){
	if(!empty($input['category_name']) && strapos(config('category.staticURI'), '{category_name}') === 0 && (file_exists(__ROOT__.$input['category_name']) || file_exists(__ROOT__.config('mod.template.appPath').$input['category_name']) || file_exists(template_path($input['category_name'])))) {
		return error(lang('category.invalidName'));
	}
});