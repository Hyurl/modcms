<?php
/** PHP 扩展函数 */
/**
 * camelcase2underline() 将使用驼峰法命名的字符串转为下划线命名
 * @param  string $str 驼峰命名字符串
 * @return string      下划线命名字符串
 */
function camelcase2underline($str){
	return strtolower(preg_replace('/((?<=[a-z])(?=[A-Z]))/', '_', $str));
}

/**
 * underline2camelcase() 将使用下划线命名的字符串转换为驼峰法命名
 * @param  string   $str     下划线命名字符串
 * @param  boolean  $ucfirst 首字母大写
 * @return string            驼峰命名字符串
 */
function underline2camelcase($str, $ucfirst = false){
	$str = preg_replace_callback('/_([a-zA-Z0-9])/', function($match){
		return strtoupper(ltrim($match[0], '_'));
	}, $str);
	return $ucfirst ? ucfirst($str) : $str;
}

/** 
 * str2bin() 将普通字符串转换为二进制字符串
 * @param  string $str 字符串
 * @return string      二进制数
 */
function str2bin($str){
	$arr = preg_split('/(?<!^)(?!$)/u', $str);
	foreach($arr as &$v){
		$v = unpack('H*', $v);
		$v = base_convert($v[1], 16, 2);
	}
	return join(' ', $arr);
}

/**
 * bin2str() 将二进制字符串转换为普通字符串
 * @param  string $str 二进制数
 * @return string      字符串
 */
function bin2str($str){
	$arr = explode(' ', $str);
	foreach($arr as &$v){
		$v = base_convert($v, 2, 16);
		if(strlen($v) % 2) $v = '0'.$v; //补齐长度
		$v = pack('H*', $v);
	}
	return join('', $arr);
}

/**
 * md5_crypt() 生成一个随机的 MD5 哈希密文
 * @param  string $str 字符串
 * @return string      哈希值
 */
function md5_crypt($str){
	return crypt($str, '$1$'.rand_str(8).'$');
}

/** 
 * hash_verify() 验证一个哈希密文是否与字符串相等
 * @param  string $hash 哈希值
 * @param  string $str  字符串
 * @return bool
 */
function hash_verify($hash, $str){
	return $hash == crypt($str, $hash);
}

/**
 * get_uploaded_files() 获取上传文件的数组，与 $_FILES 不同，当同一个键名包含多个文件时，这个键的值是一个索引数组，数组下面是一个包含文件信息的关联数组
 * @param  string $key  设置只获取指定键(一维键名)的文件信息，如不设置，则返回所有上传文件的信息
 * @return array        包含所有上传文件的数组, 如果没有上传的文件，或者设置获取的键没有文件，则返回空数组
 */
function get_uploaded_files($key = '') {
	$files = $_FILES;
	foreach ($files as &$file) {
		if(is_array($file)){
			$_files = array();
			foreach ($file as $prop => $val) {
				if(is_array($val)){
					array_walk_recursive($val, function(&$item) use ($prop) {
						$item = array($prop => $item); //$item 是遍历 $val 时产生的值
					}, $file);
					$_files = array_replace_recursive($_files, $val);
				}else{
					$_files[$prop] = $val;
				}
			}
			$file = $_files;
		}
	}
	return $key ? (@$files[$key] ?: false) : $files;
}

/**
 * array_xmerge() 递归、深层增量地合并数组
 * @param  array $array 待合并的数组
 * @return array        合并后的数组
 */
function array_xmerge(array $array){
	switch(func_num_args()){
		case 1: return $array; break;
		case 2:
			$args = func_get_args();
			$args[2] = array();
			if(is_array($args[0]) && is_array($args[1])){
				foreach(array_unique(array_merge(array_keys($args[0]),array_keys($args[1]))) as $k){
					if(isset($args[0][$k]) && isset($args[1][$k]) && is_array($args[0][$k]) && is_array($args[1][$k]))
						$args[2][$k] = array_xmerge($args[0][$k], $args[1][$k]);
					elseif(isset($args[0][$k]) && isset($args[1][$k]))
						$args[2][$k] = $args[1][$k];
					elseif(isset($args[0][$k]) || !isset($args[1][$k]))
						$args[2][$k] = $args[0][$k];
					elseif(!isset($args[0][$k]) || isset($args[1][$k]))
						$args[2][$k] = $args[1][$k];
				}
				return $args[2];
			}else{
				return $args[1]; break;
			}
		default:
			$args = func_get_args();
			$args[1] = array_xmerge($args[0], $args[1]);
			array_shift($args);
			return call_user_func_array('array_xmerge', $args); //递归并将 $args 作为多个参数转入
			break;
	}
}

/**
 * xscandir() 递归扫描目录结构
 * @param  string   $dir     起始目录
 * @param  integer  $sort    排序，0 升序，1 降序
 * @return array             目录树，如果提供的 $dir 不是一个目录名，则返回 false
 */
function xscandir($dir, $sort = 0){
	if(!is_dir($dir)) return false;
	$tree = array();
	foreach(scandir($dir, $sort) as $file){
		if(is_dir("$dir/$file") && $file != '.' && $file != '..'){
			$tree[$file] = xscandir("$dir/$file"); //扫描子目录
		}else{
			$tree[] = $file;
		}
	}
	return $tree;
}

/**
 * xrmdir() 强制删除目录，无论目录是否为空
 * @param  string $dir 目录名称
 * @return bool
 */
function xrmdir($dir){
	if(!is_dir($dir)) return false;
	$files = array_diff(scandir($dir), array('.', '..'));
	foreach($files as $file){
		//删除目录下的文件，如果是文件夹，则递归地删除
		$bool = is_dir("$dir/$file") ? xrmdir("$dir/$file") : unlink("$dir/$file");
		if(!$bool) return false;
	}
	return rmdir($dir);
}

/**
 * xcopy() 复制目录和它的文件
 * @param  string $src   源地址
 * @param  string $dst   目标地址
 * @param  bool   $cover 设置为 true 时，如果文件已存在，则进行覆盖；设置为 false 时，则跳过
 * @return bool
 */
function xcopy($src, $dst, $cover = false){
	if(!file_exists($src) || (is_file($dst) && !$cover)) return false;
	if(is_dir($src)){
		@mkdir($dst);
		$files = array_diff(scandir($src), array('.', '..'));
		foreach($files as $file){
			xcopy("$src/$file", "$dst/$file", $cover); //递归复制文件(夹)
		}
		return true;
	}
	return copy($src, $dst);
}

/**
 * xchmod() 尝试更改文件（夹）属性并应用到子文件（夹）
 * @param  string   $path 文件（夹）路径
 * @param  int(oct) $mode 属性模式
 * @return bool
 */
function xchmod($path, $mode){
	$ok = false;
	if(is_dir($path)){
		foreach(array2path(xscandir($path)) as $file){ //遍历目录下的所有文件
			if(strrpos($file, '..') != strlen($file)-2){
				$ok = chmod($path.'/'.$file, $mode);
			}
		}
	}elseif(file_exists($path)){
		$ok = chmod($path, $mode);
	}
	return $ok;
}

/**
 * array2path() 将数组结构化数据转换为路径
 * @param  array  $array 数组结构数据
 * @param  string $dir   设置父目录
 * @return array         结构数据路径
 */
function array2path(array $array, $dir = ""){
	$paths = array();
	if($dir && $dir[strlen($dir)-1] != '/') $dir .= '/';
	foreach ($array as $k => $v) {
		if(is_array($v)){
			$paths = array_merge($paths, array2path($v, $dir.$k));
		}else{
			$paths[] = str_replace(array('/', '\\\\'), DIRECTORY_SEPARATOR, $dir.$v); //使用系统的目录分隔符
		}
	}
	return $paths;
}

/**
 * zip_compress() 快速压缩文件（夹）为 ZIP
 * @param  string $path 文件（夹）路径
 * @param  string $file ZIP 文件名
 * @return bool
 */
function zip_compress($path, $file){
	$zip = new ZipArchive();
	$zip->open($file, ZipArchive::CREATE | ZipArchive::OVERWRITE); //以新建/重写模式打开 ZIP
	$ok = false;
	if(is_dir($path)){ //压缩文件夹
		foreach(array2path(xscandir($path)) as $file){
			$_file = $_file = $path.'/'.$file; //本地文件名
			if(is_file($_file)){
				$ok = $zip->addFile($_file, $file); //将文件添加到 ZIP 中
			}elseif(is_empty_dir($_file)){
				$ok = $zip->addEmptyDir(rtrim($file, '/\\.')); //将空文件夹添加到 ZIP 中
			}
		}
	}elseif(file_exists($path)){ //仅压缩文件
		$i = strrpos($path, '/');
		if($i !== false) $i++;
		$ok = $zip->addFile($path, substr($path, $i));
	}
	$zip->close();
	return $ok;
}

/**
 * zip_extract() 解压 ZIP 到指定目录
 * @param  string $file ZIP 文件名
 * @param  string $path 解压路径
 * @return bool
 */
function zip_extract($file, $path){
	$zip = new ZipArchive();
	$ok = $zip->open($file) ? @$zip->extractTo($path) : false;
	$zip->close();
	return $ok;
}

/**
 * rand_str() 获取随机字符串
 * @param  integer $len   字符串长度
 * @param  string  $chars 可能出现的字符串
 * @return string         随机的字符串
 */
function rand_str($len = 4, $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'){
	for($i=0, $str="",$end=strlen($chars)-1; $i<$len; $i++){
		$str .= $chars[rand(0, $end)];
	}
	return $str;
}

/**
 * escape_tags() 转义字符串中的 HTML 标签
 * @param  string $str  待转义的字符串
 * @param  string $tags 需转义的标签
 * @return string       转义后的字符串
 */
function escape_tags($str, $tags){
	$tags = explode('><', str_replace(' ', '', $tags));
	foreach ($tags as $tag) {
		$tag = trim($tag, '< >');
		$re1 = array('/<'.$tag.'([\s\S]*)>([\s\S]*)<\/'.$tag.'[\s\S]*>/Ui', '/<'.$tag.'([\s\S]*)>/Ui');
		$re2 = array('&lt;'.$tag.'$1&gt;$2&lt;/'.$tag.'&gt;', '&lt;'.$tag.'$1&gt;');
		$str = preg_replace($re1, $re2, $str); //将 < 和 > 分别转换为 &lt; 和 &gt;
	}
	return $str;
}

/**
 * export() 输出变量的原始信息
 * @param  mixed  $var  变量名
 * @param  string $path 输出到文件
 * @return mixed        如果输出到文件，则返回写出字符长度，否则返回 null
 */
function export($var, $path = ''){
	$str = var_export($var, true);
	if($path){
		return file_put_contents($path, "<?php\nreturn ".$str.';'); //将代码输出到文件
	}elseif(is_browser() && !is_ajax()){
		$str = trim(highlight_string("<?php\n".$str, true)); //高亮代码
		$_str = ($var === null || is_int($var) || is_bool($var) || is_object($var) || is_resource($var)) ? '&lt;?php<br />' : '<span style="color: #0000BB">&lt;?php<br /></span>';
		echo strstr($str, $_str, true).substr($str, strpos($str, $_str)+strlen($_str))."<br/>";
	}else{
		echo $str."\n";
	}
}

/**
 * function_alias() 创建函数别名
 * @param  string  $original 原函数名
 * @param  string  $alias    函数别名
 * @return boolean           如果创建别名成功则返回 true, 否则返回 false
 */
function function_alias($original, $alias){
	if(!function_exists($original) || function_exists($alias)) return false;
	eval('function '.$alias.'(){return call_user_func_array("'.$original.'", func_get_args());}');
	return true;
}

/**
 * unicode_encode() 将字符串进行 unicode 编码
 * @param  string $str 字符串
 * @return string      编码后的字符串
 */
function unicode_encode($str){
	$str = unpack('H*', mb_convert_encoding($str, 'UCS-2BE', 'UTF-8'));
	return '\u'.implode('\u', str_split($str[1], 4));
}

/**
 * unicode_decode() 解码 unicode 字符串
 * @param  string $str 加密的 unicode 字符串
 * @return string 	   解码后的字符串
 */
function unicode_decode($str){
	return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', function($matches){
		return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");
	}, $str);
}

/**
 * is_assoc() 判断一个变量是否为完全关联数组
 * @param  array   $input 待判断的变量
 * @return boolean
 */
function is_assoc($input) {
	if(!is_array($input) || !$input) return false;
	return array_keys($input) !== range(0, count($input) - 1);
}

/**
 * mb_str_split() 将字符串分割为数组
 * @param  string $str     待分割的字符串
 * @param  int    $len     每个数组元素的长度
 * @param  string $charset 编码
 * @return array           分割成的数组
 */
function mb_str_split($str, $len = 1, $charset = 'UTF-8') {
	$start = 0;
	$strlen = mb_strlen($str);
	while ($strlen) {
		$array[] = mb_substr($str, $start, $len, $charset);
		$str = mb_substr($str, $len, $strlen, $charset);
		$strlen = mb_strlen($str);
	}
	return $array;
}

/**
 * implode_assoc() 将关联数组合并为字符串
 * @param  array  $assoc 待合并的关联数组
 * @param  string $sep   分割符
 * @param  string $sep2  第二分割符
 * @return string        合并后的字符串
 */
function implode_assoc($assoc, $sep, $sep2){
	$arr = array();
	foreach ($assoc as $key => $value) {
		$arr[] = $key.$sep.$value;
	}
	return implode($sep2, $arr);
}

/**
 * explode_assoc() 将字符串分割为关联数组
 * @param  string $str  待分割的字符串
 * @param  string $sep  分割符
 * @param  string $sep2 第二分割符
 * @return array        分割后的关联数组
 */
function explode_assoc($str, $sep, $sep2){
	if(!$str) return array();
	$arr = explode($sep, $str);
	$assoc = array();
	foreach ($arr as $value) {
		$i = strpos($value, $sep2);
		$k = substr($value, 0, $i);
		$v = substr($value, $i+1) ?: '';
		$assoc[$k] = $v;
	}
	return $assoc;
}

/**
 * is_empty_dir() 判断目录是否为空
 * @param  string  $dir 名录名
 * @return boolean
 */
function is_empty_dir($dir){
	return is_dir($dir) && count(scandir($dir)) <= 2;
}

/**
 * is_img() 判断文件是否为图片
 * @param  string  $src    文件源
 * @param  boolean $strict 严格模式
 * @return boolean
 */
function is_img($src, $strict = false){
	if(!$strict || !function_exists('finfo_open'))
		return in_array(pathinfo($src, PATHINFO_EXTENSION), array('jpg','jpeg','png','gif','bmp'));
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	return strpos(finfo_file($finfo, $src), 'image/') === 0; //比较 MimeType
}
function_alias('is_img', 'is_image');

/**
 * is_agent() 判断当前是否为客户端请求
 * @param  string  $agent 客户端类型
 * @return boolean
 */
function is_agent($agent = ''){
	if(!isset($_SERVER['HTTP_HOST'])) return false;
	if($agent === true || $agent === 1) return !empty($_SERVER['HTTP_USER_AGENT']);
	return $agent ? stripos(@$_SERVER['HTTP_USER_AGENT'], $agent) !== false : true;
}

/**
 * is_browser() 判断当前是否为浏览器访问
 * @param  string  $agent 客户端类型
 * @return boolean
 */
function is_browser($agent = ''){
	return isset($_SERVER['HTTP_USER_AGENT']) && is_agent($agent) && !is_curl() && !empty($_SERVER['HTTP_ACCEPT']) && !empty($_SERVER['HTTP_CONNECTION']) && !strcasecmp($_SERVER['HTTP_CONNECTION'], 'keep-alive');
}

/**
 * is_mobile() 判断当前是否为手机浏览器访问
 * @param  string  $agent 客户端类型
 * @return boolean
 */
function is_mobile($agent = ''){
	if(!is_browser()) return false;
	if(!$agent) return preg_match('/Android|BlackBerry|BB|PlayBook|iPhone|iPad|iPod|Windows\sPhone|IEMobile/i', $_SERVER['HTTP_USER_AGENT']);
	return is_browser($agent);
}

/**
 * is_ajax() 判断当前是否为 AJAX 请求
 * @return boolean
 */
function is_ajax(){
	if(is_agent(true) && !is_curl() && !empty($_SERVER['HTTP_CONNECTION'])){
		if(!strcasecmp(@$_SERVER["HTTP_X_REQUESTED_WITH"], 'XMLHttpRequest')) return true; //jQuery
		if(empty($_SERVER['CONTENT_TYPE'])){
			return @$_SERVER['HTTP_ACCEPT'] == '*/*' || !empty($_SERVER['HTTP_ORIGIN']);
		}else{
			return @$_SERVER['HTTP_ACCEPT'] == '*/*';
		}
	}
	return false;
}

/**
 * is_curl() 判断当前是否为 CURL 请求
 * @return boolean
 */
function is_curl(){
	return is_agent() && !empty($_SERVER['HTTP_ACCEPT']) && (empty($_SERVER['HTTP_USER_AGENT']) || stripos($_SERVER['HTTP_USER_AGENT'], 'curl/') === 0 || empty($_SERVER['HTTP_CONNECTION']));
}

/**
 * is_post() 判断当前是否为 POST 请求
 * @return boolean
 */
function is_post(){
	return is_agent() && $_SERVER['REQUEST_METHOD'] == 'POST';
}

/**
 * is_get() 判断当前是否为 GET 请求
 * @return boolean 
 */
function is_get(){
	return is_agent() && $_SERVER['REQUEST_METHOD'] == 'GET';
}

/**
 * is_ssl() 判断当前请求是否使用 SSL 协议
 * @return boolean
 */
function is_ssl() {
	return isset($_SERVER['HTTPS']) && !strcasecmp($_SERVER['HTTPS'], 'on');
}

/**
 * redirect() 设置网页重定向
 * @param  string|int  $url  重定向 URL，特殊值 0(当前页)，-1(上一页)
 * @param  integer     $code 状态号 301 或 302
 * @param  integer     $time 等待时间
 * @param  string      $msg  跳转提示
 * @return null
 */
function redirect($url, $code = 302, $time = 0, $msg = ''){
	if(!is_agent()) return;
	if(ob_get_length()) ob_end_clean(); //清空缓冲区
	if(!$url) $url = url();
	elseif($url == -1) $url = @$_SERVER['HTTP_REFERER'] ?: url();	
	if($code == 301) header('HTTP/1.1 301 Moved Permanently'); //永久重定向
	else header('HTTP/1.1 302 Moved Temporarily');
	if(!headers_sent()){
		header($time ? "Refresh: $time; URL=$url" : "Location: $url"); //使用 HTTP 响应头进行跳转
	}else{
		echo "<meta http-equiv=\"Refresh\" content=\"$time; URL=$url\">\n"; //使用 HTML 元信息跳转
	}
	exit($msg);
}

/**
 * set_query_string() 设置 URL 查询字符串，并重新加载页面
 * @param  string|array $key   参数名，也可设置为关联数组同时设置多个参数
 * @param  string       $value 参数值
 */
function set_query_string($key, $value = ''){
	if(!is_array($key)) $key = array($key => $value);
	foreach ($key as $k => $v) {
		if($v === null){
			unset($_GET[$k]); //删除参数
		}else{
			if($v === false) $v = 'false'; //将 false 当作 'false'
			$_GET[$k] = $v; //设置参数
		}
	}
	$url = explode('?', url());
	redirect($url[0].'?'.http_build_query($_GET));
}

/**
 * set_content_type() 设置文档类型和编码
 * @param string $type     文档类型
 * @param string $encoding 编码
 */
function set_content_type($type, $encoding = 'UTF-8'){
	if(!headers_sent()){
		header("Content-Type: $type; charset=$encoding"); //在响应头中设置
	}else{
		echo "<meta http-equiv=\"content-type\" content=\"$type; charset=$encoding\">\n"; //在元信息中设置
	}
}

/**
 * url() 获取当前 URL 地址(不包括 # 及后面的的内容)
 * @return string URL 地址
 */
function url(){
	if(!is_agent()) return false;
	$protocol = strstr(strtolower($_SERVER['SERVER_PROTOCOL']), '/', true).(is_ssl() ? 's' : '');
	return $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
}

/**
 * get_client_ip() 获取客户端 IP 地址
 * @param  boolean $strict 严格模式，排除 192.168 等私有 IP 地址
 * @return string          客户端 IP 地址
 */
function get_client_ip($strict = false){
	$ip = false;
	if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){ //从代理地址中获取 IP
		$ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
		$ip = $ips[0];
	}else if(isset($_SERVER['HTTP_CLIENT_IP'])){
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	}else if(isset($_SERVER['REMOTE_ADDR'])){
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	if($ip && $strict){
		$ip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
	}
	return $ip;
}
function_alias('get_client_ip', 'get_agent_ip');

/**
 * rand_ip() 获取一个随机的 IP 地址
 * @return string IP 地址
 */
function rand_ip(){
	$ip = rand(1, 255).'.'.rand(0, 255).'.'.rand(0, 255).'.'.rand(0, 255);
	return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) ?: rand_ip();
}

/**
 * cn_rand_ip() 获取一个随机的中国大陆 IP 地址
 * @return string IP 地址
 */
function cn_rand_ip(){
	$long = array(
		array(607649792, 608174079),
		array(1038614528, 1039007743),
		array(1783627776, 1784676351),
		array(2035023872, 2035154943),
		array(2078801920, 2079064063),
		array(-1950089216, -1948778497),
		array(-1425539072, -1425014785),
		array(-1236271104, -1235419137),
		array(-770113536, -768606209),
		array(-569376768, -564133889),
		);
	$i = mt_rand(0, 9);
	return long2ip(mt_rand($long[$i][0], $long[$i][1]));
}

/**
 * array2xml() 将数组转换为 XML 结构数据
 * @param  array   $array    数组
 * @param  string  $root     根标签
 * @param  string  $encoding 文档编码
 * @return string            XML 文档
 */
function array2xml(array $array, $root = '<xml>', $encoding = 'UTF-8'){
	$head = '<?xml version="1.0" encoding="'.$encoding.'"?><'.trim($root, '<>').'/>';
	$xml = simplexml_load_string($head); //创建 XML 对象
	$createXML = function($xml, $arr) use (&$createXML){ //use 匿名函数自身的引用，就可以在内部进行递归运算
		foreach($arr as $k => $v) {
			if(is_array($v)) {
				$xml = $xml->addChild($k);
				$createXML($xml, $v); //递归
			}else{
				$xml->addChild($k, $v); //添加子节点
			}
		}
	};
	$createXML($xml, $array);
	return html_entity_decode($xml->saveXML(), null, $encoding);
}

/**
 * curl() 进行远程 HTTP 请求，需要开启 CURL 模块
 * @param  array|string   $options 设置请求的参数或者请求的 URL 地址
 * @return string                  返回请求结果，结果是字符串
 */
function curl($options = array()){
	$curl = curl_version();
	/* 定义默认的参数 */
	$defaults = array(
		'url'=>'', //远程请求地址
		'method'=>'GET', //请求方式: POST 或 GET;
		'data'=>'', //POST 数据, 支持关联数组、索引数组、URL 查询字符串以及原生 POST 数据, 要发送文件，需要在文件名前面加上@前缀并使用完整路径
		'cookie'=>'', //发送 Cookie, 支持关联数组、索引数组和 Cookie 字符串
		'referer'=>'', //来路页面
		'userAgent'=>'curl/'.$curl['version'], //客户端信息
		'requestHeaders'=>array(), //请求头部信息，支持索引数组和关联数组
		'followLocation'=>0, //跟随跳转，设置数值为最大跳转次数
		'autoReferer'=>true, //跳转时自动设置来路页面
		'sslVerify'=>false, //SSL 安全验证
		'proxy'=>'', //代理服务器(格式: 8.8.8.8:80)
		'clientIp'=>'', //客户端 IP
		'timeout'=>10, //设置超时;
		'onlyIpv4'=>true, //只解析 IPv4
		'username'=>'', //HTTP 访问认证用户名;
		'password'=>'', //HTTP 访问认证密码;
		'charset'=>'', //目标页面编码
		'convert'=>'', //转换为指定的编码
		'parseJSON'=>'', //解析 JSON
		'success'=>null, //请求成功时的回调函数
		'error'=>null, //请求失败时的回调函数
		'extra'=>array() //其他 CURL 选项参数
		);
	if(is_string($options)) $options = array('url'=>$options);
	$options = array_merge($defaults, $options);
	extract($options);
	$ch = curl_init();
	if($data && is_array($data) && !is_assoc($data)){
		$data = explode_assoc(implode('&', $data), '&', '=');
	}
	if(strtolower($method) == 'post'){ //POST 请求
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	}else{ //GET 请求
		if($data) $url .= (strpos($url, '?') ? '&' : '?').(is_array($data) ? http_build_query($data) : $data);
	}
	if($cookie && is_array($cookie)){ //组合 Cookie
		$cookie = is_assoc($cookie) ? implode_assoc($cookie, '=', '; ') : implode('; ', $cookie);
	}
	if(is_assoc($requestHeaders)) { //组合请求头
		foreach ($requestHeaders as $key => $value) {
			$requestHeaders[] = "$key: $value";
			unset($requestHeaders[$key]);
		}
	}
	if($clientIp){
		$requestHeaders[] = 'Cleint-IP: '.$clientIp; //设置客户端 IP
		if($proxy) //标记使用代理
			$requestHeaders[] = 'X-Forwarded-For: '.$clientIp.', '.strstr($proxy, ':', true);
	}
	//设置请求参数
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, true);
	curl_setopt($ch, CURLINFO_HEADER_OUT, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followLocation ? true : false);
	curl_setopt($ch, CURLOPT_MAXREDIRS, $followLocation);
	curl_setopt($ch, CURLOPT_AUTOREFERER, $autoReferer);
	curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	if($sslVerify) curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 2);
	else curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	if($cookie) curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	if($referer) curl_setopt($ch, CURLOPT_REFERER, $referer);
	if($userAgent) curl_setopt($ch, CURLOPT_USERAGENT, $userAgent);
	if($proxy) curl_setopt($ch, CURLOPT_PROXY, $proxy);
	if($onlyIpv4) curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
	if($requestHeaders) curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
	if($username) curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);
	if($extra) curl_setopt_array($ch, $extra);
	$data = curl_exec($ch); //执行请求并获取返回值
	$curlInfo = curl_getinfo($ch); //获取请求信息
	$curlInfo['error'] = '';
	if(isset($curlInfo['request_header'])){
		$curlInfo['request_headers'] = parse_header($curlInfo['request_header']); //解析请求头
		unset($curlInfo['request_header']);
	}
	if(curl_errno($ch)){
		$curlInfo['error'] = curl_error($ch);
		goto returnError;
	}
	curl_close($ch);
	if(!$charset){ //自动获取目标页面使用的编码
		if(preg_match('/.*charset=(.+)/i', $curlInfo['content_type'], $match)){
			$charset = $match[1];
		}else{
			$_data = str_replace(array('\'', '"', '/'), '', $data);
			if(preg_match('/<meta.*charset=(.+)>/iU', $_data, $match)){
				$charset = strstr($match[1], ' ', true) ?: $match[1];
			}else if(preg_match('/<\?xml.*encoding=(.+)?>/iU', $_data, $match)){
				$charset = strstr($match[1], ' ', true) ?: $match[1];
			}else{
				$charset = 'UTF-8';
			}
		}
	}
	if(strtolower(str_replace('-', '', $charset)) != 'utf8')
		$data = iconv($charset, 'UTF-8', $data); //将数据转码为 UTF-8
	if($convert) $data = iconv('UTF-8', $convert, $data); //将数据转换为其他编码
	$curlInfo['response_headers'] = parse_header(substr($data, 0, $curlInfo['header_size'])); //解析响应头
	$data = unicode_decode(substr($data, $curlInfo['header_size'])); //进行 Unicode 解码
	if($curlInfo['http_code'] >= 400){ //400 以上的 HTTP 代码表示网页有错误
		$curlInfo['error'] = $data;
		goto returnError;
	}
	if($parseJSON || ($parseJSON === '' && stripos($curlInfo['content_type'], 'application/json') !== false))
		$data = json_decode($data, true); //解析 JSON
	curl_info($curlInfo);
	if(is_callable($success)){
		$_data = $success($data); //执行成功回调函数
		if($_data !== null) $data = $_data;
	}
	return $data;
	returnError: //处理并返回错误
	curl_info($curlInfo);
	if(is_callable($error)){
		curl_info('error', $error(curl_info('error'))); //执行失败回调函数并保存返回值
	}
	return curl_info('error');
}

/**
 * curl_info() 函数获取 CURL 请求的相关信息，需要运行在 curl() 函数之后
 * @param  string $key   设置键名，如果为关联数组，则填充返回值并将填充后的数组返回
 * @param  string $value 设置值
 * @return array|string  当未设置 $key 时返回所有数组内容
 *                       当设置 $key 并且 $key 是一个关联数组，则返回填充后的数组内容
 *                       当设置 $key 为字符串时，如果为设置 $value, 则返回 $key 对应的值
 *                       当设置 $key 为字符串并且设置 $value, 则始终返回 $vlaue;
 */
function curl_info($key = '', $value = null){
	static $info = array();
	if(is_assoc($key)) {
		return $info = array_merge($info, $key);
	}elseif($value !== null){
		return $info[$key] = $value;
	}
	return $key ? (@$info[$key] ?: false) : $info;
}

/**
 * curl_cookie_str() 获取 CURL 响应头中的 Cookie 字符串
 * @param  boolean $withSentCookie 返回值包含发送的 Cookie(如果有)
 * @return string                  返回所有的 Cookie 字符串
 */
function curl_cookie_str($withSentCookie = false){
	$cookie = '';
	$reqHr = curl_info('request_headers'); //请求头
	$resHr = curl_info('response_headers'); //响应头
	if(!empty($resHr['Set-Cookie'])){
		if(is_string($resHr['Set-Cookie'])) $resHr['Set-Cookie'] = array($resHr['Set-Cookie']);
		foreach ($resHr['Set-Cookie'] as $value) {
			$cookie .= strstr($value, ';', true).'; ';
		}
	}
	if($withSentCookie && !empty($reqHr['Cookie'])){
		$cookie .= $reqHr['Cookie']; //加上发送的 Cookie
	}
	return rtrim($cookie, '; ');
}

/**
 * parse_header() 解析头部信息为关联数组
 * @param  string $str 头部信息字符串
 * @return array       解析后的数组
 */
function parse_header($str){
	$headers = explode("\n", trim(str_replace(array("\r\n", "\r"), "\n", $str), "\n")); //兼容行尾
	$_headers = array();
	$__headers = array();
	foreach($headers as $header){
		if($i = strpos($header, ':')){
			$header = array(strstr($header, ':', true), trim(substr($header, $i+1)));
			if(array_key_exists($header[0], $_headers)){ //如果一个参数多次使用，如 Set-Cookie
				if(!is_array($_headers[$header[0]]))     //则将其保存为索引数组
					$_headers[$header[0]] = array($_headers[$header[0]]);
				$_headers[$header[0]][] = $header[1];
			}else{
				$_headers[$header[0]] = $header[1];
			}
		}else{
			$__headers[] = $header;
		}
	}
	return array_merge($__headers, $_headers);
}

/**
 * session_retrieve() 重现会话
 * @param  string $id Session ID
 * @return bool
 */
function session_retrieve($id){
	if(!file_exists(session_save_path().'/sess_'.$id)) return false;
	session_id($id);
	@session_start();
	return true;
}

if(!function_exists('session_status')):
define('PHP_SESSION_DISABLED', 0);
define('PHP_SESSION_NONE', 1);
define('PHP_SESSION_ACTIVE', 2);
/**
 * session_status() 返回当前会话状态，该函数自 PHP5.4 起为内置函数
 * @return int      PHP_SESSION_DISABLED 会话是被禁用的
 *                  PHP_SESSION_NONE 会话是启用的，但不存在当前会话
 *                  PHP_SESSION_ACTIVE 会话是启用的，而且存在当前会话
 */
function session_status(){
	if(!extension_loaded('session')){
		return 0;
	}elseif(!file_exists(session_save_path().'/sess_'.session_id())){
		return 1;
	}else{
		return 2;
	}
}
endif;

if(!function_exists('hex2bin')):
/**
 * hex2bin() 将十六进制字符串转换为 ASCII，该函数自 PHP5.4 起为内置函数
 * @param  string $hex 十六进制字符串
 * @return string      ASCII 字符串
 */
function hex2bin($hex){
	if(strlen($hex) % 2) return false;
	$bin = array_map(function($v){
		return chr(hexdec($v));
	}, str_split($hex, 2));
	return join('', $bin);
}
endif;

/** 
 * parse_cli_param() 解析 PHP 运行于 CLI 模式时传入的参数，支持的格式包括 --key value，-k value 以及 valve
 * @param  array  $argv 通常为 $_SERVER['agrv']
 * @return array        包含键值对 file=>当前运行文件, param=>(array)参数列表
 */
function parse_cli_param($argv = array(), $i = 0, $isArg = false, $args = array()){
	if(!$argv) return false;
	$_i = 1;
	if(!$args){
		$args = array(
			'file'=>$argv[0], //当前运行文件名
			'param'=>array() //参数列表
			);
		$argv = array_slice($argv, 1);
	}
	if(!$argv) return $args;
	if($argv[0][0] == '-'){
		if(isset($argv[0][1]) && $argv[0][1] != '-' && !isset($argv[0][2])){ //--key 长参数
			$key = ltrim($argv[0], '-');
		}elseif(isset($argv[0][1]) && $argv[0][1] == '-' && isset($argv[0][2]) && $argv[0][2] != '-'){ //-k 短参数
			$key = ltrim($argv[0], '-');
		}else{ //无键名参数
			$value = $argv[0];
		}
	}elseif($argv[0] != ';'){
		$value = $argv[0];
	}
	if(isset($key)){
		if(isset($argv[1]) && $argv[1][0] != '-' && $key[strlen($key)-1] != ';'){
			$value = $argv[1]; //命令参数值
			$_i = 2;
		}else $value = '';
	}
	if(!$isArg){
		$args['param'][$i] = array(
			'cmd'=>$value, //命令
			'args'=>array() //参数
			);
	}else{
		if(isset($key))
			$args['param'][$i]['args'][rtrim($key, ';')] = rtrim($value, ';');
		elseif($argv[0] != ';') //下一个命令开始
			$args['param'][$i]['args'][] = rtrim($value, ';');
	}
	if($argv[$_i-1][strlen($argv[$_i-1])-1] == ';'){ //多命令分句
		$i += 1;
		$isArg = false;
	}else{
		$isArg = true;
	}
	$argv = array_slice($argv, $_i);
	if($argv){
		return parse_cli_param($argv, $i, $isArg, $args); //递归计算
	}
	return $args;
}

/**
 * parse_cli_str() 解析命令行格式的字符串为数组
 * @param  string $str 输入字符串
 * @return array       解析后的字符串
 */
function parse_cli_str($str){
	if(preg_match_all('/(["].+["].*)[\s]|(.+)[\s]|([\'].+)\b[\']/U', $str." ", $matches)){
		return array_map(function($v){
			$v = trim($v);
			if(substr_count($v, '"') == 2){ //使用双引号包裹内容
				$i1 = strpos($v, '"');
				$i2 = strrpos($v, '"');
				$v = substr($v, 0, $i1).substr($v, $i1+1, $i2-1).substr($v, $i2+1);
			}
			return $v;
		}, $matches[0]);
	}
	return false;
}

/**
 * get_cmd_encoding() 获取 Windows 命令行编码
 * @return string 编码号
 */
function get_cmd_encoding(){
	return PHP_OS == 'WINNT' ? 'CP'.trim(strstr(`chcp`, ': '), ": \r\n") : '';
}

/**
 * object2array() 将对象转换为数组
 * @param  object $obj 对象
 * @return array       数组
 */
function object2array($obj){
	return json_decode(json_encode($obj), true);
}

/**
 * array2object() 将数组转换为对象
 * @param  array  $arr 数组
 * @return object      基本对象
 */
function array2object(array $arr){
	return json_decode(json_encode($arr));
}