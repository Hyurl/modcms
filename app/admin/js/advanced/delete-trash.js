$(function(){
	WS = _window.WS;
	/** 检测无效记录 */
	$('#start-test').click(function(){
		var $this = $(this),
			list = $('#trash-list-div'),
			hasTrash = false,
			i = length = 0,
			submitSuccess = function(result){
				if(result.success){
					hasTrash = true;
					list.show().find('tbody').append('<tr><td>'+Tables[result.obj]+'</td><td>'+result.data.length+'</td><td><span title="'+Lang.clean+'" class="delete" data-table="'+result.obj+'"><i class="glyphicon glyphicon-remove"></i></span></td><tr>');
				}
				if(i == length-1){
					if(!hasTrash){
						alert(Lang.noCleanableData);
					}else{
						$this.hide().next('button').show();
					}
					$this.button('reset');
				}
				i++;
			};
		$this.button('loading');
		for(var table in Tables){
			length++;
		}
		for(var table in Tables){
			// if(WS.source){
			// 	WS.bind(table+'.getTrash', submitSuccess);
			// 	WS.send({obj: table, act: 'getTrash'});
			// }else{
				$.ajax({
					url: SITE_URL+'mod.php?'+table+'::getTrash',
					success: submitSuccess,
					error: function(xhr){
						alert(Lang.serverConnectionError);
						console.log(xhr.responseText);
						$this.button('reset');
						if(i == length-1){
							$this.button('reset');
						}
						i++;
					}
				});
			//}
		}
	});
	/** 删除无效记录 */
	$('#trash-list-div tbody').on('click', 'span.delete', function(){
		var $this = $(this),
			table = $this.data('table'),
			submitSuccess = function(result){
				alert(result.data);
				if(result.success){
					$this.closest('tr').remove();
				}
			};
		if(WS.source){
			WS.bind(table+'.cleanTrash', submitSuccess);
			WS.send({obj: table, act: 'cleanTrash'});
		}else{
			$.ajax({
				url: SITE_URL+'mod.php?'+table+'::cleanTrash',
				success: submitSuccess,
				error: function(xhr){
					alert(lang.serverConnectionError);
					console.log(xhr.responseText);
				}
			});
		}
	});
	/** 一键删除记录 */
	$('#onekey-clean').click(function(){
		var $_this = $(this),
			target  = $('#trash-list-div').find('span.delete');
		target.each(function(i){
			var $this = $(this),
				table = $this.data('table'),
				submitSuccess = function(result){
					if(result.success){
						$this.closest('tr').remove();
					}
					if(i == target.length-1){
						alert(Lang.dataCleanComplete);
						$('#trash-list-div').hide();
						$_this.hide().prev('button').show();
						$_this.button('reset');
					}
				};
			// if(WS.source){
			// 	WS.bind(table+'.cleanTrash', submitSuccess);
			// 	WS.send({obj: table, act: 'cleanTrash'});
			// }else{
				$.ajax({
					url: SITE_URL+'mod.php?'+table+'::cleanTrash',
					success: submitSuccess,
					error: function(xhr){
						alert(Lang.serverConnectionError);
						console.log(xhr.responseText);
						if(i == target.length-1){
							$_this.button('reset');
						}
					}
				});
			//}
		});
	});

	/** 显示帮助信息 */
	$.showHelp(Lang.deleteTrashHelp);
});