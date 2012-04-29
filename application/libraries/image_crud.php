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
    		$results[$num]->image_url = base_url().$this->image_path.'/'.$row->{$this->url_field};
    		$results[$num]->thumbnail_url = base_url().$this->image_path.'/'.$this->thumbnail_prefix.$row->{$this->url_field};
    		$results[$num]->delete_url = $this->_get_delete_url($row->id);
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