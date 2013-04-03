<?php $this->load->view(PLATFORM."/Template_Header",array(
	"sub_title" => "Chat - " . $RoomID
)); ?>

<div id="ChatContainer">
	<p align="center">Loading Client&hellip;</p>
</div>

<div id="PreLoad" style="display:none">
	<img src="/assets/images/help.png" />
	<img src="/assets/images/edit.png" />
	<img src="/assets/images/mail.png" />
	<img src="/assets/images/close.png" />
</div>

<?php $this->load->view(PLATFORM."/Template_ClientScripts",array(
	"RoomID" => $RoomID
)); ?>

<?php $this->load->view(PLATFORM."/Template_Footer"); ?>