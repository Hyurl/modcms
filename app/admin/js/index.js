$(function(){
	/** 定义全局变量 */
	min_width = 640; //小屏幕设备的最大屏幕宽度
	timer = 10; //定时器(单位 秒)
	container = $('#container'); //主体容器
	sidebar = $('#aside-menu'); //侧边菜单栏
	copyright = $('#copyright'); //版权标识
	backdrop = $('#backdrop'); //遮罩
	frame = $('#main-frame'); //ifrmae 框架
	search = $('#search>div'); //搜索弹框
	searchform = $(search.find('form')[0]); //搜索表单
	searchkey = $('#search-keyword'); //搜索输入框
	userinfo = $('#userinfo'); //用户信息弹框
	helpinfo = $('#help-info'); //帮助信息弹框
	_window = window.frames['main-frame']; //框架的 window 变量

	/** 设置 iframe 的加载行为 */
	frame.load(function(event){
		try{
			var $_document = frame.contents(),
				title = $_document.find('title').text() || Lang.dashboard,
				menu = $_document.find('body').data('menu'),
				icon = '<i class="glyphicon glyphicon-dashboard"></i>';
			$('title').text(title);
			$('#dashboard').html(icon+title).attr('title', title);
			/** 菜单高亮 */
			if(menu && $('#'+menu).closest('.sidebar').length > 0){
				sidebar.find('li').removeClass('active');
				$('#'+menu).closest('li').addClass('active');
			}
			storage('ModAdminURL', _window.location.href);
			$_document.on('click', function(){
				/** 点击 iframe 时关闭搜索、用户信息和帮助弹框 */
				$.searchHide();
				$.userinfoHide();
				helpinfo.slideUp('fast');
				$('.li-with-submenu>nav').fadeOut('fast');
			}).find('body').css('background-color', '#eee').find('a').each(function(){
				var href = $(this).attr('href'),
					target = $(this).attr('target');
				if(!target && (href.indexOf('http://') === 0 || href.indexOf('https://') === 0) && href.indexOf(ADMIN_URL) !== 0 && href.indexOf(PLUGIN_DIR_URL) !== 0 && href.indexOf(SITE_URL) !== 0) {
					$(this).attr('target', '_blank');
				}
			});
			if(_window.location.href.indexOf(ADMIN_URL) !== 0){
				/** 关闭菜单 */
				$.showHelp(null);
				$.showSearch(null);
				$.showMenu(null);
			}
			/** 检查数据库更新 */
			checkUpdate();
			/** 连接 WebSocket */
			if(WebSocketMode && !WS.source && typeof WebSocket != 'undefined'){
				WS.start((location.protocol == 'https:' ? 'wss' : 'ws')+'://'+location.hostname+':'+WebSocketPort+location.pathname+location.search);
			}
		}catch(error){
			frame.attr('src', storage('ModAdminURL'));
		}
	});

	/** $.searchHide() 隐藏搜索弹框 */
	$.searchHide = function(){
		searchkey.blur();
		search.animate({width: 0, right: '40px'}, 'fast', function(){
			search.hide();
		});
	};

	/** $.userinfoHide() 隐藏用户信息弹框 */
	$.userinfoHide = function(){
		userinfo.animate({width: 0, height: 0}, 'fast', function(){
			userinfo.hide();
		});
	};

	/** $.sidebarHide() 隐藏侧边栏 */
	$.sidebarHide = function(){
		sidebar.animate({width: 0}, 'fast');
		copyright.animate({width: 0}, 'fast');
		backdrop.fadeOut('fast');
	};

	/** $.setBottomMenuWidth() 设置底部菜单按钮的宽度为平均值 */
	$.setBottomMenuWidth = function(){
		$('#footer-menu .footbar-nav>li').width((1 / $('#footer-menu .footbar-nav>li').length)*100 + '%');
	}
	$.setBottomMenuWidth();

	$(document).on('click', function(e){
		/** 关闭弹框、侧栏等 */
		var target = $(e.target);
		if(target.closest('#aside-menu').length == 0 && target.closest('#dashboard').length == 0 && $(window).width() <= min_width){
			$.sidebarHide();
		}
		if(target.closest('#search').length == 0){
			$.searchHide();
		}
		if(target.closest('#avatar').length == 0){
			$.userinfoHide();
		}
		if(target.closest('#help-info').length == 0 && target.closest('#btn-help').length == 0){
			helpinfo.slideUp('fast');
		}
		if(target.closest('.li-with-submenu').length == 0){
			$('.li-with-submenu>nav').fadeOut('fast');
		}
	}).on('click', 'a', function(e){
		/** 在 iframe 中打开超链接 */
		var $this = $(this);
		var target = $this.attr('target');
		if($this.attr('id') != 'user-logout' && $this.attr('id') != 'site-home' && $this.closest('#footer-menu').length < 1 && (!target || target == '_self')){
			e.preventDefault();
			var url = $this.attr('href'),
				_url = url.indexOf('://') > 0 ? url : ADMIN_URL + url;
			if($(window).width() <= min_width){
				$.sidebarHide();
			}
			if(url && (url != frame.attr('src') || _window.location.href != _url) && url.indexOf('javascript:') !== 0 && url.indexOf('#') !== 0 && url.indexOf('mailto:') !== 0 && url.indexOf('tel:') !== 0 && url.indexOf('sms:') !== 0){
				$.userinfoHide();
				$('.sidebar').find('li').removeClass('active');
				frame.attr('src', url);
			}
		}
	});

	/** 设置仪表盘按钮的行为 */
	$('#dashboard').click(function(){
		var width = sidebar.width();
		if(width == 0){
			sidebar.animate({width: '200px'}, 'fast');
			copyright.animate({width: '200px'}, 'fast');
			if($(window).width() > min_width){
				container.animate({left: '200px'}, 'fast');
				$('.footbar').animate({marginLeft: '200px'}, 'fast');
			}else{
				backdrop.fadeIn('fast');
			}
		}else{
			$.sidebarHide();
			container.animate({left: 0}, 'fast');
			$('.footbar').animate({marginLeft: 0}, 'fast');
		}
	});

	/** 设置窗口大小被改变时的行为 */
	$(window).resize(function(){
		if($(window).width() > min_width){
			sidebar.animate({width: '200px'}, 'fast');
			copyright.animate({width: '200px'}, 'fast');
			container.animate({left: '200px'}, 'fast');
			helpinfo.animate({left: '200px'}, 'fast');
			$('.footbar').animate({marginLeft: '200px'}, 'fast');
			backdrop.fadeOut('fast');
		}else{
			if(backdrop.css('display') == 'none'){
				sidebar.animate({width: 0}, 'fast');
				copyright.animate({width: 0}, 'fast');
			}
			container.animate({left: 0}, 'fast');
			helpinfo.animate({left: 0}, 'fast');
			$('.footbar').animate({marginLeft: 0}, 'fast');
		}
	});

	/** 设置搜索按钮的行为 */
	$('#btn-search').click(function(){
		if(search.css('display') == 'none'){
			var input = search.find('input[type="text"]');
			search.show(0, function(){
				search.animate({width: '200px', right: '43px'}, 'fast', 'swing', function(){
					searchkey.focus();
				});
			})
		}else{
			if(searchkey.val()){
				searchform.submit();
			}else{
				$.searchHide();
			}
		}
	});

	/** 设置头像按钮的行为 */
	$('#btn-avatar').click(function(){
		if(userinfo.css('display') == 'none'){
			userinfo.show(0, function(){
				var height = userinfo.find('nav').height();
				userinfo.animate({width: '200px', height: height}, 'fast');
			});
		}else{
			$.userinfoHide();
		}
	});

	/** 设置帮助按钮的行为 */
	$('#btn-help').click(function(){
		helpinfo.slideToggle('fast');
	});

	/** 点击登出按钮登出用户 */
	$('#user-logout').click(function(){
		$.getJSON(SITE_URL+'mod.php?user::logout', submitSuccess = function(result){
			alert(result.data);
			if(result.success){
				location.href = ADMIN_URL+'login.html';
			}
		});
	});

	/** 根据 URL 地址加载 iframe 框架内容 */
	if(storage('ModAdminURL')){
		frame.attr('src', storage('ModAdminURL'));
	}else{
		if($('#home').length >0){
			$('#home').click();
		}else{
			$('#user-center>a').click();
		}
	}

	WS.bind('comment.getUnreviewedCount', function(result){
		$.showNews('comments', result.success ? result.data : null);
	});
	WS.bind('WebSocket.open', function(){
		WS.send({obj: 'comment', act: 'getUnreviewedCount'});
	});
	getUnreviewedCount = function(){
		if(WS.source){
			WS.send({obj: 'comment', act: 'getUnreviewedCount'});
		}else{
			$.get(SITE_URL+'mod.php?comment::getUnreviewedCount', function(result){
				WS.trigger('comment.getUnreviewedCount', result);
			});
		}
	}

	/** 定时检测是否有未审核评论 */
	if(IS_AUTH && timer){
		e = setInterval(getUnreviewedCount, timer*1000);
		getUnreviewedCount();
	}

	/** 评论菜单右边小标记点击事件 */
	$(document).on('click', '#comments+.badge', function(){
		_window.location.href = ADMIN_URL+'comments.html?comment_status=0&tablist=unreviewed';
	});

	/** 检测是否存在更新 */
	checkUpdate = function(){
		if(IS_ADMIN && autoCheckUpdate){
			/** 初始化 */
			storage('newMODVersion', null);
			storage('newCMSVersion', null);
			storage('newTemplateVersion', null);
			storage('newPluginVersion', null);
			/** 检查数据库更新 */
			$.get(SITE_URL+'mod.php?mod::checkDbUpdate', function(result){
				if(result.success){
					$.showNews('settings', '+1');
				}
			});
			/** 检查 CMS/ModPHP/模板/插件更新 */
			versionURLs = $.extend(versionURLs, VERSION_URLS);
			var versionCounts = {};
			for(var key in versionURLs){
				if(typeof versionURLs[key] == 'string'){
					var version = key == 'modcms' ? CMS_VERSION : MOD_VERSION;
					versionURLs[key] = [{url: versionURLs[key], version: version}];
				}
				versionCounts[key] = 0;
				for(var i in versionURLs[key]){
					if(!versionURLs[key][i]['url']) continue;
					$.get(SITE_URL+'mod.php?obj=mod&act=checkUpdate&type='+key+'&version='+versionURLs[key][i]['version']+'&versionURL='+encodeURIComponent(versionURLs[key][i]['url']), function(result){
						var key = result.type;
						if(key == 'modphp') key = 'modcms';
						if(result.success && version_compare(result.data.version, result.version) > 0){
							versionCounts[key] += 1;
							if(key == 'template'){
								$.showNews('features', versionCounts[key]);
								storage('newTemplateVersion', versionCounts[key]);
							}else if(key == 'plugin'){
								$.showNews('plugins', versionCounts[key]);
								storage('newPluginVersion', versionCounts[key]);
							}else{
								$.showNews('settings', versionCounts[key]);
								storage(result.type == 'modcms' ? 'newCMSVersion' : 'newMODVersion', versionCounts[key]);
							}
						}
					});
				}
			}
			autoCheckUpdate = false;
		}
	}
});