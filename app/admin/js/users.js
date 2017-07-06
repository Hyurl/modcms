$(function(){
	if($_GET['profile'] == 'me'){
		$_GET['user_id'] = ME_ID;
	}

	var userListTbody = $('#user-list-table tbody'),
		EditorMenu = {}, //改变用户组页面菜单
		ListMenu = {};
	EditorMenu[Lang.giveup] = function(){
		history.back(-1);
	};
	EditorMenu[Lang.submit] = function(){
		$('#user-edit-form').find(':submit').click();
	};
	ListMenu[Lang.prevPage] = function(){
		$_GET['page'] -= 1;
		getUserList();
	};
	ListMenu[Lang.nextPage] = function(){
		$_GET['page'] += 1
		getUserList();
	};
	ListMenu[Lang.edit] = function(){
		var id = userListTbody.find('.active').eq(0).data('id');
		location.search = '?action=update&user_id='+id;
	};
	ListMenu[Lang.delete] = function(){
		userListTbody.editMulti($.extend({
			obj: 'user',
			act: 'delete',
			callback: function(){
				getUserList(true);
			}
		}, Lang));
	};
	var showListMenu = function(){ //显示列表菜单
		var menu = {},
			length = userListTbody.find('.active').length;
		if(PAGES > 1){
			if($_GET['page'] == 1){
				menu[Lang.nextPage] = ListMenu[Lang.nextPage];
			}else if($_GET['page'] == PAGES){
				menu[Lang.prevPage] = ListMenu[Lang.prevPage];
			}else{
				menu[Lang.prevPage] = ListMenu[Lang.prevPage];
				menu[Lang.nextPage] = ListMenu[Lang.nextPage];
			}
		}
		if(menu[Lang.prevPage] && menu[Lang.nextPage]){
			menu[Lang.change] = {};
			if(length == 1){
				menu[Lang.change][Lang.edit] = ListMenu[Lang.edit];
			}
			if(length) menu[Lang.change][Lang.delete] = ListMenu[Lang.delete];
		}else{
			if(length == 1){
				menu[Lang.edit] = ListMenu[Lang.edit];
			}
			if(length) menu[Lang.delete] = ListMenu[Lang.delete];
		}
		$.showMenu($.isEmptyObject(menu) ? null : menu);
	},
	getUserList = function(noAlert){ //获取用户列表数据
		var submitSuccess = function(result){
			userListTbody.html('');
			if(result.success){
				var data = result.data,
					id = 1 + ($_GET['page'] - 1) * $_GET['limit'],
					gender = {male: Lang.male, female: Lang.female};
				for(var i in data){
					if(data[i].user_id == ME_ID){
						var title = Lang.viewMyUserCenter;
					}else if(data[i].user_gender == 'female'){
						var title = Lang.viewHerUserCenter;
					}else{
						var title = Lang.viewHisUserCenter;
					}
					userListTbody.append('<tr data-id="'+data[i].user_id+'"><td>'+id+'</td><td><a title="'+title+'" href="users.html?user_id='+data[i].user_id+'">'+data[i].user_name+'</a></td><td>'+data[i].user_nickname+'</td><td>'+(gender[data[i].user_gender] || '')+'</td><td>'+data[i].user_email+'</td></tr>');
					id++;
				}
				TOTAL = result.total;
				PAGES = result.pages;
				showListMenu();
			}else if(!noAlert){
				alert(result.data);
			}
		};
		if(WS.source){
			WS.bind('user.getMulti', submitSuccess);
			WS.send($.extend({obj: 'user', act: 'getMulti'}, $_GET));
		}else{
			$.ajax({
				url: SITE_URL+'mod.php?obj=user&act=getMulti&'+$.param($_GET), 
				success: submitSuccess,
				error: function(xhr){
					console.log(xhr);
					alert(Lang.serverConnectionError);
				}
			});
		}
	};

	/** 显示菜单 */
	if($_GET['action']){
		$('select, :checkbox, :radio').change(function(){
			$.showMenu(EditorMenu);
		});
		$('input, textarea').on('input', function(){
			$.showMenu(EditorMenu);
		});
		$('#user_name').focus();
	}else if(!$_GET['user_id']){
		showListMenu();
	}

	/** 点击列表项时对其进行高亮 */
	userListTbody.on('click', 'td', function(){
		$(this).closest('tr').toggleClass('active');
		showListMenu();
	});

	/** 点击列表选项卡时获取指定级别的用户 */
	$('#user-list-tabs a[href="javascript:;"]').click(function(event){
		event.preventDefault();
		$(this).parent('li').addClass('active').siblings('li').removeClass('active');
		if($(this).data('level')){
			$_GET['user_level'] = $(this).data('level');
		}else{
			delete $_GET['user_level'];
		}
		getUserList(true);
	});

	/** 点击改变用户组页面选项卡时切换页面 */
	$('#user-edit-tabs a').click(function(){
		var $this = $(this),
			id = $this.data('id');
		$(this).parent('li').addClass('active').siblings('li').removeClass('active');
		$('#'+id).siblings('div, table').hide();
		$('#'+id).fadeIn('fast');
	});

	/** 点击选择头像按钮时预览头像 */
	$('#select-avatar').click(function(){
		$('#jquery-upload-form').find(':file').click();
	});

	/** 切换密码框 type 属性 */
	$('#old_password, #user_password').focus(function(){
		if(!$(this).val()) $(this).attr('type', 'password');
	});

	$('#user-edit-tabs a[data-id="set-protection"]').click(function(){
		$('#set-protection').find('input[type="text"], input[type="password"]').eq(0).focus();
	});

	$('#user-edit-tabs a[data-id="set-basic"]').click(function(){
		$('#user_name').focus();
	});

	/** 保存用户信息 */
	$('#user-edit-form').ajaxSubmit({
		beforeSend: function(options){
			if($('#user-avatar').attr('src') != $('#user-avatar').data('origin')){
				$('#jquery-upload-form').submit();
				return false;
			}
			if(WS.source){
				WS.bind(['user.add', 'user.update'], options.success);
				WS.send($.extend({obj: 'user', act: $_GET['action']}, options.data));
				return false;
			}
		},
		success: function(result){
			if(result.success){
				alert($_GET['action'] == 'update' ? Lang.updatedTip : Lang.addedTip);
				history.back(-1);
			}else{
				alert(result.data);
			}
		},
		error: function(xhr){
			console.log(xhr);
			alert(Lang.serverConnectionError);
		}
	});

	/** 上传头像 */
	$('#jquery-upload-form').ajaxSubmit({
		success: function(result){
			var form = $('#user-edit-form');
			if(result.success){
				$('#user-avatar').attr('src', result.data[0].file_src).data('origin', result.data[0].file_src);
				form.find('input[name="user_avatar"]').val(result.data[0].file_src);
				form.find(':submit').click(); //提交表单
			}else{
				$('#user-edit-tabs a[data-id="set-avatar"]').click();
				if(typeof result.data == 'string'){
					alert(result.data);
				}else{
					alert(result.data[0].error);
				}
			}
		},
		error: function(xhr){
			alert(Lang.serverConnectionError);
			console.log(xhr.responseText);
		}
	}).find(':file').previewImage('#user-avatar', function(){
		$('#user-avatar').show();
		$.showMenu(EditorMenu);
	});
});