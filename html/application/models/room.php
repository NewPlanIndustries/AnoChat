<?php defined("BASEPATH") OR exit("No direct script access allowed");

class Room extends CI_Model
{
	var $roomID;
	var $roomData;
	
	function __construct()
	{
		parent::__construct();
		
		$this->load->library("infer");
		$this->load->library("socket");
		
		if (!$this->input->is_cli_request())
		{
			$this->load->library("session");
		}
	}
	
	public function _init($roomID, $port = NULL)
	{
		$this->roomID = $roomID;
		
		$port = empty($port) ? $this->infer->decode($roomID) : $port;
		
		if (!$this->input->is_cli_request())
		{
			$this->roomData = $this->session->userdata($this->roomID);
			if (empty($this->roomData)) $this->roomData = array();
		}
		
		$this->socket->_init("localhost", $port);
	}
	
	private function getFromArray($array, $key, $default = false)
	{
		if (!is_array($array) || !isset($array[$key])) return $default;
		return $array[$key];
	}
	
	private function setData($data, $value = NULL)
	{
		if (is_array($data)) $this->roomData = $data;
		else if (is_string($data))
		{
			$this->roomData = array_merge($this->roomData,array(
				$data => $value
			));
		}
		$this->session->set_userdata($this->roomID,$this->roomData);
	}
	
	private function getData($key)
	{
		return $this->getFromArray($this->roomData, $key);
	}
	
	public function start($data)
	{
		$exceptionLabel = "Unable To Start Room";
		
		$name = trim($this->getFromArray($data,"name",""));
		
		$noConflict = false;
		$portRange = range(PORT_MIN, PORT_MAX);
		while($noConflict == false && count($portRange) > 0)
		{
			$port_key = array_rand($portRange);
			$port = $portRange[$port_key];
			$roomID = $this->infer->encode($port);
			
			unset($portRange[$port_key]);
			
			$noConflict = false;
			try
			{
				$this->socket->_init("localhost", $port);
				$this->socket->connect();
			}
			catch(Exception $e)
			{
				$noConflict = true;
			}
		}
		
		if ($noConflict == false) throw new Exception($exceptionLabel . ": Server is full.");
		
		$this->socket->_init("localhost", DAEMON_PORT);
		
		$this->socket->send(array(
			"action" => "start",
			"data" => array(
				"roomID" => $roomID,
				"port" => $port
			)
		));
		
		$data = $this->socket->listen();
		$this->throwStatus($data, $exceptionLabel);
		sleep(1);
		
		$this->_init($roomID);
		$this->join(array(
			"name" => $name
		));
		
		return $roomID;
	}
	
	
	public function checkSocket()
	{
		$this->socket->connect();
		return true;
	}
	
	public function checkJoin($data)
	{
		$throw = $this->getFromArray($data, 'throw', true);
		$confirm = $this->getFromArray($data, 'confirm', false);
		$confirmed = false;
		
		$name = $this->getData("name");
		if ($confirm)
		{
			$userID = $this->getData("userID");
			
			if ($userID !== false)
			{
				$this->socket->send(array(
					"action" => "check",
					"data" => array(
						"userID" => $userID,
					)
				));
				$data = $this->socket->listen();
				$this->throwStatus($data, "Unable to confirm user");
				$confirmed = $data['data'];
			}
		}
		
		if (($confirm && !$confirmed) || $name == false)
		{
			if ($throw)
			{
				throw new Exception("User Has Not Joined the Room");
			}
		}
		
		return $name;
	}
	
	public function userID($throw = true)
	{
		$this->checkJoin(array(
			"throw" => $throw
		));
		return $this->getData("userID");
	}
	
	public function join($data)
	{
		$userID = $this->userID(false);
		$name = trim($this->getFromArray($data,"name",""));
		
		$exceptionMessage = "Unable To " . ($userID ? "Change Name" : "Join");
			
		$this->socket->send(array(
			"action" => "join",
			"data" => array(
				"userID" => $userID,
				"name" => $name
			)
		));
		$data = $this->socket->listen();
		$this->throwStatus($data, $exceptionMessage);
		if (isset($data["data"]) && isset($data["data"]["userID"]) && isset($data["data"]["name"]))
		{
			$userID = $data["data"]["userID"];
			$name = $data["data"]["name"];
			
			$this->setData("userID",$data["data"]["userID"]);
			$this->setData("name",$name);
		}
		else throw new Exception($exceptionMessage);
		
		return $name;
	}
	
	public function leave()
	{
		$userID = $this->userID();
		$leaveData = array(
			"action" => "leave",
			"data" => array(
				"userID" => $userID
			)
		);
		$this->socket->send($leaveData);
		
		$this->setData(array());
		return true;
	}
	
	public function send($data)
	{
		$exceptionMessage = "Could Not Send Message";
		
		$userID = $this->userID();
		
		$message = $this->getFromArray($data,"message");
		if (empty($message) && $message !== "0") throw new Exception($exceptionMessage . ": Message is missing or empty");
		
		$sendData = array(
			"action" => "send",
			"data" => array(
				"userID" => $userID,
				"message" => $message
			)
		);
		$this->socket->send($sendData);
		
		$data = $this->socket->listen();
		$this->throwStatus($data, $exceptionMessage);
		
		return true;
	}
	
	public function userList()
	{
		$userID = $this->userID();
		
		$getUserList = array("action" => "userList");
		$this->socket->send($getUserList);
		
		return true;
	}
	
	public function listen()
	{
		$userID = $this->userID();
		$getData = array(
			"action" => "get",
			"data" => array(
				"userID" => $userID
			)
		);
		
		$queue = array();
		$sleepTime = 0.5;
		$tries = 30/$sleepTime;
		
		while(empty($queue) && $tries > 0)
		{
			$this->socket->send($getData);
			$data = $this->socket->listen();
			$this->throwStatus($data);
			$queue = $data["data"];
			if (empty($queue))
			{
				usleep($sleepTime * 1000000);
				$tries--;
			} else break;
		}
		
		
		return $queue;
	}
	
	public function clean()
	{
		$this->socket->connect();
		
		try
		{
			$this->socket->send(array(
				"action" => "clean"
			));
			
			$data = $this->socket->listen();
			
			$closing = array_key_exists('closing',$data) ? $data['closing'] : false;
		}
		catch(Exception $e)
		{
			$closing = true;
		}
		
		return $closing;
	}
	
	private function throwStatus($data, $message = "")
	{
		$message = trim($message);
		if (!empty($message)) $message .= ": ";
		
		if (!isset($data["status"]) || $data["status"] !== true)
		{
			throw new Exception($message . (isset($data["message"]) ? $data["message"] : ""));
		}
	}
	
	public function invite($data)
	{
		$emails = $this->getFromArray($data,"emails");
		if (empty($emails)) throw new Exception("Please enter at least one email address");
		
		$name = $this->getData("name");
		
		$subject = $name . ' has invited you to chat';
		$message = $this->load->view('email',array(
			"name" => $name,
			"roomID" => $this->roomID
		),true);
		$message_alt = $this->load->view('email_alt',array(
			"name" => $name,
			"roomID" => $this->roomID
		),true);
		
		$this->load->library('email');
		$this->email->initialize(array(
			"mailtype" => "html"
		));
		foreach($emails as $email)
		{
			$this->email->clear();
			
			$this->email->from('noresponse@snapchat.org','SnapChat');
			$this->email->to($email);
			$this->email->subject($subject);
			$this->email->message($message);
			$this->email->set_alt_message($message_alt);
			$this->email->send();
		}
		
		return true;
	}
}
