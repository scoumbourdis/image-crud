<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/*
 * Image_CRUD
 *
 * @author		John Skoumbourdis
 *
 * Copyright (c) 2012 John Skoumbourdis
 */

class Image_CRUD {
	
	protected $table_name = null;
	protected $priority_field = null;
	protected $url_field = 'url';
	protected $relation_field = null;
	protected $subject = 'Record';
	protected $image_path = '';
	protected $primary_key = 'id';
	protected $ci = null;
	protected $thumbnail_prefix = 'thumb__';
	protected $views_as_string = '';
	protected $css_files = array();
	protected $js_files = array();
	
	/**
	 * 
	 * @var Image_moo
	 */
	private $image_moo = null;
	
	function __construct() {
		$this->ci = &get_instance();
	}
	
	function set_table($table_name)
	{
		$this->table_name = $table_name;
		
		return $this;
	}
	
	function set_relation_field($field_name)
	{
		$this->relation_field = $field_name;
		
		return $this;
	}
	
	function set_ordering_field($field_name)
	{
		$this->priority_field = $field_name;
		
		return $this;
	}
	
	function set_primary_key_field($field_name)
	{
		$this->primary_key = $field_name;
	}
	
	function set_subject($subject)
	{
		$this->subject = $subject;
		
		return $this;
	}
	
	function set_url_field($url_field)
	{
		$this->url_field = $url_field;
		
		return $this;
	}
	
	function set_image_path($image_path)
	{
		$this->image_path = $image_path;
		
		return $this;
	}
	
	function set_thumbnail_prefix($prefix)
	{
		$this->thumbnail_prefix = $prefix;
		
		return $this;
	}
	
	public function set_css($css_file)
	{
		$this->css_files[sha1($css_file)] = base_url().$css_file;
	}
	
	public function set_js($js_file)
	{
		$this->js_files[sha1($js_file)] = base_url().$js_file;
	}	
	
	protected function _library_view($view, $vars = array(), $return = FALSE)
	{
		$vars = (is_object($vars)) ? get_object_vars($vars) : $vars;
	
		$file_exists = FALSE;
	
		$ext = pathinfo($view, PATHINFO_EXTENSION);
		$file = ($ext == '') ? $view.'.php' : $view;
	
		$view_file = 'assets/image_crud/views/';
	
		if (file_exists($view_file.$file))
		{
			$path = $view_file.$file;
			$file_exists = TRUE;
		}
	
		if ( ! $file_exists)
		{
			throw new Exception('Unable to load the requested file: '.$file, 16);
		}
	
		extract($vars);
	
		#region buffering...
		ob_start();
	
		include($path);
	
		$buffer = ob_get_contents();
		@ob_end_clean();
		#endregion
	
		if ($return === TRUE)
		{
		return $buffer;
		}
	
		$this->views_as_string .= $buffer;
	}	
	
	public function get_css_files()
	{
		return $this->css_files;
	}
	
	public function get_js_files()
	{
		return $this->js_files;
	}
	
	protected function get_layout()
	{
		$js_files = $this->get_js_files();
		$css_files =  $this->get_css_files();
	
		return (object)array('output' => $this->views_as_string, 'js_files' => $js_files, 'css_files' => $css_files);

	}	
	
	protected function _upload_file($path) {  
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);

        /* Resizing to 800 x 600 if its required */
        	list($width, $height) = getimagesize($path);
        	if($width > 800 || $height > 600)
        	{
        		$this->image_moo->load($path)->resize(800,600)->save($path,true);
        	}
        /* ------------------------------------- */
        
        return true;
    }
	
    protected function _changing_priority($post_array)
    {
    	$counter = 1;
		foreach($post_array as $photo_id)
		{
			$this->ci->db->update($this->table_name, array($this->priority_field => $counter), array($this->primary_key => $photo_id));
			$counter++;
		}
    }    
    
    protected function _insert_table($file_name, $relation_id = null)
    {
    	$insert = array($this->url_field => $file_name);
    	if(!empty($relation_id))
    		$insert[$this->relation_field] = $relation_id;
    	$this->ci->db->insert($this->table_name, $insert);
    }
    
    protected function _delete_file($id)
    {
    	$this->ci->db->where($this->primary_key,$id);
    	$result = $this->ci->db->get($this->table_name)->row();
    	
    	unlink( $this->image_path.'/'.$result->{$this->url_field} );
    	unlink( $this->image_path.'/'.$this->thumbnail_prefix.$result->{$this->url_field} );
    	
    	$this->ci->db->delete($this->table_name, array($this->primary_key => $id));
    }    
    
    protected function _get_delete_url($value)
    {
    	$rsegments_array = $this->ci->uri->rsegment_array();
    	return site_url($rsegments_array[1].'/'.$rsegments_array[2].'/delete_file/'.$value);
    }
    
    protected function _get_photos($relation_value = null)
    {
    	if(!empty($this->priority_field))
    	{
    		$this->ci->db->order_by($this->priority_field);
    	}
    	if(!empty($relation_value))
    	{
    		$this->ci->db->where($this->relation_field, $relation_value);
    	}
    	$results = $this->ci->db->get($this->table_name)->result();
    	
    	$thumbnail_url = !empty($this->thumbnail_path) ? $this->thumbnail_path : $this->image_path;
    	
    	foreach($results as $num => $row)
    	{
			if (!file_exists($this->image_path.'/'.$this->thumbnail_prefix.$row->{$this->url_field})) {
				$this->_create_thumbnail($this->image_path.'/'.$row->{$this->url_field}, $this->image_path.'/'.$this->thumbnail_prefix.$row->{$this->url_field});
			}		
		
    		$results[$num]->image_url = base_url().$this->image_path.'/'.$row->{$this->url_field};
    		$results[$num]->thumbnail_url = base_url().$this->image_path.'/'.$this->thumbnail_prefix.$row->{$this->url_field};
    		$results[$num]->delete_url = $this->_get_delete_url($row->{$this->primary_key});
    	}
    	
    	return $results;
    }
    
    protected function _to_greeklish($str_i) {
  
	  $g_c = array("α","ά","β","γ","δ","ε","έ","ζ","η","ή","θ","ι","ί","κ","λ","μ","ν","ξ","ο","ό","π","ρ","σ","ς","τ","υ","ύ","φ","χ","ψ","ω","ώ","Α","Ά","Β","Γ","Δ","Ε","Έ","Ζ","Η","Ή","Θ","Ι","Ί","Κ","Λ","Μ","Ν","Ξ","Ο","Ό","Π","Ρ","Σ","Τ","Υ","Ύ","Φ","Χ","Ψ","Ω","Ώ");
	  $e_c = array("a","a","b","g","d","e","e","z","i","i","th","i","i","k","l","m","n","ks","o","o","p","r","s","s","t","u","u","f","x","y","w","w","A","A","B","G","D","E","E","Z","I","I","TH","I","I","K","L","M","N","KS","O","O","P","R","S","T","Y","Y","F","X","Y","W","W");
	  
	  for($i=0;$i<count($g_c);$i++){
	      $str_i = str_replace($g_c[$i], $e_c[$i], $str_i);
	  }
	  
	  return $str_i;
	}
    
	protected function _create_thumbnail($image_path, $thumbnail_path)
	{
		$this->image_moo
			->load($image_path)
			->resize_crop(90,60)
			->save($thumbnail_path,true);
	}
	
	protected function getState()
	{
		$rsegments_array = $this->ci->uri->rsegment_array();
		
		if(isset($rsegments_array[3]) && is_numeric($rsegments_array[3]))
		{
			$upload_url = site_url($rsegments_array[1].'/'.$rsegments_array[2].'/upload_file/'.$rsegments_array[3]);
			$ajax_list_url  = site_url($rsegments_array[1].'/'.$rsegments_array[2].'/'.$rsegments_array[3].'/ajax_list');
			$ordering_url  = site_url($rsegments_array[1].'/'.$rsegments_array[2].'/ordering');
			
			$state = array( 'name' => 'list', 'upload_url' => $upload_url, 'relation_value' => $rsegments_array[3]);
			$state['ajax'] = isset($rsegments_array[4]) && $rsegments_array[4] == 'ajax_list'  ? true : false;
			$state['ajax_list_url'] = $ajax_list_url;
			$state['ordering_url'] = $ordering_url;
			
			return (object)$state;
		}
		elseif( (empty($rsegments_array[3]) && empty($this->relation_field)) || (!empty($rsegments_array[3]) &&  $rsegments_array[3] == 'ajax_list'))
		{
			$upload_url = site_url($rsegments_array[1].'/'.$rsegments_array[2].'/upload_file');
			$ajax_list_url  = site_url($rsegments_array[1].'/'.$rsegments_array[2].'/ajax_list');
			$ordering_url  = site_url($rsegments_array[1].'/'.$rsegments_array[2].'/ordering');
			
			$state = array( 'name' => 'list', 'upload_url' => $upload_url);
			$state['ajax'] = isset($rsegments_array[3]) && $rsegments_array[3] == 'ajax_list'  ? true : false;
			$state['ajax_list_url'] = $ajax_list_url;
			$state['ordering_url'] = $ordering_url;
			
			return (object)$state;
		}
		elseif(isset($rsegments_array[3]) && $rsegments_array[3] == 'upload_file')
		{
			#region Just rename my file
				$new_file_name = '';
				$old_file_name = $this->_to_greeklish($_GET['qqfile']);
				$max = strlen($old_file_name);
				for($i=0; $i< $max;$i++)
				{
					$numMatches = preg_match('/^[A-Za-z0-9.-_]+$/', $old_file_name[$i], $matches);
					if($numMatches >0)
					{
						$new_file_name .= strtolower($old_file_name[$i]);
					}
					else
					{
						$new_file_name .= '-';	
					}
				}
				$file_name = substr( substr( uniqid(), 9,13).'-'.$new_file_name , 0, 100) ;
			#endregion
			
			$results = array( 'name' => 'upload_file', 'file_name' => $file_name);
			if(isset($rsegments_array[4]) && is_numeric($rsegments_array[4]))
			{
				$results['relation_value'] = $rsegments_array[4];
			}
			return (object)$results;
		}
		elseif(isset($rsegments_array[3]) && isset($rsegments_array[4]) && $rsegments_array[3] == 'delete_file' && is_numeric($rsegments_array[4]))
		{
			$state = array( 'name' => 'delete_file', 'id' => $rsegments_array[4]);
			return (object)$state;
		}
		elseif(isset($rsegments_array[3]) && $rsegments_array[3] == 'ordering')
		{
			$state = array( 'name' => 'ordering');
			return (object)$state;			
		}
	}    
    
	function render()
	{
		$ci = &get_instance();
		$ci->load->library('Image_moo');
		$this->image_moo = new Image_moo();
		
		$state_info = $this->getState();
		
		if(!empty($state_info))
		{
			switch ($state_info->name) {
				case 'list':
					$photos = isset($state_info->relation_value) ? $this->_get_photos($state_info->relation_value) : $this->_get_photos();
					$this->_library_view('list.php',array(
						'upload_url' => $state_info->upload_url, 
						'photos' => $photos, 
						'ajax_list_url' => $state_info->ajax_list_url,
						'ordering_url' => $state_info->ordering_url,
						'primary_key' => $this->primary_key
					));
					
					if($state_info->ajax === true)
					{	
						echo $this->get_layout()->output;
						die();
					}
					return $this->get_layout();
				break;
				
				case 'upload_file':
					$file_name = $state_info->file_name;
					$this->_upload_file( $this->image_path.'/'.$file_name );
					$this->_create_thumbnail( $this->image_path.'/'.$file_name , $this->image_path.'/'.$this->thumbnail_prefix.$file_name );
					$this->_insert_table($file_name, $state_info->relation_value);
					echo json_encode((object)array('success' => true));
					
					die();
				break;	

				case 'delete_file':
					$id = $state_info->id;
					
					$this->_delete_file($id);
					
					redirect($_SERVER['HTTP_REFERER']);
				break;
				
				case 'ordering':
					$this->_changing_priority($_POST['photos']);
				break;
			}
		}
		
	}
	
}



/*
 * jQuery File Upload Plugin PHP Example 5.5
* https://github.com/blueimp/jQuery-File-Upload
*
* Copyright 2010, Sebastian Tschan
* https://blueimp.net
*
* Licensed under the MIT license:
* http://www.opensource.org/licenses/MIT
*/

class UploadHandler
{
	private $options;

	function __construct($options=null) {
		$this->options = array(
				'script_url' => $this->getFullUrl().'/'.basename(__FILE__),
				'upload_dir' => dirname(__FILE__).'/files/',
				'upload_url' => $this->getFullUrl().'/files/',
				'param_name' => 'files',
				// The php.ini settings upload_max_filesize and post_max_size
		// take precedence over the following max_file_size setting:
				'max_file_size' => null,
				'min_file_size' => 1,
				'accept_file_types' => '/.+$/i',
				'max_number_of_files' => null,
				// Set the following option to false to enable non-multipart uploads:
				'discard_aborted_uploads' => true,
				// Set to true to rotate images based on EXIF meta data, if available:
				'orient_image' => false,
				'image_versions' => array(
				// Uncomment the following version to restrict the size of
		// uploaded images. You can also add additional versions with
		// their own upload directories:
		/*
		 'large' => array(
		 		'upload_dir' => dirname(__FILE__).'/files/',
		 		'upload_url' => dirname($_SERVER['PHP_SELF']).'/files/',
		 		'max_width' => 1920,
		 		'max_height' => 1200
		 ),

		'thumbnail' => array(
				'upload_dir' => dirname(__FILE__).'/thumbnails/',
				'upload_url' => $this->getFullUrl().'/thumbnails/',
				'max_width' => 80,
				'max_height' => 80
		)
		*/
				)
		);
		if ($options) {
			// Or else for PHP >= 5.3.0 use: $this->options = array_replace_recursive($this->options, $options);
			foreach($options as $option_name => $option)
			{
				$this->options[$option_name] = $option;
			}
		}
	}

	function getFullUrl() {
		return
		(isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
		(isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
		(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
				(isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] === 443 ||
						$_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
						substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
	}

	private function get_file_object($file_name) {
		$file_path = $this->options['upload_dir'].$file_name;
		if (is_file($file_path) && $file_name[0] !== '.') {
			$file = new stdClass();
			$file->name = $file_name;
			$file->size = filesize($file_path);
			$file->url = $this->options['upload_url'].rawurlencode($file->name);
			foreach($this->options['image_versions'] as $version => $options) {
				if (is_file($options['upload_dir'].$file_name)) {
					$file->{$version.'_url'} = $options['upload_url']
					.rawurlencode($file->name);
				}
			}
			$file->delete_url = $this->options['script_url']
			.'?file='.rawurlencode($file->name);
			$file->delete_type = 'DELETE';
			return $file;
		}
		return null;
	}

	private function get_file_objects() {
		return array_values(array_filter(array_map(
				array($this, 'get_file_object'),
				scandir($this->options['upload_dir'])
		)));
	}

	private function create_scaled_image($file_name, $options) {
		$file_path = $this->options['upload_dir'].$file_name;
		$new_file_path = $options['upload_dir'].$file_name;
		list($img_width, $img_height) = @getimagesize($file_path);
		if (!$img_width || !$img_height) {
			return false;
		}
		$scale = min(
				$options['max_width'] / $img_width,
				$options['max_height'] / $img_height
		);
		if ($scale > 1) {
			$scale = 1;
		}
		$new_width = $img_width * $scale;
		$new_height = $img_height * $scale;
		$new_img = @imagecreatetruecolor($new_width, $new_height);
		switch (strtolower(substr(strrchr($file_name, '.'), 1))) {
			case 'jpg':
			case 'jpeg':
				$src_img = @imagecreatefromjpeg($file_path);
				$write_image = 'imagejpeg';
				break;
			case 'gif':
				@imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
				$src_img = @imagecreatefromgif($file_path);
				$write_image = 'imagegif';
				break;
			case 'png':
				@imagecolortransparent($new_img, @imagecolorallocate($new_img, 0, 0, 0));
				@imagealphablending($new_img, false);
				@imagesavealpha($new_img, true);
				$src_img = @imagecreatefrompng($file_path);
				$write_image = 'imagepng';
				break;
			default:
				$src_img = $image_method = null;
		}
		$success = $src_img && @imagecopyresampled(
				$new_img,
				$src_img,
				0, 0, 0, 0,
				$new_width,
				$new_height,
				$img_width,
				$img_height
		) && $write_image($new_img, $new_file_path);
		// Free up memory (imagedestroy does not delete files):
		@imagedestroy($src_img);
		@imagedestroy($new_img);
		return $success;
	}

	private function has_error($uploaded_file, $file, $error) {
		if ($error) {
			return $error;
		}
		if (!preg_match($this->options['accept_file_types'], $file->name)) {
			return 'acceptFileTypes';
		}
		if ($uploaded_file && is_uploaded_file($uploaded_file)) {
			$file_size = filesize($uploaded_file);
		} else {
			$file_size = $_SERVER['CONTENT_LENGTH'];
		}

		if ($this->options['max_file_size'] && (
				$file_size > $this->options['max_file_size'] ||
				$file->size > $this->options['max_file_size'])
		) {
			return 'maxFileSize';
		}
		if ($this->options['min_file_size'] &&
				$file_size < $this->options['min_file_size']) {
			return 'minFileSize';
		}
		if (is_int($this->options['max_number_of_files']) && (
				count($this->get_file_objects()) >= $this->options['max_number_of_files'])
		) {
			return 'maxNumberOfFiles';
		}
		return $error;
	}

	private function trim_file_name($name, $type) {
		// Remove path information and dots around the filename, to prevent uploading
		// into different directories or replacing hidden system files.
		// Also remove control characters and spaces (\x00..\x20) around the filename:
		$file_name = trim(basename(stripslashes($name)), ".\x00..\x20");
		// Add missing file extension for known image types:
		if (strpos($file_name, '.') === false &&
				preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
			$file_name .= '.'.$matches[1];
		}

		//Ensure that we don't have disallowed characters and add a unique id just to ensure that the file name will be unique
		$file_name = substr(uniqid(),-5).'-'.preg_replace("/([^a-zA-Z0-9\.\-\_]+?){1}/i", '-', $file_name);

		return $file_name;
	}

	private function orient_image($file_path) {
		$exif = exif_read_data($file_path);
		$orientation = intval(@$exif['Orientation']);
		if (!in_array($orientation, array(3, 6, 8))) {
			return false;
		}
		$image = @imagecreatefromjpeg($file_path);
		switch ($orientation) {
			case 3:
				$image = @imagerotate($image, 180, 0);
				break;
			case 6:
				$image = @imagerotate($image, 270, 0);
				break;
			case 8:
				$image = @imagerotate($image, 90, 0);
				break;
			default:
				return false;
		}
		$success = imagejpeg($image, $file_path);
		// Free up memory (imagedestroy does not delete files):
		@imagedestroy($image);
		return $success;
	}

	private function handle_file_upload($uploaded_file, $name, $size, $type, $error) {
		$file = new stdClass();
		$file->name = $this->trim_file_name($name, $type);
		$file->size = intval($size);
		$file->type = $type;
		$error = $this->has_error($uploaded_file, $file, $error);
		if (!$error && $file->name) {
			$file_path = $this->options['upload_dir'].$file->name;
			$append_file = !$this->options['discard_aborted_uploads'] &&
			is_file($file_path) && $file->size > filesize($file_path);
			clearstatcache();
			if ($uploaded_file && is_uploaded_file($uploaded_file)) {
				// multipart/formdata uploads (POST method uploads)
				if ($append_file) {
					file_put_contents(
							$file_path,
							fopen($uploaded_file, 'r'),
							FILE_APPEND
					);
				} else {
					move_uploaded_file($uploaded_file, $file_path);
				}
			} else {
				// Non-multipart uploads (PUT method support)
				file_put_contents(
						$file_path,
						fopen('php://input', 'r'),
						$append_file ? FILE_APPEND : 0
				);
			}
			$file_size = filesize($file_path);
			if ($file_size === $file->size) {
				if ($this->options['orient_image']) {
					$this->orient_image($file_path);
				}
				$file->url = $this->options['upload_url'].rawurlencode($file->name);
				foreach($this->options['image_versions'] as $version => $options) {
					if ($this->create_scaled_image($file->name, $options)) {
						$file->{$version.'_url'} = $options['upload_url']
						.rawurlencode($file->name);
					}
				}
			} else if ($this->options['discard_aborted_uploads']) {
				unlink($file_path);
				$file->error = 'abort';
			}
			$file->size = $file_size;
			$file->delete_url = $this->options['script_url']
			.'?file='.rawurlencode($file->name);
			$file->delete_type = 'DELETE';
		} else {
			$file->error = $error;
		}
		return $file;
	}

	public function get() {
		$file_name = isset($_REQUEST['file']) ?
		basename(stripslashes($_REQUEST['file'])) : null;
		if ($file_name) {
			$info = $this->get_file_object($file_name);
		} else {
			$info = $this->get_file_objects();
		}
		header('Content-type: application/json');
		echo json_encode($info);
	}

	public function post() {
		if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
			return $this->delete();
		}
		$upload = isset($_FILES[$this->options['param_name']]) ?
		$_FILES[$this->options['param_name']] : null;
		$info = array();
		if ($upload && is_array($upload['tmp_name'])) {
			foreach ($upload['tmp_name'] as $index => $value) {
				$info[] = $this->handle_file_upload(
						$upload['tmp_name'][$index],
						isset($_SERVER['HTTP_X_FILE_NAME']) ?
						$_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
						isset($_SERVER['HTTP_X_FILE_SIZE']) ?
						$_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
						isset($_SERVER['HTTP_X_FILE_TYPE']) ?
						$_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
						$upload['error'][$index]
				);
			}
		} elseif ($upload || isset($_SERVER['HTTP_X_FILE_NAME'])) {
			$info[] = $this->handle_file_upload(
					isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
					isset($_SERVER['HTTP_X_FILE_NAME']) ?
					$_SERVER['HTTP_X_FILE_NAME'] : $upload['name'],
					isset($_SERVER['HTTP_X_FILE_SIZE']) ?
					$_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'],
					isset($_SERVER['HTTP_X_FILE_TYPE']) ?
					$_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'],
					isset($upload['error']) ? $upload['error'] : null
			);
		}
		header('Vary: Accept');

		$redirect = isset($_REQUEST['redirect']) ?
		stripslashes($_REQUEST['redirect']) : null;
		if ($redirect) {
			header('Location: '.sprintf($redirect, rawurlencode($json)));
			return;
		}
		if (isset($_SERVER['HTTP_ACCEPT']) &&
				(strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
			header('Content-type: application/json');
		} else {
			header('Content-type: text/plain');
		}
		return $info;
	}

	public function delete() {
		$file_name = isset($_REQUEST['file']) ?
		basename(stripslashes($_REQUEST['file'])) : null;
		$file_path = $this->options['upload_dir'].$file_name;
		$success = is_file($file_path) && $file_name[0] !== '.' && unlink($file_path);
		if ($success) {
			foreach($this->options['image_versions'] as $version => $options) {
				$file = $options['upload_dir'].$file_name;
				if (is_file($file)) {
					unlink($file);
				}
			}
		}
		header('Content-type: application/json');
		echo json_encode($success);
	}

}