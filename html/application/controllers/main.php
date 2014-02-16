<?php defined("BASEPATH") OR exit("No direct script access allowed");

class Main extends CI_Controller
{
	function __construct()
	{
		parent::__construct();
		$this->load->library("user_agent");
	}
	
	function index()
	{
		$this->load->view(PLATFORM."/start");
	}
	
	function client($roomID)
	{
		$this->load->view(PLATFORM."/client",array(
			"roomID" => $roomID
		));
	}
	
	function _404()
	{
		$this->load->view(PLATFORM."/404");
	}
	
	function bugs($mode = false)
	{
		if ($mode == "thanks")
		{
			$this->load->view(PLATFORM."/bugs_submitted");
		}
		else if ($mode == "submit")
		{
			$description = $this->input->post('description');
			
			if (!empty($description))
			{
				$this->load->helper('email');
				$this->load->library('email');
				
				$name = $this->input->post('name');
				$email = $this->input->post('email');
				$subject = $this->input->post('subject');
				$room = $this->input->post('room');
				
				$name = $name ? $name : "(NO NAME)";
				$email = valid_email($email) ? $email : "(no@email.com)";
				$subject = $subject ? $subject : "(NO SUBJECT)";
				$room = $room ? $room : "(NO ROOM)";
				
				$this->email->from($email,$name);
				$this->email->to('bugs@anochat.org');
				$this->email->subject($subject);
				$this->email->message(join(PHP_EOL,array(
					"Room: " . $room,
					"",
					"Description:",
					$description
				)));
				
				$this->email->send();
				
				if ($email != "(no@email.com)")
				{
					$this->email->clear();
					
					$this->email->from("bugs@anochat.org","AnoChat Bug Reports");
					$this->email->to($email);
					$this->email->subject("Bug Report Recieved");
					$this->email->message(join(PHP_EOL,array(
						"We've received your bug report as detailed below and are looking into it.",
						"",
						"Thanks,",
						"AnoChat Development",
						str_repeat("=",50),
						"",
						"Room: " . $room,
						"",
						"Description:",
						$description,
					)));
					
					$this->email->send();
				}
				
				redirect("main/bugs/thanks");
			}
			
			redirect("main/bugs");
		}
		else
		{
			$this->load->view(PLATFORM."/bugs_report");
		}
	}
	
	function help($framed = false)
	{
		if ($framed) $this->load->view("help_frame");
		else $this->load->view(PLATFORM."/help");
	}
}