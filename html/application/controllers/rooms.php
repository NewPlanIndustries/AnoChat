<?php defined("BASEPATH") OR exit("No direct script access allowed");

class Rooms extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->model("room");
		
		$action = $this->uri->segment(2);
		$roomID = $this->uri->segment(3);
		
		$data = $this->input->post("data");
		
		$return = array(
			"action" => $action,
			"status" => true
		);
		
		try
		{
			if (!method_exists($this->room, $action)) throw new Exception("Invalid Room Action [m]");
			
			$this->room->_init($roomID);
			$return["data"] = $this->room->$action($data);
		}
		catch(Exception $e)
		{
			$return = array_merge($return, array(
				"status" => false,
				"data" => array(
					"message" => $e->getMessage()
				)
			));
		}
		
		echo json_encode($return);
		exit;
	}
}