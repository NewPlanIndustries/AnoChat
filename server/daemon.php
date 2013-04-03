<?php
require_once('../common/constants.php');
require_once('socket.php');

class daemon extends Socket
{
	function process($socketID, $buffer)
	{
		$data = parent::process($socketID,$buffer);
		
		if (!array_key_exists('action', $data)) throw new Exception('MISSING ACTION');
		if (!array_key_exists('data', $data)) throw new Exception('MISSING DATA');
		
		switch($data['action'])
		{
			case 'start':
				$this->startRoom($socketID, $data['data']);
				break;
			default:
				throw new Exception('Unknown Action');
		}
	}
	
	function startRoom($socketID, $data)
	{
		$this->debug(date('Y-m-d H:i:s'));
		$this->debug('STARTING NEW ROOM SERVER');
		
		if (!array_key_exists('roomID', $data)) throw new Exception('MISSING ROOM ID');
		if (!array_key_exists('port', $data)) throw new Exception('MISSING ROOM PORT');
		
		$roomID = $data['roomID'];
		$port = $data['port'];
		
		$startCommand = dirname(__FILE__) . '/startRoom ' . $port . ' ' . $roomID;
		
		$this->debug(array(
			'Port: ' . $port,
			$startCommand
		));
		
		exec($startCommand);
		
		$this->writeDataToSocket($socketID, array(
			"status" => true,
			"action" => "start"
		));
	}
}

new daemon('localhost', DAEMON_PORT);
?>