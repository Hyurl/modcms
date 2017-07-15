<?php
final class blacklist extends mod{
	const TABLE = "blacklist";
	const PRIMKEY = "blacklist_id";

	static function getUsers($arg = array()){
		$limit = @(int)$arg['limit'] ?: 0;
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
		$limit = @(int)$arg['limit'] ?: 0;
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

	static function ignoreExistingRecord($arg){
		if(!empty($arg['blacklist_uid']) && get_blacklist(array('blacklist_uid'=>$arg['blacklist_uid']))){
			unset($arg['blacklist_uid']);
		}
		if(!empty($arg['blacklist_email']) && get_blacklist(array('blacklist_email'=>$arg['blacklist_email']))){
			unset($arg['blacklist_email']);
		}
		if(!empty($arg['blacklist_ip']) && get_blacklist(array('blacklist_ip'=>$arg['blacklist_ip']))){
			unset($arg['blacklist_ip']);
		}
		if(empty($arg['blacklist_uid']) && empty($arg['blacklist_email']) && empty($arg['blacklist_ip'])){
			if(get_blacklist()){
				return error(lang('blacklist.alreadyInBlacklist'));
			}else{
				return error(lang('mod.missingArguments'));
			}
		}
	}

	static function checkPermission($arg){
		if(!is_admin() && !is_editor()){
			return error(lang('mod.permissionDenied'));
		}
	}

	static function getUserFromUid($arg){
		if($arg['blacklist_uid']){
			$user = database::open(0)
							->select('user', 'user_name, user_nickname', "user_id = ".(int)$arg['blacklist_uid'])
							->fetch();
			$user = array(
				'blacklist_user' => $user['user_name'] ?: $user['user_nickname']
				);
		}else{
			$user = array('blacklist_user'=>'');
		}
		return array_merge($arg, $user);
	}
}