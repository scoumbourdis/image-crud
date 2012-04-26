<?php 
	$this->set_js('assets/image_crud/js/jquery-1.7.1.min.js');
	$this->set_js('assets/image_crud/js/fileuploader.js');
	$this->set_js('assets/image_crud/js/jquery-ui-1.8.13.custom.min.js');
	$this->set_css('assets/image_crud/css/fileuploader.css');
	$this->set_css('assets/image_crud/css/photogallery.css');
	$this->set_css('assets/image_crud/css/ui/jquery-ui-1.8.13.custom.css');
?>
<script type='text/javascript'>
$(function(){
    createUploader();  
});
function createUploader(){            
    var uploader = new qq.FileUploader({
        element: document.getElementById('file-uploader-demo1'),
        action: '<?=$upload_url?>',
        debug: true,
        onComplete: function(id, fileName, responseJSON){
			$.ajax({
				url: '<?=$ajax_list_url?>',
				dataType: 'text',
				success: function(data){
					$('#ajax-list').html(data);
				}
			});
        }
    });           
}
</script>
<div id="file-uploader-demo1"></div>
<div id='ajax-list'>
	<?php if(!empty($photos)){?>
	<script type='text/javascript'>
		$(function(){
			$('.delete-anchor').click(function(){
				return confirm('Are you sure that you want to delete this image?');
			});

    		$(".photos-crud").sortable({ opacity: 0.6, cursor: 'move', revert: true,  update: function() {
    			var order = $(this).sortable("serialize");
	    			$.post("<?=$ordering_url?>", order, function(theResponse){});
    			}									  
    		});			
		});
	</script>
	<ul class='photos-crud'>
	<?php foreach($photos as $photo){?>
			<li id="photos_<?php echo $photo->$primary_key; ?>">
				<div class='photo-box'>
					<a href='<?=$photo->image_url?>' target='_blank'><img src='<?=$photo->thumbnail_url?>' width='90' height='60' class='basic-image' /></a>
					<div class='delete-box'><a href='<?=$photo->delete_url?>' class='delete-anchor'>Delete</a></div>
				</div>
			</li> 
	<?php }?>
		</ul>
		<div class='clear'></div>
	<?php }?>
</div>