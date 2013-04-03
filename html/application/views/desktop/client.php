<?php $this->load->view(PLATFORM."/templateHeader",array(
	"subTitle" => "Chat - " . $roomID,
	"roomID" => $roomID
)); ?>

<div id="chatContainer">
	<p align="center">Loading Client&hellip;</p>
</div>

<div id="preLoad" style="display:none">
	<img src="/assets/images/help.png" />
	<img src="/assets/images/edit.png" />
	<img src="/assets/images/mail.png" />
	<img src="/assets/images/close.png" />
</div>

<?php $this->load->view(PLATFORM."/templateFooter"); ?>