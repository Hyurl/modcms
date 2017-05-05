$(function(){
	if(!IS_AUTH && !$_GET['post_id']){
		$_GET['user_id'] = ME_ID;
	}
	if(!$_GET['qequence']){
		$_GET['sequence'] = 'desc';
	}
	var commentListDiv = $('#comment-list-div'),
		modal = $('#comment-modal'),
		ListMenu = {}; //评论列表菜单
	ListMenu[Lang.sequence] = {};
	ListMenu[Lang.sequence][Lang.latestSequence] = function(){
		$_GET = $.extend($_GET, {page: 1, orderby: 'comment_time', sequence: 'desc'});
		location.search = '?'+$.param($_GET);
	};
	ListMenu[Lang.sequence][Lang.idSequence] = function(){
		$_GET = $.extend($_GET, {page: 1, orderby: 'comment_id', sequence: 'asc'});
		location.search = '?'+$.param($_GET);
	};
	ListMenu[Lang.prevPage] = function(){
		$_GET['page'] -= 1;
		location.search = '?'+$.param($_GET);
	};
	ListMenu[Lang.nextPage] = function(){
		$_GET['page'] += 1;
		location.search = '?'+$.param($_GET);
	};
	ListMenu[Lang.change] = {};
	ListMenu[Lang.change][Lang.reply] = function(){
		var target = commentListDiv.children('.active').eq(0);
		modal.find('form').attr('action', SITE_URL+'mod.php?comment::add').prepend('<input type="hidden" name="comment_parent" value="'+target.data('id')+'">').find('input[name="comment_id"]').remove();
		modal.find('.modal-header>h4').text(Lang.reply);
		var placeholder = '@'+target.find('.media-heading').text().replace(/\s*/g, '');
		modal.one('shown.bs.modal', function(){
			$(this).find('textarea[name="comment_content"]').val('').attr('placeholder', placeholder).focus();
		}).modal('show');
	};
	ListMenu[Lang.change][Lang.edit] = function(){
		var target = commentListDiv.children('.active').eq(0);
		modal.find('form').attr('action', SITE_URL+'mod.php?comment::update').prepend('<input type="hidden" name="comment_id" value="'+target.data('id')+'">').find('input[name="comment_parent"]').remove();
		if(target.find('.comment-at').length == 1){
			placeholder = target.find('.comment-at').text();
		}else{
			placeholder = Lang.commentEditTip;
		}
		modal.one('shown.bs.modal', function(){
			$(this).find('textarea[name="comment_content"]').attr('placeholder', placeholder).val(target.find('.comment-content').text()).focus();
		}).modal('show');
	};
	ListMenu[Lang.change][Lang.delete] = function(){
		commentListDiv.editMulti($.extend({obj: 'comment', act: 'delete'}, Lang));
	};
	ListMenu[Lang.change][Lang.reviewPass] = function(){
		var options = {
			obj: 'comment',
			act: 'update',
			data: {comment_status: 1},
			callback: function(){
				$.get(SITE_URL+'mod.php?comment::getUnreviewedCount', function(result){
					$.showNews('comments', result.success ? result.data : null);
					location.reload();
				});
			}
		}
		commentListDiv.editMulti($.extend(options, Lang));
	};
	ListMenu[Lang.change][Lang.blackPerson] = function(){
		var target = commentListDiv.children('.active'),
			modal = $('#comment-modal-blacklist'),
			uid = target.eq(0).data('user-id'),
			email = target.eq(0).data('user-email'),
			ip = target.eq(0).data('comment-ip');
		modal.find('input[name="blacklist_uid"]').val(uid);
		modal.find('input[name="blacklist_email"]').val(email);
		modal.find('input[name="blacklist_ip"]').val(ip);
		modal.modal('show');
	};
	var showListMenu = function(){
		var menu = {};
		if($_GET['page'] == 1 && $_GET['page'] == PAGES){
			menu[Lang.sequence] = ListMenu[Lang.sequence];
		}else if($_GET['page'] == 1){
			menu[Lang.sequence] = ListMenu[Lang.sequence];
			menu[Lang.nextPage] = ListMenu[Lang.nextPage];
		}else if($_GET['page'] == PAGES){
			menu[Lang.sequence] = ListMenu[Lang.sequence];
			menu[Lang.prevPage] = ListMenu[Lang.prevPage];
		}else{
			menu[Lang.sequence] = ListMenu[Lang.sequence];
			menu[Lang.prevPage] = ListMenu[Lang.prevPage];
			menu[Lang.nextPage] = ListMenu[Lang.nextPage];
		}
		var target = commentListDiv.find('.active'),
			length = target.length,
			hasMe = target.is('[data-user-id="'+ME_ID+'"]');
			unreviewed = target.is('[data-status="0"]');
		if(length == 1){
			if(hasMe){
				menu[Lang.change] = {};
				menu[Lang.change][Lang.edit] = ListMenu[Lang.change][Lang.edit];
				menu[Lang.change][Lang.delete] = ListMenu[Lang.change][Lang.delete];
			}else{
				if(!IS_AUTH){
					menu[Lang.reply] = ListMenu[Lang.change][Lang.reply];
				}else{
					menu[Lang.change] = $.extend({}, ListMenu[Lang.change]);
					if(!COMMENT_REVIEW || !unreviewed){
						delete menu[Lang.change][Lang.reviewPass];
					}
				}
			}
		}else if(length > 1){
			if(hasMe || (IS_AUTH && !unreviewed)){
				menu[Lang.delete] = ListMenu[Lang.change][Lang.delete];
			}else{
				if(IS_AUTH){
					menu[Lang.change] = {};
					menu[Lang.change][Lang.delete] = ListMenu[Lang.change][Lang.delete];
					if(COMMENT_REVIEW && unreviewed){
						menu[Lang.change][Lang.reviewPass] = ListMenu[Lang.change][Lang.reviewPass];
					}
				}
			}
		}
		$.showMenu(menu);
	};
	showListMenu();

	/** 点击列表项时对其进行高亮 */
	commentListDiv.on('click', '.media', function(){
		$(this).toggleClass('active');
		showListMenu();
	});
	
	/** 通过网络获取 IP 地址 */
	$.get('https://ipv4.ip.nf/me.json', function(data){
		if(typeof data != 'object') data = JSON.parse(data);
		modal.find('form').find('input[name="comment_ip"]').val(data.ip.ip);
	});

	/** 提交评论表单 */
	modal.find('form').ajaxSubmit({
		beforeSend: function(options){
			if(WS.source){
				WS.bind(['comment.update', 'comment.add'], options.success);
				var act = $(this).attr('action').match('comment::update') ? 'update' : 'add';
				WS.send($.extend({obj: 'comment', 'act': act}, options.data));
				return false;
			}
		},
		success: function(result){
			if(result.success){
				if(result.act == 'update'){
					commentListDiv.children('.active').find('.comment-content').text(result.data.comment_content);
				}else{
					alert(Lang.replySuccess);
					if(!$_GET['commentsTo']){
						window.location.reload();
					}
				}
				modal.modal('hide');
			}else{
				alert(result.data);
			}
		},
		error: function(xhr){
			alert(Lang.serverConnectionError);
			console.log(xhr.responseText);
		}
	});

	/** 提交黑名单表单 */
	$('#comment-modal-blacklist').on('hide.bs.modal', function(){
		$(this).find('button[type="reset"]').click();
	}).find('form').ajaxSubmit({
		beforeSend: function(options){
			if($(this).find(':checked').length < 1){
				alert(Lang.blackWarning);
				return false;
			}
			if(!confirm(Lang.commentBlackWarning)) return false;
			if(WS.source){
				WS.bind('blacklist.add', options.success);
				WS.send($.extend({obj: 'blacklist', act: 'add'}, options.data));
				return false;
			}
		},
		success: function(result){
			if(result.success){
				alert(Lang.blackedTip);
				var target = $('#comment-modal-blacklist form');
				target.find('button[type="reset"]').click();
				target.closest('.modal').modal('hide');
			}else{
				alert(result.data);
			}
		},
		error: function(xhr){
			alert(Lang.serverConnectionError);
			console.log(xhr.responseText);
		}
	}).find('button[type="reset"]').click(function(){
		var modal = $('#comment-modal-blacklist');
		modal.find('input[name="blacklist_uid"]').val('');
		modal.find('input[name="blacklist_email"]').val('');
		modal.find('input[name="blacklist_ip"]').val('');
	});

	/** 显示帮助信息 */
	$.showHelp(Lang.commentHelp);
})