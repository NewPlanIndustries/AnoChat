<?php

class Platform
{
	function check()
	{
		/*
		$CI =& get_instance();
		define("PLATFORM", $CI->agent->is_mobile() ? "mobile" : "desktop");
		*/
		define("PLATFORM", "desktop");
		define("CACHE", "");
		define("WRAPPER", TRUE);
	}
}