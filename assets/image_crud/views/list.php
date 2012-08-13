<?php 
	$this->set_js('assets/image_crud/js/jquery-1.7.1.min.js');
	$this->set_js('assets/image_crud/js/fileuploader.js');
	$this->set_js('assets/image_crud/js/jquery-ui-1.8.13.custom.min.js');
	$this->set_js('assets/image_crud/js//jquery.fancybox-1.3.4.pack.js');
	$this->set_js('assets/image_crud/js/jquery.easing-1.3.pack.js');
	$this->set_js('assets/image_crud/js/jquery.mousewheel-3.0.4.pack.js');
	
	$this->set_css('assets/image_crud/css/fileuploader.css');
	$this->set_css('assets/image_crud/css/photogallery.css');
	$this->set_css('assets/image_crud/css/ui/jquery-ui-1.8.13.custom.css');
	$this->set_css('assets/image_crud/css/jquery.fancybox-1.3.4.css');
?>
<script type='text/javascript'>
$(function(){
    createUploader();
    loadFancybox();
});
function loadFancybox()
{
	$('.fancybox').fancybox({
		'transitionIn'	:	'elastic',
		'transitionOut'	:	'elastic',
		'speedIn'		:	600, 
		'speedOut'		:	200, 
		'overlayShow'	:	false
	});	
}
function loadPhotoGallery(){
	$.ajax({
		url: '<?=$ajax_list_url?>',
		dataType: 'text',
		beforeSend: function()
		{
			$('.file-upload-messages-container:first').show();
			$('.file-upload-message').html('Loading...');
		},
		complete: function()
		{
			$('.file-upload-messages-container').hide();
			$('.file-upload-message').html('');
		},
		success: function(data){
			$('#ajax-list').html(data);
			loadFancybox();
		}
	});
}
function createUploader(){            
    var uploader = new qq.FileUploader({
        element: document.getElementById('file-uploader-demo1'),
        action: '<?=$upload_url?>',
        debug: true,
        onComplete: function(id, fileName, responseJSON){
        	loadPhotoGallery();
        }
    });           
}
function saveTitle(data_id, data_title)
{
	  	$.ajax({
			url: '<?php echo $insert_title_url; ?>',
			type: 'post',
			data: {primary_key: data_id, value: data_title},
			beforeSend: function()
			{
				$('.file-upload-messages-container:first').show();
				$('.file-upload-message').html('Saving data...');
			},
			complete: function()
			{
				$('.file-upload-messages-container').hide();
				$('.file-upload-message').html('');
			}
			});
}
</script>
<div id="file-uploader-demo1" class="floatL upload-button-container"></div>
<div class="file-upload-messages-container hidden">
	<div class="message-loading"></div>
	<div class="file-upload-message"></div>
	<div class="clear"></div>
</div>
<div class="clear"></div>
<div id='ajax-list'>
	<?php if(!empty($photos)){?>
	<script type='text/javascript'>
		$(function(){
			$('.delete-anchor').click(function(){
				if(confirm('Are you sure that you want to delete this image?'))
				{
					$.ajax({
						url:$(this).attr('href'),
						beforeSend: function()
						{
							$('.file-upload-messages-container:first').show();
							$('.file-upload-message').html('Deleting, please wait...');
						},				
						success: function(){
							loadPhotoGallery();
						}
					});
				}
				return false;
			});
			$(".fancybox img").mousedown(function(){
				return false;
			});
    		$(".photos-crud").sortable({
        		handle: '.move-box', 
        		opacity: 0.6, 
        		cursor: 'move', 
        		revert: true,  
        		update: function() {
    				var order = $(this).sortable("serialize");
	    				$.post("<?=$ordering_url?>", order, function(theResponse){});
    			}									  
    		});
    		$('.ic-title-field').keyup(function(e) {
    			if(e.keyCode == 13) {
					var data_id = $(this).attr('data-id');
					var data_title = $(this).val();
      			  	
					saveTitle(data_id, data_title);
    			}
    		});
    		
    		$('.ic-title-field').bind({
    			  click: function() {
    				$(this).css('resize','both');
    			    $(this).css('overflow','auto');
    			    $(this).animate({height:80},600);
    			  },
    			  blur: function() {
      			    $(this).css('resize','none');
      			  	$(this).css('overflow','hidden');
      			  	$(this).animate({height:20},600);

					var data_id = $(this).attr('data-id');
					var data_title = $(this).val();
      			  	
					saveTitle(data_id, data_title);
    			  }
    		});
		});
	</script>
	<ul class='photos-crud'>
	<?php foreach($photos as $photo_num => $photo){?>
			<li id="photos_<?php echo $photo->$primary_key; ?>">
				<div class='photo-box'>
					<a href='<?=$photo->image_url?>' target='_blank' class="fancybox" rel="fancybox" tabindex="-1"><img src='<?=$photo->thumbnail_url?>' width='90' height='60' class='basic-image' /></a>
					<?php if($title_field !== null){ ?>
					<textarea class="ic-title-field" data-id="<?php echo $photo->$primary_key; ?>"><?php echo $photo->$title_field; ?></textarea>
					<div class="clear"></div><?php }?>
					<?php if($has_priority_field){?><div class="move-box"></div><?php }?>
					<div class='delete-box'><a href='<?=$photo->delete_url?>' class='delete-anchor' tabindex="-1">Delete</a></div>
					<div class="clear"></div>
				</div>
			</li> 
	<?php }?>
		</ul>
		<div class='clear'></div>
	<?php }?>
</div>