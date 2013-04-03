<?php

class Socket
{
	var $domain;
	var $port;
	
	var $master;
	var $clients = array();
	
	function __construct($domain, $port)
	{
		$this->domain = $domain;
		$this->port = $port;
		$this->start();
	}
	
	function start()
	{
		try
		{
			$this->debug('STARTING SERVER AT "' . $this->domain . '" ON PORT ' . $this->port);
			
			$this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
			
			if (!is_resource($this->master)) throw new Exception('Could not create socket');
			
			if (!socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1)) throw new Exception('Could not set socket options');
			
			if (!@socket_bind($this->master, $this->domain, $this->port)) throw new Exception('Could not bind socket');
			
			if (!socket_listen($this->master, 5)) throw new Exception('Could not listen to socket');
			
			$this->debug('SERVER STARTED');
			
			array_push($this->clients, $this->master);
			
			while (true) $this->listen();
		}
		catch (Exception $e)
		{
			$this->socketError($e->getMessage());
		}
	}
	
	function socketError($label)
	{
		$errorCode = socket_last_error($this->master);
		$errorMessage = socket_strerror($errorCode);
		
		$this->debug('ERROR [' . $errorCode . '] ' . $label . ': ' . $errorMessage);
	}
	
	function listen()
	{
		$changedSockets = $this->clients;
		socket_select($changedSockets, $write=NULL, $except=NULL, NULL);
		
		foreach($changedSockets as $changedSocket) {
			if ($changedSocket == $this->master)
			{
				$this->connect();
			}
			else
			{
				$socketID = array_search($changedSocket, $this->clients);
				
				$buffer = NULL;
				if (socket_recv($changedSocket, $buffer, 2048, 0))
				{
					try
					{
						$this->process($socketID, $buffer);
					}
					catch (Exception $e)
					{
						$this->error($socketID, $e);
					}
				}
				else
				{
					$this->disconnect($socketID, $changedSocket);
				}
			}
		}
	}
	
	function connect()
	{
		if (($newSocket = socket_accept($this->master)) === false) return;
		
		array_push($this->clients, $newSocket);
		$socketID = array_search($newSocket, $this->clients);
		
		/*
		$this->debug(array(
			'CONNECTED ' . $socketID,
			$this->clients
		));
		*/
		
		return $socketID;
	}
	
	function disconnect($socketID, $socket)
	{
		unset($this->clients[$socketID]);
		socket_close($socket);
		/*
		$this->debug(array(
			'DISCONNECTED ' . $socketID,
			$this->clients
		));
		*/
		return $socketID;
	}
	
	function writeDataToSocket($socketID, $data)
	{
		if (!isset($this->clients[$socketID]) || $this->clients[$socketID] == $this->master) return;
		socket_write($this->clients[$socketID], json_encode($data));
		
		/*
		$this->debug(array(
			'WRITING DATA TO ' . $socketID,
			json_encode($data)
		));
		*/
	}
	
	function process($socketID, $buffer)
	{
		//$this->debug('PROCESSING ' . $socketID);
		
		$buffer = trim($buffer);
		//$this->debug($buffer);
		
		$bufferData = json_decode($buffer, true);
		//$this->debug($bufferData);
		
		return $bufferData;
	}
	
	function debug($data)
	{
		echo print_r($data, true) . PHP_EOL;
	}
	
	function error($socketID, $e)
	{
		$errorData = array(
			'status' => false,
			'message' => $e->getMessage()
		);
		$this->debug('ERROR : ' . print_r($errorData, true));
		$this->writeDataToSocket($socketID, $errorData);
	}
}