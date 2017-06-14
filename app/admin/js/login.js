$(function(){
	/** 居中登录面板 */
	$('.panel-login').centralize().show(),
	$('input').eq(0).focus();
	$(window).resize(function(){
		$('.panel-login').centralize();
	});
	/** 提交登录表单 */
	$('form').ajaxSubmit({
		success:function(result){
			console.log(result);
			var action = $(this).attr('action');
			if(result.success){
				if(action.match(/user::add/)){
					alert(Lang.regSuccessTip);
					location.href = 'login.html';
				}else if(action.match(/user::mailRepass/) || action.match(/user::recoverPassword/)){
					alert(result.data);
					location.href = 'login.html';
				}else if(document.referrer != ADMIN_URL+'install.html'){
					location.href = document.referrer || ADMIN_URL;
				}else{
					location.href = ADMIN_URL;
				}
			}else{
				alert(result.data);
			}
		},
		beforeSend: function(){
			$(this).find('button[type="submit"]').button('loading');
		},
		error: function(xhr){
			alert(Lang.serverConnectionError);
			console.log(xhr.responseText);
		},
		complete: function(){
			$('button[type="submit"]').button('reset');
		}
	});
	/** 发送邮件验证码 */
	$('#email-vcode').not('.disabled').click(function(){
		var email = $('#user_email'),
			addr = email.length ? email.val() : $('#user').val(),
			$this = $(this),
			exists = false;
		if(!email.length){ //通过邮箱检查用户是否存在
			$.ajax({
				url: SITE_URL+'mod.php?user::checkExistenceByEmail|user_email:'+addr,
				async: false,
				success: function(result){
					exists = result.success;
					if(!exists) alert(result.data);
				},
				error: function(xhr){
					alert(Lang.serverConnectionError);
					console.log(xhr.responseText);
				}
			})
		}
		if(!email.length && !exists) return false;
		if(addr && addr.match(/[\S]+\@[\S]+\.[\S]+/)){
			$this.button('loading');
			$.ajax({
				url: SITE_URL+'mod.php?mod::mailVcode',
				type: 'post',
				data: {
					action: $this.data('action'),
					user_nickname: $('#user_name').val(),
					user_email: addr,
				},
				success: function(result){
					alert(result.data);
					$this.button('reset');
				},
				error: function(xhr){
					alert(Lang.serverConnectionError);
					console.log(xhr.responseText);
				}
			});
		}else{
			alert(Lang.illegalEmailWarnig);
			email.length ? $('#user_email').focus() : $('#user').focus();
		}
		return false;
	});
	/** 更换图片验证码 */
	$('#vcode-img').click(function(){
		var src = $(this).attr('src'),
		date = new Date();
		src = src.split('?')[0]+'?'+date.getTime();
		$(this).attr('src', src);
	});

	storage('ModAdminURL', false);

	// 关闭头像并重新输入账号
	$('#avatar-box .glyphicon-remove').click(function(){
		$('#avatar-box').hide();
		$('#username-box').show().children('input').val('').focus();
		$('.panel-login').centralize();
	});

	// 账号输入框失焦时获取并显示用户头像
	$('#user').blur(function(){
		var target = $('#avatar-box');
		if(target.length){
			$.get(SITE_URL+'mod.php?user::getAvatar|'+$(this).attr('name')+':'+$(this).val(), function(result){
				if(result.success){
					if(result.data.user_avatar){
						$('#avatar-box img.avatar').attr("src", result.data.user_avatar).show().siblings('.avatar').hide();
					}else{
						$('#avatar-box span.avatar').show().siblings('.avatar').hide();
					}
					$('#username-box').fadeOut('fast', function(){
						target.fadeIn('fast');
						$('.panel-login').centralize();
					});
				}
			});
		}
	});
});