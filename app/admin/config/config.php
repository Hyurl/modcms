<?php
/** 
 * ModCMS 常规配置，与 __ROOT__/user/config/config.php 文件合并
 */
return array (
  'mod' => 
  array (
    'installed' => false,
    'language' => 'zh-CN',
    'timezone' => 'Asia/Shanghai',
    'dateFormat' => 'Y-m-d H:i', //日期格式
    'escapeTags' => '<script><style><iframe>',
    'pathinfoMode' => false,
    'database' => 
    array (
      'host' => 'localhost',
      'name' => 'modphp',
      'port' => 3306,
      'username' => 'root',
      'password' => '',
      'prefix' => 'mod_',
    ),
    'session' => 
    array (
      'name' => '',
      'maxLifeTime' => 10080,
      'savePath' => 'tmp/',
    ),
    'template' => 
    array (
      'appPath'=>'app/',
      'savePath' => 'template/simplet/',
      'compiler' => 
      array (
        'enable' => 1,
        'extraTags' => 
        array (
          0 => 'import',
          1 => 'redirect',
        ),
        'savePath' => 'tmp/',
        'stripComment' => false,
      ),
    ),
    'SocketServer' => 
    array (
      'port' => 8080,
    ),
    'plugins' => 
    array (
      'savePath' => 'app/plugins/',
      'enable' => '',
    ),
    'mail' => 
    array (
      'host' => '',
      'port' => 25,
      'from' => '',
      'username' => '',
      'password' => '',
    ),
    'versionURL'=>array(
      'modcms' => 'http://modphp.hyurl.com/modcms/version',
      'modphp' => 'http://modphp.hyurl.com/version'
      ),
  ),
  'site' => 
  array (
    'name' => '另一个 ModCMS 网站',
    'URL' => '',
    'subname' => '',
    'author' => 'Ayon Lee',
    'generator' => 'ModPHP',
    'keywords' => '',
    'description' => '',
    'ICP' => '',
    'logo' => '',
    'home' => 
    array (
      'template' => 'index.html',
      'staticURI' => 'page/{page}.html',
    ),
    'errorPage' => 
    array (
      403 => '403.html',
      404 => '404.html',
      500 => '500.html',
    ),
    'maintenance' => 
    array (
      'pages' => '',
      'exception' => 'is_admin()',
      'report' => 'report_500()',
    ),
    'admin' => 
    array (
      'listCount' => 20,
      'listSequence' => 'desc',
      'autoCheckUpdate' => true,
    ),
  ),
  'user' => 
  array (
    'template' => 'profile.html',
    'staticURI' => 'profile/{user_id}.html',
    'keys' => 
    array (
      'login' => 'user_name|user_email',
      'require' => 'user_name|user_password|user_level',
      'filter' => 'user_level',
      'serialize' => 'user_protect',
    ),
    'name' => 
    array (
      'minLength' => 3,
      'maxLength' => 30,
    ),
    'password' => 
    array (
      'minLength' => 5,
      'maxLength' => 18,
      'recoverEmail' => false,
    ),
    'level' => 
    array (
      'admin' => 5,
      'editor' => 4,
    ),
    'register' => 
    array (
      'enable' => true,
      'verify' => 'vcode',
    ),
    'login' => 
    array (
      'verify' => 'vcode',
      'showAvatar'=>false,
    ),
  ),
  'file' => 
  array (
    'keys' => 
    array (
      'require' => 'file_name|file_src',
      'filter' => 'file_src',
    ),
    'upload' => 
    array (
      'savePath' => 'upload/',
      'acceptTypes' => 'jpg|jpeg|png|gif|bmp|zip',
      'maxSize' => 1024*2,
      'imageSizes' => '64|96|128',
    ),
  ),
  'category' => 
  array (
    'template' => 'category.html',
    'staticURI' => '{category_name}/page/{page}.html',
    'keys' => 
    array (
      'require' => 'category_name',
      'filter' => 'category_name',
    ),
  ),
  'post' => 
  array (
    'template' => 'single.html',
    'staticURI' => '{category_name}/{post_id}.html',
    'keys' => 
    array (
      'require' => 'post_title|post_content|post_time|category_id|user_id',
      'filter' => 'post_time|user_id',
      'search' => 'post_title|post_content',
    ),
    'baiduSEO' => 
    array (
      'site' => '',
      'token' => '',
    ),
  ),
  'comment' => 
  array (
    'keys' => 
    array (
      'require' => 'comment_content|comment_time|post_id|user_id',
      'filter' => 'comment_time|post_id|*comment_parent',
    ),
    'enable' => true,
    'notify' => false,
    'review' => true,
    'anonymous' => 
    array (
      'enable' => false,
      'verify' => false,
      'require' => 'user_nickname|user_email',
    ),
  ),
  'blacklist' => 
  array (
  ),
  'link' => 
  array (
  	'keys' => 
  	array (
  	  'require' => 'link_title|link_url',
  	),
  ),
);