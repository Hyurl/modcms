$(function(){
	/** 选择模板 */
	$('#select-template').ajaxSubmit({
		beforeSend: function(options){
			if(WS.source){
				WS.bind('mod.config', options.success);
				WS.send($.extend({obj: 'mod', act: 'config'}, options.data));
				return false;
			}
		},
		success: function(result){
			if(result.success){
				alert(Lang.changedTip);
				location.reload();
			}else{
				alert(result.data);
			}
		},
		error: function(xhr){
			alert(Lang.serverConnectionError);
			console.log(xhr.responseText);
		}
	}).find('input[type="radio"]').change(function(){
		if(confirm(Lang.changeSiteFuncWarning)){
			$(this).closest('form').submit();
		}else{
			$(this).closest('form')[0],reset();
		}
	});

	/** 选择插件 */
	$('#select-plugin').ajaxSubmit({
		beforeSend: function(options){
			var enable = [];
			$(this).find('input[data-plugin]').each(function(){
				if($(this).is(':checked')){
					enable.push($(this).data('plugin'));
				}
			});
			options.data = $.extend({}, {'mod.plugins.enable': enable.join('|')});
			if(WS.source){
				WS.bind('mod.config', options.success);
				WS.send($.extend({obj: 'mod', act: 'config'}, options.data));
				return false;
			}
			return options;
		},
		success: function(result){
			if(result.success){
				alert(Lang.changedTip);
				location.reload();
			}else{
				alert(result.data);
			}
		},
		error: function(xhr){
			console.log(xhr);
			alert(Lang.serverConnectionError);
		}
	}).find('input[type="checkbox"]').change(function(){
		if(confirm(Lang.changeSiteFuncWarning)){
			$(this).closest('form').submit();
		}else{
			$(this).closest('form')[0].reset();
		}
	});

	/** 删除模板和插件 */
	$('form').find('a.delete').click(function(){
		var $this = $(this),
			type = $this.data('type'),
			selector = type == 'template' ? '.block' : '.media';
		if(!confirm(Lang.deleteWarning)) return false;
		var submitSuccess = function(result){
			alert(result.data);
			if(result.success){
				$this.closest(selector).remove();
			}
		};
		if(WS.source){
			WS.bind('file.removeFolder', submitSuccess);
			WS.send({obj: 'file', act: 'removeFolder', type: type, folder: $this.data('folder')});
		}else{
			$.ajax({
				url: SITE_URL+'mod.php?file::removeFolder|type:'+type+'|folder:'+$this.data('folder'), 
				success: submitSuccess,
				error: function(xhr){
					alert(Lang.serverConnectionError);
					console.log(xhr.responseText);
				}
			});
		}
	});

	/** 清除模板缓存 */
	$('a.remove-temp').click(function(){
		var $this = $(this),
			folder = $this.data('type') == 'template' ? 'template/' : 'plugins/',
			submitSuccess = function(result){
				alert(result.data);
				if(result.success){
					$this.remove();
				}
			};
		if(WS.source){
			WS.bind('file.removeTempFolder', submitSuccess);
			WS.send({obj: 'file', act: 'removeTempFolder', folder: folder+$this.data('folder')});
		}else{
			$.ajax({
				url: SITE_URL+'mod.php?file::removeTempFolder|folder:'+folder+$this.data('folder'), 
				success: submitSuccess,
				error: function(xhr){
					alert(Lang.serverConnectionError);
					console.log(xhr.responseText);
				}
			});
		}
	});

	/** 选择压缩包 */
	$('#upload-zip').click(function(){
		$('#jquery-upload-form').find(':file').click();
	});

	/** 上传压缩包 */
	$('#jquery-upload-form').ajaxSubmit({
		success: function(result){
			if(result.success){
				alert(Lang.uploadedTip);
				location.href = $('ul[role="tablist"]').find('a').eq(0).attr('href');
			}else{
				if(typeof result.data == 'string'){
					alert(result.data);
				}else{
					alert(result.data[0].error);
				}
			}
		},
		error: function(xhr){
			alert(Lang.serverConnectionError);
			console.log(xhr.responseText);
		}
	}).find(':file').change(function(){
		if($(this).val()){
			$(this).parent().submit();
		}
	});

	/** 检查更新 */
	$('.check-new').click(function(){
		var $this = $(this),
			submitSuccess = function(result){
				$this.button('reset').hide();
				if(result.success && version_compare(result.data.version, result.version) > 0){
					var html = Lang.newVersion+': <code>'+result.data.version+'</code>';
					if(result.data.url) html += '<a target="_blank" title="'+Lang.clickToVisit+'" href="'+result.data.url+'">'+Lang.viewUpdateLog+'</a>';
					$this.siblings('.new-version').html(html).show().siblings('.update').data('version', result.data).css('display', 'block');
				}else{
					$this.siblings('.new-version').html(Lang.noNewVersionTip).show();
				}
			},
			query = 'obj=mod&act=checkUpdate&type='+$this.data('type')+'&version='+$this.data('version')+'&versionURL='+encodeURIComponent($this.data('version-url'));
		$this.button('loading');
		if(WS.source){
			WS.bind('mod.checkUpdate', submitSuccess);
			WS.send(query.parseStr());
		}else{
			$.ajax({
				url: SITE_URL+'mod.php?'+query,
				success: submitSuccess,
				error: function(xhr){
					$this.button('reset').hide().siblings('.new-version').html(Lang.noNewVersionTip).show();
					console.log(xhr.responseText);
				}
			});
		}
	});

	/** 更新 */
	$('.update').click(function(){
		if(!confirm(Lang.changeSiteFuncWarning)) return false;
		var $this = $(this),
			data = $.extend($this.data('version'), {type: $this.data('type')}),
			submitSuccess = function(result){
				alert(result.data);
				$this.button('reset');
				if(result.success){
					if($this.data('type') == 'template'){
						storage('newTemplateVersion', storage('newTemplateVersion')-1 || null);
						$.showNews('features', '-1');
						$this.closest('.block').find('a.remove-temp').click();
					}else{
						storage('newPluginVersion', storage('newPluginVersion')-1 || null);
						$.showNews('plugins', '-1');
						$this.closest('.media').find('a.remove-temp').click();
					}
					location.reload();
				}
			};
		$this.button('loading');
		if(WS.source){
			WS.bind('mod.updateComponent', submitSuccess);
			WS.send($.extend({obj: 'mod', act: 'updateComponent'}, data));
		}else{
			$.ajax({
				url: SITE_URL+'mod.php?mod::updateComponent',
				type: 'post',
				data: data,
				success: submitSuccess,
				error: function(xhr){
					alert(Lang.serverConnectionError);
					console.log(xhr.responseText);
					$this.button('reset');
				}
			});
		}
	});

	/** 自动检测更新 */
	if(storage('newTemplateVersion') > 0){
		$('#select-template').find('button.check-new').click();
	}
	if(storage('newPluginVersion') > 0){
		$('#select-plugin').find('button.check-new').click();
	}

	/** 阻止禁止链接跳转 */
	$('.disabled').click(function(event){
		event.preventDefault();
	});

	/** 显示帮助信息 */
	$.showHelp(Lang.templateHelp);
});