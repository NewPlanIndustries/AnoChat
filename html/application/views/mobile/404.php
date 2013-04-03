<?php
$CI =& get_instance();
$CI->load->view(PLATFORM."/Error",array(
	"title" => "404 Page Not Found",
	"heading" => "404 Page Not Found",
	"message" => "<p>Your web page is in another website.</p><img src=\"/assets/toad.png\" align=\"texttop\" />"
));
?>