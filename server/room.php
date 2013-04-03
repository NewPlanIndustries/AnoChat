<?php
require_once("socket.php");
require_once("helpers.php");

class Room extends Socket
{
	var $logging = false;
	
	var $roomID;
	var $users = array();
	
	var $lastClean = NULL;
	var $cleanInterval = 10;
	var $awayInterval = 1; //multiplier of cleanInterval
	var $leaveInterval = 2; //multiplier of awayInterval
	
	function __construct($domain, $port, $roomID)
	{
		$this->roomID = $roomID;
		$this->lastClean = time();
		$this->awayInterval *= $this->cleanInterval;
		$this->leaveInterval *= $this->awayInterval;
		parent::__construct($domain, $port);
	}
	
	function process($socketID, $buffer)
	{
		$data = parent::process($socketID, $buffer);
		
		if (!array_key_exists("action", $data)) throw new Exception("Missing action");
		
		switch($data["action"])
		{
			case "join":
			case "leave":
			case "send":
			case "get":
			case "check":
				if (!array_key_exists("data", $data)) throw new Exception("Missing data");
				$this->$data["action"]($socketID, $data["data"]);
				break;
			case "userList":
				$this->updateUserList();
				break;
			case "clean":
				$force = array_key_exists("force", $data) ? $data["force"] : false;
				$this->clean($socketID, $force);
				break;
			default:
				throw new Exception("Invalid action:" . $data["action"]);
		}
		
		$this->log($this->users);
	}
	
	function check($socketID, $data)
	{
		if (!array_key_exists("userID", $data)) throw new Exception("Missing userID");
		$userID = $data["userID"];
		
		$this->writeDataToSocket($socketID, array(
			"status" => true,
			"action" => "check",
			"data" => array_key_exists($userID, $this->users)
		));
	}
	
	var $anonCount = 0;
	function join($socketID, $data)
	{
		if (!array_key_exists("name", $data)) throw new Exception("Missing name");
		$name = htmlentities(trim($data["name"]));
		
		$userID = array_key_exists("userID", $data) ? $data['userID'] : false;
		$joining = $userID === false;
		if (!$joining)
		{
			$currentName = $this->users[$userID]["name"];
		}
		else
		{
			do $userID = $this->generateID($socketID, $name);
			while (isset($this->users[$userID]));
		}
		
		if (empty($name))
		{
			$this->anonCount++;
			$name = "Anonymous " . $this->anonCount;
		}
		
		foreach($this->users as $loopID => $loopUser)
		{
			if ($loopUser["name"] == $name)
			{
				$exceptionMessage = ($loopID == $userID) ? "Name has not changed" : "There is already a user with this name present";
				throw new Exception($exceptionMessage);
			}
		}
		
		$queue = $joining ? array() : $this->users[$userID]["queue"];
		
		$this->users[$userID] = array(
			"name" => $name,
			"queue" => $queue,
			"status" => "online"
		);
		
		$this->updateUserList();
		
		$this->writeDataToSocket($socketID, array(
			"status" => true,
			"action" => "join",
			"data" => array(
				"name" => $name,
				"userID" => $userID
			)
		));
		
		$message = $joining ? $name . " has joined the room" : $currentName . " has changed their name to " . $name;
		
		$this->writeDataToRoom(array(
			"action" => "system",
			"data" => array(
				"timestamp" => time(),
				"message" => $message
			)
		));
	}
	
	function leave($socketID, $data)
	{
		if (!array_key_exists("userID", $data)) throw new Exception("Missing userID");
		
		$userID = $data["userID"];
		$name = $this->users[$userID]["name"];
		unset($this->users[$userID]);
		
		$this->updateUserList();
		
		$this->writeDataToRoom(array(
			"action" => "system",
			"data" => array(
				"timestamp" => time(),
				"message" => $name . " has left the room"
			)
		));
	}
	
	function send($socketID, $data)
	{
		if (!array_key_exists("userID", $data)) throw new Exception("Missing userID");
		if (!array_key_exists("message", $data)) throw new Exception("Missing message");
		
		$userID = $data["userID"];
		if (!array_key_exists($userID, $this->users)) throw new Exception("Invalid userID");
		
		$this->writeDataToSocket($socketID, array(
			"status" => true,
			"action" => "send"
		));
		
		$message = auto_link(htmlentities($data["message"],ENT_NOQUOTES));
		$this->writeDataToRoom(array(
			"action" => "message",
			"data" => array(
				"name" => $this->users[$userID]["name"],
				"timestamp" => time(),
				"message" => $message
			)
		), $userID);
	}
	
	function get($socketID, $data)
	{
		if (!array_key_exists("userID", $data)) throw new Exception("Missing userID");
		
		$userID = $data["userID"];
		$this->log($userID);
		if (!array_key_exists($userID, $this->users)) throw new Exception("Invalid userID");
		
		$queue = $this->users[$userID]["queue"];
		$this->writeDataToSocket($socketID, array(
			"status" => true,
			"action" => "queue",
			"data" => $queue
		));
		
		$this->users[$userID]["queue"] = array();
		$this->users[$userID]["lastGet"] = time();
	}
	
	function generateID($socketID, $name)
	{
		return md5(time() . $socketID . $name);
	}
	
	function clean($socketID, $force)
	{
		$now = time();
		$cleanTime = $this->lastClean + $this->cleanInterval;
		
		$this->log(array(
			'now' => $now,
			'lastClean' => $this->lastClean,
			'cleanInterval' => $this->cleanInterval
		));
		
		if ($now < $cleanTime && !$force) throw new Exception("Cleaning Interval has not yet elapsed, " . ($cleanTime - $now) . "s left");
		
		$leftPeriod = $now - $this->leaveInterval;
		$awayPeriod = $now - $this->awayInterval;
		
		$this->log($leftPeriod);
		$this->log($awayPeriod);
		
		$this->log($this->users);
		foreach($this->users as $userID => $userData)
		{
			if ($userData["lastGet"] < $leftPeriod)
			{
				$this->leave(NULL, array(
					"userID" => $userID
				));
			}
			else if ($userData["lastGet"] < $awayPeriod)
			{
				$this->users[$userID]["status"] = "away";
			}
			else
			{
				$this->users[$userID]["status"] = "online";
			}
		}
		$this->log($this->users);
		
		$this->writeDataToSocket($socketID, array(
			"status" => true,
			"action" => "clean",
			"closing" => empty($this->users)
		));
			
		if (empty($this->users))
		{
			$this->log("Room Is Empty, Closing");
			exit;
		}
		
		$this->lastClean = $now;
		$this->updateUserList();
	}
	
	function updateUserList()
	{
		$userList = array();
		foreach($this->users as $userID => $userData) array_push($userList, array(
			"name" => $userData["name"],
			"status" => $userData["status"]
		));
		
		$this->writeDataToRoom(array(
			"action" => "userList",
			"data" => $userList
		));
	}
	
	function writeDataToRoom($data, $skipUserID = NULL)
	{
		$this->log("WRITING DATA TO ROOM");
		$this->log($data);
		
		foreach(array_keys($this->users) as $userID)
		{
			if ($userID == $skipUserID) continue;
			array_push($this->users[$userID]["queue"], $data);
		}
	}
	
	function log($data)
	{
		if ($this->logging == true)
		{
			echo print_r($data, true) . PHP_EOL;
		}
	}
}

try
{
	if (count($argv) < 3) throw new Exception("INVALID NUMBER OF ARGUMENTS");
	if (!is_numeric($argv[1])) throw new Exception("FIRST ARGUMENT (PORT #) SHOULD BE NUMERIC");
	new Room("localhost", (int)$argv[1], $argv[2]);
}
catch (Exception $e)
{
	echo $e->getMessage() . PHP_EOL;
}
?>