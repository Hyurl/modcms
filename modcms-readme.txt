ModCMS 说明：

后台模板制作说明：
	1. ModCMS 后台目录为 __ROOT__/app/admin/，使用通用的外部框架，通过 iframe 加载并显示具体页面。
	2. 模板页面使用编译型模板，后缀名默认为 .html；
	   模板中应在 <head> 标签的开始位置引入 head.html 文件，而在 <body> 标签的末尾引入 foot.html，其中包含了文档元信息和公共的 css 和 js 等。
	3. 模板中可以使用预先定义好的变量、常量和函数等，
	   用户也可以在 __ROOT__/app/admin/php/ 目录下创建与模板文件名相同(后缀为 .php)的文件来设置自己所需要的其他变量、常量和函数，他们将是局部的，仅在模板中有效。
	   另外，模板中所需要的 css 和 js 文件也可以按照此种方式自动引入，它们的目录分别是 __ROOT__/app/admin/css/ 和 __ROOT__/app/admin/js/。
	4. 页面可以使用下面的方法来更改外部框架显示的内容：
		(1) $.showHelp(content) 显示帮助信息，content 设置为一段纯文本或者 HTML 代码。
		(2) $.showSearch(content) 显示搜索，content 可以设置为：
			① URL 地址，提交表单将加载设置的页面，并添加查询字符串 keyword=[搜索值]；
			② JS 回调函数，触发设置的回调函数。
		(3) $.showMenu(content) 显示底部菜单，content 为一个 JS 对象，对象的属性名为菜单显示名，值为功能，按钮个数为属性个数。
			属性值可以设置为下面这些类型：
			① URL 地址，点击按钮时在 iframe 中加载设置的页面；
			② JS 回调函数，点击按钮时触发回调函数；
			③ JS 对象，设置二级菜单，二级菜单项目的设置和功能同上。
		(4) $.showNews(menuId, count, warning) 在侧边导航列表上显示新消息
			menuId 菜单 id（即 5 中的项目）；
			count 数目（可使用 '-1', '+1' 的方式设置相对值）；
			warning 设置是否使用警告颜色。
	5. 页面可以通过为 <body> 标签设置 data-menu 属性，当其加载时，外部框架对应的菜单项会被高亮，默认可用的值为：
		(1) home 主页
		(2) site-home 网站首页
		(3) users 用户
		(4) categories 分类目录
		(5) posts 文章
		(6) comments 评论
		(7) files 文件
		(8) modules 其他模块
		(9) features 外观
		(10) plugins 插件
		(11) settings 设置
	6. 后台页面应通过查询字符串来获取请求参数，并按照请求显示相应的内容，例如 http://localhost/admin/users.html?profile=me，users.html 页面应当显示当前登录用户的个人中心页面。
	7. 可以通过修改 __ROOT__/user/config/ 目录下的配置文件 menus.php 和 privileges.php 来设置显示菜单和可访问的页面。

前台模板制作说明：
	1. 前台使用多模板，统一存放到 __ROOT__/app/template/ 目录下，例如默认模板的路径应该是 __ROOT__/app/template/default/。
	2. 各模板目录下需要创建一个 info.json 文件，用来标识模板并描述相关信息，JSON 中支持下面这些信息：
		name: 模板名称
		thumbnail: 缩略图
		author: 作者
		url: 链接
		version: 版本号
		desc: 简介，可以在简介中使用标记 {SITE_URL}，{ADMIN_URL} 来标记网站地址和后台地址，通常用来链接模板设置页面
		versionURL: 检测新版本的链接，链接应返回一条 json 数据来描述最新版本的信息，json 中应包含如下信息：
			version: 版本号
			src: ZIP 压缩包源地址
			md5: 压缩包的 MD5 值
			size: 压缩包大小（KB，可选）
			url: 更新说明链接（可选）

插件制作说明：
	1. 插件统一存放到 __ROOT__/app/plugins/ 目录下，每一个插件存放在各自的目录下，目录下的 php 文件会在系统初始化过程中自动载入。
	2. 各插件目录下需要创建一个 info.json 文件，用来标识插件并描述相关信息，JSON 中支持下面这些信息：
		name: 插件名称
		logo: 标志图片
		author: 作者
		url: 链接
		version: 版本号
		desc: 简介，可以在简介中使用标记 {SITE_URL}，{ADMIN_URL}，{PLUGIN_URL} 来标记网站地址、后台地址和当前插件地址，通常用来链接插件设置页面
		versionURL: 检测新版本的链接，链接应返回一条 json 数据来描述最新版本的信息，json 中应包含如下信息：
			version: 版本号
			src: ZIP 压缩包源地址
			md5: 压缩包的 MD5 值
			size: 压缩包大小（KB，可选）
			url: 更新说明链接（可选）

全局请求参数：
	除了数据表字段外，ModCMS 还在模板或插件开发者可能接触到的场景中使用了下面这些参数，
	如果你在模板或插件中需要实现并完成相应的功能，根据你的设置情况，应向程序入口文件 mod.php 提交一个或多个这些参数：
	1. vcode: 验证码，图形的或邮件的，使用场景：匿名评论验证、注册用户、找回密码等
	2. old_password: 旧的用户密码，使用场景：修改密码
	3. client_ip: 客户端地址，使用场景：前台评论
	4. action: 中文操作名称，使用场景：发送邮件验证码，说明为何发送邮件
	5. user_nickname: 用户昵称，使用场景：发送邮件
	6. user_email: 用户邮箱，使用场景：发送邮件

全局函数：
	除了 ModPHP 自带的函数，ModCMS 还添加了下面这些函数来更方便使用者实现相应的功能：
	1. get_post_desc() 获取文章简介，支持一个数字参数用来设置获取的长度
	2. get_post_link() 获取文章的链接
	3. get_post_date() 获得格式化的文章发表日期
	4. get_comment_desc() 获取评论简介，支持一个数字参数用来设置获取的长度
	5. get_comment_data() 获得格式化的评论发表日期
	6. admin_dir() 获取后台管理中心的服务器地址
	7. admin_url() 获取后台管理中心的 URL 地址
	8. plugin_dir() 获取插件根目录的服务器地址
	9. get_user_avatar() 获取用户头像，支持一个数字参数用来设置获取的长度，以及第二个参数设置文件源地址
	10. pagination() 获取分页按钮组，基于 Bootsrap 样式
	11. cms_socket_server_mode() 判断是否开启了 Socket 服务器

全局常量：
	除了 ModPHP 自带的常量，ModCMS 还增加了下面这些常量：
	1. CMS_VERSION ModCMS 版本号
	2. SITE_URL 网站 URL 地址
	3. ADMIN_URL 后台管理中心 URL 地址
	4. ADMIN_DIR 后台管理中心服务器地址
	5. IS_LOGINED 是否已登录
	6. IS_ADMIN 是否为管理员
	7. IS_EDITOR 是否为编辑
	8. IS_AUTH 是否为管理员或编辑
	9. ME_ID 当前用户 ID
	10. __LANG__ 使用的语言

后台 JS 变量、常量、函数：
	为了更方便使用，ModCMS 在后台定义了下面这些变量、常量和函数：
	1. $_GET 包含查询字符串数组，和 PHP 的 $_GET 相同
	2. CMS_VERSION ModCMS 版本号
	3. SITE_URL 网站 URL 地址
	4. ADMIN_URL 后台管理中心 URL 地址
	5. IS_LOGINED 是否已登录
	6. IS_ADMIN 是否为管理员
	7. IS_EDITOR 是否为编辑
	8. IS_AUTH 是否为管理员或编辑
	9. ME_ID 当前用户 ID
	10. create_url() 创建 URL 地址，和 PHP 的 create_url() 相同

/**
 * 注：除了上述说明外，ModCMS 还定义并使用了其他常量和函数，它们被保留而不能被用在前台模板和插件中
 *     如果在制作模板和插件时遇到命名冲突问题，你应当将其改为其他名称。
 *     你可以自己查看相关的文件来获得更多定义的功能，并将它们应用在你的项目中。   
 */