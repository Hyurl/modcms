<?php
function admin_count_blacklist($key){
	$result = database::select('blacklist', 'count(*) as count', "`blacklist_{$key}` <> ''");
	return $result ? (int)$result->fetchObject()->count : 0;
}

$blacklist = array(
	'all'=>admin_count_records('blacklist'),
	'uid'=>admin_count_blacklist('uid'),
	'email'=>admin_count_blacklist('email'),
	'ip'=>admin_count_blacklist('ip')
	);