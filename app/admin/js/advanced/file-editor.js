$(function(){
	$.showHelp(Lang.fileEditorHelp);
	
	var WS = _window.WS,
		editor = $('#file-editor'),
		menu = {};
	menu[Lang.giveup] = function(){
		$('#filename').text($('#filename').data('text'));
		editor.find('[name="filename"]').val('');
		editor.find('[name="content"]').val('');
		$.showMenu(null);
	};
	menu[Lang.submit] = function(){
		if(confirm(Lang.changeSiteFuncWarning)){
			editor.find('[type="submit"]').click();
		}
	};
	$('li.list-group-item').click(function(event){
		event.stopPropagation();
		if($(this).is('.has-children')){
			$(this).next('li').slideToggle();
			$(this).children('span').toggleClass('glyphicon-chevron-down').toggleClass('glyphicon-chevron-right');
		}else{
			$('#folder-list').find('.active').removeClass('active');
			$(this).addClass('active');
			var filename = $(this).data('filename'),
				fileContent = '',
				submitSuccess = function(result){
					if(result.success){
						fileContent = result.data;
						if($(window).width() < 768){
							$('#folder-list>.list-group>li').next('li').slideUp();
						}
					}else{
						alert(result.data);
					}
					editor.find('[name="filename"]').val(filename);
					editor.find('[name="content"]').val(fileContent);
					if(fileContent){
						$.showMenu(menu);
					}else{
						$.showMenu(null);
					}
				};
			$('#filename').text($(this).text());
			if(WS.source){
				WS.bind('file.getContent', submitSuccess);
				WS.send({obj: 'file', act: 'getContent', filename: filename});
			}else{
				$.ajax({
					url: SITE_URL+'mod.php?file::getContent|filename:'+filename,
					success: submitSuccess,
					error: function(xhr){
						alert(Lang.serverConnectionError);
						console.log(xhr.responseText);
					}
				});
			}
		}
	});
	editor.ajaxSubmit({
		beforeSend: function(options){
			if(WS.source){
				WS.bind('file.putContent', options.success);
				WS.send($.extend({obj: 'file', act: 'putContent'}, options.data));
				return false;
			}
		},
		success: function(result){
			alert(result.data);
			if(result.success){
				$.showMenu(null);
			}
		},
		error: function(xhr){
			alert(Lang.serverConnectionError);
			console.log(xhr.responseText);
		}
	});
});