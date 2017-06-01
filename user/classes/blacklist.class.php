<?php
final class blacklist extends mod{
	const TABLE = "blacklist";
	const PRIMKEY = "blacklist_id";

	static function getUsers($arg = array()){
		$limit = @$limit['limit'] ?: 0;
		if($limit && !empty($arg['page'])){
			$limit = $limit*($arg['page']-1).",".$limit;
		}
		$result = database::open(0)->select('blacklist', 'blacklist_id, blacklist_uid', 'blacklist_uid > 0', $limit, 'blacklist_uid asc');
		$blacklist = array();
		while($single = $result->fetch()){
			$id = $single;
			$user = database::select('user', 'user_name, user_nickname', "user_id = {$single['blacklist_uid']}")->fetch();
			$user = array('blacklist_user'=>($user['user_name'] ?: $user['user_nickname']));
			$blacklist[] = array_merge($id, $user);
		}
		$extra = array(
			'total' => (int)database::select('blacklist', 'count(*) as count', 'blacklist_uid > 0')->fetchObject()->count
			);
		return $blacklist ? success($blacklist, $extra) : error('无黑名单数据。');
	}

	static function getEmails(array $arg, $name = 'email'){
		$limit = @$limit['limit'] ?: 0;
		if($limit && !empty($arg['page'])){
			$limit = $limit*($arg['page']-1).",".$limit;
		}
		$result = database::open(0)->select('blacklist', 'blacklist_id, blacklist_'.$name, 'blacklist_'.$name.' <> ""', $limit, 'blacklist_'.$name.' asc');
		$blacklist = array();
		while($single = $result->fetch()){
			$blacklist[] = $single;
		}
		$extra = array(
			'total' => (int)database::select('blacklist', 'count(*) as count', 'blacklist_'.$name.' <> ""')->fetchObject()->count
			);
		return $blacklist ? success($blacklist, $extra) : error('无黑名单数据。');
	}

	static function getIps(array $arg){
		return self::getEmails($arg, 'ip');
	}
}