$(function(){
	navbarToTop = $('.navbar').offset() ? $('.navbar').offset().top : 0; //导航条到顶端距离
	content = $('#content');
	minWidth = 640;

	/** 用户名点击事件 */
	$('#username').click(function(){
		$(this).next('.dropdown-menu').slideToggle('fast');
	});

	/** 关闭弹出用户菜单 */
	$(window).on('click', function(event){
		var target = event.target.closest('.dropdown-menu');
		if(event.target.id != 'username' && (!target || target.length < 1)){
			$('.dropdown-menu').slideUp();
		}
	});

	/** 登出 */
	$('#logout').click(function(){
		$.get(SITE_URL+'mod.php?user::logout', function(result){
			alert(result.data);
			if(result.success) location.reload();
		});
	});

	/** 使用宽屏 */
	if(typeof fixedWidth != 'undefined' && fixedWidth){
		$('.container').css('max-width', '940px');
	}

	/** 弹出下拉导航 */
	$('.navbar li').mouseover(function(){
		if($(window).width() < minWidth) return false;
		$('.navbar li>ul').hide();
		$(this).children('ul').show();
	});

	/** 关闭下拉导航 */
	$(window).on('mouseover', function(event){
		if($(window).width() < minWidth) return false;
		var target = $(event.target);
		if(!target.closest('.navbar li') || target.closest('.navbar li').length < 1){
			$('.navbar li>ul').hide();
		}
	})

	/** 浮动导航条 */
	$(window).scroll(function(){
		if($(window).width() < minWidth) return false;
		if($(window).scrollTop() > navbarToTop && fixedNavbar){
			$('.navbar').addClass('fixed');
			$('body').css('margin-top', $('.navbar').height());
		}else{
			$('.navbar').removeClass('fixed');
			$('body').css('margin-top', 0);
		}
	});

	/** 显示导航栏 */
	$('.navbar-toggle').click(function(){
		$('.navbar .container').slideToggle();
	});

	/** 高亮导航条 */
	if(content.length){
		var id = content.data('id');
		$('.navbar').find('li').removeClass('active').filter('li[data-id="'+id+'"]').addClass('active');
		if($('.navbar').find('li.active').length < 1){
			$('.navbar').find('li').eq(0).addClass('active');
		}
	}

	/** .disabled 禁止点击 */
	$(document).on('click', '.disabled, .disabled a', function(event){
		event.preventDefault();
		event.stopPropagation();
	});

	/** 评论 */
	$('#comment-form').ajaxSubmit(function(result){
		if(result.success){
			var tip = Lang.commentSuccess;
			if(!result.data.comment_status){
				tip = Lang.commentSuccess2;
			}
			alert(tip);
			location.reload();
		}else{
			alert(result.data);
		}
	}).find('textarea').focus(function(){
		$('#comment-anonymous').slideDown();
	});

	/** 重置按钮 */
	$(':reset').click(function(){
		$('#comment-anonymous').slideUp();
		var form = $('#comment-form');
		form.find('textarea').attr('placeholder', Lang.editTip);
		form.find('input[name="comment_parent"]').remove();
	});

	/** 回复按钮 */
	$('.comment-reply').click(function(){
		var form = $('#comment-form');
		form.find('textarea').attr('placeholder', '@'+$(this).closest('h4').find('.comment-author').text()).focus();
		form.prepend('<input type="hidden" name="comment_parent" value="'+$(this).closest('.comment').data('id')+'"/>');
		$.gotoComment();
	});

	/** 定位到评论框 */
	$.gotoComment = function(){
		$('body').animate({scrollTop: $('#comment-form textarea').offset().top-10});
	}

	/** 外观设置 */
	$('#setting-form').ajaxSubmit(function(result){
		if(result.success){
			alert(Lang.featureSettingsUpdated);
			history.go(-1);
		}else{
			alert(result.data);
		}
	});
})