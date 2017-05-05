<?php
require_once('mod/common/init.php');
/*
 * URL 请求使说明：
 * 可以通过 URL 携带参数访问 mod.php 文件直接访问模块类方法，通常在 AJAX 中使用。
 * 需要至少提供两个参数，obj 和 act，用来调用相应的对象(类)和操作(方法)，其他的参数将作为类方法的参数。
 * ModPHP 会自动收集向后台提交的数据，执行请求的操作并将结果返回给客户端。
 * 默认有三种 URL 格式可以提交请求，以获取 user_id = 1 的用户为例：
 * 	 1. mod.php?obj=user&act=get&user_id=1[&更多参数];
 * 	 2. mod.php?user::get|user_id:1[|更多参数]
 * 	 3. mod.php/user/get/user_id/1[/更多参数]
 */

/** 交互式控制台，兼容 shell 命令 */
if(__SCRIPT__ != 'mod.php' && !is_socket()) goto end;
$ENCODING = get_cmd_encoding(); //命令行编码，仅 Windows
$TITLE = 'ModPHP'; //窗口标题
$PROMPT = '$ '; //提示字符
$STDIN = null;
if(!isset($_SERVER['argv'][1]) || realpath($_SERVER['argv'][0]) != __FILE__){
	fwrite(STDOUT, 'ModPHP '.MOD_VERSION.' started at '.date('D M d H:i:s Y').PHP_EOL);
	do_hooks('console.open'); //在控制台打开前执行挂钩函数
	if(error()) exit(); //可以在挂钩函数中返回错误以终止程序
	fwrite(STDOUT, $PROMPT);
}
while(true){
	if(PHP_OS == 'WINNT') exec("title $TITLE");
	error(null);
	if((isset($_SERVER['argv'][1]) && realpath($_SERVER['argv'][0]) == __FILE__) || $STDIN = fgets(STDIN)){
		if($STDIN){ //交互式控制台
			$STDIN = trim($STDIN);
			$argv = parse_cli_param(parse_cli_str('"'.$_SERVER['argv'][0].'" '.$STDIN));
		}else{ //命令行直接调用
			$argv = parse_cli_param($_SERVER['argv']);
		}
		ob_start();
		if(!$argv['param']){
			fwrite(STDOUT, $PROMPT);
			continue;
		}
		foreach($argv['param'] as $PARAM){
			if(!strpos($PARAM['cmd'], '(') && (is_callable($PARAM['cmd']) || strpos($PARAM['cmd'], '::'))) {
				//将输入按 shell 命令来运行
				array_walk($PARAM['args'], function(&$v){ //转换参数
					if($v === 'true') $v = true;
					elseif($v === 'false') $v = false;
					elseif($v === 'undefined' || $v === 'null') $v = null;
					elseif(is_numeric($v) && (int)$v < 2147483647) $v = (int)$v;
				});
				if(is_assoc($PARAM['args'])){ //长参数
					print_r(call_user_func($PARAM['cmd'], $PARAM['args']));
				}elseif(is_array($PARAM['args'])){ //短参数
					print_r(call_user_func_array($PARAM['cmd'], $PARAM['args']));
				}
			}elseif($STDIN !== null){ //变量或者其他
				eval($STDIN && $STDIN[strlen($STDIN)-1] == ';' ? $STDIN : $STDIN.';');
				${'BREAK'.__TIME__} = true;
			}else{ //命令行直接调用
				print_r(eval('return '.rtrim($PARAM['cmd'], ';').';'));
			}
			${'STDOUT'.__TIME__} = trim(ob_get_clean(), PHP_EOL); //获取输出缓存
			if($ENCODING && strcasecmp($ENCODING, 'UTF-8')) //转换编码
				${'STDOUT'.__TIME__} = iconv('UTF-8', $ENCODING, ${'STDOUT'.__TIME__}) ?: ${'STDOUT'.__TIME__};
			if($STDIN === null){ //命令行直接调用
				echo ${'STDOUT'.__TIME__}.($PARAM != end($argv['param']) ? PHP_EOL : ''); //输出代命令行
			}else{ //交互式控制台
				fwrite(STDOUT, $STDIN && ${'STDOUT'.__TIME__} ? ${'STDOUT'.__TIME__}.PHP_EOL : ''); //输出到交互式控制台
				if(!empty(${'BREAK'.__TIME__})){
					unset(${'BREAK'.__TIME__});
					break;
				}
			}
			unset($php_errormsg, ${'STDOUT'.__TIME__});
		}
		if($STDIN === null) break;
		else fwrite(STDOUT, $PROMPT);
	}
}
end: