<?php $this->load->view(PLATFORM."/templateHeader",array(
	"subTitle" => "Report a Bug"
)); ?>

<style>
.niceInput {
	width:500px;
}
</style>

<div class="container_16 body">
	<div class="grid_9 prefix_4">
		<h2>Bug Report, Feature Requests &amp; Other Questions</h2>
		<form method="post" action="/main/bugs/submit">
			<div style="float:left;">
				<label>
					Your Name:<br>
					<input type="text" name="name" value="" class="niceInput" style="width:240px;">
				</label>
			</div>
			<div style="float:left;">
				<label>
					Your Email:<br>
					<input type="text" name="email" value="" class="niceInput" style="width:240px;">
				</label>
			</div>
			<div class="clear"></div>
			<div>
				<label>
					Subject:<br>
					<input type="text" name="subject" value="" class="niceInput">
				</label>
			</div>
			<div>
				<label>
					Browser:<br>
					<input type="text" name="browser" value="<?php echo $this->agent->browser(); ?>" class="niceInput">
				</label>
			</div>
			<div>
				<label>
					Room:<br>
					<input type="text" name="room" value="<?php echo $this->agent->is_referral() ? $this->agent->referrer() : ""; ?>" class="niceInput">
				</label>
			</div>
			<div>
				<label>
					Description: <span class="required">(required)</span><br>
					<textarea name="description" class="niceInput" style="height:100px;"></textarea>
				</label>
			</div>
			<input type="submit" class="niceButton" value="Submit">
		</form>
	</div>
	<div class="clear"></div>
</div>

<?php $this->load->view(PLATFORM."/templateFooter"); ?>
