<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Infer
{
	var $Map = array(
		"F" => "0","D" => "1","w" => "2","6" => "3","T" => "4","R" => "5","b" => "6","g" => "7","B" => "8","d" => "9",
		"E" => "0","u" => "1","s" => "2","Q" => "3","J" => "4","t" => "5","Z" => "6","5" => "7","8" => "8","r" => "9",
		"V" => "0","7" => "1","p" => "2","9" => "3","W" => "4","4" => "5","C" => "6","a" => "7","3" => "8","c" => "9",
		"m" => "0","k" => "1","Y" => "2","q" => "3","y" => "4","X" => "5","h" => "6","N" => "7","S" => "8","z" => "9",
		"H" => "0","n" => "1","j" => "2","e" => "3","M" => "4","A" => "5","G" => "6","K" => "7","U" => "8","P" => "9"
	);
	
	function Encode($String, $MinLength = 0)
	{
		$String = (string)$String;
		$Encoded = '';
		
		for($Char = 0; $Char < strlen($String); $Char++)
		{
			$Chars = array_keys($this->Map,$String[$Char]);
			$Encoded .= $Chars[array_rand($Chars)];
		}
		
		if (strlen($Encoded) < $MinLength)
		{
			$Encoded = $this->Encode(str_pad('',$MinLength - strlen($Encoded),0)) . $Encoded;
		}
		
		return $Encoded;
	}
	
	function Decode($String)
	{
		$Decoded = '';
		for($Char = 0; $Char < strlen($String); $Char++)
		{
			$Decoded .= $this->Map[$String[$Char]];
		}
		
		return $Decoded;
	}
}