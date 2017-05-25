$(function(){
	var catList = $('#category-list'),
		catDetails = $('#category-details'),
		catEditForm = $('#category-edit-form'),
		EditorMenu = {},
		ListMenu = {},
		showListMenu = function(onlyAdd){
			if(!$('.list-group-item.active').length || onlyAdd){
				var menu = {};
				menu[Lang.add] = ListMenu[Lang.add];
				$.showMenu(menu);
			}else{
				$.showMenu(ListMenu);
			}
		};
	EditorMenu[Lang.giveup] = function(){
		location.href = 'categories.html';
	};
	EditorMenu[Lang.submit] = function(){
		$('#category-edit-form').find('button').click();
	};
	ListMenu[Lang.add] = 'categories.html?action=add',
	ListMenu[Lang.edit] = function(){
		location.search = '?action=update&category_id='+$('.list-group-item.active').data('category_id');
	};
	ListMenu[Lang.delete] = function(){
		if($('.list-group-item.active').length < 1){
			alert(Lang.noDeleteItemWarning);
		}else if(confirm(Lang.deleteWarning)){
			var id = $('.list-group-item.active').data('category_id');
			var submitSuccess = function(result){
				alert(result.data);
				if(result.success){
					location.reload();
				}
			};
			if(WS.source){
				WS.bind('category.delete', submitSuccess);
				WS.send({obj: 'category', act: 'delete', 'category_id': id});
			}else{
				$.ajax({
					url: SITE_URL+'mod.php?category::delete|category_id:'+id,
					success: submitSuccess,
					error: function(xhr){
						alert(Lang.serverConnectionError);
						console.log(xhr.responseText);
					}
				});
			}
		}
	};	

	/** 根据请求参数显示菜单 */
	if($_GET['action']){
		$.showMenu(EditorMenu);
	}else{
		showListMenu();
	}

	$('li.list-children').prev('li').children('.glyphicon').click(function(event){
		event.stopPropagation();
		var $this = $(this);
		if($this.is('.glyphicon-chevron-down')){
			$this.removeClass('glyphicon-chevron-down').addClass('glyphicon-chevron-right');
			$this.parent('li').next('li').slideUp('fast');
		}else{
			$this.removeClass('glyphicon-chevron-right').addClass('glyphicon-chevron-down');
			$this.parent('li').next('li').slideDown('fast');
		}
	});

	$('.list-group').on('click', 'li', function(event){
		event.stopPropagation();
		var $this = $(this),
			data = $this.data();
		if(!$this.is('.list-children') && event.target.nodeName != 'I'){
			if(catDetails.css('display') == 'none'){
				catList.hide();
				catDetails.slideDown('fast');
			}
			if(!$_GET['action']){
				showListMenu();
				$('.list-group-item').removeClass('active');
				$this.addClass('active');
				$('#category_name').text(data.category_name);
				$('#category_alias').text(data.category_alias);
				$('#category_desc').text(data.category_desc);
				$('#content-posts').html(Lang.loading);
				var submitSuccess = function(result){
					if(result.success){
						var text = Lang.contentPostsTip+'<a href="posts.html?category_id='+data.category_id+'">'+Lang.viewMore+'»</a>',
							index = 1;
						for(var i in result.data){
							text = text.replace('<post'+index+'>', result.data[i].post_title);
							index++;
						}
						$('#content-posts').html(text.replace('<count>', result.total));
					}else{
						$('#content-posts').text(result.data);
					}
				};
				if(WS.source){
					WS.bind('post.getMulti', submitSuccess);
					WS.send({obj: 'post', act: 'getMulti', limit: 3, category_id: data.category_id});
				}else{
					$.ajax({
						url: SITE_URL+'mod.php?post::getMulti|limit:3|category_id:'+data.category_id,
						success: submitSuccess,
						error: function(xhr){
							alert(Lang.serverConnectionError);
							console.log(xhr.responseText);
						}
					});
				}
			}else{
				location.search = '?action=update&category_id='+data.category_id;
			}
		}
	});

	catEditForm.ajaxSubmit({
		beforeSend: function(options){
			if(WS.source){
				WS.bind(['category.add', 'category.update'], options.success);
				WS.send($.extend({obj: 'category', act: $_GET['action']}, options.data));
				return false;
			}
		},
		success: function(result){
			if(result.success){
				alert(result.act == 'update' ? Lang.updatedTip : Lang.addedTip);
				location.search = '?category_id='+result.data.category_id;
			}else{
				alert(result.data);
			}
		},
		error: function(xhr){
			alert(Lang.serverConnectionError)
			console.log(xhr.responseText);
		}
	});

	/** 关闭详情页面 */
	$('#category-details>.page-header h4>.glyphicon').click(function(){
		if(!$_GET['action']){
			catDetails.hide();
			catList.slideDown('fast');
			showListMenu(true);
		}else{
			history.back(-1);
		}
	})
	if(!$_GET['category_id'] && !$_GET['action']){
		var firstLi = $('.list-group').find('li[data-category_id]').eq(0);
		if(catDetails.css('display') != 'none'){
			firstLi.trigger('click');
		}else{
			firstLi.addClass('active');
		}
	}else if($_GET['action']){
		if(catDetails.css('display') == 'none'){
			catList.hide();
			catDetails.slideDown('fast');
		}
		catEditForm.find('#category_name').focus();
	}else{
		catEditForm.find('#category_name').focus();
	}
});