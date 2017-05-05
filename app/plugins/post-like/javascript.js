$(function(){
	/** 点赞 */
	$('.post-like').on('click', function(){
		$this = $(this);
		if($this.is('.disabled')) return false;
		$.ajax({
			url: SITE_URL+'mod.php?post::like|post_id:'+$this.attr('data-id'),
			success: function(result){
				if(result.success){
					$this.addClass('disabled').html('<i class="fa fa-thumbs-o-up"></i> 赞(' + result.data.post_likes + ')');
				}else{
					alert(result.data);
				}
			},
			error: function(result){
				alert(result.responseText);
			}
		});			
	});
});