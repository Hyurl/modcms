<?php
namespace extensions;

class mod{
	static function checkUpdate($arg){
		if(error()) return error();
		if(!is_admin()) return error(lang('mod.permissionDenied'));
		if(empty($arg['versionURL']) || empty($arg['type']) || empty($arg['version']))
			return error(lang('mod.missingArguments'));
		try{
			$result = json_decode(file_get_contents($arg['versionURL']), true) ?: curl(array('url'=>$arg['versionURL'], 'parseJSON'=>true));
			return success($result, array('type'=>$arg['type'], 'version'=>$arg['version']));
		}catch(Exception $e){
			return error(lang('admin.noNewVersionTip'));
		}
	}

	static function updateCMS($arg){
		if(error()) return error();
		$ok = false;
		if(config('mod.installed') && !is_console() && (!is_admin() || me_id() != 1)) return error(lang('mod.permissionDenied'));
		if(empty($arg['src']) || empty($arg['md5'])) return error(lang('mod.missingArguments'));
		$file = __ROOT__.'modcms.zip';
		$len = file_put_contents($file, @file_get_contents($arg['src']) ?: @curl(array('url'=>$arg['src'], 'followLocation'=>1)));
		if($len && md5_file($file) == $arg['md5']){
			$ok = @zip_extract($file, __ROOT__);
			if($ok){
				cms_rewrite_config('config.php');
				cms_rewrite_config('database.php');
				if(is_dir($dir = \template::$saveDir.'app/template/simplet/')){
					xrmdir($dir); //删除内置模板缓存
				}
			}
		}
		// unlink($file);
		return $ok ? success(lang('mod.updated')) : error(lang('admin.systemUpdateFailWarning'));
	}

	static function updateComponent($arg){
		if(error()) return error();
		$ok = false;
		$isTpl = @$arg['type'] == 'template';
		if(!is_admin()) return error(lang('mod.permissionDenied'));
		if(empty($arg['src']) || empty($arg['md5'])) return error(lang('mod.missingArguments'));
		$file = __ROOT__.(int)INIT_TIME.'.zip';
		@file_put_contents($file, @file_get_contents($arg['src']) ?: @curl(array('url'=>$arg['src'], 'followLocation'=>1)));
		if(md5_file($file) == $arg['md5']){
			$ok = zip_extract($file, __ROOT__.'app/'.($isTpl ? 'template/' : 'plugins/'));
		}
		unlink($file);
		if($ok){
			return success($isTpl ? lang('admin.templateUpdateSuccess') : lang('admin.pluginUpdateSuccess'));
		}else{
			return error($isTpl ? lang('admin.templateUpdateFail') : lang('admin.pluginUpdateFail'));
		}
	}

	static function checkDbUpdate(){
		if(error()) return error();
		if(!is_admin()) return error(lang('mod.permissionDenied'));
		$sqlite = \database::open(0)->set('type') == 'sqlite';
		$database = database();
		$tip = lang('admin.dbUpdateTip');
		$key = 'Tables_in_'.config('mod.database.name');
		$prefix = config('mod.database.prefix');

		//获取数据表
		$tables = array();
		$sql = $sqlite ? "select name from sqlite_master where type = 'table'" : 'SHOW TABLES';
		$key = 'Tables_in_'.config('mod.database.name');
		$result = \database::query($sql);
		while($result && $table = $result->fetchObject()){
			$name = $sqlite ? $table->name : $table->$key;
			if($prefix && strpos($name, $prefix) === 0){
				$tables[] = substr($name, strlen($prefix));
			}
		}

		// 获取字段属性
		foreach ($tables as $i => $table) {
			if(empty($tables[$table])){
				$tables[$table] = array();
				unset($tables[$i]);
			}
			$sql = $sqlite ? "pragma table_info(`{$prefix}{$table}`)" : "SHOW COLUMNS FROM `{$prefix}{$table}`";
			$result = \database::query($sql);
			$name = $sqlite ? 'name' : 'Field';
			$type = $sqlite ? 'type' : 'Type';
			while ($result && $column = $result->fetch()) {
				$tables[$table][$column[$name]] = str_ireplace(array(' UNSIGNED', ' '), '', $column[$type]);
			}
		}

		// 比较字段属性
		foreach($database as $name => $table){
			if(!isset($tables[$name])) return success($tip);
			foreach($table as $key => $attr){
				if(!isset($tables[$name][$key]) || stripos($attr, $tables[$name][$key]) !== 0){
					return success($tip);
				}
			}
		}
		foreach($tables as $name => $table){
			if(!isset($database[$name])) return success($tip);
			foreach($table as $key => $attr){
				if(!isset($database[$name][$key]) || stripos($database[$name][$key], $attr) !== 0){
					return success($tip);
				}
			}
		}
		return error(lang('admin.noUpdateItems'));
	}

	static function mailVcode($input){
		if(error()) return error();
		if(!empty($input['user_email']) && filter_var($input['user_email'], FILTER_VALIDATE_EMAIL)){
			$action = @$input['action'] ?: lang('admin.sendVcode');
			$uid = cms_get_uid_by_email($input['user_email']);
			if(get_user($uid)){
				if($action == lang('user.register')){
					return error(lang('user.emailIsUsed'));
				}else{
					$user = user_nickname() ?: user_name();
				}
			}else{
				if($action != lang('user.register')) return error(lang('user.emailIsNotUsed'));
			}
			if(session_status() != PHP_SESSION_ACTIVE) session_start();
			$_SESSION['vcode'] = rand_str(5);
			$_SESSION['time'] = time();
			$_SESSION['user_email'] = $input['user_email'];
			$user = isset($user) ? $user : (!empty($input['user_nickname']) ? $input['user_nickname'] : lang('user.label'));
			$body = '<p>'.lang('admin.emailVcodeTip', $user, $action).'</p>';
			$body .= '<p style="color: #4f9fcf;font-size:24px;">'.$_SESSION['vcode'].'</p>';
			$body .= '<p>'.lang('admin.emailIgnoreTip').'</p>';
			\mail::to($input['user_email'])
				->subject('['.lang('admin.vcode').']'.config('site.name'))
				->send($body);
			$params = session_get_cookie_params();
			setcookie(session_name(), session_id(),  $_SESSION['time']+60*30, $params['path']); //重写客户端 Cookie
			return success(lang('admin.vcodeSendedTip'));
		}else{
			return error(lang('user.invalidEmailFormat'));
		}
	}

	static function setTimezone($input){
		if(config('mod.installed')) return error(lang('mod.installed'));
		config('mod.timezone', $input['timezone']);
		export(config(), __ROOT__.'user/config/config.php');
		return success(lang('mod.updated'));
	}
}