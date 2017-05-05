$(function(){
	if(!$_GET['page']) $_GET['page'] = 1;
	if(!$_GET['limit']) $_GET['limit'] = 0;
	WS = _window.WS;
	Table = $('#blacklist-list-div>table');
	Tablist = $('ul[role="tablist"]');
	$.getBlacklist = function(arg){
		var url = SITE_URL+'mod.php?blacklist::',
			submitSuccess = function(result){
				var data = result.data,
					th = '<th>#</th>',
					tr = tr2 = '',
					i = 1 + ($_GET['page'] - 1) * ($_GET['limit'] || 10);
				if(!arg['blacklist']){
					th += '<th>'+Lang.user+'</th><th>'+Lang.emailAddress+'</th><th>'+Lang.ipAddress+'</th>';
				}else if(arg['blacklist'] == 'uid'){
					th += '<th>'+Lang.username+'/'+Lang.nickname+'</th>'
				}else if(arg['blacklist'] == 'email'){
					th += '<th>'+Lang.emailAddress+'</th>';
				}else if(arg['blacklist'] == 'ip'){
					th += '<th>'+Lang.ipAddress+'</th>';
				}
				th += '<th>'+Lang.action+'</th>';
				if(arg['blacklist'] == 'email' || arg['blacklist'] == 'ip'){
					var email = arg['blacklist'] == 'email';
					tr2 += '<tr><td><label for="new">'+Lang.add+'</td><td><input id="new" type="'+(email ? 'email' : 'text')+'" name="blacklist_'+arg['blacklist']+'" placeholder="'+(email ? Lang.emailAddressTip : ' '+Lang.ipAddressTip)+'"/></td><td><span title="'+Lang.addTip+'" class="add"><i class="glyphicon glyphicon-plus"></i></span></td></tr>';
				}
				if(result.success){
					for(var x in data){
						tr += '<tr data-id="'+data[x]['blacklist_id']+'"><td>'+i+'</td>';
						if(!arg['blacklist']){
							tr += '<td>';
							if(data[x]['blacklist_user']){
								tr += '<a href="'+ADMIN_URL+'users.html?action=update&user_id='+data[x]['blacklist_uid']+'">'+data[x]['blacklist_user']+'</a>';
							}
							tr += '</td><td>'+data[x]['blacklist_email']+'</td><td>'+data[x]['blacklist_ip']+'</td>';
						}else if(arg['blacklist'] == 'uid'){
							tr += '<td><a href="'+ADMIN_URL+'users.html?action=update&user_id='+data[x]['blacklist_uid']+'">'+data[x]['blacklist_user']+'</a></td>';
						}else if(arg['blacklist'] == 'email'){
							tr += '<td>'+data[x]['blacklist_email']+'</td>';
						}else if(arg['blacklist'] == 'ip'){
							tr += '<td>'+data[x]['blacklist_ip']+'</td>';
						}
						tr += '<td><span title="'+Lang.deleteTip+'" class="delete"><i class="glyphicon glyphicon-remove"></i></span></td></tr>';
						i++;
					}
				}
				Table.html('<thead><tr>'+th+'</tr></thead><tbody>'+tr+tr2+'</tbody>').find('input[name]').focus();
				var target = Tablist.find('li.active').find('a'),
					text = target.text().replace(/\(.*\)/, '('+(result.total || 0)+')');
				target.text(text);
			};
		if(!arg['blacklist']){
			url += act= 'getMulti';
		}else if(arg['blacklist'] == 'uid'){
			url += act = 'getUsers';
		}else if(arg['blacklist'] == 'email'){
			url += act = 'getEmails';
		}else if(arg['blacklist'] == 'ip'){
			url += act = 'getIps';
		}
		if(WS.source){
			WS.bind(['blacklist.getMulti', 'blacklist.getUsers', 'blacklist.getEmails', 'blacklist.getIps'], submitSuccess);
			WS.send({obj: 'blacklist', act: act});
		}else{
			$.ajax({
				url: url,
				success: submitSuccess,
				error: function(xhr){
					alert(Lang.serverConnetionError);
					console.log(xhr.responseText);
				}
			});
		}
	}
	Tablist.find('a').click(function(event){
		event.preventDefault();
		$(this).parent('li').addClass('active').siblings('li').removeClass('active');
		var arg = {'blacklist': $(this).data('blacklist')};
		$_GET['page'] = 1;
		$.extend(arg, $_GET);
		$.getBlacklist(arg);
	});
	if(!$_GET['blacklist']){
		Tablist.find('a').eq(0).click();
	}
	Table.on('click', 'tbody span.delete', function(event){
		if(confirm(Lang.deleteWarning)){
			var target = $(this).closest('tr'),
				submitSuccess = function(result){
					alert(result.data);
					if(result.success){
						Tablist.find('li.active').find('a').click();
					}
				};
			if(WS.source){
				WS.bind('blacklist.delete', submitSuccess);
				WS.send({obj: 'blacklist', act: 'delete', blacklist_id: target.data('id')});
			}else{
				$.ajax({
					url: SITE_URL+'mod.php?blacklist::delete|blacklist_id:'+target.data('id'),
					success: submitSuccess,
					error: function(xhr){
						alert(Lang.serverConnetionError);
						console.log(xhr.responseText);
					}
				});
			}
		}
	});
	Table.on('click', 'tbody span.add', function(event){
		var target = $(this).closest('tr').find('input[name]'),
			value = target.val(),
			key = target.attr('name');
		if(key == 'blacklist_email' && !value.match(/[\S]+\@[\S]+\.[\S]+/)){
			alert(Lang.illegalEmailWarnig);
			return false;
		}else if(key == 'blacklist_ip' && !value.match(/[0-9]+.[0-9]+.[0-9]+.[0-9]+/)){
			alert(Lang.illegalIpWarnig);
			return false;
		}
		data = {};
		data[key] = value;
		var submitSuccess = function(result){
			if(result.success){
				alert(Lang.addedTip);
				Tablist.find('li.active').find('a').click();
			}else{
				alert(result.data);
			}
		};
		if(WS.source){
			WS.bind('blacklist.add', submitSuccess);
			WS.send($.extend({obj: 'blacklist', act: 'add'}, data));
		}else{
			$.ajax({
				url: SITE_URL+'mod.php?blacklist::add',
				type: 'post',
				data: data,
				success: submitSuccess,
				error: function(xhr){
					alert(Lang.serverConnetionError);
					console.log(xhr.responseText);
				}
			});
		}
	});

	/** 显示帮助信息 */
	$.showHelp(Lang.blacklistHelp);
})