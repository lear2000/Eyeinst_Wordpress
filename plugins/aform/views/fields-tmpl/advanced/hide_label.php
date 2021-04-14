<?php 
/* note: $self is referencing $this */ 
?>
<p>
	<label for="<?php echo $inputString; ?>[hide_label]">Hide label</label>
	<span class="af-input-switch">
		<input id="<?php echo $inputString; ?>[hide_label]" name="<?php echo $inputString; ?>[hide_label]" type="checkbox" value="true" <?php echo ( $self->inputSettings( 'hide_label' ) == true ) ? 'checked' : '';?>>
		<label for="<?php echo $inputString; ?>[hide_label]"></label>
	</span>
</p>