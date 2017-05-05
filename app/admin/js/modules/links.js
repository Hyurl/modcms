$(function(){
	var WS = _window.WS;
		linkListDiv = $('#link-list-div');
		modal = $('#link-modal'), //模态框
		ListMenu = {}, //列表菜单
		showListMenu = function(){ //显示菜单
			var length = linkListDiv.find('.active').length, menu = {};
			if(!length){
				menu[Lang.add] = ListMenu[Lang.add];
			}else{
				if(length == 1){
					menu[Lang.edit] = ListMenu[Lang.edit];
					menu[Lang.delete] = ListMenu[Lang.delete];
				}else{
					menu[Lang.delete] = ListMenu[Lang.delete];
				}
			}
			$.showMenu(menu);
		};
	ListMenu[Lang.add] = function(){
		modal.one('shown.bs.modal', function(){
			$('#link_title').focus();
		}).modal('show').find('form').attr('action', SITE_URL+'mod.php?link::add').find('input[name="link_id"]').remove();
		$('#link_title').val('');
		$('#link_url').val('');
		$('#link_desc').val('');
		$('#link_logo').val('');
		$('#link-logo').attr('src', '');
	};
	ListMenu[Lang.edit] = function(){
		var target = linkListDiv.find('.active').eq(0),
			id = target.data('id'),
			mediaBody = target.find('.media-body'),
			linkTitle = mediaBody.children('h4').text().replace(/\s*/g, ''),
			linkURL = mediaBody.children('p').eq(0).text().replace(/\s*/g, ''),
			linkDesc = mediaBody.children('p').eq(1).text().replace(/\s*/g, ''),
			linkLOGO = target.find('.media-left img').attr('src') || '';
		modal.one('shown.bs.modal', function(){
			$('#link_title').focus();
		}).modal('show').find('form').attr('action', SITE_URL+'mod.php?link::update').prepend('<input type="hidden" name="link_id" value="'+id+'">');
		$('#link_title').val(linkTitle);
		$('#link_url').val(linkURL);
		$('#link_desc').val(linkDesc);
		$('#link_logo').val(linkLOGO);
		$('#link-logo').attr('src', linkLOGO).attr('data-origin', linkLOGO);
	};
	ListMenu[Lang.delete] = function(){
		linkListDiv.editMulti($.extend({obj: 'link', act: 'delete'}, Lang));
	};
	showListMenu();

	/** 点击列表项时对其进行高亮 */
	linkListDiv.on('click', '.media', function(){
		$(this).toggleClass('active');
		showListMenu();
	});

	/** 提交编辑链接的表单 */
	$('#link-modal form').ajaxSubmit({
		beforeSend: function(options){
			if(WS.source){
				var act = options.url == SITE_URL+'mod.php?link::add' ? 'add' : 'update';
				WS.bind(['link.add', 'link.update'], options.success);
				WS.send($.extend({obj: 'link', act: act}, options.data));
				return false;
			}
		},
		success: function(result){
			if(result.success){
				if(result.act == 'update'){
					var active = linkListDiv.find('.active').eq(0);
					var target = active.find('.media-body');
					target.children('h4').text(result.data.link_title);
					target.children('p').eq(0).text(result.data.link_url);
					target.children('p').eq(1).text(result.data.link_desc);
					if(result.data.link_logo) active.find('.media-left .media-logo').html('<img src="'+result.data.link_logo+'" alt="'+Lang.linkLogo+'"/>');
					$('#link-modal').modal('hide');
				}else{
					alert(Lang.addedTip);
					location.reload();
				}
			}else{
				alert(result.data);
			}
		},
		error: function(xhr){
			alert(Lang.serverConnetionError);
			console.log(xhr.responseText);
		}
	});

	/** 选择 LOGO */
	$('#select-logo').click(function(){
		$('#jquery-upload-form').find(':file').click();
	});

	/** 上传文件 */
	$('#jquery-upload-form').ajaxSubmit({
		success: function(result){
			if(result.success){
				$('#link_logo').val(result.data[0].file_src);
				$('#link-logo').attr('src', result.data[0].file_src);
			}else{
				if(typeof result.data == 'string'){
					alert(result.data);
				}else{
					alert(result.data[0].error);
				}
			}
		},
		error: function(xhr){
			alert(Lang.serverConnetionError);
			console.log(xhr.responseText);
		}
	}).find(':file').change(function(){
		if($(this).val()){
			$(this).parent().submit();
		}
	});

	/** 显示帮助信息 */
	$.showHelp(Lang.linkHelp);
})