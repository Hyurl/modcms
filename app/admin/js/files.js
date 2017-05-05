$(function(){
	if(!IS_AUTH){
		$_GET['user_id'] = ME_ID;
	}
	if(!$_GET['page']) $_GET['page'] = 1;

	var fileListDiv = $('#file-list-div'),
		ListMenu = {}; //列表页面菜单
	ListMenu[Lang.sequence] = {};
	ListMenu[Lang.sequence][Lang.latestSequence] = function(){
		$_GET = $.extend($_GET, {page: 1, orderby: 'file_time', sequence: 'desc'});
		location.search = '?'+$.param($_GET);
	};
	ListMenu[Lang.sequence][Lang.idSequence] = function(){
		$_GET = $.extend($_GET, {page: 1, orderby: 'file_id', sequence: 'asc'});
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
	ListMenu[Lang.change][Lang.edit] = function(){
		var target = fileListDiv.find('.active').eq(0),
			id = target.data('id'),
			mediaBody = target.find('.media-body'),
			fileName = mediaBody.children('h4').text().replace(/\s*/g, ''),
			fileDesc = mediaBody.children('p').eq(0).text().replace(/\s*/g, ''),
			modal = $('#file-modal');
		modal.one('shown.bs.modal', function(){
			$('#file_name').focus();
		}).modal('show').find('form').find('input[name="file_id"]').val(id);
		$('#file_name').val(fileName);
		$('#file_desc').val(fileDesc);
	};
	ListMenu[Lang.change][Lang.delete] = function(){
		fileListDiv.editMulti($.extend({obj: 'file', act: 'delete'}, Lang));
	};

	var showListMenu = function(){ //显示列表菜单
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
		var length = fileListDiv.find('.active').length;
		if(length == 1){
			menu[Lang.change] = $.extend({}, ListMenu[Lang.change]);
		}else if(length > 1){
			menu[Lang.delete] = ListMenu[Lang.change][Lang.delete];
		}
		$.showMenu(menu);
	};

	showListMenu(); //显示列表菜单

	/** 点击列表项时对其进行高亮 */
	fileListDiv.on('click', '.media', function(){
		$(this).toggleClass('active');
		showListMenu();
	});

	/** 提交编辑文件的表单 */
	$('#file-modal form').ajaxSubmit({
		beforeSend: function(options){
			if(WS.source){
				WS.bind('file.update', options.success);
				WS.send($.extend({obj: 'file', act: 'update'}, options.data));
				return false;
			}
		},
		success: function(result){
			if(result.success){
				var target = fileListDiv.find('.active').eq(0).find('.media-body');
				target.children('h4').text(result.data.file_name);
				target.children('p').eq(0).text(result.data.file_desc);
				$('#file-modal').modal('hide');
			}else{
				alert(result.data);
			}
		},
		error: function(xhr){
			alert(Lang.serverConnectionError);
			console.log(xhr.responseText);
		}
	});

	/** 查看大图 */
	$('.media-logo img').click(function(event){
		event.stopPropagation();
		_$('#help-info').slideUp('fast');
		var modal = $('#file-modal-picture'),
			src = $(this).attr('src').replace('_64.', '.'),
			ext = src.getExt(),
			fileName = $(this).closest('.media').find('h4.media-heading').text();
		modal.find('.modal-title').text(fileName);
		modal.find('img').attr('src', src);
		modal.modal('show');
	});

	$.showHelp(Lang.fileHelp);
})