<script type="text/javascript" src="/assets/jQuery.js"></script>
<script type="text/javascript" src="/assets/Functions.js?<?=time()?>"></script>
<script type="text/javascript" src="/assets/Client_API.js?<?=time()?>"></script>
<script type="text/javascript" src="/assets/Client_Mobile.js?<?=time()?>"></script>
<script type="text/javascript">
	$(document).ready(function(e) {
		RoomID = "<?php echo $RoomID; ?>";
		StartClient("<?php echo ((isset($StartMode) && !empty($StartMode) ? $StartMode : "")); ?>");
	});
</script>