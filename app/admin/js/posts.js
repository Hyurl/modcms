$(function(){
	if(!IS_AUTH){
		$_GET['user_id'] = ME_ID;
	}
	if(!$_GET['action'] || $_GET['action'] == 'search'){
		$.showSearch(ADMIN_URL+'posts.html?action=search');
	}else{
		$.showSearch(null);
	}

	var uploadForm = $('#jquery-upload-form'),
		postContent = $('#post-content-div'),
		thumbnail = $('#post_thumbnail'),
		thumbnailImg = $('#post-thumbnail-img'),
		thumbnailClean = $('#thumbnail-clean'),
		postListTbody = $('#post-list-table>tbody'),
		EditorMenu = {}, //编辑页面菜单
		ListMenu = {};
	EditorMenu[Lang.giveup] = function(){
		if(typeof localStorage != 'undefined'){
			/** 清除本地存储数据 */
			for(var i in localStorage){
				if($_GET['action'] == 'add' && i.indexOf('post_new_') === 0){
					localStorage.removeItem(i);
				}else if($_GET['action'] == 'update' && i.indexOf('post_id_') ===0){
					var id = parseInt(i.substr(8)),
						name = i.substr(8+id.toString().length+1);
					if(id == postId.val()){
						localStorage.removeItem(i);
					}
				}
			}
		}
		history.back(-1);
	};
	EditorMenu[Lang.submit] = function(){
		/** 提交文章编辑表单 */
		$('#post-edit-form').find(':submit').click();
	};
	ListMenu[Lang.sequence] = {}; //列表页面菜单
	ListMenu[Lang.sequence][Lang.latestSequence] = function(){
		$_GET = $.extend($_GET, {page: 1, orderby: 'post_time', sequence: 'desc'});
		location.search = '?'+$.param($_GET);
	};
	ListMenu[Lang.sequence][Lang.idSequence] = function(){
		$_GET = $.extend($_GET, {page: 1, orderby: 'post_id', sequence: 'asc'});
		location.search = '?'+$.param($_GET);
	};
	ListMenu[Lang.prevPage] = function(){
		$_GET['page'] -= 1;
		getPostList();
	};
	ListMenu[Lang.nextPage] = function(){
		$_GET['page'] += 1;
		getPostList();
	};
	ListMenu[Lang.change] = {};
	ListMenu[Lang.change][Lang.edit] = function(){
		var id = postListTbody.find('.active').eq(0).data('id');
		location.search = '?action=update&post_id='+id;
	};
	ListMenu[Lang.change][Lang.delete] = function(){
		postListTbody.editMulti($.extend({
			obj: 'post',
			act: 'delete',
			callback: function(){
				getPostList(true);
			}
		}, Lang));
	};
	ListMenu[Lang.change][Lang.changeCategory] = function(){
		$('#change-category-modal').one('shown.bs.modal', function(){
			$(this).find('select').focus();
		}).modal('show');
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
		var length = postListTbody.find('.active').length;
		if(length){
			menu[Lang.change] = $.extend({}, ListMenu[Lang.change]);
			if(length > 1){
				delete menu[Lang.change][Lang.edit];
			}
		}
		$.showMenu(menu);
	};
	var getPostList = function(noAlert){ //获取文章列表数据
		var act = $_GET['action'] == 'search' ? 'search' : 'getMulti';
		var url = SITE_URL+'mod.php?obj=post&act='+act+'&'+$.param($_GET);
		var submitSuccess = function(result){
				postListTbody.html('');
				if(result.success){
					var data = result.data,
						id = 1 + ($_GET['page'] - 1) * $_GET['limit'];
					for(var i in data){
						var html = '<tr data-id="'+data[i]['post_id']+'"><td>'+id+'</td><td><a title="'+Lang.viewDetails+'" target="_blank" href="'+(data[i]['post_link'] || create_url(POST_STATIC_URI, data[i]))+'">'+data[i]['post_title']+'</a></td>';
						if(!$_GET['user_id']){
							var isMe = data[i].user_id == ME_ID;
							if(isMe){
								var title = Lang.viewMyPosts;
							}else if(data[i].user_gender == 'female'){
								var title = Lang.viewHerPosts;
							}else{
								var title = Lang.viewHisPosts;
							}
							var user = isMe ? Lang.me : (data[i]['user_nickname'] || data[i]['user_name']);
							html += '<td><a title="'+title+'" href="posts.html?user_id='+data[i].user_id+'&tablist=person">'+user+'</a></td>';
						}
						html += '<td>'+date(Lang.dateFormat, data[i]['post_time']*1000)+'</td><td data="category-name">'+(data[i]['category_alias'] || data[i]['category_name'])+'</td><td>'+(data[i]['post_comments'] ? '<a title="'+Lang.viewDetails+'" href="comments.html?post_id='+data[i]['post_id']+'">'+data[i]['post_comments']+'</a>' : Lang.none)+'</td></tr>';
						postListTbody.append(html);				
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
			WS.bind(['post.search', 'post.getMulti'], submitSuccess);
			WS.send($.extend({obj: 'post', act: act}, $_GET));
		}else{
			$.ajax({
				url: url, 
				success: submitSuccess,
				error: function(xhr){
					alert(Lang.serverConnectionError);
					console.log(xhr.responseText);
				}
			});
		}
	};

	/** 根据查询参数显示指定菜单 */
	if($_GET['action'] == 'add' || $_GET['action'] == 'update'){
		var postId = $('#post-edit-form').find('input[name="post_id"]'),
			storeBaseName = postId.length ? 'post_id_'+postId.val()+'_' : 'post_new_';
		$.showMenu(EditorMenu);
		$('#post_title').focus();
	}else{
		showListMenu();

		/** 点击列表项时对其进行高亮 */
		postListTbody.on('click', 'td', function(event){
			$(this).closest('tr').toggleClass('active');
			showListMenu();
		});

		/** 更改目录模态框 */
		var modal = $('#change-category-modal');
		modal.find(':submit').click(function(){
			var catId = parseInt(modal.find('select').val());
			if(catId > 0){
				postListTbody.editMulti($.extend({
					obj: 'post',
					act: 'update',
					data: {category_id: catId},
					callback: function(data){
						var catName = modal.find('select').find('option[value="'+catId+'"]').text();
						console.log($(this).find('td[data="category-name"]'));
						postListTbody.find('tr.active').children('td[data="category-name"]').text(catName);
						modal.modal('hide');
					}
				}, Lang));
			}else{
				alert(Lang.changeCategoryWarning);
			}
		});
	}

	var insertFile = function (context) {
		var ui = $.summernote.ui;
		var button = ui.button({
			contents: '<i class="glyphicon glyphicon-file" style="line-height: 1.5"></i>',
			tooltip: Lang.file,
			click: function () {
				uploadForm.find('[name="file_desc"]').val(Lang.postFile);
				uploadForm.find(':file').attr('accept', '*').click();
			}
		});
		return button.render();   // return button as jquery object 
	}

	/** 编辑器工具条 */
	toolbar = [
		// [groupName, [list of button]]
		['paragraph', ['style']],
		['font', ['fontname', 'fontsize']],
		['fontstyle', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
		['color', ['color']],
		['table', ['table']],
		['para', ['ul', 'ol', 'paragraph']],
		['height', ['height']],
		['insert', ['link', 'picture', 'video', 'file', 'hr']],
		['misc', ['fullscreen', 'codeview', 'undo', 'redo', 'help']],
	];

	postContent.summernote({
		height: 400,
		lang: 'zh-CN',
		dialogsFade: true,
		placeholder: Lang.postEditTip,
		callbacks: {
			onInit: function(){
				$('#post-edit-form').data('sendAvailable', true);
				var input = $('.note-image-input');
				input.after('<button type="button" class="btn btn-default" id="select-image" style="display:block">'+Lang.selectImage+'</button>');
				input.remove();
				var label = $('<label>');
				label.attr({'for': 'as-thumbnail', 'class': 'radio-inline', 'style': 'float: left'});
				label.append('<input type="checkbox" id="as-thumbnail"/> '+Lang.setAsThumbnail);
				$('.note-image-btn').parent().prepend(label);
				// 上传视频按钮
				var input2 = $('.note-video-url');
				var body = input2.closest('.modal-body');
				body.prepend('<div class="form-group row-fluid"><label>'+Lang.uploadFromLocal+'</label><div class="progress"><div id="video-progress-bar" class="progress-bar progress-bar-success progress-bar-striped active" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;">0%</div></div><input id="video-button" type="file" name="file" accept="video/mp4" style="margin-bottom: 10px;"/><button id="upload-video" type="button" class="btn btn-default">'+Lang.upload+'</button></div>');
			},
			onImageUpload: function(files){
				if(typeof FormData == 'undefined'){
					alert(Lang.notHTML5Warning);
				}else{
					var fd = new FormData();
					for(var i=0; i<files.length; i++){
						fd.append('file[]', files[i]);
					}
					fd.append('file_desc', uploadForm.children('input[name="file_desc"]').val());
					$.ajax($.extend({
						url: uploadForm.attr('action'),
						method: 'post',
						data: fd,
						processData : false,
						contentType : false
					}, handleImageUpload));
				}
			},
			onChange: function(content) {
				$('#post_content').val(content);
				if(typeof localStorage != 'undefined') localStorage.setItem(storeBaseName+'post_content', content);
			}
		},
		toolbar: toolbar,
		buttons: {
			file: insertFile
		}
	});

	/** 提交文章表单 */
	$('#post-edit-form').ajaxSubmit({
		beforeSend: function(options){
			if(!$(this).data('sendAvailable')){
				return false;
			}else{
				$(this).data('sendAvailable', false);
			}
			if(WS.source){
				WS.bind(['post.add', 'post.update'], options.success);
				WS.send($.extend({obj: 'post', act: $_GET['action']}, options.data));
				return false;
			}
		},
		success: function(result){
			if(result.success){
				if(typeof localStorage != 'undefined'){
					for(var i in localStorage){
						if($_GET['action'] == 'add' && i.indexOf('post_new_') === 0){
							var name = i.substr(9);
							localStorage.removeItem(i);
						}else if($_GET['action'] == 'update' && i.indexOf('post_id_') === 0){
							var id = parseInt(i.substr(8)),
								name = i.substr(8+id.toString().length+1);
							if(id == result.data.post_id){
								localStorage.removeItem(i);
							}
						}
					}
				}
				if($_GET['action'] == 'add'){
					alert(Lang.addedTip)
					location.search = '?post_id='+result.data.post_id;
				}else{
					alert(Lang.updatedTip);
					history.go(-1);
				}
			}else{
				alert(result.data);
				$(this).data('sendAvailable', true);
			}
		},
		error: function(xhr){
			if(typeof localStorage != 'undefined'){
				alert(Lang.serverConnectionError1);
			}else{
				alert(Lang.serverConnectionError2);
			}
			console.log(xhr.responseText);
			$(this).data('sendAvailable', true);
		}
	});

	/** 打开文件对话框 */
	$(document).on('click', '#select-image', function(){
		uploadForm.find('[name="file_desc"]').val(Lang.postImage);
		uploadForm.find(':file').attr('accept', 'image/png,image/jpeg,image/gif,image/bmp').click();
	});

	var handleImageUpload = {
		success: function(result){
			if(result.success){
				var checkbox = $('#as-thumbnail');
				$.each(result.data, function(i, n){
					if(n.error){
						alert(n.name+' '+Lang.uploadFailed+': '+n.error);
					}else{
						var ext = n.file_src.substring(n.file_src.lastIndexOf('.')+1);
						if($.inArray(ext, ['png', 'jpg', 'jpeg', 'gif', 'bmp']) >= 0){ //处理图片
							var image = $('<img>').attr('src', n.file_src);
							postContent.summernote('insertNode', image[0]);
							if(i == 0 && checkbox.is(':checked')){
								thumbnail.val(n.file_src);
								thumbnailImg.attr('src', n.file_src);
								thumbnailClean.show();
								checkbox.click();
							}
						}else{ //处理普通文件
							var link = document.createElement('a');
							link.setAttribute('href', n.file_src);
							link.innerText = n.file_name;
							postContent.summernote('insertNode', link);
						}
					}
				});
			}else{
				if(typeof result.data != 'string'){
					$.each(result.data, function(i, n){
						alert(n.name+' '+Lang.uploadFailed+': '+n.error);
					});
				}else{
					alert(result.data);
				}
			}
			uploadForm.find(':file').val('');
		},
		error: function(xhr){
			alert(Lang.serverConnectionError);
			console.log(xhr.responseText);
			uploadForm.find(':file').val('');
		}
	};

	/** 上传文件 */
	uploadForm.ajaxSubmit($.extend({
		beforeSend: function(){
			if(!$(document.activeElement).is('.note-editable')){
				$('#select-image').closest('.modal').modal('hide');
				postContent.one('summernote.focus', function(){
					setTimeout(function(){
						uploadForm.submit();
					}, 200);
				}).summernote('focus');
				return false;
			}
		}
	}, handleImageUpload)).find(':file').on('change', function(){
		if($(this).val()){
			$(this).parent().submit();
		}
	});

	/** 切换分类目录 */
	$('#change-category').on('change', function(){
		if($(this).val() == 0){
			delete $_GET['category_id'];
		}else{
			$_GET['category_id'] = $(this).val();
		}
		$_GET['page'] = 1;
		$_GET['tablist'] = 'category';
		location.search = '?'+$.param($_GET);
	});

	if($_GET['action'] == 'add'){
		if($_GET['category_id']){
			$('#category_id').find('option[value="'+$_GET['category_id']+'"]').attr('selected', true);
		}
		if($_GET['post_type']){
			$('#post_type').find('option[value="'+$_GET['post_type']+'"]').attr('selected', true);
		}
	}

	if(typeof localStorage != 'undefined' && ($_GET['action'] == 'add' || $_GET['action'] == 'update')){
		var postMeta = ['post_title', 'post_tags', 'post_link', 'post_desc'];
		for(var i in localStorage){
			if(i.indexOf('post_new_') === 0 && $_GET['action'] == 'add'){
				var name = i.substr(9);
				if($.inArray(name, postMeta) >=0){
					$('#'+name).val(localStorage[i]);
				}else if(name == 'post_content'){
					$('#post_content').val(localStorage[i]);
					$('.note-editable').html(localStorage[i]);
				}else if(name == 'category_id' || name == 'post_type'){
					$('#'+name).children('option[value="'+localStorage[i]+'"]').attr('selected', true).siblings().attr('selected', false);
				}
			}else if(i.indexOf('post_id_') === 0 && $_GET['action'] == 'update'){
				var id = parseInt(i.substr(8)),
					name = i.substr(8+id.toString().length+1);
				if(id == postId.val()){
					if($.inArray(name, postMeta) >= 0){
						$('#'+name).val(localStorage[i]);
					}else if(name == 'post_content'){
						$('#post_content').val(localStorage[i]);
						$('.note-editable').html(localStorage[i]);
					}else if(name == 'category_id' || name == 'post_type'){
						$('#'+name).children('option[value="'+localStorage[i]+'"]').attr('selected', true).siblings().attr('selected', false);
					}
				}
			}
		}
		$('#post-edit-form').find('input[id], textarea[id]').on('input', function(){
			localStorage.setItem(storeBaseName+$(this).attr('name'), $(this).val());
		}).each(function(){
			localStorage.setItem(storeBaseName+$(this).attr('name'), $(this).val());
		});
		$('#post-edit-form').find('select[id]').change(function(){
			localStorage.setItem(storeBaseName+$(this).attr('name'), $(this).val());
		});
		localStorage.setItem(storeBaseName+'category_id', $('#category_id').val());
		localStorage.setItem(storeBaseName+'post_type', $('#post_type').val());
		localStorage.setItem(storeBaseName+'post_content', postContent.html());
	}

	// 添加网络特色图
	$(document).on('click', '.note-image-btn', function(){
		var src = $('.note-image-url').val();
		var checkbox = $('#as-thumbnail');
		if(checkbox.is(':checked')){
			thumbnail.val(src);
			thumbnailImg.attr('src', src);
			thumbnailClean.show();
			checkbox.click();
		}
	});

	// 删除特色图
	thumbnailClean.click(function(){
		thumbnail.val('');
		thumbnailImg.attr('src', '');
		thumbnailClean.hide();
	});

	// 分段上传视频
	$('#upload-video').click(upload);
	var xhr = new XMLHttpRequest();
	var bar = document.getElementById('video-progress-bar'); //进度条
	var videoBtn = document.getElementById('video-button');
	var start = 0; //切片开始位置
	var end = LENGTH; //切片结束位置
	var file_src = ''; //分段上传时的目标文件地址

	function upload(){						
		var file = videoBtn.files[0]; //文件对象
		if(!file){
			return;
		}else if(file.name.substring(file.name.lastIndexOf('.')+1) != 'mp4'){
			alert(Lang.onlySupportMp4);
			return;
		}
		if(start < file.size){
			bar.parentNode.style.display = 'block';
			videoBtn.style.display = 'none';
			xhr.open('POST', SITE_URL+'mod.php?file::upload', true);
			xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest'); //将其标记为 ajax 请求
			xhr.onreadystatechange = function(){
				if(this.readyState == 4){
					if(this.status == 200){
						result = JSON.parse(this.responseText); //解析 JOSN
						if(result.success){
							if(!file_src)
								file_src = result.data[0].file_src; //记录文件地址
							start = end; //新的切片起始点
							end = start + LENGTH; //新的切片结束点
							upload(); //递归执行上传函数
						}else{
							alert(result.data[0].error);
							bar.style.width = '0%';
						}
					}else{
						alert(Lang.serverConnectionError);
					}
				}
			};
			// 上传进度
			xhr.upload.onprogress = function(ev){
				if(ev.lengthComputable){
					var pecent = 100 *(ev.loaded + start) / file.size; //获取上传百分比
					if(pecent > 100){
						pecent = 100;
					}
					// 改变进度条
					bar.style.width = pecent + '%';
					bar.innerHTML = parseInt(pecent) + '%';
				}
			};

			var blob = file.slice(start, end); //将文件切割为 blob 对象
			var fd = new FormData(); //使用 FormData 对象上传
			fd.append('file', blob); //将 blob 添加到 FormData 中
			fd.append('file_name', file.name); //标记文件名
			fd.append('file_desc', Lang.postVideo);
			if(file_src){
				fd.append('file_src', file_src); //标记为后续片段
			}
			xhr.send(fd); //发送请求
		}else{ //上传完成
			// alert(file.name + " 上传成功。");
			$('.modal').one('hidden.bs.modal', function(){
				var video = $('<iframe>').attr({
					src: file_src,
					height: 360,
					width: 640,
					frameborder: "0",
					controls: 'controls',
				});
				postContent.summernote('insertNode', video[0]);
				video.load(function(){
					$(this).contents().find('video').attr('autoplay', false);
				});
				bar.style.width = '0%';
				bar.innerHTML = '0%';
				bar.parentNode.style.display = 'none';
				videoBtn.value = '';
				videoBtn.files = null;
				videoBtn.style.display = 'block';
			}).modal('hide');
		}
	}

	var iframe = $('iframe');

	//禁止 Iframe 中的视频自动播放 
	iframe.load(function(){
		var $this = $(this)
		try{
			$this.contents().find('video').attr('autoplay', false);
		}catch(e){}
	});

	// 改变窗口带小时同步改变 Iframe 大小
	// var height = iframe.attr('height') || iframe.height();
	// var width = iframe.attr('width') || iframe.width();
	// var resizeIframe = function(){
	// 	var pct = iframe.width()/width;
	// 	iframe.css('height', height*pct);
	// };
	// resizeIframe();
	// $(window).resize(resizeIframe);
});