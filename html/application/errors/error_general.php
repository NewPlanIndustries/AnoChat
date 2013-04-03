<?php
$CI =& get_instance();
$CI->load->view("Error",array(
	"title" => "Error",
	"heading" => $heading,
	"message" => $message
));
?>