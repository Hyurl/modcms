<?php
add_action(array('link.add', 'link.update', 'link.delete'), function(){
	if(!is_admin() && is_editor()) return error(lang('mod.permissionDenied'));
});