if(typeof sessionStorage === 'undefined' && !cookie('noBrowserWarning')){
	alert(Lang.notHTML5Warning);
	cookie('noBrowserWarning', true);
}

/** $_GET 和 PHP 的 $_GET 变量相同 */
$_GET = location.search.parseStr();

/** _$ 父窗口 jQuery */
_$ = _window.$;

/** _lang 语言包 */
_lang = {};

/** WebSocket 连接 */
if(!_window.WS){
	WS = {
		source: null,
		bind: function(api, func){
			var hook = {};
			if(typeof api == 'string') api = [api];
			for(var i in api){
				hook[api[i]] = func;
			}
			this.hooks = $.extend({}, this.hooks, hook);
			return this.hooks;
		},
		trigger: function(api, input){
			var hooks = this.bind();
			if(typeof hooks[api] == 'function'){
				hooks[api](input);
			}
		},
		start: function(descriptor){
			this.source = new WebSocket(descriptor);
			this.source.onopen = function(event){
				console.log('WebSocket 连接已建立。');
				WS.trigger('WebSocket.open', event);
			};
			this.source.onclose = function(event){
				console.log('WebSocket 连接已关闭。');
				WS.trigger('WebSocket.close', event);
				WS.source = null;
			};
			this.source.onerror = function(event){
				console.log('WebSocket 连接出现问题。');
				WS.trigger('WebSocket.error', event);
			};
			this.source.onmessage = function(event){
				var result = JSON.parse(event.data);
				if(result.obj && result.act){
					var api = result.obj+'.'+result.act;
					WS.trigger(api, result);
				}
				WS.trigger('WebSocket.message', event);
			}
		},
		send: function(data){
			data['HTTP_REFERER'] = _window.location.href;
			return this.source && this.source.readyState === 1 ? this.source.send(JSON.stringify(data)) : false;
		},
		close: function(code, reason){
			this.source.close(code, reason);
		}
	};
}else{
	WS = _window.WS;
}

/** 
 * $.showHelp() 显示帮助信息
 * @param  {string} content 帮助信息内容，支持纯文本和 HTML
 * @return null
 */
$.showHelp = function(content){
	if(!content){
		_$('#help-info').children('section').html('');
		_$('#help').hide();
		return;
	}
	_$('#help-info').children('section').html(content);
	_$('#help').show();
}

/**
 * $.showSearch() 显示搜索
 * @param  {mixed} data 搜索页面 URL 或一个回调函数
 * @return null
 */
$.showSearch = function(data){
	if(!data){
		_$('#search').hide();
		_$('#help-info>.prismatic').css('right', '55px');
		return;
	}
	var search = _$('#search form'),
		searchkey = _$('#search-keyword');
	if(typeof data == 'string'){
		search.attr('action', data).submit(function(event){
			event.preventDefault();
			location.href = data+(data.indexOf('?') >= 0 ? '&' : '?')+'keyword='+searchkey.val();
		});
	}else if(typeof data == 'function'){
		search.submit(function(){
			data();
		});
	}
	_$('#search').show();
	_$('#help-info>.prismatic').css('right', '95px');
}

/**
 * $.showMenu() 显示菜单
 * @param  {object} menu 菜单项目，支持二维对象，对象的属性是菜单显示名称，值可以设置为一个 URL 地址或者回调函数
 * @return null
 */
$.showMenu = function(menu){
	if(!menu || $.isEmptyObject(menu)){
		_$('#footer-menu').fadeOut('fast', function(){
			_$('#container').css('bottom', 0);
		});
		return false;
	}
	var nav = _$('#footer-menu ul.footbar-nav'),
		count = _count = 0;
	nav.html('');
	for(var i in menu){
		count++;
		if(typeof menu[i] == 'string'){
			nav.append('<li><a id="footer-menu-'+count+'" href="'+menu[i]+'" title="'+i.replace(/<.*?>/g,"")+'">'+i+'</a></li>');
			_$('#footer-menu-'+count).off('click').click(function(event){
				event.preventDefault();
				location.href = _$(this).attr('href');
			});
		}else if(typeof menu[i] == 'function'){
			nav.append('<li><a id="footer-menu-'+count+'" href="javascript:;" title="'+i.replace(/<.*?>/g,"")+'">'+i+'</a></li>');
			var _menu = _$('#footer-menu-'+count);
			_menu.data('callback', menu[i]);
			_menu.off('click').click(function(e){
				_$(this).data('callback')();
			});
		}else if(typeof menu[i] == 'object'){
			nav.append('<li class="li-with-submenu"><a id="footer-menu-'+count+'" href="javascript:;" title="'+i.replace(/<.*?>/g,"")+'">'+i+'</a></li>');
			var _menu = _$('#footer-menu-'+count);
			_menu.off('click').click(function(){
				_$(this).siblings('nav').fadeToggle('fast').closest('li').siblings('li').children('nav').fadeOut('fast');
			}).closest('li').append('<nav><ul class="list-group"></ul><span class="prismatic"></span></nav>');
			for(var j in menu[i]){
				_count++;
				if(typeof menu[i][j] == 'string'){
					_menu.siblings('nav').children('ul').append('<li class="list-group-item"><a id="footer-submenu-'+_count+'" href="'+menu[i][j]+'" title="'+j.replace(/<.*?>/g,"")+'">'+j+'</a></li>');
					_$('#footer-submenu-'+_count).off('click').click(function(e){
						e.preventDefault();
						_$('.li-with-submenu>nav').fadeOut('fast');
						location.href = _$(this).attr('href');
					});
				}else if(typeof menu[i][j] == 'function'){
					_menu.siblings('nav').children('ul').append('<li class="list-group-item"><a id="footer-submenu-'+_count+'" href="javascript:;" title="'+j.replace(/<.*?>/g,"")+'">'+j+'</a></li>');
					var __menu = _$('#footer-submenu-'+_count);
					__menu.data('callback', menu[i][j]);
					__menu.off('click').click(function(){
						_$('.li-with-submenu>nav').fadeOut('fast');
						_$(this).data('callback')();
					});
				}
			}
		}
	}
	nav.children('li').width((100 / count) + '%');
	_$('#footer-menu').fadeIn('fast', function(){
		_$('#container').css('bottom', '40px');
	});
};

/**
 * $.showNews() 显示新的消息
 * @param  {string} target  目标 id，即侧边导航列表项 id
 * @param  {int}    count   消息数目
 * @param  {bool}   warning 使用警告颜色
 * @return {[type]}         [description]
 */
$.showNews = function(target, count, warning){
	var target = _$('#'+target),
		badge = target.next('.badge'),
		_count = badge.length ? parseInt(badge.text()) : 0;
	if(typeof count == 'string' && (count[0] == '+' || count[0] == '-')){
		count = parseInt(count);
		count += _count;
	}
	badge.remove();
	if(count > 0){
		target.after('<span class="badge'+(warning ? ' warning' : '')+'">'+count+'</span>');
	}
};

/**
 * editMulti() 编辑多项目，需要为操作项目添加 .active 类名，并且该项目有一个 data-id 属性标记它的数据库记录 id
 * @param  {object} options 选项参数
 * @return {[type]}         [description]
 */
$.fn.editMulti = function(options){
	var defaults = {
		obj: '', //请求对象
		act: '', //请求操作
		data: {}, //提交的数据
		callback: null, //回调函数, 如果将 options.once 设置为 false, 那么 回掉函数可以携带一个参数，它将被填充为请求结果的 data 数据，并且可以在内部使用 this 选择器
		once: true, //回调函数仅运行一次，即最后运行
		noItemWarning: '请选择需要操作的项目',
		deleteWarning: '即将永久删除所选项目，确认？',
		successTip: '项目操作成功',
		failWarning: '项目操作失败',
		successCountTip: '<successCount> 项操作成功。',
		failCountWarning: '<successCount> 项操作成功，<failCount> 项失败。',
		errorWarning: '与服务器的连接出现错误。',
	}
	options = $.extend(defaults, typeof options == 'object' ? options : {});
	var target = $(this).find('.active'),
		count = i = 0;
	if(target.length < 1){
		alert(options.noItemWarning);
		return false;
	}
	if(options.act == 'delete' && !confirm(options.deleteWarning)) return false;
	target.each(function(){
		var $this = $(this);
		options.data[options.obj+'_id'] = $this.data('id');
		$.post(SITE_URL+'mod.php?'+options.obj+'::'+options.act, options.data, function(result){
			if(result.success){
				if(options.act == 'delete'){
					$this.remove();
				}
				if(typeof options.callback == 'function' && !options.once){
					options.callback.call($this, result.data);
				}
				count++;
			}
			i++;
			if(i == target.length){
				var failed = i-count, tip;
				if(failed){
					if(target.length > 1){
						tip = options.failCountWarning.replace('<successCount>', count).replace('<failCount>', failed);
					}else{
						tip = options.failWarning;
					}
				}else{
					if(i > 1){
						tip = options.successCountTip.replace('<successCount>', count);
					}else{
						tip = options.successTip;
					}
				}
				alert(tip);
				if(typeof options.callback == 'function'){
					if(options.once) options.callback.call();
				}else{
					location.reload();
				}
			}
		});
	});
}

/**
 * create_url() 创建 URL 地址，用法同 php 函数 create_url()
 * @param  {[type]} format [description]
 * @param  {[type]} data   [description]
 * @return {[type]}        [description]
 */
function create_url(format, data){
	for(var x in data){
		if(typeof data[x] != 'object' && format.match(/\{(.*)\}/)){
			format = format.replace('{'+x+'}', data[x]);
		}
	}
	return SITE_URL+format;
}

/** 关闭菜单 */
$.showHelp(null);
$.showSearch(null);
$.showMenu(null);

$(function(){
	/** 自动高亮选项卡 */
	var tablist = $('ul[role="tablist"]');
	if($_GET['tablist'] && tablist.length){
		var target = tablist.find('li[data="'+$_GET['tablist']+'"]');
		if(target.length){
			target.addClass('active').siblings('li').removeClass('active');
		}
	}
	if(tablist.find('li.active').length == 0){
		tablist.children('li').eq(0).addClass('active');
	}

	/** 移除禁用按钮的点击事件 */
	$(document).on('click', '.disabled', function(event){
		event.preventDefault();
		event.stopPropagation();
	});

	/** 眼睛按钮事件 */
	$(document).on('click', 'input[type=text]+.glyphicon, input[type=password]+.glyphicon', function(){
		if(!navigator.userAgent.match('Edge')){
			if($(this).is('.glyphicon-eye-open')){
				$(this).removeClass('glyphicon-eye-open').addClass('glyphicon-eye-close').prev('input').attr('type', 'text').focus();
			}else{
				$(this).removeClass('glyphicon-eye-close').addClass('glyphicon-eye-open').prev('input').attr('type', 'password').focus();
			}
		}
	});
	$(document).on('input', 'input[type=text], input[type=password]', function(event){
		if(!navigator.userAgent.match('Edge')){
			if($(this).val()){
				$(this).next('.glyphicon').css('display', 'inline');
			}else{
				$(this).next('.glyphicon').css('display', 'none');
			}
		}
	});

	/** 展开/收起二级菜单 */
	$(document).on('click', '.has-submenu>.glyphicon', function(event){
		var $this = $(this);
		$this.parent('li').next('ul').slideToggle('fast');
		if($this.is('.glyphicon-chevron-down')){
			$this.removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
		}else{
			$this.removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
		}
	});
	$(document).on('click', '.has-submenu>a', function(event){
		if(!$(this).attr('href')){
			$(this).siblings('.glyphicon').click();
		}
	});
	if($(window).width() > 640){
		$('.has-submenu>.glyphicon').click();
	}

});