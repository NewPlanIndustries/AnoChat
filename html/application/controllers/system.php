<?php

class System extends CI_Controller
{
	function clean($force = false)
	{
		header('Content-type:text/plain');
		
		$this->load->model('room');
		
		$portRange = range(PORT_MIN, PORT_MAX);
		foreach($portRange as $port)
		{
			try
			{
				$this->room->_init(false,$port);
				$this->room->clean();
			}
			catch(Exception $e)
			{
			}
		}
		
		echo "done @ " . date('Y-m-d H:i:s');
	}
	
	function check()
	{
		$this->load->library("socket");
		
		$status = true;
		try
		{
			$this->socket->_init("localhost", DAEMON_PORT);
			$this->socket->connect();
		}
		catch(Exception $e)
		{
			$status = false;
		}
		
		echo json_encode($status);
	}
}