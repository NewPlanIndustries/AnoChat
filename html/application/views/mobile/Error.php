<?php $this->load->view(PLATFORM."/templateHeader",array(
	"sub_title" => $title
)); ?>

<div class="container_16" id="ChatContainer">
	<div class="grid_16">
		<h1><?php echo $heading; ?></h1>
		<?php echo $message; ?>
	</div>
	<div class="clear"></div>
</div>

<?php $this->load->view(PLATFORM."/templateFooter"); ?>