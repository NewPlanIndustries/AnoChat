<?php

class System extends CI_Controller
{
	function clean($force = false)
	{
		header('Content-type:text/plain');
		
		$this->load->model('room');
		
		$column_separator = "| ";
		$column_length = 8;
		
		$headers = array(
			str_pad('port',$column_length),
			str_pad('exists',$column_length),
			str_pad('closing',$column_length)
		);
		
		$row_sep = str_repeat("=",($column_length+strlen($column_separator))*count($headers)) . PHP_EOL;;
		
		echo implode($column_separator,$headers) . PHP_EOL . $row_sep;
		
		$portRange = range(PORT_MIN, PORT_MAX);
		foreach($portRange as $port)
		{
			try
			{
				$this->room->_init(false,$port);
				$cleaning = $this->room->clean();
				$status = true;
			}
			catch(Exception $e)
			{
				$status = false;
				$cleaning = false;
			}
			
			if (!$status) continue;
			
			echo implode($column_separator,array(
				str_pad($port,$column_length),
				str_pad($status ? "true" : "false",$column_length),
				str_pad($cleaning ? "true" : "false",$column_length)
			)) . PHP_EOL . $row_sep;
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