<?php
$this->load->view(PLATFORM."/templateHeader",array(
	"subTitle" => "Help",
));
?>

<div class="container_16 body">
	<div class="grid_16">
		<?php $this->load->view("help_text"); ?>
	</div>
	<div class="clear"></div>
</div>

<?php $this->load->view(PLATFORM."/templateFooter"); ?>