<?php

class Socket
{
	var $connection = false;
	var $domain = false;
	var $port = false;
	
	function _init($domain,$port)
	{
		$this->disconnect();
		$this->domain = $domain;
		$this->port = $port;
	}
	
	function connect()
	{
		if (!empty($this->connection)) return;
		if (empty($this->domain) || empty($this->domain)) $this->error("Missing connection information");
		
		$this->connection = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP) or $this->error("Could not create socket");
		@socket_connect($this->connection, $this->domain, $this->port) or $this->error("Could not connect");
	}
	
	function listen()
	{
		$this->connect();
		
		$data = NULL;
		@socket_recv($this->connection, $data, 2048, 0) or $this->error("Disconnected from socket");
		return json_decode($data,true);
	}
	
	function send($data)
	{
		$this->connect();
		
		$data = json_encode($data);
		@socket_write($this->connection, $data, strlen($data)) or $this->error("Could not write to socket");
		return true;
	}
	
	function error($message)
	{
		if (!empty($this->connection))
		{
			$errorCode = socket_last_error($this->connection);
			$errorMessage = socket_strerror($errorCode);
			
			$message .= ": [" . $errorCode . "] ". $errorMessage;
		}
		
		throw new Exception($message);
	}
	
	function disconnect()
	{
		if (!empty($this->connection)) socket_close($this->connection);
		$this->connection = false;
	}
	
	function __destruct()
	{
		$this->disconnect();
	}
}