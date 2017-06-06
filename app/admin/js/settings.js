$(function(){
	var settingsEditForm = $('#settings-edit-form'),
		menu = {},
		target = _$('#settings').next('.badge');
	menu[Lang.reset] = function(){
		settingsEditForm[0].reset();
		var target = $('#site-logo-preview');
		$('input+i.glyphicon').hide();
		var upload = $('#jquery-upload-form');
		if(upload.length) upload[0].reset();
		$.showMenu(null);
		if(target.length){
			var src = target.children('img').data('origin');
			target.children('img').attr('src', src);
			if(!src){
				target.hide();
			}
		}
	};
	menu[Lang.submitChange] = function(){
		if(confirm(Lang.submitSettingsWarning)){
			settingsEditForm.find(':submit').click();
		}
	};

	if(target.length){
		$('#list-group-item-update').append(target.prop('outerHTML'));
	}

	/** 提交设置 */
	settingsEditForm.ajaxSubmit({
		beforeSend: function(options){
			var img = $('#site-logo-preview>img');
			if(img.length && img.attr('src') != img.data('origin') && !img.data('uploaded')){
				$('#jquery-upload-form').submit();
				return false;
			}
			if(WS.source){
				WS.bind('mod.config', options.success);
				WS.send($.extend({obj: 'mod', act: 'config'}, options.data));
				return false;
			}
		},
		success: function(result){
			if(result.success){
				alert(Lang.updatedTip);
				var lang = $('#mod-lang');
				if(lang.length && lang.val() != lang.data('origin')){
					_window.location.reload();
				}else{
					location.reload();
				}
			}else{
				alert(result.data);
			}
		},
		error: function(xhr){
			console.log(xhr);
			alert(Lang.serverConnectionError);
		}
	}).find('select, :checkbox, :radio').change(function(){
		$.showMenu(menu);
	});
	settingsEditForm.find('input, textarea').on('input', function(){
		$.showMenu(menu);
	});

	/** 选择文件 */
	$('#upload-logo').click(function(){
		$('#jquery-upload-form').find(':file').click();
	});

	/** 上传网站 LOGO */
	$('#jquery-upload-form').ajaxSubmit({
		success: function(result){
			if(result.success){
				settingsEditForm.find('input[name="site.logo"]').val(result.data[0].file_src);
				$('#site-logo-preview>img').data('uploaded', true);
				settingsEditForm.submit();
			}else{
				if(typeof result.data == 'string'){
					alert(result.data);
				}else{
					alert(result.data[0].error);
				}
			}
			$('#site-logo-preview>img').data('uploaded', true);
		},
		error: function(xhr){
			alert(Lang.serverConnectionError);
			console.log(xhr.responseText);
		}
	}).find(':file').previewImage('#site-logo-preview>img', function(){
		$('#site-logo-preview').show();
		if($(this).val()){
			$.showMenu(menu);
		}
	});
	
	/** 检查 CMS/ModPHP 更新 */
	$('#check-update, #check-modphp-update').click(function(){
		var $this = $(this),
			id = $this.attr('id'),
			isCms = id == 'check-update',
			type = isCms ? 'modcms' : 'modphp',
			version = isCms ? CMS_VERSION : MOD_VERSION,
			versionURL = isCms ? VERSION_URLS['modcms'] : VERSION_URLS['modphp'],
			newVersion = isCms ? $('#new-version') : $('#new-modphp-version'),
			updateVersion = isCms ? $('#update-new-version') : $('#update-new-modphp-version'),
			query = 'obj=mod&act=checkUpdate&type='+type+'&version='+version+'&versionURL='+encodeURIComponent(versionURL),
			submitSuccess = function(result){
				$this.hide().button('reset');
				if(version_compare(result.data.version, result.version) > 0){
					newVersion.html(Lang.newVersionTip+': <code>'+result.data.version+'</code> <a href="'+result.data.url+'" target="_blank">'+Lang.viewUpdateLog+'</a>').show();
					updateVersion.data('version', result.data).show();
				}else{
					newVersion.html(Lang.noNewVersionTip).show();
				}
			};
		$this.button('loading');
		if(WS.source){
			WS.bind('mod.checkUpdate', submitSuccess);
			WS.send(query.parseStr());
		}else{
			$.ajax({
				url: SITE_URL+'mod.php?'+query,
				success: submitSuccess,
				error: function(){
					$this.hide().button('reset');
					newVersion.html(Lang.noNewVersionTip).show();
				}
			});
		}
	});

	/** 更新 CMS/ModPHP/数据库 */
	$('#update-new-version, #update-new-modphp-version, #update-db').not('.disabled').click(function(){
		if(!confirm(Lang.changeSiteFuncWarning)) return false;
		var $this = $(this),
			id = $this.attr('id'),
			isCms = id == 'update-new-version',
			version = $this.data('version'),
			method = isCms ? 'updateCMS' : 'update',
			storageKey = isCms ? 'newCMSVersion' : 'newMODVersion',
			upgrade = id == 'update-new-modphp-version',
			submitSuccess = function(result){
				alert(result.data);
				$this.button('reset');
				if(result.success){
					$.showNews('settings', '-1');
					if(upgrade || storageKey == 'newCMSVersion'){
						storage(storageKey, null);
					}
					if($('#clear-temp').length){
						$('#clear-temp').click();
					}else{
						location.reload();
					}
				}
			};
		if(upgrade){
			version['upgrade'] = true;
		}
		$this.button('loading');
		if(WS.source){
			WS.bind('mod.'+method, submitSuccess);
			WS.send($.extend({obj: 'mod', act: method}, version));
		}else{
			$.ajax({
				url: SITE_URL+'mod.php?mod::'+method,
				type: 'post',
				data: version,
				success: submitSuccess,
				error: function(xhr){
					$this.button('reset');
					console.log(xhr);
					alert(Lang.serverConnectionError);
				}
			});
		}
	});

	/** 清除缓存 */
	$('#clear-temp').click(function(){
		var submitSuccess = function(result){
			alert(result.data);
			_window.location.reload();
		};
		if(WS.source){
			WS.bind('file.removeTempFolder', submitSuccess);
			WS.send({obj: 'file', act: 'removeTempFolder', folder: 'admin/'});
		}else{
			$.ajax({
				url: SITE_URL+'mod.php?file::removeTempFolder|folder:admin/', 
				success: submitSuccess,
				error: function(xhr){
					console.log(xhr);
					alert(Lang.serverConnectionError);
				}
			});
		}
	});

	/** 检测数据库更新 */
	var checkDbUpdateSubmitSuccess = function(result){
		$('#check-db-update').hide();
		$('#new-db').text(result.data).show();
		if(result.success){
			$('#update-db').show();
		}
	};
	$('#check-db-update').click(function(){
		if(WS.source){
			WS.bind('mod.checkDbUpdate', checkDbUpdateSubmitSuccess);
			WS.send({obj: 'mod', act: 'checkDbUpdate'});
		}else{
			$.ajax({
				url: SITE_URL+'mod.php?mod::checkDbUpdate', 
				success: checkDbUpdateSubmitSuccess,
				error: function(){
					$('#new-db').text(Lang.serverConnectionError);
				}
			});
		}
	});
	if(autoCheckUpdate){
		$('#check-db-update').click();
	}
	
	/** 检测CMS/ModPHP 更新 */
	if(storage('newCMSVersion')){
		$('#check-update').click();
	}
	if(storage('newMODVersion')){
		$('#check-modphp-update').click();
	}

	/** 开启 Socket 服务器 */
	$('#start-socket-server').click(function(){
		if($(this).is('.disabled') || !confirm(Lang.startSocketWarning)) return false;
		var $this = $(this);
		$this.button('loading');
		$.ajax({
			url: SITE_URL+'socket-server.php',
			timeout: 2000,
			complete: function(xhr){
				if(xhr.statusText == 'timeout'){
					alert(Lang.socketStartedTip);
					$this.hide().next('span').show();
				}else{
					alert(Lang.serverConnectionError);
					console.log(xhr.responseText);
				}
				$this.button('reset');
			}
		})
	});

	/** 切换数据库类型 */
	$('#mod-database-type').change(function(){
		var target = $('#mod-database-host, #mod-database-port, #mod-database-username, #mod-database-password');
		if($(this).val() == 'sqlite'){
			$('i.glyphicon').hide();
			target.attr({'disabled': true}).each(function(){
				var $this = $(this);
				$this.data({'value': $this.val(), 'placeholder': $this.attr('placeholder')}).attr({'placeholder': Lang.dbSqliteTip}).val('');
			}).val('');
		}else{
			target.attr({'disabled': false}).each(function(){
				var $this = $(this);
				if($this.data('value'))
					$this.attr({'placeholder': $this.data('placeholder')}).val($this.data('value'));
				if($this.attr('type') == 'password' && $this.val()){
					$('i.glyphicon-eye-open').show();
				}
			});
		}
	}).trigger('change');
	$.showMenu(null);
});