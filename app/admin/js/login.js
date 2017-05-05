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
		var email = $('#user_email').val(),
			$this = $(this);
		if(email && email.match(/[\S]+\@[\S]+\.[\S]+/)){
			$this.button('loading');
			$.ajax({
				url: SITE_URL+'mod.php?mod::mailVcode',
				type: 'post',
				data: {
					action: $this.data('action'),
					user_nickname: $('#user_name').val(),
					user_email: email,
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
			$('#user_email').focus();
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
});