<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class cURL
{
	var $ch;
	
	function get($URL)
	{
		$this->_init($URL);
		return $this->_exec();
	}
	
	function post($URL, $Data)
	{
		$this->_init($URL);
		$this->_post($Data);
		return $this->_exec();
	}
	
	function _init($URL)
	{
		$this->ch = curl_init();
		curl_setopt($this->ch, CURLOPT_URL, $URL);
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, TRUE);
	}
	
	function _post($Data)
	{
		$PostFields = array();
		foreach($Data as $Field => $Value)
		{
			$PostFields[] = urlencode($Field) . '=' .urlencode($Value);
		}
		
		curl_setopt($this->ch,CURLOPT_POST,count($PostFields));
		curl_setopt($this->ch,CURLOPT_POSTFIELDS,implode('&',$PostFields));
	}
	
	function _exec()
	{
		$Return = curl_exec($this->ch);
		curl_close($this->ch);
		
		return $Return;
	}
}