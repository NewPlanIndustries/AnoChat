<!-- desktop/404.php -->
<?php
$CI =& get_instance();
$CI->load->view(PLATFORM."/error",array(
	"title" => HEADER_404,
	"heading" => HEADER_404,
	"message" => MESSAGE_404//"<p>Your web page is in another website.</p><img src=\"/assets/toad.png\" align=\"texttop\" />"
));
?>