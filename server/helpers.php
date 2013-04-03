<?php

function auto_link($str)
{
	// LINK DOMAINS
	$pattern = "#\b((http(s?)://)?(([a-z0-9\-]+?)((\.[a-z]{2,3}){1,2})([a-z0-9-._~:/\#\=\?\[\]\$\(\)\*\+\@\!\&\'\,\;])*))#i";
	$replace = "<a href=\"http$3://$4\" target=\"_blank\">$1</a>";
	$str = preg_replace($pattern,$replace,$str);
	
	/*
	// LINK EMAILS
	$pattern = "/(([a-zA-Z0-9_\.\-\+]+)@([a-zA-Z0-9\-]+)\.([a-zA-Z0-9\-\.]*))/i";
	$replace = "<a href=\"mailto:someone@example.com\">$1</a>";
	$str = preg_replace($pattern,$replace,$str);
	*/
	
	return $str;
}
