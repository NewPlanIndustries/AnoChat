<?php
$CI =& get_instance();
$CI->load->view("Error",array(
	"title" => "Database Error",
	"heading" => $heading,
	"message" => $message
));
?>