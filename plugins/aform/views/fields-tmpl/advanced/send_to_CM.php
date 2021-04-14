<?php 
/* note: $self is referencing $this */ 
?>
<div class="af-toggle-grouped">
	<p>
		<label for="<?php echo $inputString; ?>[send_to_CM]">Send to Campaign Monitor</label> 
		<span class="af-input-switch">
			<input class="enabler" data-target="cm-field" id="<?php echo $inputString; ?>[send_to_CM]" name="<?php echo $inputString; ?>[send_to_CM]" type="checkbox" value="true" <?php echo ( $self->inputSettings( 'send_to_CM' ) == true ) ? 'checked' : '' ;?>>
			<label for="<?php echo $inputString; ?>[send_to_CM]"></label>
		</span>
	</p>
	<p class="cm-field" <?php echo ( $self->inputSettings( 'send_to_CM' ) == false ) ? 'style="display: none;"' : ''; ?>>
		<label for="<?php echo $inputString; ?>[CM_field]">Campaign Monitor Field Name</label>
		<span class="af-input-switch">
			<input id="<?php echo $inputString; ?>[CM_field]" class="form-control-inline" name="<?php echo $inputString; ?>[CM_field]" type="text" value="<?php echo $self->inputSettings( 'CM_field' ); ?>">
			<label for="<?php echo $inputString; ?>[CM_field]"></label>
		</span>
	</p>
</div>