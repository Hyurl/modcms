<?php
namespace extensions;

use \database as database;

class category{
	static function rollId($arg){
		if(empty($arg['id1']) || empty($arg['id2']))
			return error(lang('mod.permissionDenied'));
		$prefix = database::open(0)->set('prefix');
		$id1 = (int)$arg['id1'];
		$id2 = (int)$arg['id2'];
		$type = $id1 < $id2 ? 'asc' : 'desc'; //asc 向前滚，desc 向后滚
		database::query("begin"); //开启事务
		$sql1 = "select max(`category_id`) as max_id from `{$prefix}category`";
		$ok = ($result = database::query($sql1)) !== false;
		if($ok){
			$nextId = $result->fetchObject()->max_id + 1;
			$sql2 = "update `{$prefix}category` set `category_id` = $nextId where `category_id` = $id1";
			$ok = database::query($sql2) !== false;
			if($ok){
				$_sql1 = "update `{$prefix}category` set `category_parent` = $nextId where `category_parent` = $id1";
				database::query($_sql1);
				$sql3 = "select `category_id` from `{$prefix}category` where `category_id` between ".($type === 'asc' ? ($id1+1)." and $id2" : "$id2 and ".($id1-1)) ." order by category_id $type";
				$result = database::query($sql3);
				while ($result && ($cat = $result->fetchObject()) && $ok) {
					$id = $cat->category_id;
					$sql4 = "update `{$prefix}category` set `category_id` = `category_id` ".($type === 'asc' ? '-' : '+')." 1 where `category_id` = $id";
					$ok = database::query($sql4) !== false;
					if($ok){
						$_sql1 = "update `{$prefix}category` set `category_parent` = `category_parent`".($type === 'asc' ? '-' : '+')." 1 where `category_parent` = $id";
						database::query($_sql1);
					}
				}
				if($ok){
					$sql5 = "update `{$prefix}category` set `category_id` = $id2 where `category_id` = $nextId";
					$ok = database::query($sql5) !== false;
					if($ok){
						$_sql1 = "update `{$prefix}category` set `category_parent` = $id2 where `category_parent` = $nextId";
						database::query($_sql1);
					}
				}
			}
		}
		if($ok){
			database::query('commit'); //提交修改
			return success(lang('category.rollIdSucceeded'));
		}else{
			database::query('rollback'); //取消修改
			return error(lang('category.rollIdFailed'));
		}
	}
}