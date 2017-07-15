<?php
final class link extends mod{
	const TABLE = "link";
	const PRIMKEY = "link_id";

	static function checkPermission(){
		if(!is_admin() && is_editor()) return error(lang('mod.permissionDenied'));
	}
}