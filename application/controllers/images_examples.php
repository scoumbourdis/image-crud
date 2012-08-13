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
	
	function example1()
	{
		$this->load->library('Image_CRUD');
		$image_crud = new Image_CRUD();
		
		$image_crud->set_primary_key_field('id');
		$image_crud->set_url_field('url');
		$image_crud->set_table('example_1')
			->set_image_path('assets/uploads');
			
		$output = $image_crud->render();
		
		$this->_example_output($output);
	}
	
	function example2()
	{
		$this->load->library('Image_CRUD');
		$image_crud = new Image_CRUD();
	
		$image_crud->set_primary_key_field('image_id');
		$image_crud->set_url_field('url');
		$image_crud->set_table('example_2')
		->set_ordering_field('priority')
		->set_image_path('assets/uploads');
			
		$output = $image_crud->render();
	
		$this->_example_output($output);
	}

	function example3()
	{
		$this->load->library('Image_CRUD');
		$image_crud = new Image_CRUD();
	
		$image_crud->set_primary_key_field('id');
		$image_crud->set_url_field('url');
		$image_crud->set_table('example_3')
		->set_relation_field('category_id')
		->set_ordering_field('priority')
		->set_image_path('assets/uploads');
			
		$output = $image_crud->render();
	
		$this->_example_output($output);
	}

	function example4()
	{
		$this->load->library('Image_CRUD');
		$image_crud = new Image_CRUD();
	
		$image_crud->set_primary_key_field('image_id');
		$image_crud->set_url_field('url');
		$image_crud->set_title_field('title');
		$image_crud->set_table('example_2')
		->set_ordering_field('priority')
		->set_image_path('assets/uploads');
			
		$output = $image_crud->render();
	
		$this->_example_output($output);
	}
}