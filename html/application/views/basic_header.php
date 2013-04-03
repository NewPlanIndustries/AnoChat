<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8">
<title><?php echo SITE_TITLE . (isset($subTitle) && !empty($subTitle) ? " - " . $subTitle : ""); ?></title>
<?php $this->load->view(PLATFORM."/templateStyles"); ?>
</head>

<body>