<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
<?php 
foreach($css_files as $file): ?>
	<link type="text/css" rel="stylesheet" href="<?php echo $file; ?>" />
<?php endforeach; ?>
<?php foreach($js_files as $file): ?>
	<script src="<?php echo $file; ?>"></script>
<?php endforeach; ?>
<style type='text/css'>
body
{
	font-family: Arial;
	font-size: 14px;
}
a {
    color: blue;
    text-decoration: none;
    font-size: 14px;
}
a:hover
{
	text-decoration: underline;
}
</style>
</head>
<body>
	<div>
		<a href='<?php echo site_url('images_examples/example1')?>'>Example 1 - Simple</a> |
		<a href='<?php echo site_url('images_examples/example2')?>'>Example 2 - Ordering</a> |
		<a href='<?php echo site_url('images_examples/example3/22')?>'>Example 3 - With group id</a> |
		<a href='<?php echo site_url('images_examples/example4')?>'>Example 4 - Images with title</a> | 
		<a href='<?php echo site_url('images_examples/simple_photo_gallery')?>'>Simple Photo Gallery</a>
	</div>
	<div style='height:20px;'></div>  
    <div>
		<?php echo $output; ?>
    </div>
</body>
</html>
