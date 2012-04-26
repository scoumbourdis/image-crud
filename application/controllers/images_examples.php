<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Images_examples extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		/* Standard Libraries */
		$this->load->database();
		$this->load->helper('url');
		/* ------------------ */	
	}
	
	function _example_output($output = null)
	{
		$this->load->view('example.php',$output);	
	}
	
	function index()
	{
		$this->_example_output((object)array('output' => '' , 'js_files' => array() , 'css_files' => array()));
	}	
	
	function test_photo_gallery()
	{
		$this->load->library('Photos_CRUD');
		$photos_crud = new Photos_CRUD();
		
		$photos_crud->set_table('photo_gallery')
			//->set_relation_field('category_id')
			->set_ordering_field('priority')
			->set_subject('Photo')
			->set_image_path('assets/uploads');
			
		$output = $photos_crud->render();
		
		$this->_example_output($output);
	}
}