<?php
function admin_count_blacklist($key){
	return (int)mysql::select('blacklist', 'count(*) as count', "`blacklist_{$key}` <> ''")->fetch_object()->count;
}

$blacklist = array(
	'all'=>admin_count_records('blacklist'),
	'uid'=>admin_count_blacklist('uid'),
	'email'=>admin_count_blacklist('email'),
	'ip'=>admin_count_blacklist('ip')
	);