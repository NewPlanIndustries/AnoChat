<!-- error_404.php -->
<?php
$CI =& get_instance();
echo $CI->load->view(PLATFORM."/error",array(
	"title" => "404 Page Not Found",
	"heading" => HEADER_404,//$heading,
	"message" => MESSAGE_404//$message
), true);
?>