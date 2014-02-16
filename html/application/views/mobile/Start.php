<?php $this->load->view(PLATFORM."/Template_Header",array(
	"sub_title" => "Start Room"
)); ?>

<div class="container_16" id="ChatContainer">
	<div class="grid_8">
		<h2>Start a Quick Chat</h2>
		<div id="StartArea">Loading client&hellip;</div>
	</div>
	<div class="grid_8">
		<h2>Why use AnoChat?</h2>
		<ul>
			<li>No information required, not even your name.</li>
			<li>Doesn"t require any special software.</li>
			<li>Works with most major browsers, desktop or mobile.</li>
			<li>It"s free!</li>
		</ul>
	</div>
	<div class="clear"></div>
</div>

<div id="PreLoad" style="display:none">
	<img src="/assets/images/help.png" />
	<img src="/assets/images/edit.png" />
	<img src="/assets/images/mail.png" />
	<img src="/assets/images/close.png" />
</div>

<?php $this->load->view(PLATFORM."/Template_ClientScripts",array(
	"RoomID" => "",
	"StartMode" => "Start"
)); ?>

<?php $this->load->view(PLATFORM."/Template_Footer"); ?>
