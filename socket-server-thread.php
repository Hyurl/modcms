<?php
if(!class_exists('Thread')){
	exit("PHP does not support multi-threading yet.\n");
}
include 'mod/classes/socket-server.class.php'; //引入 SocketServer 扩展
/** 监听端口 */
$server = SocketServer::listen(@$_SERVER['argv'][1] ?: 8080, function($server, $port){
	fwrite(STDOUT, "SocketServer $server started on $port at ".date('m-d-Y H:i:s').".\n");
}, false); //将第三个参数($autoStart)设置为 false
/** 创建线程类 */
class SocketServerThread extends Thread{
	/** 将服务器资源传入线程作用域 */
	function __construct($server){
		$this->server = $server;
	}
	function run(){
		SocketServer::server($this->server); //设置服务器
		include 'socket-server.php'; //引入 SocketServer 服务
		SocketServer::start(); //开启服务
	}
}
$threads = array(); //线程组
/** 创建若干个线程并加入线程组 */
for ($i=0; $i < (@$_SERVER['argv'][2] ?: 5); $i++) {
	$threads[$i] = new SocketServerThread($server);
	$threads[$i]->start();
}
/** 引入交互式控制台，可以监控线程组 */
include 'mod.php';