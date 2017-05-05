$(function(){
	storage('ModAdminURL', null);

	$('#database-form').ajaxSubmit({
		beforeSend: function(){
			var form = $('#site-form');
			$(this).find('select, input').each(function(){
				var input = form.find('input[name="'+$(this).attr('name')+'"]');
				if(input.length){
					input.val($(this).val());
				}else{
					form.prepend('<input type="hidden" name="'+$(this).attr('name')+'" value="'+$(this).val()+'">');
				}
			});
			return false;
		}
	})

	$('#site-form').ajaxSubmit({
		beforeSend: function(options){
			$('#user-name-finish').text($('#user-name').val());
			$(this).find('button[type="submit"]').button('loading');
		},
		success: function(result, status, xhr){
			if(result.success){
				$('#install-site').hide();
				$('#install-finish').fadeIn('fast');
				$('.container').centralize();
			}else{
				alert(result.data);
			}
		},
		error: function(xhr){
			alert(Lang.serverConnectionError);
			console.log(xhr.responseText);
		},
		complete: function(){
			$('button[type="submit"]').button('reset');
		}
	});

	$('button[data-target]').not('.disabled').click(function(){
		$(this).closest('.install-container').hide();
		$('#'+$(this).data('target')).fadeIn('fast');
		$('.container').centralize();
	});

	/** 居中容器 */
	$('.container').centralize().show();
	$(window).resize(function(){
		$('.container').centralize();
	});

	/** 更新 MODCMS */
	$('#update-modcms').click(function(){
		var $this = $(this);
		$this.button('loading');
		$.ajax({
			url: SITE_URL+'mod.php?mod::updateCMS',
			type: 'post',
			data: Version,
			success: function(result){
				var tip = result.data;
				if(result.success){
					$.ajax({
						url: SITE_URL+'mod.php?file::removeTempFolder|folder:admin/template/', 
						success: function(result){
							alert(tip);
							location.reload();
						},
						error: function(xhr){
							console.log(xhr);
							alert(Lang.serverConnectionError);
						}
					});
				}
			},
			error: function(xhr){
				console.log(xhr);
				alert(Lang.serverConnectionError);
			},
			complete: function(){
				$this.button('reset');
			}
		});
	});

	/** 切换数据库类型 */
	$('#mod-database-type').change(function(){
		var target = $('#mod-database-host, #mod-database-port, #mod-database-username, #mod-database-password');
		if($(this).val() == 'sqlite'){
			$('i.glyphicon').hide();
			target.attr({'disabled': true}).each(function(){
				var $this = $(this);
				$this.attr({'data-value': $this.val(), 'data-placeholder': $this.attr('placeholder'), 'placeholder': Lang.dbSqliteTip}).val('');
			}).val('');
		}else{
			target.attr({'disabled': false}).each(function(){
				var $this = $(this);
				$this.attr({'placeholder': $this.attr('data-placeholder')}).val($this.attr('data-value'));
				if($this.attr('type') == 'password' && $this.val()){
					$('i.glyphicon-eye-open').show();
				}
			});
		}
	});

	timezone = jstz.determine().name();
	if(Timezone != timezone && !Installed){
		$.post(SITE_URL+'mod.php?mod::setTimezone', {timezone: timezone});
	}
});