<?php $this->load->view(PLATFORM."/templateHeader",array(
	"subTitle" => "Start Room",
	"roomID" => ""
)); ?>

<div class="container_16 body" id="chatContainer">
	<div class="grid_8 prefix_4">
		<h2>Start a Quick Chat</h2>
		<div id="startBlock">
			<noscript>AnoChat requires JavaScript. Please enable this in your browser.</noscript>
		</div>
		<script type="text/javascript">
			$('#startBlock').empty().append(makeElement('div',{id:"startArea"},'<p>Loading client&hellip;</p>'));
		</script>
	</div>
	<div class="clear"></div>
	
	<div class="grid_8 prefix_4">
		<h2>What is AnoChat?</h2>
		<p>
			AnoChat is a free, private web chat service.
			Opening a new room is as simple as entering your name, and that's not even required either.
			Check out the <a href="/help">Help</a> section for more information!
		</p>
	</div>
	<div class="clear"></div>
</div>

<div id="PreLoad" style="display:none">
	<img src="/assets/images/help.png" />
	<img src="/assets/images/edit.png" />
	<img src="/assets/images/mail.png" />
	<img src="/assets/images/close.png" />
</div>

<?php $this->load->view(PLATFORM."/templateFooter"); ?>
