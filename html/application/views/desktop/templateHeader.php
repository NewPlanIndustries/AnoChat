<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title><?php echo SITE_TITLE . (isset($subTitle) && !empty($subTitle) ? " - " . $subTitle : ""); ?></title>

<?php $this->load->view(PLATFORM."/templateStyles"); ?>

<?php
if (isset($roomID))
{
	$this->load->view(PLATFORM."/templateClientScripts",array(
		"roomID" => $roomID
	));
}
?>
</head>

<body>

<div id="header">
	<div class="container_16">
		<div class="grid_8">
			<h1><a href="/"><img src="/assets/images/logo_small.png" align="SnapChat" height="40" /></a></h1>
		</div>
		<div class="grid_8 nav">
			<a href="/help">Help</a><a href="/bugs">Bugs</a>
		</div>
		<div class="clear"></div>
	</div>
</div>

<table cellpadding="0" cellspacing="0" border="0" width="100%" height="100%">
	<tr>
		<td align="left" valign="top" id="content">
